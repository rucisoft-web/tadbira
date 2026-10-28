<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\ProjectRisk;
use App\Services\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PortfolioService $portfolio): Response
    {
        $org = $this->organization($request);
        $fy = $this->fiscalYear($request, $org);

        return Inertia::render('Dashboard', [
            'fiscalYears' => $org->fiscalYears()->get(['ulid', 'code', 'year', 'status']),
            'fiscalYear' => $fy ? Arr::only($fy->toArray(), ['ulid', 'code', 'year', 'status', 'start_date', 'end_date', 'ceiling_amount']) : null,
            'portfolio' => $fy ? $portfolio->summary($fy, null, $request->string('unit')->toString() ?: null) : null,
            'units' => $fy ? $fy->projects()->where('is_template', false)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category') : [],
            'unit' => $request->string('unit')->toString() ?: null,
            'today' => now()->toDateString(),
            'control' => $fy ? $this->control($fy->id, $request->string('unit')->toString() ?: null) : null,
        ]);
    }

    /** Risiko tinggi/ekstrem yang masih terbuka dan permintaan perubahan yang menunggu keputusan. */
    private function control(int $fiscalYearId, ?string $unit): array
    {
        $scope = fn ($q) => $q->where('is_template', false)->where('fiscal_year_id', $fiscalYearId)->when($unit, fn ($q) => $q->where('category', $unit));

        $risks = ProjectRisk::whereHas('project', $scope)->where('status', '!=', 'closed')
            ->with(['project:id,ulid,name', 'owner:id,name'])->get()
            ->map(fn (ProjectRisk $r) => [
                'title' => $r->title,
                'type' => $r->type,
                'score' => $r->score(),
                'level' => ProjectRisk::level($r->score()),
                'owner' => $r->owner?->name,
                'project' => $r->project->name,
                'project_ulid' => $r->project->ulid,
            ]);

        return [
            'open_risks' => $risks->where('type', 'risk')->count(),
            'open_issues' => $risks->where('type', 'issue')->count(),
            // Isu paling berat dulu, lalu risiko tinggi/ekstrem, agar keduanya tampak.
            'top_risks' => $risks->where('type', 'issue')->sortByDesc('score')->take(2)
                ->concat($risks->where('type', 'risk')->where('score', '>=', 10)->sortByDesc('score')->take(4))->values(),
            'pending_changes' => ChangeRequest::whereHas('project', $scope)->where('status', 'submitted')
                ->with(['project:id,ulid,name', 'requester:id,name'])->latest()->limit(6)->get()
                ->map(fn (ChangeRequest $c) => [
                    'code' => $c->code(),
                    'title' => $c->title,
                    'project' => $c->project->name,
                    'project_ulid' => $c->project->ulid,
                    'requester' => $c->requester?->name,
                    'cost' => $c->cost_impact,
                    'days' => $c->schedule_impact_days,
                ]),
        ];
    }
}
