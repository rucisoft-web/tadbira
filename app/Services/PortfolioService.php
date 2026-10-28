<?php

namespace App\Services;

use App\Models\Expenditure;
use App\Models\FiscalYear;
use App\Models\Goal;
use App\Models\GoalIndicator;
use App\Models\Project;
use App\Models\Task;
use App\Services\Performance\EarnedValue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Ringkasan portofolio proyek dalam satu tahun anggaran untuk dasbor.
 */
class PortfolioService
{
    /**
     * @param  string|null  $unit  batasi pada satu unit kerja (kolom kategori proyek)
     */
    public function summary(FiscalYear $fy, ?CarbonImmutable $today = null, ?string $unit = null): array
    {
        $today ??= CarbonImmutable::today();

        $projects = Project::real()
            ->where('fiscal_year_id', $fy->id)
            ->when($unit, fn ($q) => $q->where('category', $unit))
            ->with(['leader:id,name', 'tasks' => fn ($q) => $q->select([
                'id', 'project_id', 'parent_id', 'kind', 'title', 'duration_days', 'start_date', 'finish_date',
                'percent_complete', 'is_critical', 'status', 'wbs_code', 'baseline_start', 'baseline_finish', 'baseline_duration',
            ])])
            ->orderBy('computed_start_date')
            ->get();

        $realized = Expenditure::query()
            ->join('budget_lines', 'budget_lines.id', '=', 'expenditures.budget_line_id')
            ->whereIn('budget_lines.project_id', $projects->pluck('id'))
            ->where('expenditures.status', 'paid')
            ->selectRaw('budget_lines.project_id, SUM(expenditures.amount) as total')
            ->groupBy('budget_lines.project_id')
            ->pluck('total', 'project_id');

        $rows = $projects->map(fn (Project $p) => $this->projectRow($p, $today, (float) ($realized[$p->id] ?? 0)));
        $evm = EarnedValue::compute(
            (float) $rows->sum(fn ($r) => $r['evm']['bac']),
            0, 0, (float) $rows->sum(fn ($r) => $r['evm']['ac']),
        );
        $pv = (float) $rows->sum(fn ($r) => $r['evm']['pv']);
        $ev = (float) $rows->sum(fn ($r) => $r['evm']['ev']);
        $portfolioEvm = $evm['bac'] > 0
            ? EarnedValue::compute($evm['bac'], $pv / $evm['bac'] * 100, $ev / $evm['bac'] * 100, $evm['ac'])
            : $evm;

        return [
            'projects' => $rows->values(),
            'totals' => [
                'count' => $rows->count(),
                'ceiling' => (float) $fy->ceiling_amount,
                'budget' => round($rows->sum('budget'), 2),
                'realized' => round($rows->sum('realized'), 2),
                'avg_progress' => round($rows->avg('progress') ?? 0, 1),
                'avg_planned' => round($rows->avg('planned') ?? 0, 1),
                'late_tasks' => $rows->sum('late_tasks'),
                'beyond_fiscal_year' => $rows->where('beyond_fiscal_year', true)->count(),
                'evm' => $portfolioEvm,
                'baselined' => $rows->where('has_baseline', true)->count(),
            ],
            'by_status' => collect(Project::STATUSES)
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'count' => $rows->where('status', $key)->count()])
                ->values(),
            'by_health' => collect(['baik' => 'Sesuai jadwal', 'waspada' => 'Perlu perhatian', 'kritis' => 'Terlambat'])
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'count' => $rows->where('health', $key)->count()])
                ->values(),
            'monthly' => $this->monthly($fy, $projects, $rows, $today),
            'by_unit' => $this->byUnit($rows),
            'goals' => $this->goals($fy, $rows),
        ];
    }

    /**
     * Anggaran, realisasi, dan kesehatan per unit kerja, dari yang anggarannya terbesar.
     */
    private function byUnit(Collection $rows): array
    {
        return $rows->groupBy(fn ($r) => $r['category'] ?: 'Tanpa unit')
            ->map(fn (Collection $g, $unit) => [
                'unit' => $unit,
                'count' => $g->count(),
                'budget' => round($g->sum('budget'), 2),
                'realized' => round($g->sum('realized'), 2),
                'absorption' => $g->sum('budget') > 0 ? round($g->sum('realized') / $g->sum('budget') * 100, 1) : 0,
                'progress' => round($g->avg('progress'), 1),
                'planned' => round($g->avg('planned'), 1),
                'kritis' => $g->where('health', 'kritis')->count(),
                'waspada' => $g->where('health', 'waspada')->count(),
            ])
            ->sortByDesc('budget')
            ->values()
            ->all();
    }

    /**
     * Sasaran program tahun anggaran ini: indikator kinerja dan kegiatan pendukungnya.
     */
    private function goals(FiscalYear $fy, Collection $rows): array
    {
        $byGoal = $rows->groupBy('goal_id');

        return $fy->goals()->with('indicators')->get()->map(function (Goal $g) use ($byGoal) {
            $projects = $byGoal->get($g->id, collect());

            return [
                'ulid' => $g->ulid,
                'id' => $g->id,
                'code' => $g->code,
                'title' => $g->title,
                'color' => $g->color,
                'project_count' => $projects->count(),
                'budget' => round($projects->sum('budget'), 2),
                'realized' => round($projects->sum('realized'), 2),
                'progress' => round($projects->avg('progress') ?? 0, 1),
                'planned' => round($projects->avg('planned') ?? 0, 1),
                'kritis' => $projects->where('health', 'kritis')->count(),
                'indicators' => $g->indicators->map(fn (GoalIndicator $i) => [
                    'name' => $i->name,
                    'unit' => $i->unit,
                    'target' => $i->target,
                    'realization' => $i->realization,
                    'score' => $i->score,
                    'category' => GoalIndicator::category($i->score),
                    'baseline_realization' => $i->baseline_realization,
                    'baseline_score' => $i->baseline_score,
                ])->all(),
            ];
        })->all();
    }

    public function projectRow(Project $p, CarbonImmutable $today, float $realized): array
    {
        $tasks = $p->tasks;
        $leaves = $tasks->filter(fn (Task $t) => ! $tasks->contains('parent_id', $t->id));
        $hasBaseline = $p->baseline_number > 0;
        // Rencana dibandingkan dengan baseline bila sudah ditetapkan, selain itu dengan jadwal berjalan.
        $planned = $this->plannedPercent($leaves, $today, $hasBaseline);
        $progress = (float) $p->percent_complete;

        $lateTasks = $leaves->filter(fn (Task $t) => $t->finish_date && $t->finish_date->lt($today) && $t->percent_complete < 100)->count();
        $spi = $planned > 0 ? $progress / $planned : 1;

        $health = match (true) {
            in_array($p->status, ['closed', 'cancelled'], true) => 'baik',
            $planned <= 0 || $spi >= 0.95 => 'baik',
            $spi >= 0.8 => 'waspada',
            default => 'kritis',
        };

        $finish = $p->computed_finish_date;
        $fyEnd = $p->fiscalYear?->end_date;
        $bac = $hasBaseline && $p->baseline_budget !== null ? (float) $p->baseline_budget : (float) $p->budget_amount;

        return [
            'ulid' => $p->ulid,
            'code' => $p->code,
            'name' => $p->name,
            'category' => $p->category,
            'goal_id' => $p->goal_id,
            'color' => $p->color,
            'status' => $p->status,
            'status_label' => $p->statusLabel(),
            'leader' => $p->leader?->name,
            'start' => $p->computed_start_date?->toDateString(),
            'finish' => $finish?->toDateString(),
            'progress' => round($progress, 1),
            'planned' => round($planned, 1),
            'spi' => round($spi, 2),
            'health' => $health,
            'budget' => (float) $p->budget_amount,
            'realized' => $realized,
            'absorption' => $p->budget_amount > 0 ? round($realized / $p->budget_amount * 100, 1) : 0,
            'late_tasks' => $lateTasks,
            'critical_tasks' => $leaves->where('is_critical', true)->count(),
            'task_count' => $leaves->count(),
            'beyond_fiscal_year' => $finish && $fyEnd && $finish->gt($fyEnd),
            'has_baseline' => $hasBaseline,
            'baseline_finish' => $p->baseline_finish?->toDateString(),
            'finish_variance' => $hasBaseline && $finish && $p->baseline_finish ? (int) $p->baseline_finish->diffInDays($finish, false) : null,
            'evm' => EarnedValue::compute($bac, $planned, $progress, $realized),
            'phases' => $tasks->whereNull('parent_id')->where('kind', '!=', 'activity')->values()->map(fn (Task $t) => [
                'title' => $t->title,
                'start' => $t->start_date?->toDateString(),
                'finish' => $t->finish_date?->toDateString(),
                'progress' => $t->percent_complete,
                'critical' => $t->is_critical,
            ]),
            'milestones' => $tasks->where('kind', 'milestone')->values()->map(fn (Task $t) => [
                'title' => $t->title,
                'date' => $t->start_date?->toDateString(),
            ]),
        ];
    }

    /**
     * Kemajuan rencana per hari ini: bagian lama pengerjaan yang semestinya sudah berlalu,
     * ditimbang lama pengerjaan (pendekatan hari kalender).
     */
    public function plannedPercent(Collection $leaves, CarbonImmutable $today, bool $baseline = false): float
    {
        $weight = 0;
        $sum = 0;
        foreach ($leaves as $t) {
            $start = $baseline ? ($t->baseline_start ?? $t->start_date) : $t->start_date;
            $finish = $baseline ? ($t->baseline_finish ?? $t->finish_date) : $t->finish_date;
            if (! $start || ! $finish) {
                continue;
            }
            $w = max(1, $baseline ? ($t->baseline_duration ?? $t->duration_days) : $t->duration_days);
            $weight += $w;
            if ($today->gte($finish)) {
                $sum += $w * 100;
            } elseif ($today->gt($start)) {
                $span = max(1, $start->diffInDays($finish) + 1);
                $sum += $w * 100 * min(1, $start->diffInDays($today) / $span);
            }
        }

        return $weight > 0 ? $sum / $weight : 0;
    }

    /**
     * Rencana vs realisasi penyerapan anggaran per bulan (kumulatif) dan rencana kemajuan.
     * Rencana anggaran tiap proyek disebar merata sepanjang jadwalnya.
     */
    private function monthly(FiscalYear $fy, Collection $projects, Collection $rows, CarbonImmutable $today): array
    {
        $months = [];
        $start = CarbonImmutable::parse($fy->start_date)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $months[] = $start->addMonths($i);
        }

        $plan = array_fill(0, 12, 0.0);
        foreach ($projects as $p) {
            if (! $p->computed_start_date || ! $p->computed_finish_date || $p->budget_amount <= 0) {
                continue;
            }
            $s = CarbonImmutable::parse($p->computed_start_date);
            $f = CarbonImmutable::parse($p->computed_finish_date);
            $days = max(1, $s->diffInDays($f) + 1);
            foreach ($months as $i => $m) {
                $from = max($s, $m);
                $to = min($f, $m->endOfMonth()->startOfDay());
                if ($to->gte($from)) {
                    $plan[$i] += $p->budget_amount * (($from->diffInDays($to) + 1) / $days);
                }
            }
        }

        $paid = Expenditure::query()
            ->join('budget_lines', 'budget_lines.id', '=', 'expenditures.budget_line_id')
            ->whereIn('budget_lines.project_id', $projects->pluck('id'))
            ->where('expenditures.status', 'paid')
            ->get(['expenditures.amount', 'expenditures.transaction_date']);

        $actual = array_fill(0, 12, 0.0);
        foreach ($paid as $e) {
            $i = $start->diffInMonths(CarbonImmutable::parse($e->transaction_date)->startOfMonth());
            if ($i >= 0 && $i < 12) {
                $actual[(int) $i] += $e->amount;
            }
        }

        $out = [];
        $cumPlan = $cumActual = 0;
        foreach ($months as $i => $m) {
            $cumPlan += $plan[$i];
            $cumActual += $actual[$i];
            $out[] = [
                'month' => $m->locale('id')->translatedFormat('M'),
                'plan' => round($cumPlan, 2),
                'actual' => $m->gt($today) ? null : round($cumActual, 2),
            ];
        }

        return $out;
    }
}
