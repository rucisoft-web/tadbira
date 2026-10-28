<?php

namespace App\Services\Scheduling;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Kalender kerja berbasis hari: hari kerja mingguan dikurangi hari libur,
 * ditambah hari kerja pengganti. Semua tanggal dinormalkan ke awal hari.
 */
class WorkingCalendar
{
    /** @var array<string, bool> */
    private array $holidays;

    /** @var array<string, bool> */
    private array $extraWorkingDays;

    /**
     * @param  list<string>  $holidays  tanggal libur (Y-m-d)
     * @param  list<string>  $extraWorkingDays  hari kerja pengganti (Y-m-d)
     * @param  list<int>  $workingWeekdays  ISO weekday: 1 = Senin … 7 = Minggu
     */
    public function __construct(
        array $holidays = [],
        array $extraWorkingDays = [],
        private array $workingWeekdays = [1, 2, 3, 4, 5],
    ) {
        $this->holidays = array_fill_keys($holidays, true);
        $this->extraWorkingDays = array_fill_keys($extraWorkingDays, true);
    }

    public static function day(DateTimeInterface|string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date)->startOfDay();
    }

    public function isWorkingDay(DateTimeInterface|string $date): bool
    {
        $d = self::day($date);
        $key = $d->toDateString();

        if (isset($this->extraWorkingDays[$key])) {
            return true;
        }

        return in_array($d->dayOfWeekIso, $this->workingWeekdays, true) && ! isset($this->holidays[$key]);
    }

    /** Hari kerja pertama pada atau setelah tanggal. */
    public function nextWorkingDay(DateTimeInterface|string $date): CarbonImmutable
    {
        $d = self::day($date);
        for ($i = 0; $i < 3660 && ! $this->isWorkingDay($d); $i++) {
            $d = $d->addDay();
        }

        return $d;
    }

    /** Hari kerja terakhir pada atau sebelum tanggal. */
    public function previousWorkingDay(DateTimeInterface|string $date): CarbonImmutable
    {
        $d = self::day($date);
        for ($i = 0; $i < 3660 && ! $this->isWorkingDay($d); $i++) {
            $d = $d->subDay();
        }

        return $d;
    }

    /** Geser sebanyak $days hari kerja (boleh negatif) dari hari kerja $date. */
    public function addWorkingDays(DateTimeInterface|string $date, int $days): CarbonImmutable
    {
        $d = self::day($date);
        $step = $days >= 0 ? 1 : -1;
        $remaining = abs($days);

        while ($remaining > 0) {
            $d = $d->addDays($step);
            if ($this->isWorkingDay($d)) {
                $remaining--;
            }
        }

        return $d;
    }

    /** Tanggal selesai aktivitas yang mulai $start dan berlangsung $duration hari kerja. */
    public function finishFrom(DateTimeInterface|string $start, int $duration): CarbonImmutable
    {
        $start = $this->nextWorkingDay($start);

        return $duration <= 1 ? $start : $this->addWorkingDays($start, $duration - 1);
    }

    /** Tanggal mulai aktivitas yang harus selesai $finish dan berlangsung $duration hari kerja. */
    public function startFrom(DateTimeInterface|string $finish, int $duration): CarbonImmutable
    {
        $finish = $this->previousWorkingDay($finish);

        return $duration <= 1 ? $finish : $this->addWorkingDays($finish, -($duration - 1));
    }

    /** Selisih hari kerja dari $a ke $b (positif bila $b setelah $a). */
    public function workingDaysBetween(DateTimeInterface|string $a, DateTimeInterface|string $b): int
    {
        $a = self::day($a);
        $b = self::day($b);
        if ($a->equalTo($b)) {
            return 0;
        }

        $sign = $b->greaterThan($a) ? 1 : -1;
        [$from, $to] = $sign > 0 ? [$a, $b] : [$b, $a];

        $count = 0;
        for ($d = $from->addDay(); $d->lessThanOrEqualTo($to); $d = $d->addDay()) {
            if ($this->isWorkingDay($d)) {
                $count++;
            }
        }

        return $sign * $count;
    }

    /** Jumlah hari libur (bukan akhir pekan) di antara dua tanggal, inklusif. */
    public function holidaysBetween(DateTimeInterface|string $a, DateTimeInterface|string $b): int
    {
        $count = 0;
        for ($d = self::day($a); $d->lessThanOrEqualTo(self::day($b)); $d = $d->addDay()) {
            if (isset($this->holidays[$d->toDateString()]) && in_array($d->dayOfWeekIso, $this->workingWeekdays, true)) {
                $count++;
            }
        }

        return $count;
    }
}
