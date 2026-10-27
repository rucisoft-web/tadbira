<?php

namespace App\Services\Scheduling;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Validation\ValidationException;

/**
 * Mengubah teks pendahulu bergaya lembar kerja, mis. "1.2, 1.3+2, 2.1-1",
 * menjadi daftar urutan Selesai-ke-Mulai dengan jeda (hari kerja).
 */
class PredecessorParser
{
    /**
     * @return list<array{predecessor_id: int, lag_days: int}>
     */
    public function parse(Project $project, Task $task, ?string $text): array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return [];
        }

        $byWbs = $project->tasks()->get(['id', 'wbs_code', 'parent_id'])->keyBy('wbs_code');
        $parentIds = $project->tasks()->whereNotNull('parent_id')->pluck('parent_id')->flip();
        $out = [];

        foreach (preg_split('/[,;]+/', $text) as $token) {
            $token = strtoupper(trim($token));
            if ($token === '') {
                continue;
            }
            if (preg_match('/^([\d.]+?)\s*(?:SM|FS)?\s*(?:([+-])\s*(\d+)\s*(?:H|HARI|D)?)?$/', $token, $m) !== 1) {
                throw ValidationException::withMessages(['predecessors' => "Format pendahulu \"{$token}\" tidak dikenali. Contoh: 1.2, 1.3+2"]);
            }
            $pred = $byWbs->get(rtrim($m[1], '.'));
            if (! $pred) {
                throw ValidationException::withMessages(['predecessors' => "Aktivitas nomor {$m[1]} tidak ditemukan."]);
            }
            if ($pred->id === $task->id) {
                throw ValidationException::withMessages(['predecessors' => 'Aktivitas tidak boleh menjadi pendahulu dirinya sendiri.']);
            }
            if ($parentIds->has($pred->id)) {
                throw ValidationException::withMessages(['predecessors' => "Nomor {$m[1]} adalah tahap/paket kerja. Pilih aktivitas di dalamnya."]);
            }
            $lag = isset($m[3]) && $m[3] !== '' ? (int) $m[3] * ($m[2] === '-' ? -1 : 1) : 0;
            $out[$pred->id] = ['predecessor_id' => $pred->id, 'lag_days' => $lag];
        }

        return array_values($out);
    }

    /** Teks pendahulu untuk ditampilkan di kisi. */
    public static function format(iterable $dependencies, array $wbsById): string
    {
        $parts = [];
        foreach ($dependencies as $d) {
            $code = $wbsById[$d->predecessor_id] ?? '?';
            $parts[] = $code.($d->lag_days > 0 ? '+'.$d->lag_days : ($d->lag_days < 0 ? (string) $d->lag_days : ''));
        }

        return implode(', ', $parts);
    }
}
