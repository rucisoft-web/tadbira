<?php

namespace App\Services;

use App\Models\BudgetLine;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Support\Facades\DB;

/**
 * Templat proyek: SRK + lama pengerjaan + urutan tanpa tanggal mutlak.
 * Cukup isi tanggal mulai (atau tanggal selesai) untuk melihat jadwal lengkapnya.
 */
class TemplateService
{
    public function __construct(private ProjectScheduler $scheduler)
    {
    }

    /**
     * Pratinjau tanpa menyimpan apa pun.
     */
    public function preview(Project $template, string $direction, string $anchor, ?FiscalYear $fy = null): array
    {
        $result = $this->scheduler->preview($template, $direction, $anchor);
        $calendar = $this->scheduler->calendarFor($template->organization_id);
        $tasks = $template->tasks()->get(['id', 'parent_id', 'kind', 'title']);
        $parentIds = $tasks->pluck('parent_id')->filter()->flip();

        $finish = $result->projectFinish;

        return [
            'start_date' => $result->projectStart->toDateString(),
            'finish_date' => $finish->toDateString(),
            'working_days' => $calendar->workingDaysBetween($result->projectStart, $finish) + 1,
            'calendar_days' => (int) $result->projectStart->diffInDays($finish) + 1,
            'holidays' => $calendar->holidaysBetween($result->projectStart, $finish),
            'within_fiscal_year' => $fy ? ($result->projectStart->gte($fy->start_date) && $finish->lte($fy->end_date)) : null,
            'phases' => $tasks->filter(fn ($t) => $t->parent_id === null)->map(fn ($t) => [
                'title' => $t->title,
                'start' => $result->tasks[$t->id]['start']->toDateString(),
                'finish' => $result->tasks[$t->id]['finish']->toDateString(),
                'critical' => $result->tasks[$t->id]['critical'],
                'is_summary' => $parentIds->has($t->id),
            ])->values(),
            'milestones' => $tasks->where('kind', 'milestone')->map(fn ($t) => [
                'title' => $t->title,
                'date' => $result->tasks[$t->id]['start']->toDateString(),
            ])->values(),
        ];
    }

    /**
     * Buat proyek baru dari templat lalu jadwalkan.
     */
    public function instantiate(Project $template, array $attributes): Project
    {
        return DB::transaction(function () use ($template, $attributes) {
            $project = Project::create($attributes + [
                'template_id' => $template->id,
                'category' => $template->category,
                'description' => $template->description,
                'budget_amount' => $template->budget_amount,
            ]);

            $this->copyContent($template, $project);
            $this->scheduler->reschedule($project);

            return $project->refresh();
        });
    }

    /**
     * Simpan proyek sebagai templat. Bila $useActualDurations, lama pengerjaan
     * aktual aktivitas yang sudah selesai menjadi lama pengerjaan templat.
     */
    public function saveAsTemplate(Project $project, string $name, bool $useActualDurations = false): Project
    {
        return DB::transaction(function () use ($project, $name, $useActualDurations) {
            $template = Project::create([
                'organization_id' => $project->organization_id,
                'name' => $name,
                'category' => $project->category,
                'description' => $project->description,
                'is_template' => true,
                'scheduling_direction' => 'forward',
                'budget_amount' => $project->budget_amount,
                'created_by' => auth()->id(),
            ]);

            $this->copyContent($project, $template, $useActualDurations);

            return $template;
        });
    }

    private function copyContent(Project $from, Project $to, bool $useActualDurations = false): void
    {
        $calendar = $useActualDurations ? $this->scheduler->calendarFor($from->organization_id) : null;
        $map = [];
        foreach ($from->tasks()->orderBy('position')->get() as $t) {
            $duration = $t->duration_days;
            if ($calendar && $t->actual_start && $t->actual_finish) {
                $duration = $calendar->workingDaysBetween($t->actual_start, $t->actual_finish) + 1;
            }
            $copy = Task::create([
                'project_id' => $to->id,
                'parent_id' => null,
                'kind' => $t->kind,
                'position' => $t->position,
                'title' => $t->title,
                'expected_output' => $t->expected_output,
                'priority' => $t->priority,
                'duration_days' => $duration,
            ]);
            $map[$t->id] = [$copy, $t->parent_id];
        }
        foreach ($map as [$copy, $oldParent]) {
            if ($oldParent && isset($map[$oldParent])) {
                $copy->update(['parent_id' => $map[$oldParent][0]->id]);
            }
        }

        foreach ($from->dependencies()->get() as $d) {
            if (isset($map[$d->predecessor_id], $map[$d->successor_id])) {
                TaskDependency::create([
                    'project_id' => $to->id,
                    'predecessor_id' => $map[$d->predecessor_id][0]->id,
                    'successor_id' => $map[$d->successor_id][0]->id,
                    'type' => $d->type,
                    'lag_days' => $d->lag_days,
                ]);
            }
        }

        $lineMap = [];
        foreach (BudgetLine::where('project_id', $from->id)->orderBy('position')->get() as $l) {
            $copy = $l->replicate(['project_id', 'fiscal_year_id', 'parent_id', 'task_id']);
            $copy->project_id = $to->id;
            $copy->fiscal_year_id = $to->fiscal_year_id;
            $copy->parent_id = $l->parent_id ? ($lineMap[$l->parent_id] ?? null) : null;
            $copy->task_id = $l->task_id && isset($map[$l->task_id]) ? $map[$l->task_id][0]->id : null;
            $copy->save();
            $lineMap[$l->id] = $copy->id;
        }
    }
}
