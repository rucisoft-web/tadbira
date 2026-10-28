<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Support\Access;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeAbility($request, 'documents', $project);
        $config = config('tadbira.documents');

        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['file', 'max:'.$config['max_kb'], 'extensions:'.implode(',', $config['extensions'])],
            'category' => ['nullable', 'string', 'max:40'],
            'title' => ['nullable', 'string', 'max:200'],
            'task' => ['nullable', Rule::exists('tasks', 'ulid')->where('project_id', $project->id)],
        ], [
            'files.required' => 'Pilih minimal satu berkas.',
            'files.*.max' => 'Ukuran setiap berkas paling besar '.round($config['max_kb'] / 1024).' MB.',
            'files.*.extensions' => 'Jenis berkas tidak didukung. Gunakan: '.implode(', ', $config['extensions']).'.',
            'files.*.uploaded' => 'Berkas gagal diunggah. Kemungkinan melebihi batas unggah server.',
        ]);

        $taskId = ($data['task'] ?? null) ? $project->tasks()->where('ulid', $data['task'])->value('id') : null;
        $files = $data['files'];
        $stored = [];

        try {
            DB::transaction(function () use ($files, $data, $project, $taskId, $request, &$stored) {
                foreach ($files as $file) {
                    $ext = Str::lower($file->getClientOriginalExtension());
                    $original = Str::limit($file->getClientOriginalName(), 250, '');
                    $path = $file->storeAs("dokumen/{$project->organization_id}/{$project->ulid}", Str::ulid().'.'.$ext, 'local');
                    $stored[] = $path;

                    $doc = $project->documents()->create([
                        'task_id' => $taskId,
                        'category' => $data['category'] ?? null,
                        // Judul bersama hanya dipakai bila satu berkas; jika banyak, tiap berkas memakai namanya sendiri.
                        'title' => count($files) === 1 && filled($data['title'] ?? null) ? $data['title'] : pathinfo($original, PATHINFO_FILENAME),
                        'original_name' => $original,
                        'path' => $path,
                        'extension' => $ext,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $request->user()->id,
                    ]);

                    ActivityLogger::log($project, $doc, 'document', sprintf('%s mengunggah dokumen "%s"', $request->user()->name, $original));
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($stored);
            throw $e;
        }

        return back()->with('success', count($files) === 1 ? 'Dokumen diunggah.' : count($files).' dokumen diunggah.');
    }

    public function show(Request $request, ProjectDocument $document): StreamedResponse
    {
        $this->authorizeProject($request, $document->project ?? abort(404));
        abort_unless($document->disk()->exists($document->diskPath()), 404, 'Berkas tidak ditemukan di server.');

        $headers = ['Content-Type' => $document->mime ?: 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff'];

        // Pratinjau di peramban hanya untuk PDF, gambar, dan teks; jenis lain selalu diunduh.
        if ($request->boolean('lihat') && $document->previewable()) {
            // Gambar dan teks dikurung CSP sandbox; PDF tidak, karena penampil PDF peramban menolak halaman ber-sandbox.
            $csp = $document->extension === 'pdf' ? "default-src 'none'; object-src 'self'" : "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";

            return $document->disk()->response($document->diskPath(), $document->original_name, $headers + ['Content-Security-Policy' => $csp]);
        }

        return $document->disk()->download($document->diskPath(), $document->original_name, $headers);
    }

    public function update(Request $request, ProjectDocument $document): RedirectResponse
    {
        $project = $document->project ?? abort(404);
        $this->authorizeProject($request, $project);
        $this->authorizeChange($request, $document);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:40'],
            'task' => ['nullable', Rule::exists('tasks', 'ulid')->where('project_id', $project->id)],
        ]);

        $document->update([
            'title' => $data['title'],
            'category' => $data['category'] ?? null,
            'task_id' => ($data['task'] ?? null) ? $project->tasks()->where('ulid', $data['task'])->value('id') : null,
        ]);

        return back()->with('success', 'Keterangan dokumen disimpan.');
    }

    public function destroy(Request $request, ProjectDocument $document): RedirectResponse
    {
        $project = $document->project ?? abort(404);
        $this->authorizeProject($request, $project);
        $this->authorizeChange($request, $document);

        $document->delete();
        ActivityLogger::log($project, $document, 'document', sprintf('%s menghapus dokumen "%s"', $request->user()->name, $document->original_name));

        return back()->with('success', 'Dokumen dihapus.');
    }

    /** Hanya pengunggah, penanggung jawab kegiatan, atau admin/perencana yang boleh mengubah dan menghapus. */
    private function authorizeChange(Request $request, ProjectDocument $document): void
    {
        abort_unless(
            self::canManage($this->access($request), $document),
            403,
            'Hanya pengunggah, penanggung jawab kegiatan, admin, atau perencana yang dapat mengubah dokumen ini.',
        );
    }

    public static function canManage(Access $access, ProjectDocument $document): bool
    {
        $uid = (int) $access->user->id;

        return ! $access->user->isGuest() && (
            (int) $document->uploaded_by === $uid
            || (int) $document->project->leader_id === $uid
            || $access->canOn('delete_project', $document->project)
        );
    }
}
