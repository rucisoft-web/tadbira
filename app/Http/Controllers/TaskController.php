<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\ActivityLogger;
use App\Services\Scheduling\PredecessorParser;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\ScheduleCycleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        private ProjectScheduler $scheduler,
        private PredecessorParser $predecessors,
    ) {
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request, $project);
        $data = $this->validated($request, $project, creating: true);

        DB::transaction(function () use ($project, $data, $request) {
            $task = $project->tasks()->create([
                'parent_id' => $data['parent_id'] ?? null,
                'kind' => $data['kind'] ?? 'activity',
                'title' => $data['title'],
                'duration_days' => ($data['kind'] ?? null) === 'milestone' ? 0 : ($data['duration_days'] ?? 1),
                'position' => (int) $project->tasks()->max('position') + 1,
            ]);
            $this->scheduler->renumberAndSave($project);
            $this->syncPredecessors($project, $task, $data['predecessors'] ?? null);
            $this->syncAssignees($task, $data);
            $this->reschedule($project);
            ActivityLogger::log($project, $task, 'created', "{$request->user()->name} menambah \"{$task->title}\"");
        });

        return back();
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $project = $task->project;
        $this->authorizeProject($request, $project);
        $data = $this->validated($request, $project);

        DB::transaction(function () use ($project, $task, $data, $request) {
            $fill = collect($data)->only([
                'title', 'kind', 'duration_days', 'percent_complete', 'status', 'scheduling_mode', 'manual_start', 'parent_id',
                'constraint_type', 'constraint_date',
            ])->all();
            if (array_key_exists('percent_complete', $fill)) {
                $fill['status'] = match (true) {
                    $fill['percent_complete'] >= 100 => 'completed',
                    $fill['percent_complete'] > 0 => 'in_progress',
                    default => 'not_started',
                };
                if ($fill['percent_complete'] > 0 && ! $task->actual_start) {
                    $fill['actual_start'] = $task->start_date ?? now();
                }
                $fill['actual_finish'] = $fill['percent_complete'] >= 100 ? ($task->actual_finish ?? now()) : null;
            }
            if (($fill['kind'] ?? $task->kind) === 'milestone') {
                $fill['duration_days'] = 0;
            }
            $task->fill($fill);
            $changes = $task->getDirty();
            $task->save();

            if (array_key_exists('parent_id', $changes)) {
                $this->scheduler->renumberAndSave($project);
            }
            if ($request->has('predecessors')) {
                $this->syncPredecessors($project, $task, $data['predecessors']);
            }
            $this->syncAssignees($task, $data);
            $this->reschedule($project);

            if ($changes) {
                ActivityLogger::log($project, $task, 'updated', "{$request->user()->name} mengubah \"{$task->title}\"", $changes);
            }
        });

        return back();
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $project = $task->project;
        $this->authorizeProject($request, $project);

        DB::transaction(function () use ($project, $task, $request) {
            ActivityLogger::log($project, $task, 'deleted', "{$request->user()->name} menghapus \"{$task->title}\"");
            TaskDependency::where('predecessor_id', $task->id)->orWhere('successor_id', $task->id)->delete();
            $task->forceDelete();
            $this->scheduler->renumberAndSave($project);
            $this->reschedule($project);
        });

        return back();
    }

    private function validated(Request $request, Project $project, bool $creating = false): array
    {
        $data = $request->validate([
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'kind' => ['sometimes', Rule::in(array_keys(Task::KINDS))],
            'parent' => ['sometimes', 'nullable', 'string'],
            'duration_days' => ['sometimes', 'integer', 'min:0', 'max:3650'],
            'percent_complete' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'scheduling_mode' => ['sometimes', Rule::in(['auto', 'manual'])],
            'manual_start' => ['sometimes', 'nullable', 'date'],
            'constraint_type' => ['sometimes', Rule::in(['asap', 'snet', 'mso'])],
            'constraint_date' => ['sometimes', 'nullable', 'date'],
            'predecessors' => ['sometimes', 'nullable', 'string', 'max:500'],
            'assignee_ids' => ['sometimes', 'array'],
            'assignee_ids.*' => ['integer'],
        ]);

        if (array_key_exists('parent', $data)) {
            $data['parent_id'] = $data['parent']
                ? $project->tasks()->where('ulid', $data['parent'])->value('id') ?? throw ValidationException::withMessages(['parent' => 'Induk tidak ditemukan.'])
                : null;
        }

        return $data;
    }

    private function syncPredecessors(Project $project, Task $task, ?string $text): void
    {
        $rows = $this->predecessors->parse($project, $task, $text);
        TaskDependency::where('successor_id', $task->id)->delete();
        foreach ($rows as $r) {
            TaskDependency::create($r + ['project_id' => $project->id, 'successor_id' => $task->id, 'type' => 'FS']);
        }
    }

    private function syncAssignees(Task $task, array $data): void
    {
        if (! array_key_exists('assignee_ids', $data)) {
            return;
        }
        $allowed = $task->project->organization->users()->whereIn('users.id', $data['assignee_ids'])->pluck('users.id');
        $sync = [];
        foreach ($allowed->values() as $i => $id) {
            $sync[$id] = ['role' => $i === 0 ? 'responsible' : 'member'];
        }
        $task->assignees()->sync($sync);
    }

    private function reschedule(Project $project): void
    {
        try {
            $this->scheduler->reschedule($project->refresh());
        } catch (ScheduleCycleException $e) {
            throw ValidationException::withMessages(['predecessors' => $e->getMessage()]);
        }
    }
}
