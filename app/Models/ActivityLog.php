<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['organization_id', 'project_id', 'subject_type', 'subject_id', 'causer_id', 'event', 'description', 'changes'];

    protected $casts = ['changes' => 'array'];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
