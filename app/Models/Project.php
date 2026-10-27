<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUlids, SoftDeletes;

    public const STATUSES = [
        'planning' => 'Perencanaan',
        'executing' => 'Pelaksanaan',
        'on_hold' => 'Ditunda',
        'closing' => 'Penutupan',
        'closed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'organization_id', 'fiscal_year_id', 'leader_id', 'code', 'name', 'description', 'category', 'color',
        'status', 'scheduling_direction', 'start_date', 'target_finish_date', 'is_template', 'template_id',
        'budget_amount', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'target_finish_date' => 'date:Y-m-d',
        'computed_start_date' => 'date:Y-m-d',
        'computed_finish_date' => 'date:Y-m-d',
        'percent_complete' => 'float',
        'budget_amount' => 'float',
        'is_template' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function scopeTemplates(Builder $q): Builder
    {
        return $q->where('is_template', true);
    }

    public function scopeReal(Builder $q): Builder
    {
        return $q->where('is_template', false);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class);
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class)->orderBy('position');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
