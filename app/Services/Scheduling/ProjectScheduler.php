<?php

namespace App\Services\Scheduling;

use App\Models\Holiday;
use App\Models\Project;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Menjembatani model Eloquent dengan Scheduler: memuat graf proyek,
 * menjalankan CPM, lalu menyimpan tanggal, kelonggaran, dan jalur kritis.
 */
class ProjectScheduler
{
    public function calendarFor(int $organizationId): WorkingCalendar
    {
        $rows = Holiday::query()
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $organizationId))
            ->get(['date', 'is_working_day']);

        return new WorkingCalendar(
            $rows->where('is_working_day', false)->map(fn ($h) => $h->date->toDateString())->values()->all(),
            $rows->where('is_working_day', true)->map(fn ($h) => $h->date->toDateString())->values()->all(),
        );
    }

    /**
     * Jalankan mesin tanpa menyimpan (pratinjau templat / simulasi).
     */
    public function preview(Project $project, string $direction, CarbonImmutable|string $anchor): ScheduleResult
    {
        [$nodes, $edges] = $this->graph($project);

        return (new Scheduler($this->calendarFor($project->organization_id)))->run($nodes, $edges, $direction, $anchor);
    }

    /**
     * Jadwalkan ulang proyek dan simpan hasilnya.
     */
    public function reschedule(Project $project): ?ScheduleResult
    {
        $anchor = $project->scheduling_direction === 'backward' ? $project->target_finish_date : $project->start_date;
        if (! $anchor) {
            return null;
        }

        return Cache::lock("project:{$project->id}:schedule", 10)->block(5, function () use ($project, $anchor) {
            return DB::transaction(function () use ($project, $anchor) {
                $tasks = $project->tasks()->get();
                $this->renumberWbs($tasks);

                $calendar = $this->calendarFor($project->organization_id);
                [$nodes, $edges] = $this->graph($project);
                $result = (new Scheduler($calendar))->run($nodes, $edges, $project->scheduling_direction ?? 'forward', $anchor);
                $parentIds = $tasks->pluck('parent_id')->filter()->flip();

                foreach ($tasks as $task) {
                    $row = $result->tasks[$task->id] ?? null;
                    $task->forceFill([
                        'start_date' => $row['start'] ?? null,
                        'finish_date' => $row['finish'] ?? null,
                        'late_start' => $row['late_start'] ?? null,
                        'late_finish' => $row['late_finish'] ?? null,
                        'total_slack_days' => $row['slack'] ?? null,
                        'is_critical' => $row['critical'] ?? false,
                    ]);
                    if ($row && $parentIds->has($task->id)) {
                        $task->duration_days = $calendar->workingDaysBetween($row['start'], $row['finish']) + 1;
                    }
                }

                $this->rollUpProgress($tasks);
                $tasks->each(fn (Task $t) => $t->isDirty() ? $t->saveQuietly() : null);

                $leaves = $tasks->filter(fn (Task $t) => ! $tasks->contains('parent_id', $t->id));
                $totalDuration = max(1, $leaves->sum(fn ($t) => max(1, $t->duration_days)));

                $project->forceFill([
                    'computed_start_date' => $result->projectStart,
                    'computed_finish_date' => $result->projectFinish,
                    'percent_complete' => round($leaves->sum(fn ($t) => max(1, $t->duration_days) * $t->percent_complete) / $totalDuration, 2),
                    'schedule_version' => $project->schedule_version + 1,
                ])->saveQuietly();

                return $result;
            });
        });
    }

    /**
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    public function graph(Project $project): array
    {
        $nodes = $project->tasks()->get()->map(fn (Task $t) => [
            'id' => $t->id,
            'parent_id' => $t->parent_id,
            'kind' => $t->kind,
            'duration' => $t->duration_days,
            'mode' => $t->scheduling_mode,
            'manual_start' => $t->manual_start,
            'constraint_type' => $t->constraint_type,
            'constraint_date' => $t->constraint_date,
        ])->all();

        $edges = $project->dependencies()->get()->map(fn ($d) => [
            'predecessor' => $d->predecessor_id,
            'successor' => $d->successor_id,
            'lag' => $d->lag_days,
        ])->all();

        return [$nodes, $edges];
    }

    /** Hitung ulang nomor SRK dan simpan (dipakai setelah menambah/memindah aktivitas). */
    public function renumberAndSave(Project $project): void
    {
        $tasks = $project->tasks()->get();
        $this->renumberWbs($tasks);
        $tasks->each(fn (Task $t) => $t->isDirty('wbs_code') ? $t->saveQuietly() : null);
    }

    /** Nomor SRK bertingkat ("1", "1.2", "1.2.3") menurut urutan posisi. */
    public function renumberWbs(Collection $tasks): void
    {
        $byParent = $tasks->sortBy('position')->groupBy(fn ($t) => $t->parent_id ?? 0);

        $walk = function ($parentId, string $prefix) use (&$walk, $byParent) {
            foreach ($byParent->get($parentId, collect())->values() as $i => $task) {
                $code = $prefix === '' ? (string) ($i + 1) : $prefix.'.'.($i + 1);
                $task->wbs_code = $code;
                $walk($task->id, $code);
            }
        };

        $walk(0, '');
    }

    /** Kemajuan tahap/paket kerja = rata-rata kemajuan anak, ditimbang lama pengerjaan. */
    private function rollUpProgress(Collection $tasks): void
    {
        $children = $tasks->groupBy('parent_id');

        $resolve = function (Task $task) use (&$resolve, $children): array {
            $kids = $children->get($task->id);
            if (! $kids || $kids->isEmpty()) {
                return [max(1, $task->duration_days), $task->percent_complete];
            }
            $weight = 0;
            $sum = 0;
            foreach ($kids as $kid) {
                [$w, $p] = $resolve($kid);
                $weight += $w;
                $sum += $w * $p;
            }
            $task->percent_complete = (int) round($sum / max(1, $weight));
            $task->status = match (true) {
                $task->percent_complete >= 100 => 'completed',
                $task->percent_complete > 0 => 'in_progress',
                default => 'not_started',
            };

            return [$weight, $task->percent_complete];
        };

        $tasks->whereNull('parent_id')->each(fn ($t) => $resolve($t));
    }
}
