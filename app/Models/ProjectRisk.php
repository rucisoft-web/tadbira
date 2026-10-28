<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Risiko (belum terjadi) atau isu (sudah terjadi) sebuah kegiatan. */
class ProjectRisk extends Model
{
    use HasUlids;

    public const CATEGORIES = ['Jadwal', 'Biaya', 'Lingkup', 'Mutu', 'Pengadaan', 'SDM', 'Regulasi', 'Teknis', 'Eksternal', 'Pemangku kepentingan'];

    public const STRATEGIES = [
        'hindari' => 'Hindari',
        'mitigasi' => 'Mitigasi',
        'alihkan' => 'Alihkan',
        'terima' => 'Terima',
        'eskalasi' => 'Eskalasi',
    ];

    public const STATUSES = ['open' => 'Terbuka', 'monitoring' => 'Dipantau', 'closed' => 'Ditutup'];

    protected $fillable = [
        'project_id', 'task_id', 'type', 'title', 'description', 'category', 'probability', 'impact',
        'strategy', 'response', 'owner_id', 'due_date', 'status', 'created_by', 'closed_at',
    ];

    protected $casts = ['due_date' => 'date:Y-m-d', 'closed_at' => 'datetime', 'probability' => 'integer', 'impact' => 'integer'];

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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Skor 1-25: probabilitas × dampak. Isu dianggap pasti terjadi (probabilitas 5). */
    public function score(): int
    {
        return ($this->type === 'issue' ? 5 : (int) $this->probability) * (int) $this->impact;
    }

    /** rendah (1-4), sedang (5-9), tinggi (10-16), ekstrem (≥17), mengikuti matriks 5×5 yang lazim. */
    public static function level(int $score): string
    {
        return match (true) {
            $score >= 17 => 'ekstrem',
            $score >= 10 => 'tinggi',
            $score >= 5 => 'sedang',
            default => 'rendah',
        };
    }
}
