<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Unit kerja (fakultas, biro, direktorat, wilayah kerja). Namanya dipakai sebagai kategori kegiatan. */
class Unit extends Model
{
    use HasUlids;

    protected $fillable = ['organization_id', 'name', 'full_name', 'position'];

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
}
