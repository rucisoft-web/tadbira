<?php

namespace Tests\Unit;

use App\Services\Scheduling\ScheduleCycleException;
use App\Services\Scheduling\Scheduler;
use App\Services\Scheduling\WorkingCalendar;
use PHPUnit\Framework\TestCase;

class SchedulerTest extends TestCase
{
    private function node(int $id, int $duration, array $extra = []): array
    {
        return array_merge(['id' => $id, 'parent_id' => null, 'kind' => 'activity', 'duration' => $duration], $extra);
    }

    public function test_calendar_skips_weekends_and_holidays(): void
    {
        // 2027-01-01 (Jumat) libur Tahun Baru.
        $cal = new WorkingCalendar(['2027-01-01']);

        $this->assertFalse($cal->isWorkingDay('2027-01-01'));
        $this->assertSame('2027-01-04', $cal->nextWorkingDay('2027-01-01')->toDateString());
        // Mulai Senin 4 Jan, 5 hari kerja → Jumat 8 Jan.
        $this->assertSame('2027-01-08', $cal->finishFrom('2027-01-04', 5)->toDateString());
        // Mulai Kamis 31 Des 2026, 2 hari kerja melewati libur + akhir pekan → Senin 4 Jan.
        $this->assertSame('2027-01-04', $cal->finishFrom('2026-12-31', 2)->toDateString());
        $this->assertSame(4, $cal->workingDaysBetween('2027-01-04', '2027-01-08'));
    }

    public function test_finish_to_start_chain_and_critical_path(): void
    {
        //  1 (3 hari) ─┬─► 2 (5 hari) ─┬─► 4 (2 hari)
        //              └─► 3 (2 hari) ─┘
        $nodes = [$this->node(1, 3), $this->node(2, 5), $this->node(3, 2), $this->node(4, 2)];
        $edges = [
            ['predecessor' => 1, 'successor' => 2],
            ['predecessor' => 1, 'successor' => 3],
            ['predecessor' => 2, 'successor' => 4],
            ['predecessor' => 3, 'successor' => 4],
        ];

        $r = (new Scheduler(new WorkingCalendar))->run($nodes, $edges, 'forward', '2027-03-01'); // Senin

        $this->assertSame('2027-03-01', $r->tasks[1]['start']->toDateString());
        $this->assertSame('2027-03-03', $r->tasks[1]['finish']->toDateString());
        $this->assertSame('2027-03-04', $r->tasks[2]['start']->toDateString());
        $this->assertSame('2027-03-10', $r->tasks[2]['finish']->toDateString()); // Kamis–Rabu, lewat akhir pekan
        $this->assertSame('2027-03-11', $r->tasks[4]['start']->toDateString());
        $this->assertSame('2027-03-12', $r->projectFinish->toDateString());

        $this->assertEqualsCanonicalizing([1, 2, 4], $r->criticalIds());
        $this->assertSame(3, $r->tasks[3]['slack']);
    }

    public function test_lag_and_milestone(): void
    {
        $nodes = [$this->node(1, 2), ['id' => 2, 'kind' => 'milestone', 'duration' => 0], $this->node(3, 1)];
        $edges = [
            ['predecessor' => 1, 'successor' => 2, 'lag' => 2],
            ['predecessor' => 2, 'successor' => 3],
        ];

        $r = (new Scheduler(new WorkingCalendar))->run($nodes, $edges, 'forward', '2027-03-01');

        // 1 selesai Selasa 2 Mar; tonggak = Rabu 3 Mar + jeda 2 hari = Jumat 5 Mar.
        $this->assertSame('2027-03-05', $r->tasks[2]['start']->toDateString());
        $this->assertSame('2027-03-05', $r->tasks[2]['finish']->toDateString());
        // Aktivitas setelah tonggak mulai di hari yang sama.
        $this->assertSame('2027-03-05', $r->tasks[3]['start']->toDateString());
    }

    public function test_backward_scheduling_from_event_date(): void
    {
        // Persiapan 10 hari → undangan 3 hari → acara (tonggak) Jumat 30 Apr 2027.
        $nodes = [$this->node(1, 10), $this->node(2, 3), ['id' => 3, 'kind' => 'milestone']];
        $edges = [
            ['predecessor' => 1, 'successor' => 2],
            ['predecessor' => 2, 'successor' => 3],
        ];

        $r = (new Scheduler(new WorkingCalendar))->run($nodes, $edges, 'backward', '2027-04-30');

        $this->assertSame('2027-04-30', $r->tasks[3]['start']->toDateString());
        // Undangan harus selesai sehari kerja sebelum acara: 27–29 Apr.
        $this->assertSame('2027-04-27', $r->tasks[2]['start']->toDateString());
        $this->assertSame('2027-04-29', $r->tasks[2]['finish']->toDateString());
        // Persiapan 10 hari kerja selesai Senin 26 Apr → mulai Selasa 13 Apr.
        $this->assertSame('2027-04-13', $r->projectStart->toDateString());
        $this->assertSame([], array_filter($r->tasks, fn ($t) => $t['slack'] !== 0));
    }

    public function test_summary_rolls_up_children(): void
    {
        $nodes = [
            ['id' => 10, 'kind' => 'phase'],
            $this->node(1, 3, ['parent_id' => 10]),
            $this->node(2, 2, ['parent_id' => 10]),
        ];
        $edges = [['predecessor' => 1, 'successor' => 2]];

        $r = (new Scheduler(new WorkingCalendar))->run($nodes, $edges, 'forward', '2027-03-01');

        $this->assertSame('2027-03-01', $r->tasks[10]['start']->toDateString());
        $this->assertSame('2027-03-05', $r->tasks[10]['finish']->toDateString());
        $this->assertTrue($r->tasks[10]['critical']);
    }

    public function test_cycle_is_rejected(): void
    {
        $this->expectException(ScheduleCycleException::class);

        (new Scheduler(new WorkingCalendar))->run(
            [$this->node(1, 1), $this->node(2, 1)],
            [['predecessor' => 1, 'successor' => 2], ['predecessor' => 2, 'successor' => 1]],
            'forward',
            '2027-03-01',
        );
    }
}
