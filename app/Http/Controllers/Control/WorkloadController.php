<?php

namespace App\Http\Controllers\Control;

use App\Http\Controllers\Controller;
use App\Services\Scheduling\ProjectScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Beban kerja per orang per minggu dari penugasan aktivitas yang belum selesai.
 * Beban 100% = seluruh hari kerja minggu itu terisi; di atas 100% berarti tumpang tindih.
 */
class WorkloadController extends Controller
{
    public const WEEKS = 12;

    public function __invoke(Request $request, ProjectScheduler $scheduler): Response
    {
        $org = $this->organization($request);
        $fy = $this->fiscalYear($request, $org);
        $unit = $this->access($request)->forcedCategory() ?? ($request->string('unit')->toString() ?: null);
        $calendar = $scheduler->calendarFor($org->id);

        $from = CarbonImmutable::parse($request->date('mulai') ?? CarbonImmutable::today())->startOfWeek();
        $weeks = collect(range(0, self::WEEKS - 1))->map(function ($i) use ($from, $calendar) {
            $start = $from->addWeeks($i);
            $end = $start->addDays(6);
            $working = collect(range(0, 6))->filter(fn ($d) => $calendar->isWorkingDay($start->addDays($d)))->count();

            return ['start' => $start, 'end' => $end, 'working' => $working];
        });
        $until = $weeks->last()['end'];

        $rows = DB::table('task_assignments')
            ->join('tasks', 'tasks.id', '=', 'task_assignments.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->where('projects.organization_id', $org->id)
            ->where('projects.is_template', false)
            ->whereNotIn('projects.status', ['closed', 'cancelled'])
            ->when($fy, fn ($q) => $q->where('projects.fiscal_year_id', $fy->id))
            ->when($unit, fn ($q) => $q->where('projects.category', $unit))
            ->whereNull('tasks.deleted_at')
            ->where('tasks.kind', 'activity')
            ->where('tasks.percent_complete', '<', 100)
            ->where('tasks.duration_days', '>', 0)
            ->whereDate('tasks.start_date', '<=', $until)
            ->whereDate('tasks.finish_date', '>=', $from)
            ->whereNotExists(fn ($q) => $q->from('tasks as c')->whereColumn('c.parent_id', 'tasks.id')->whereNull('c.deleted_at'))
            ->get([
                'task_assignments.user_id', 'task_assignments.units_percent', 'tasks.ulid as task_ulid', 'tasks.title', 'tasks.wbs_code',
                'tasks.start_date', 'tasks.finish_date', 'tasks.is_critical', 'projects.ulid as project_ulid', 'projects.name as project_name',
            ]);

        $members = $org->users()->wherePivot('is_active', true)->where('users.email', '!=', config('tadbira.guest.email'))
            ->orderBy('name')->get(['users.id', 'users.name'])->keyBy('id');
        $units = DB::table('organization_user')->leftJoin('units', 'units.id', '=', 'organization_user.unit_id')
            ->where('organization_user.organization_id', $org->id)->pluck('units.name', 'organization_user.user_id');

        $people = $rows->groupBy('user_id')->map(function ($tasks, $userId) use ($weeks, $calendar, $members, $units) {
            $load = $weeks->map(function ($w) use ($tasks, $calendar) {
                if (! $w['working']) {
                    return ['percent' => 0, 'tasks' => []];
                }
                $sum = 0;
                $names = [];
                foreach ($tasks as $t) {
                    $s = max($w['start']->toDateString(), substr($t->start_date, 0, 10));
                    $f = min($w['end']->toDateString(), substr($t->finish_date, 0, 10));
                    if ($s > $f) {
                        continue;
                    }
                    $days = 0;
                    for ($d = CarbonImmutable::parse($s); $d->toDateString() <= $f; $d = $d->addDay()) {
                        $days += $calendar->isWorkingDay($d) ? 1 : 0;
                    }
                    if ($days) {
                        $sum += $t->units_percent * $days / $w['working'];
                        $names[] = trim($t->wbs_code.' '.$t->title);
                    }
                }

                return ['percent' => (int) round($sum), 'tasks' => $names];
            });

            return [
                'id' => $userId,
                'name' => $members[$userId]->name ?? 'Pengguna nonaktif',
                'unit' => $units[$userId] ?? null,
                'weeks' => $load->values(),
                'peak' => $load->max('percent'),
                'overloaded_weeks' => $load->where('percent', '>', 100)->count(),
                'tasks' => $tasks->sortBy('start_date')->map(fn ($t) => [
                    'ulid' => $t->task_ulid,
                    'label' => trim($t->wbs_code.' '.$t->title),
                    'project' => $t->project_name,
                    'project_ulid' => $t->project_ulid,
                    'start' => substr($t->start_date, 0, 10),
                    'finish' => substr($t->finish_date, 0, 10),
                    'critical' => (bool) $t->is_critical,
                    'units' => $t->units_percent,
                ])->values(),
            ];
        })->sortByDesc('peak')->values();

        $assigned = $people->pluck('id')->flip();
        $idle = $members->reject(fn ($u) => $assigned->has($u->id))->when($unit, fn ($c) => $c->filter(fn ($u) => ($units[$u->id] ?? null) === $unit))
            ->map(fn ($u) => ['name' => $u->name, 'unit' => $units[$u->id] ?? null])->values();

        return Inertia::render('Workload', [
            'fiscalYears' => $org->fiscalYears()->get(['ulid', 'code', 'year']),
            'fiscalYear' => $fy ? Arr::only($fy->toArray(), ['ulid', 'code']) : null,
            'weeks' => $weeks->map(fn ($w) => ['start' => $w['start']->toDateString(), 'end' => $w['end']->toDateString(), 'working' => $w['working']]),
            'people' => $people,
            'idle' => $idle,
            'units' => $org->units()->orderBy('name')->pluck('name'),
            'unit' => $unit,
            'unitLocked' => (bool) $this->access($request)->forcedCategory(),
            'from' => $from->toDateString(),
        ]);
    }
}
