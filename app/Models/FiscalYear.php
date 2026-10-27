<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use HasUlids;

    protected $fillable = ['organization_id', 'year', 'code', 'start_date', 'end_date', 'status', 'ceiling_amount', 'notes'];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'ceiling_amount' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class)->orderBy('position');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
