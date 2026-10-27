<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Sasaran program dalam Perjanjian Kinerja satu tahun anggaran. */
class Goal extends Model
{
    use HasUlids;

    protected $fillable = ['organization_id', 'fiscal_year_id', 'code', 'title', 'description', 'color', 'position'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(GoalIndicator::class)->orderBy('position');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
