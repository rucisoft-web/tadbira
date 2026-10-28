<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasUlids, SoftDeletes;

    public const KINDS = [
        'phase' => 'Tahap',
        'work_package' => 'Paket kerja',
        'activity' => 'Aktivitas',
        'milestone' => 'Tonggak',
    ];

    public const STATUSES = [
        'not_started' => 'Belum dimulai',
        'in_progress' => 'Sedang berjalan',
        'completed' => 'Selesai',
    ];

    protected $fillable = [
        'project_id', 'parent_id', 'kind', 'wbs_code', 'position', 'title', 'expected_output', 'notes',
        'status', 'priority', 'scheduling_mode', 'duration_days', 'constraint_type', 'constraint_date',
        'manual_start', 'percent_complete', 'actual_start', 'actual_finish',
    ];

    protected $casts = [
        'constraint_date' => 'date:Y-m-d',
        'manual_start' => 'date:Y-m-d',
        'start_date' => 'date:Y-m-d',
        'finish_date' => 'date:Y-m-d',
        'late_start' => 'date:Y-m-d',
        'late_finish' => 'date:Y-m-d',
        'baseline_start' => 'date:Y-m-d',
        'baseline_finish' => 'date:Y-m-d',
        'actual_start' => 'date:Y-m-d',
        'actual_finish' => 'date:Y-m-d',
        'is_critical' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    public function predecessors(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'successor_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')->withPivot(['role', 'units_percent']);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('position');
    }

    public function isSummary(): bool
    {
        return in_array($this->kind, ['phase', 'work_package'], true);
    }
}
