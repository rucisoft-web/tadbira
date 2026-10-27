<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Indikator kinerja (IKU) sebuah sasaran program. */
class GoalIndicator extends Model
{
    protected $fillable = [
        'goal_id', 'name', 'unit', 'target', 'realization', 'score', 'baseline_realization', 'baseline_score', 'position',
    ];

    protected $casts = ['score' => 'float', 'baseline_score' => 'float'];

    /**
     * Kategori capaian menurut KMA 94/2021: >100–120 sangat baik, 80–100 baik,
     * 50–79,99 cukup, <50 kurang.
     */
    public static function category(?float $score): ?string
    {
        return match (true) {
            $score === null => null,
            $score > 100 => 'sangat_baik',
            $score >= 80 => 'baik',
            $score >= 50 => 'cukup',
            default => 'kurang',
        };
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
