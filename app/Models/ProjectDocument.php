<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectDocument extends Model
{
    use HasUlids;

    protected $fillable = ['project_id', 'task_id', 'category', 'title', 'original_name', 'path', 'extension', 'mime', 'size', 'uploaded_by'];

    protected $casts = ['size' => 'integer'];

    /** Jenis yang aman ditampilkan langsung di peramban, selain diunduh. */
    public const PREVIEWABLE = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'txt'];

    protected static function booted(): void
    {
        // Berkas contoh data demo dipakai bersama, jadi tidak ikut dihapus.
        static::deleted(fn (self $doc) => $doc->isDemo() || Storage::disk('local')->delete($doc->path));
    }

    /** Berkas contoh data demo disimpan dengan awalan "demo/" dan dibaca dari resources/demo-dokumen. */
    public function isDemo(): bool
    {
        return str_starts_with($this->path, 'demo/');
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->isDemo() ? 'demo' : 'local');
    }

    public function diskPath(): string
    {
        return $this->isDemo() ? substr($this->path, 5) : $this->path;
    }

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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function previewable(): bool
    {
        return in_array($this->extension, self::PREVIEWABLE, true);
    }
}
