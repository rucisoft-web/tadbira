<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Baseline: jadwal dan anggaran yang disepakati, dibekukan sebagai pembanding kemajuan.
 * Menetapkan ulang (rebaseline) sebaiknya hanya lewat permintaan perubahan yang disetujui.
 */
class BaselineService
{
    public function capture(Project $project, ?User $by = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($project, $by, $reason) {
            DB::table('tasks')->where('project_id', $project->id)->whereNull('deleted_at')->update([
                'baseline_start' => DB::raw('start_date'),
                'baseline_finish' => DB::raw('finish_date'),
                'baseline_duration' => DB::raw('duration_days'),
            ]);

            $project->forceFill([
                'baseline_start' => $project->computed_start_date,
                'baseline_finish' => $project->computed_finish_date,
                'baseline_budget' => $project->budget_amount,
                'baseline_number' => $project->baseline_number + 1,
                'baseline_at' => now(),
                'baseline_by' => $by?->id,
            ])->saveQuietly();

            $label = $project->baseline_number === 1 ? 'menetapkan baseline' : "menetapkan ulang baseline (ke-{$project->baseline_number})";
            ActivityLogger::log($project, $project, 'baseline', trim(($by?->name ?? 'Sistem')." {$label}".($reason ? ": {$reason}" : '')));
        });
    }
}
