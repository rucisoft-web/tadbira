<?php

namespace App\Services\Reports;

use App\Models\BudgetLine;
use App\Models\ChangeRequest;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\Task;
use App\Services\PortfolioService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/** Data laporan status satu kegiatan, dipakai halaman cetak dan ekspor Excel. */
class ProjectReport
{
    public function __construct(private PortfolioService $portfolio)
    {
    }

    public function build(Project $project, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();
        $project->load(['fiscalYear', 'leader:id,name', 'organization', 'tasks.assignees:id,name', 'goal']);

        $realizedByLine = DB::table('expenditures')
            ->join('budget_lines', 'budget_lines.id', '=', 'expenditures.budget_line_id')
            ->where('budget_lines.project_id', $project->id)
            ->where('expenditures.status', 'paid')
            ->groupBy('expenditures.budget_line_id')
            ->selectRaw('expenditures.budget_line_id, SUM(expenditures.amount) as total')
            ->pluck('total', 'budget_line_id');
        $summary = $this->portfolio->projectRow($project, $today, (float) $realizedByLine->sum());

        $tasks = $project->tasks->sortBy(fn (Task $t) => collect(explode('.', (string) $t->wbs_code))->map(fn ($n) => str_pad($n, 4, '0', STR_PAD_LEFT))->implode('.'))->values();
        $parentIds = $tasks->pluck('parent_id')->filter()->flip();
        $rows = $tasks->map(fn (Task $t) => [
            'wbs' => $t->wbs_code,
            'title' => $t->title,
            'kind' => $t->kind,
            'is_summary' => $parentIds->has($t->id),
            'duration' => $t->duration_days,
            'start' => $t->start_date?->toDateString(),
            'finish' => $t->finish_date?->toDateString(),
            'baseline_finish' => $t->baseline_finish?->toDateString(),
            'variance' => $t->baseline_finish && $t->finish_date ? (int) $t->baseline_finish->diffInDays($t->finish_date, false) : null,
            'progress' => $t->percent_complete,
            'critical' => (bool) $t->is_critical,
            'slack' => $t->total_slack_days,
            'responsible' => $t->assignees->pluck('name')->implode(', '),
            'late' => ! $parentIds->has($t->id) && $t->finish_date && $t->finish_date->lt($today) && $t->percent_complete < 100,
        ]);

        $lines = BudgetLine::where('project_id', $project->id)->orderBy('position')->get();
        $childrenOf = $lines->groupBy(fn ($l) => $l->parent_id ?? 0);
        $realizedOf = function (BudgetLine $l) use (&$realizedOf, $childrenOf, $realizedByLine): float {
            return (float) ($realizedByLine[$l->id] ?? 0) + $childrenOf->get($l->id, collect())->sum(fn ($c) => $realizedOf($c));
        };
        $depthOf = fn (BudgetLine $l) => $this->depth($l, $lines->keyBy('id'));

        return [
            'project' => $project,
            'today' => $today,
            'summary' => $summary,
            'tasks' => $rows,
            'milestones' => $rows->where('kind', 'milestone')->values(),
            'late' => $rows->where('late', true)->values(),
            'upcoming' => $rows->filter(fn ($r) => ! $r['is_summary'] && $r['progress'] < 100 && ! $r['late'] && $r['start'] && $r['start'] <= $today->addDays(30)->toDateString())->sortBy('start')->take(10)->values(),
            'risks' => $project->risks()->with(['owner:id,name', 'task:id,wbs_code'])->get()->map(fn (ProjectRisk $r) => [
                'type' => $r->type === 'issue' ? 'Isu' : 'Risiko',
                'title' => $r->title,
                'category' => $r->category,
                'probability' => $r->type === 'issue' ? null : $r->probability,
                'impact' => $r->impact,
                'score' => $r->score(),
                'level' => ucfirst(ProjectRisk::level($r->score())),
                'strategy' => ProjectRisk::STRATEGIES[$r->strategy] ?? null,
                'response' => $r->response,
                'owner' => $r->owner?->name,
                'due_date' => $r->due_date?->toDateString(),
                'status' => ProjectRisk::STATUSES[$r->status] ?? $r->status,
                'open' => $r->status !== 'closed',
                'task' => $r->task?->wbs_code,
            ])->sortByDesc('score')->values(),
            'changes' => $project->changeRequests()->with(['requester:id,name', 'decider:id,name'])->get()->map(fn (ChangeRequest $c) => [
                'code' => $c->code(),
                'title' => $c->title,
                'reason' => $c->reason,
                'schedule' => $c->schedule_impact_days,
                'cost' => $c->cost_impact,
                'status' => ChangeRequest::STATUSES[$c->status] ?? $c->status,
                'requester' => $c->requester?->name,
                'submitted' => $c->created_at?->toDateString(),
                'decider' => $c->decider?->name,
                'decided' => $c->decided_at?->toDateString(),
                'note' => $c->decision_note,
            ]),
            'budget' => $lines->map(fn (BudgetLine $l) => [
                'code' => $l->code,
                'description' => $l->description,
                'depth' => $depthOf($l),
                'is_parent' => $childrenOf->has($l->id),
                'volume' => $l->volume,
                'unit' => $l->unit,
                'unit_price' => $l->unit_price,
                'amount' => (float) $l->amount,
                'realized' => $realizedOf($l),
                'funding' => $l->funding_source,
            ]),
        ];
    }

    private function depth(BudgetLine $l, $byId): int
    {
        $d = 0;
        while ($l->parent_id && ($l = $byId->get($l->parent_id)) && $d < 10) {
            $d++;
        }

        return $d;
    }
}
