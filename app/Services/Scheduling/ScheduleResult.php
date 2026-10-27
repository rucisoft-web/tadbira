<?php

namespace App\Services\Scheduling;

use Carbon\CarbonImmutable;

class ScheduleResult
{
    /**
     * @var array<int|string, array{start: CarbonImmutable, finish: CarbonImmutable, late_start: CarbonImmutable, late_finish: CarbonImmutable, slack: int, critical: bool}>
     */
    public array $tasks = [];

    public function __construct(
        public CarbonImmutable $projectStart,
        public CarbonImmutable $projectFinish,
        public ?CarbonImmutable $targetFinish = null,
    ) {
    }

    /** Daftar id aktivitas di jalur kritis. */
    public function criticalIds(): array
    {
        return array_keys(array_filter($this->tasks, fn ($t) => $t['critical']));
    }
}
