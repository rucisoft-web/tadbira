<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BudgetLine;
use App\Models\Project;
use App\Models\Task;
use App\Services\ActivityLogger;
use App\Services\PortfolioService;
use App\Services\Scheduling\PredecessorParser;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\TemplateService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, PortfolioService $portfolio): Response
    {
        $org = $this->organization($request);
        $fy = $this->fiscalYear($request, $org);

        return Inertia::render('Projects/Index', [
            'fiscalYears' => $org->fiscalYears()->get(['ulid', 'code', 'year', 'status']),
            'fiscalYear' => $fy ? Arr::only($fy->toArray(), ['ulid', 'code', 'year', 'start_date', 'end_date']) : null,
            'projects' => $fy ? $portfolio->summary($fy)['projects'] : [],
            'goals' => $fy ? $fy->goals()->get(['id', 'code', 'title']) : [],
            'filters' => ['unit' => $request->string('unit')->toString(), 'goal' => $request->string('sasaran')->toString()],
            'templates' => Project::templates()->where('organization_id', $org->id)
                ->withCount(['tasks' => fn ($q) => $q->where('kind', '!=', 'phase')])
                ->get(['id', 'ulid', 'name', 'category', 'description']),
        ]);
    }

    public function create(Request $request): Response
    {
        $org = $this->organization($request);

        return Inertia::render('Projects/Create', [
            'fiscalYears' => $org->fiscalYears()->with('goals:id,fiscal_year_id,code,title')->get(['id', 'ulid', 'code', 'year', 'start_date', 'end_date']),
            'selectedFiscalYear' => $this->fiscalYear($request, $org)?->ulid,
            'templates' => Project::templates()->where('organization_id', $org->id)
                ->withCount('tasks')->get(['id', 'ulid', 'name', 'category', 'description']),
            'selectedTemplate' => $request->string('templat')->toString() ?: null,
            'users' => $org->users()->orderBy('name')->get(['users.id', 'users.name']),
        ]);
    }

    public function store(Request $request, TemplateService $templates, ProjectScheduler $scheduler): RedirectResponse
    {
        $org = $this->organization($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'fiscal_year' => ['required', Rule::exists('fiscal_years', 'ulid')->where('organization_id', $org->id)],
            'template' => ['nullable', Rule::exists('projects', 'ulid')->where('organization_id', $org->id)->where('is_template', true)],
            'scheduling_direction' => ['required', Rule::in(['forward', 'backward'])],
            'anchor_date' => ['required', 'date'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'leader_id' => ['nullable', 'integer'],
            'goal_id' => ['nullable', 'integer'],
        ]);

        $fy = $org->fiscalYears()->where('ulid', $data['fiscal_year'])->firstOrFail();
        $goalId = ! empty($data['goal_id']) ? $fy->goals()->whereKey($data['goal_id'])->value('id') : null;
        $attributes = [
            'organization_id' => $org->id,
            'fiscal_year_id' => $fy->id,
            'goal_id' => $goalId,
            'leader_id' => $data['leader_id'] ?? $request->user()->id,
            'created_by' => $request->user()->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'scheduling_direction' => $data['scheduling_direction'],
            'start_date' => $data['scheduling_direction'] === 'forward' ? $data['anchor_date'] : null,
            'target_finish_date' => $data['scheduling_direction'] === 'backward' ? $data['anchor_date'] : null,
            'budget_amount' => $data['budget_amount'] ?? 0,
        ];

        $project = DB::transaction(function () use ($data, $attributes, $org, $templates, $scheduler, $request) {
            if (! empty($data['template'])) {
                $template = Project::templates()->where('organization_id', $org->id)->where('ulid', $data['template'])->firstOrFail();
                $project = $templates->instantiate($template, $attributes);
            } else {
                $project = Project::create($attributes);
                $scheduler->reschedule($project);
            }
            $project->members()->syncWithoutDetaching([$project->leader_id => ['role' => 'leader']]);
            ActivityLogger::log($project, $project, 'created', "{$request->user()->name} membuat proyek");

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('success', 'Proyek dibuat.');
    }

    public function show(Request $request, Project $project, PortfolioService $portfolio): Response
    {
        $this->authorizeProject($request, $project);
        $project->load(['fiscalYear', 'goal.indicators', 'leader:id,name', 'tasks.assignees:id,name', 'dependencies']);

        $tasks = $this->orderedTasks($project->tasks);
        $wbsById = $tasks->pluck('wbs_code', 'id')->all();
        $depsBySuccessor = $project->dependencies->groupBy('successor_id');
        $parentIds = $tasks->pluck('parent_id')->filter()->flip();

        $realizedByLine = DB::table('expenditures')
            ->join('budget_lines', 'budget_lines.id', '=', 'expenditures.budget_line_id')
            ->where('budget_lines.project_id', $project->id)
            ->where('expenditures.status', 'paid')
            ->groupBy('expenditures.budget_line_id')
            ->selectRaw('expenditures.budget_line_id, SUM(expenditures.amount) as total')
            ->pluck('total', 'budget_line_id');

        $realized = (float) $realizedByLine->sum();
        $summary = $portfolio->projectRow($project, CarbonImmutable::today(), $realized);

        return Inertia::render('Projects/Show', [
            'project' => [
                ...$project->only(['ulid', 'code', 'name', 'description', 'category', 'status', 'scheduling_direction', 'is_template', 'budget_amount', 'percent_complete', 'schedule_version']),
                'start_date' => $project->start_date?->toDateString(),
                'target_finish_date' => $project->target_finish_date?->toDateString(),
                'computed_start_date' => $project->computed_start_date?->toDateString(),
                'computed_finish_date' => $project->computed_finish_date?->toDateString(),
                'status_label' => $project->statusLabel(),
                'leader' => $project->leader?->name,
                'fiscal_year' => $project->fiscalYear ? Arr::only($project->fiscalYear->toArray(), ['ulid', 'code', 'start_date', 'end_date']) : null,
                'goal_id' => $project->goal_id,
                'goal' => $project->goal ? [
                    'code' => $project->goal->code,
                    'title' => $project->goal->title,
                    'indicators' => $project->goal->indicators->map->only(['name', 'target', 'realization', 'score']),
                ] : null,
            ],
            'goals' => $project->fiscalYear ? $project->fiscalYear->goals()->get(['id', 'code', 'title']) : [],
            'summary' => $summary,
            'tasks' => $tasks->map(fn (Task $t) => [
                'id' => $t->id,
                'ulid' => $t->ulid,
                'parent_id' => $t->parent_id,
                'wbs' => $t->wbs_code,
                'depth' => substr_count((string) $t->wbs_code, '.'),
                'kind' => $t->kind,
                'is_summary' => $parentIds->has($t->id),
                'title' => $t->title,
                'status' => $t->status,
                'duration_days' => $t->duration_days,
                'start' => $t->start_date?->toDateString(),
                'finish' => $t->finish_date?->toDateString(),
                'slack' => $t->total_slack_days,
                'critical' => $t->is_critical,
                'percent_complete' => $t->percent_complete,
                'scheduling_mode' => $t->scheduling_mode,
                'manual_start' => $t->manual_start?->toDateString(),
                'predecessors' => PredecessorParser::format($depsBySuccessor->get($t->id, []), $wbsById),
                'predecessor_ids' => $depsBySuccessor->get($t->id, collect())->pluck('predecessor_id'),
                'assignees' => $t->assignees->pluck('name'),
            ])->values(),
            'budget' => $this->budgetTree($project, $realizedByLine),
            'budgetLevels' => config('tadbira.budget_levels'),
            'activity' => ActivityLog::where('project_id', $project->id)->latest('created_at')->limit(15)->with('causer:id,name')
                ->get()->map(fn ($a) => ['description' => $a->description, 'by' => $a->causer?->name, 'at' => $a->created_at?->toIso8601String()]),
            'users' => $project->organization->users()->orderBy('name')->get(['users.id', 'users.name']),
        ]);
    }

    public function update(Request $request, Project $project, ProjectScheduler $scheduler): RedirectResponse
    {
        $this->authorizeProject($request, $project);
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'code' => ['sometimes', 'nullable', 'string', 'max:60'],
            'status' => ['sometimes', Rule::in(array_keys(Project::STATUSES))],
            'scheduling_direction' => ['sometimes', Rule::in(['forward', 'backward'])],
            'anchor_date' => ['sometimes', 'date'],
            'budget_amount' => ['sometimes', 'numeric', 'min:0'],
            'goal_id' => ['sometimes', 'nullable', Rule::exists('goals', 'id')->where('fiscal_year_id', $project->fiscal_year_id)],
        ]);

        if (isset($data['anchor_date'])) {
            $direction = $data['scheduling_direction'] ?? $project->scheduling_direction;
            $project->scheduling_direction = $direction;
            $project->start_date = $direction === 'forward' ? $data['anchor_date'] : null;
            $project->target_finish_date = $direction === 'backward' ? $data['anchor_date'] : null;
            unset($data['anchor_date'], $data['scheduling_direction']);
        }
        $project->fill($data);
        $dirty = $project->getDirty();
        $project->save();

        if (array_intersect(array_keys($dirty), ['start_date', 'target_finish_date', 'scheduling_direction'])) {
            $scheduler->reschedule($project);
        }
        if ($dirty) {
            ActivityLogger::log($project, $project, 'updated', "{$request->user()->name} mengubah ".implode(', ', array_keys($dirty)), $dirty);
        }

        return back()->with('success', 'Proyek diperbarui.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request, $project);
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek dihapus.');
    }

    /** Urutkan aktivitas mengikuti SRK (penelusuran mendalam berdasarkan posisi). */
    private function orderedTasks(Collection $tasks): Collection
    {
        $byParent = $tasks->sortBy('position')->groupBy(fn ($t) => $t->parent_id ?? 0);
        $out = collect();
        $walk = function ($parentId) use (&$walk, $byParent, $out) {
            foreach ($byParent->get($parentId, []) as $t) {
                $out->push($t);
                $walk($t->id);
            }
        };
        $walk(0);

        return $out;
    }

    private function budgetTree(Project $project, Collection $realizedByLine): array
    {
        $lines = BudgetLine::where('project_id', $project->id)->orderBy('position')->get();
        $byParent = $lines->groupBy(fn ($l) => $l->parent_id ?? 0);

        $build = function ($parentId, int $depth) use (&$build, $byParent, $realizedByLine): array {
            $out = [];
            foreach ($byParent->get($parentId, []) as $l) {
                $children = $build($l->id, $depth + 1);
                $realized = (float) ($realizedByLine[$l->id] ?? 0) + array_sum(array_column($children, 'realized_total'));
                $out[] = [
                    'id' => $l->id,
                    'level' => $l->level,
                    'code' => $l->code,
                    'description' => $l->description,
                    'volume' => $l->volume,
                    'unit' => $l->unit,
                    'unit_price' => $l->unit_price,
                    'amount' => $l->amount,
                    'funding_source' => $l->funding_source,
                    'depth' => $depth,
                    'realized_total' => $realized,
                    'children' => $children,
                ];
            }

            return $out;
        };

        return $build(0, 0);
    }
}
