<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Permintaan perubahan lingkup, jadwal, atau biaya yang diputuskan pimpinan/perencana. */
class ChangeRequest extends Model
{
    use HasUlids;

    public const STATUSES = ['submitted' => 'Diajukan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'withdrawn' => 'Ditarik'];

    protected $fillable = [
        'project_id', 'number', 'title', 'description', 'reason', 'schedule_impact_days', 'cost_impact', 'scope_impact',
        'status', 'requested_by', 'decided_by', 'decided_at', 'decision_note', 'rebaselined',
    ];

    protected $casts = ['decided_at' => 'datetime', 'cost_impact' => 'float', 'schedule_impact_days' => 'integer', 'rebaselined' => 'boolean'];

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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function code(): string
    {
        return sprintf('PP-%03d', $this->number);
    }
}
