<?php

namespace App\Services\Scheduling;

use Carbon\CarbonImmutable;

/**
 * Mesin penjadwalan Critical Path Method (CPM) berbasis hari kerja.
 *
 * Masukan berupa graf murni (tanpa Eloquent) sehingga bisa dipakai untuk
 * proyek nyata, pratinjau templat, dan simulasi usulan perubahan.
 *
 * Node: ['id', 'parent_id', 'kind', 'duration', 'mode', 'manual_start', 'constraint_type', 'constraint_date']
 * Edge: ['predecessor', 'successor', 'lag'] (jenis Selesai-ke-Mulai)
 */
class Scheduler
{
    public function __construct(private WorkingCalendar $calendar)
    {
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $nodes
     * @param  list<array{predecessor: int|string, successor: int|string, lag?: int}>  $edges
     * @param  'forward'|'backward'  $direction
     */
    public function run(array $nodes, array $edges, string $direction, CarbonImmutable|string $anchor): ScheduleResult
    {
        $nodes = $this->keyById($nodes);
        $summaryIds = $this->summaryIds($nodes);
        $leaves = array_diff_key($nodes, array_flip($summaryIds));

        // Hanya urutan antar-aktivitas/tonggak (bukan tahap) yang dipakai mesin.
        $edges = array_values(array_filter($edges, fn ($e) => isset($leaves[$e['predecessor']], $leaves[$e['successor']])));
        $order = $this->topologicalOrder(array_keys($leaves), $edges);

        $preds = $succs = [];
        foreach ($edges as $e) {
            $preds[$e['successor']][] = $e;
            $succs[$e['predecessor']][] = $e;
        }

        $cal = $this->calendar;
        $anchor = WorkingCalendar::day($anchor);

        if ($direction === 'backward') {
            // Hitung mundur dari tanggal selesai, lalu tanggal mulai proyek = mulai paling lambat terawal.
            [$ls] = $this->backwardPass($order, $leaves, $succs, $cal->previousWorkingDay($anchor), []);
            $projectStart = $ls === [] ? $cal->previousWorkingDay($anchor) : min($ls);
        } else {
            $projectStart = $cal->nextWorkingDay($anchor);
        }

        // Hitungan maju.
        $es = $ef = [];
        foreach ($order as $id) {
            $n = $leaves[$id];
            $duration = $this->duration($n);

            if (($n['mode'] ?? 'auto') === 'manual' && ! empty($n['manual_start'])) {
                $start = $cal->nextWorkingDay($n['manual_start']);
            } else {
                $start = $projectStart;
                foreach ($preds[$id] ?? [] as $e) {
                    $start = max($start, $this->successorStart($e['predecessor'], $ef, $leaves, (int) ($e['lag'] ?? 0)));
                }
                if (! empty($n['constraint_date'])) {
                    $cd = $cal->nextWorkingDay($n['constraint_date']);
                    $type = $n['constraint_type'] ?? 'asap';
                    if ($type === 'snet') {
                        $start = max($start, $cd);
                    } elseif ($type === 'mso') {
                        $start = $cd;
                    }
                }
            }

            $es[$id] = $cal->nextWorkingDay($start);
            $ef[$id] = $duration === 0 ? $es[$id] : $cal->finishFrom($es[$id], $duration);
        }

        $projectFinish = $direction === 'backward'
            ? $cal->previousWorkingDay($anchor)
            : ($ef === [] ? $projectStart : max($ef));

        // Hitungan mundur → mulai/selesai paling lambat dan kelonggaran.
        [$ls, $lf] = $this->backwardPass($order, $leaves, $succs, $projectFinish, $ef);

        $result = new ScheduleResult($projectStart, $ef === [] ? $projectStart : max($ef), $direction === 'backward' ? $projectFinish : null);

        foreach ($order as $id) {
            $slack = $cal->workingDaysBetween($es[$id], $ls[$id]);
            $result->tasks[$id] = [
                'start' => $es[$id],
                'finish' => $ef[$id],
                'late_start' => $ls[$id],
                'late_finish' => $lf[$id],
                'slack' => $slack,
                'critical' => $slack <= 0,
            ];
        }

        $this->rollUpSummaries($nodes, $summaryIds, $result);

        if ($es !== []) {
            $result->projectStart = min($es);
        }

        return $result;
    }

    /**
     * @return array{0: array<int|string, CarbonImmutable>, 1: array<int|string, CarbonImmutable>}
     */
    private function backwardPass(array $order, array $leaves, array $succs, CarbonImmutable $finish, array $ef): array
    {
        $cal = $this->calendar;
        $ls = $lf = [];

        foreach (array_reverse($order) as $id) {
            $duration = $this->duration($leaves[$id]);
            $late = $finish;
            foreach ($succs[$id] ?? [] as $e) {
                $succId = $e['successor'];
                $lag = (int) ($e['lag'] ?? 0);
                // Selesai paling lambat = sehari kerja sebelum penerus harus mulai (dikurangi jeda).
                $candidate = $duration === 0
                    ? $cal->addWorkingDays($ls[$succId], -$lag)
                    : $cal->addWorkingDays($ls[$succId], -1 - $lag);
                $late = min($late, $candidate);
            }
            $lf[$id] = $cal->previousWorkingDay($late);
            $ls[$id] = $duration === 0 ? $lf[$id] : $cal->startFrom($lf[$id], $duration);
        }

        return [$ls, $lf];
    }

    private function successorStart(int|string $predId, array $ef, array $leaves, int $lag): CarbonImmutable
    {
        $predFinish = $ef[$predId];
        $base = $this->duration($leaves[$predId]) === 0 ? $predFinish : $this->calendar->addWorkingDays($predFinish, 1);

        return $lag === 0 ? $base : $this->calendar->addWorkingDays($base, $lag);
    }

    private function duration(array $n): int
    {
        if (($n['kind'] ?? 'activity') === 'milestone') {
            return 0;
        }

        return max(0, (int) ($n['duration'] ?? 1));
    }

    /**
     * Urutan topologis (Kahn). Melempar ScheduleCycleException bila ada urutan melingkar.
     *
     * @return list<int|string>
     */
    public function topologicalOrder(array $ids, array $edges): array
    {
        $indegree = array_fill_keys($ids, 0);
        $adj = [];
        foreach ($edges as $e) {
            $adj[$e['predecessor']][] = $e['successor'];
            $indegree[$e['successor']]++;
        }

        $queue = array_keys(array_filter($indegree, fn ($d) => $d === 0));
        $order = [];
        while ($queue !== []) {
            $id = array_shift($queue);
            $order[] = $id;
            foreach ($adj[$id] ?? [] as $next) {
                if (--$indegree[$next] === 0) {
                    $queue[] = $next;
                }
            }
        }

        if (count($order) !== count($ids)) {
            throw new ScheduleCycleException(array_keys(array_filter($indegree, fn ($d) => $d > 0)));
        }

        return $order;
    }

    private function keyById(array $nodes): array
    {
        $keyed = [];
        foreach ($nodes as $n) {
            $keyed[$n['id']] = $n;
        }

        return $keyed;
    }

    /** Node yang punya anak dianggap ringkasan (tahap / paket kerja). */
    private function summaryIds(array $nodes): array
    {
        $ids = [];
        foreach ($nodes as $n) {
            if (! empty($n['parent_id']) && isset($nodes[$n['parent_id']])) {
                $ids[$n['parent_id']] = true;
            }
        }

        return array_keys($ids);
    }

    private function rollUpSummaries(array $nodes, array $summaryIds, ScheduleResult $result): void
    {
        $children = [];
        foreach ($nodes as $n) {
            if (! empty($n['parent_id'])) {
                $children[$n['parent_id']][] = $n['id'];
            }
        }

        $resolve = function ($id) use (&$resolve, $children, $result) {
            if (isset($result->tasks[$id])) {
                return $result->tasks[$id];
            }
            $rows = array_filter(array_map($resolve, $children[$id] ?? []));
            if ($rows === []) {
                return null;
            }

            return $result->tasks[$id] = [
                'start' => min(array_column($rows, 'start')),
                'finish' => max(array_column($rows, 'finish')),
                'late_start' => min(array_column($rows, 'late_start')),
                'late_finish' => max(array_column($rows, 'late_finish')),
                'slack' => min(array_column($rows, 'slack')),
                'critical' => in_array(true, array_column($rows, 'critical'), true),
            ];
        };

        foreach ($summaryIds as $id) {
            $resolve($id);
        }
    }
}
