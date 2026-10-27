<?php

namespace App\Services;

use App\Models\Expenditure;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Ringkasan portofolio proyek dalam satu tahun anggaran untuk dasbor.
 */
class PortfolioService
{
    public function summary(FiscalYear $fy, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::today();

        $projects = Project::real()
            ->where('fiscal_year_id', $fy->id)
            ->with(['leader:id,name', 'tasks' => fn ($q) => $q->select([
                'id', 'project_id', 'parent_id', 'kind', 'title', 'duration_days', 'start_date', 'finish_date',
                'percent_complete', 'is_critical', 'status', 'wbs_code',
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
            ],
            'by_status' => collect(Project::STATUSES)
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'count' => $rows->where('status', $key)->count()])
                ->values(),
            'by_health' => collect(['baik' => 'Sesuai jadwal', 'waspada' => 'Perlu perhatian', 'kritis' => 'Terlambat'])
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'count' => $rows->where('health', $key)->count()])
                ->values(),
            'monthly' => $this->monthly($fy, $projects, $rows, $today),
        ];
    }

    public function projectRow(Project $p, CarbonImmutable $today, float $realized): array
    {
        $tasks = $p->tasks;
        $leaves = $tasks->filter(fn (Task $t) => ! $tasks->contains('parent_id', $t->id));
        $planned = $this->plannedPercent($leaves, $today);
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

        return [
            'ulid' => $p->ulid,
            'code' => $p->code,
            'name' => $p->name,
            'category' => $p->category,
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
    public function plannedPercent(Collection $leaves, CarbonImmutable $today): float
    {
        $weight = 0;
        $sum = 0;
        foreach ($leaves as $t) {
            if (! $t->start_date || ! $t->finish_date) {
                continue;
            }
            $w = max(1, $t->duration_days);
            $weight += $w;
            if ($today->gte($t->finish_date)) {
                $sum += $w * 100;
            } elseif ($today->gt($t->start_date)) {
                $span = max(1, $t->start_date->diffInDays($t->finish_date) + 1);
                $sum += $w * 100 * min(1, $t->start_date->diffInDays($today) / $span);
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
