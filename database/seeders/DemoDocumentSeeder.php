<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Contoh dokumen kegiatan untuk organisasi demo: TOR, RAB, kontrak, BAST, laporan, notulen,
 * dan foto, dikaitkan ke tonggak yang relevan. Berkasnya dibaca dari resources/demo-dokumen
 * (disk "demo") dan dipakai bersama, jadi tidak memakan ruang penyimpanan.
 *
 * Aman dijalankan ulang: dokumen demo lama dihapus lebih dulu, unggahan pengguna tidak disentuh.
 */
class DemoDocumentSeeder extends Seeder
{
    public const ORGANIZATIONS = ['123456', '423755', 'MEDC', 'UNJ'];

    protected const MIME = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg' => 'image/jpeg',
    ];

    protected CarbonImmutable $today;

    public function run(): void
    {
        $this->today = CarbonImmutable::today();
        ProjectDocument::where('path', 'like', 'demo/%')->delete();

        $orgs = Organization::whereIn('code', self::ORGANIZATIONS)->pluck('id');
        Project::whereIn('organization_id', $orgs)->where('is_template', false)->whereNotNull('fiscal_year_id')
            ->with(['organization', 'tasks' => fn ($q) => $q->where('kind', 'milestone')])
            ->orderBy('id')
            ->each(fn (Project $project) => $this->seedProject($project));
    }

    protected function seedProject(Project $project): void
    {
        $start = CarbonImmutable::parse($project->computed_start_date ?? $project->start_date ?? $this->today->subMonths(3));
        $finish = CarbonImmutable::parse($project->computed_finish_date ?? $start->addMonths(3));
        $progress = (float) $project->percent_complete;
        $slug = Str::limit(Str::slug($project->name), 60, '');
        /** @var Collection<int, Task> $milestones */
        $milestones = $project->tasks;
        $milestone = fn (string $title) => $milestones->first(fn (Task $t) => $t->title === $title);
        $done = fn (?Task $t) => $t && $t->percent_complete >= 100;
        $isEvent = (bool) $milestone('Kegiatan terlaksana');
        $corporate = $project->organization->code === 'MEDC';

        $docs = [
            [$corporate ? 'Kerangka acuan kerja (KAK)' : 'Kerangka acuan kerja (TOR)', 'TOR / KAK', 'tor.pdf', $start->subDays(10), null],
            [$corporate ? 'Rincian anggaran (AFE)' : 'Rencana anggaran biaya (RAB)', 'RAB', 'rab.xlsx', $start->subDays(7), null],
        ];

        if ($done($task = $milestone('Kontrak ditandatangani'))) {
            $docs[] = ['Kontrak / SPK pelaksana', 'Kontrak / SPK', 'kontrak.pdf', $this->dateOf($task), $task];
        }

        if ($isEvent) {
            $task = $milestone('Kegiatan terlaksana');
            $held = $this->dateOf($task);
            $docs[] = ['Surat undangan dan susunan acara', 'Surat', 'undangan.pdf', $held->subDays(14), null];
            if ($done($task)) {
                $docs[] = ['Notulen dan daftar hadir', 'Notulen', 'notulen.docx', $held->addDay(), $task];
                $docs[] = ['Dokumentasi pelaksanaan', 'Foto / dokumentasi', 'foto-kegiatan.jpg', $held, $task];
                if ($project->id % 2 === 0) {
                    $docs[] = ['Bahan paparan narasumber', 'Lainnya', 'paparan.pptx', $held, $task];
                }
            }
        } elseif ($progress >= 30) {
            $docs[] = ['Foto progres lapangan', 'Foto / dokumentasi', 'foto-progres.jpg', $this->between($start, $finish, 0.45), null];
        }

        if ($progress >= 30 && $progress < 100) {
            $docs[] = ['Laporan kemajuan', 'Laporan', 'laporan-kemajuan.docx', $this->between($start, $finish, 0.5), null];
        }
        if (! $isEvent && $progress >= 40 && $project->id % 3 === 0) {
            $docs[] = ['Bahan paparan rapat kemajuan', 'Lainnya', 'paparan.pptx', $this->between($start, $finish, 0.55), null];
        }

        if ($done($task = $milestone('BAST diterbitkan'))) {
            $docs[] = ['Berita acara serah terima (BAST)', 'BAST', 'bast.pdf', $this->dateOf($task), $task];
        }
        $final = $milestone('Laporan diterima') ?? $milestone('Laporan dampak sosial');
        if ($done($final)) {
            $docs[] = ['Laporan akhir', 'Laporan', 'laporan-akhir.pdf', $this->dateOf($final), $final];
        }

        foreach ($docs as [$title, $category, $file, $date, $task]) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $at = $date->greaterThan($this->today) ? $this->today->subDay() : $date;
            $at = $at->setTime(8 + crc32($file.$project->id) % 8, crc32($project->ulid.$file) % 60);

            $doc = new ProjectDocument([
                'project_id' => $project->id,
                'task_id' => $task?->id,
                'category' => $category,
                'title' => $title,
                'original_name' => pathinfo($file, PATHINFO_FILENAME).'-'.$slug.'.'.$ext,
                'path' => 'demo/'.$file,
                'extension' => $ext,
                'mime' => self::MIME[$ext],
                'size' => filesize(resource_path('demo-dokumen/'.$file)),
                'uploaded_by' => $project->leader_id ?? $project->created_by,
            ]);
            $doc->created_at = $doc->updated_at = $at;
            $doc->save();
        }
    }

    protected function dateOf(Task $task): CarbonImmutable
    {
        return CarbonImmutable::parse($task->actual_finish ?? $task->finish_date ?? $this->today);
    }

    protected function between(CarbonImmutable $from, CarbonImmutable $to, float $ratio): CarbonImmutable
    {
        return $from->addDays((int) round($from->diffInDays($to) * $ratio));
    }
}
