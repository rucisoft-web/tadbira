<?php

namespace App\Models;

use App\Support\Terminology;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasUlids;

    protected $fillable = ['parent_id', 'name', 'short_name', 'sector', 'code', 'timezone', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class)->orderByDesc('year');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    public function terminology(): Terminology
    {
        return new Terminology($this->sector);
    }

    public function isGovernment(): bool
    {
        return in_array($this->sector, config('tadbira.government_sectors'), true);
    }
}
