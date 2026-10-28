<?php

namespace Database\Seeders;

use App\Models\ChangeRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\PortfolioService;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\WorkingCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contoh pengendalian proyek untuk data demo: baseline, register risiko dan isu, serta
 * permintaan perubahan. Kegiatan yang sudah punya baseline, risiko, atau perubahan
 * dilewati, jadi aman dijalankan ulang dan tidak menimpa data yang dibuat pengguna.
 */
class DemoControlSeeder extends Seeder
{
    /** [judul, kategori, probabilitas, dampak, strategi, respons] */
    protected const RISKS = [
        'umum' => [
            ['Penyedia terlambat menyerahkan pekerjaan', 'Pengadaan', 3, 4, 'mitigasi', 'Serah terima bertahap dan denda keterlambatan di kontrak; rapat kemajuan mingguan dengan penyedia.'],
            ['Personel kunci berhalangan atau dimutasi', 'SDM', 2, 3, 'mitigasi', 'Tetapkan pengganti untuk setiap peran kunci dan dokumentasikan pekerjaan di Tadbira.'],
            ['Harga penawaran melebihi HPS', 'Biaya', 3, 3, 'mitigasi', 'Survei harga pasar ulang sebelum HPS ditetapkan; siapkan opsi pengurangan volume.'],
            ['Dokumen pertanggungjawaban tidak lengkap saat pemeriksaan', 'Mutu', 3, 2, 'hindari', 'Unggah kuitansi, BAST, dan foto ke tab Dokumen begitu kegiatan selesai.'],
            ['Kebutuhan pengguna berubah di tengah pelaksanaan', 'Lingkup', 3, 3, 'mitigasi', 'Setiap perubahan lewat permintaan perubahan dan disetujui pimpinan sebelum dikerjakan.'],
        ],
        'pemerintah' => [
            ['Anggaran diblokir atau direvisi', 'Regulasi', 2, 4, 'eskalasi', 'Koordinasi dini dengan perencana dan biro keuangan; siapkan usulan revisi POK.'],
            ['Pembayaran termin tertunda karena SPM dikembalikan', 'Biaya', 3, 3, 'mitigasi', 'Periksa kelengkapan SPM dengan daftar uji sebelum diajukan ke KPPN.'],
        ],
        'acara' => [
            ['Narasumber utama membatalkan kehadiran', 'Pemangku kepentingan', 2, 4, 'mitigasi', 'Konfirmasi tertulis H-14 dan siapkan narasumber cadangan.'],
            ['Jumlah peserta di bawah target', 'Lingkup', 3, 3, 'mitigasi', 'Undangan dikirim H-21, pengingat H-3, dan opsi hadir daring.'],
            ['Tempat acara tidak tersedia pada tanggal rencana', 'Eksternal', 2, 3, 'alihkan', 'Pesan tempat dengan uang muka yang dapat dikembalikan; siapkan ruang kampus sebagai cadangan.'],
        ],
        'fisik' => [
            ['Tender gagal dan harus diulang', 'Pengadaan', 2, 5, 'mitigasi', 'Reviu spesifikasi dan HPS bersama pokja sebelum diumumkan; buka konsultasi pasar.'],
            ['Hujan menghambat pekerjaan lapangan', 'Eksternal', 3, 3, 'terima', 'Cadangan waktu 10 hari kerja pada jadwal; kerja lembur bila diperlukan.'],
            ['Spesifikasi diubah oleh pengguna setelah kontrak', 'Lingkup', 2, 4, 'hindari', 'Spesifikasi ditandatangani pengguna sebelum lelang; perubahan hanya lewat addendum.'],
        ],
        'korporasi' => [
            ['Izin lingkungan (AMDAL/UKL-UPL) terlambat terbit', 'Regulasi', 3, 4, 'mitigasi', 'Ajukan dokumen lingkungan lebih awal dan tunjuk penghubung khusus dengan regulator.'],
            ['Harga minyak turun dan anggaran dipangkas', 'Eksternal', 2, 4, 'terima', 'Prioritaskan paket kerja bernilai tertinggi; siapkan skenario anggaran minimum.'],
            ['Kecelakaan kerja di lokasi', 'Teknis', 1, 5, 'hindari', 'Izin kerja, JSA, dan safety briefing harian; audit K3 bulanan.'],
            ['Material impor tertahan di pelabuhan', 'Pengadaan', 3, 4, 'mitigasi', 'Pesan lebih awal, gunakan jalur prioritas bea cukai, dan pantau status pengiriman.'],
        ],
    ];

    /** [judul, kategori, dampak, respons] */
    protected const ISSUES = [
        ['Penyedia meminta perpanjangan waktu pelaksanaan', 'Jadwal', 4, 'Rapat klarifikasi dengan penyedia; hitung denda dan siapkan permintaan perubahan bila disetujui.'],
        ['Aktivitas di jalur kritis tertinggal dari rencana', 'Jadwal', 4, 'Tambah personel pada aktivitas kritis dan pantau harian sampai kembali sesuai jadwal.'],
        ['Anggota tim inti dimutasi ke unit lain', 'SDM', 3, 'Penugasan pengganti dan serah terima pekerjaan dalam satu minggu.'],
    ];

    /** [judul, uraian, alasan, hari, porsi biaya dari anggaran, lingkup] */
    protected const CHANGES = [
        ['Perpanjangan waktu pelaksanaan 10 hari kerja', 'Tanggal selesai pekerjaan utama mundur 10 hari kerja tanpa tambahan biaya.', 'Keterlambatan pengiriman material oleh penyedia di luar kendali tim.', 10, 0, null],
        ['Penambahan volume pekerjaan', 'Volume ditambah sesuai kebutuhan pengguna yang baru teridentifikasi saat pelaksanaan.', 'Permintaan pengguna dan arahan pimpinan.', 5, 0.08, 'Tambahan satu paket keluaran'],
        ['Pergeseran anggaran antar akun', 'Pergeseran dari belanja perjalanan ke belanja bahan tanpa mengubah pagu.', 'Sebagian rapat dilaksanakan daring.', 0, 0, 'Lingkup tetap'],
        ['Pengurangan lingkup: modul daring ditunda', 'Modul pembelajaran daring dipindahkan ke tahun anggaran berikutnya.', 'Waktu tidak cukup sebelum akhir tahun anggaran.', -5, -0.05, 'Satu keluaran dipindah ke TA berikutnya'],
    ];

    protected CarbonImmutable $today;

    public function run(): void
    {
        $this->today = CarbonImmutable::today();
        $portfolio = app(PortfolioService::class);
        $scheduler = app(ProjectScheduler::class);
        $calendars = [];

        Project::real()->whereNotNull('fiscal_year_id')->where('baseline_number', 0)
            ->whereDoesntHave('risks')->whereDoesntHave('changeRequests')
            ->with(['organization', 'fiscalYear', 'tasks.assignees:id'])
            ->orderBy('id')
            ->each(function (Project $project) use ($portfolio, $scheduler, &$calendars) {
                $calendar = $calendars[$project->organization_id] ??= $scheduler->calendarFor($project->organization_id);
                $row = $portfolio->projectRow($project, $this->today, 0);
                DB::transaction(fn () => $this->seedProject($project, $row, $calendar));
            });
    }

    protected function seedProject(Project $project, array $row, WorkingCalendar $calendar): void
    {
        $seed = crc32($project->ulid);
        $leaves = $project->tasks->filter(fn (Task $t) => ! $project->tasks->contains('parent_id', $t->id));
        $people = $leaves->flatMap(fn (Task $t) => $t->assignees->pluck('id'))->push($project->leader_id)->filter()->unique()->values();
        $pick = fn (int $i) => $people->isEmpty() ? null : $people[($seed + $i) % $people->count()];
        $approver = $project->organization->users()->wherePivotIn('role', ['pimpinan', 'admin'])->orderByRaw("CASE WHEN organization_user.role = 'pimpinan' THEN 0 ELSE 1 END")->value('users.id');

        // Baseline untuk kegiatan yang sudah berjalan. Kegiatan bermasalah dibuat tergeser dari baseline.
        if ($project->status !== 'planning' && $project->computed_finish_date) {
            $shift = match ($row['health']) {
                'kritis' => 6,
                'waspada' => 3,
                default => 0,
            };
            foreach ($leaves as $t) {
                if (! $t->start_date || ! $t->finish_date) {
                    continue;
                }
                $moved = $shift && $t->percent_complete < 100 && $t->finish_date->gte($this->today->subDays(30));
                DB::table('tasks')->where('id', $t->id)->update([
                    'baseline_start' => $moved && $t->start_date->gte($this->today) ? $calendar->addWorkingDays($t->start_date, -$shift)->toDateString() : $t->start_date->toDateString(),
                    'baseline_finish' => $moved ? $calendar->addWorkingDays($t->finish_date, -$shift)->toDateString() : $t->finish_date->toDateString(),
                    'baseline_duration' => $t->duration_days,
                ]);
            }
            DB::statement('UPDATE tasks SET baseline_start = start_date, baseline_finish = finish_date, baseline_duration = duration_days WHERE project_id = ? AND baseline_finish IS NULL AND deleted_at IS NULL', [$project->id]);
            $finish = DB::table('tasks')->where('project_id', $project->id)->whereNull('deleted_at')->max('baseline_finish');
            $start = $project->computed_start_date ?? $this->today;

            $project->forceFill([
                'baseline_start' => $start,
                'baseline_finish' => $finish ?? $project->computed_finish_date,
                'baseline_budget' => $project->budget_amount,
                'baseline_number' => 1,
                'baseline_at' => CarbonImmutable::parse($start)->subDays(3)->setTime(9, 0),
                'baseline_by' => $project->leader_id,
            ])->saveQuietly();
        }

        // Register risiko: risiko umum + menurut jenis kegiatan dan sektor.
        $titles = $project->tasks->where('kind', 'milestone')->pluck('title');
        $corporate = $project->organization->code === 'MEDC';
        $library = [
            ...self::RISKS['umum'],
            ...($corporate ? self::RISKS['korporasi'] : self::RISKS['pemerintah']),
            ...($titles->contains('Kegiatan terlaksana') ? self::RISKS['acara'] : []),
            ...($titles->intersect(['Kontrak ditandatangani', 'BAST diterbitkan', 'Penandatanganan kontrak', 'Serah terima', 'Kontrak EPC efektif'])->isNotEmpty() ? self::RISKS['fisik'] : []),
        ];
        mt_srand($seed);
        shuffle($library);
        $done = in_array($project->status, ['closed', 'closing'], true);
        foreach (array_slice($library, 0, 3 + $seed % 3) as $i => [$title, $category, $p, $impact, $strategy, $response]) {
            $status = $done ? 'closed' : (($seed + $i) % 4 === 0 ? 'monitoring' : 'open');
            $project->risks()->create([
                'type' => 'risk', 'title' => $title, 'category' => $category, 'probability' => $p, 'impact' => $impact,
                'strategy' => $strategy, 'response' => $response, 'owner_id' => $pick($i), 'status' => $status,
                'due_date' => $this->today->addDays(7 + ($seed + $i * 5) % 30), 'created_by' => $project->leader_id,
                'closed_at' => $status === 'closed' ? $project->computed_finish_date : null,
                'task_id' => $leaves->values()->get(($seed + $i) % max(1, $leaves->count()))?->id,
            ]);
        }

        if ($row['health'] !== 'baik' && ! $done) {
            [$title, $category, $impact, $response] = self::ISSUES[$seed % count(self::ISSUES)];
            $late = $leaves->first(fn (Task $t) => $t->finish_date && $t->finish_date->lt($this->today) && $t->percent_complete < 100);
            $project->risks()->create([
                'type' => 'issue', 'title' => $title, 'category' => $category, 'impact' => $impact, 'strategy' => 'eskalasi',
                'response' => $response, 'owner_id' => $project->leader_id, 'status' => 'open', 'task_id' => $late?->id,
                'due_date' => $this->today->addDays(5), 'created_by' => $project->leader_id,
            ]);
        }

        // Permintaan perubahan pada sebagian kegiatan berjalan.
        if ($project->baseline_number > 0 && $seed % 3 === 0) {
            [$title, $description, $reason, $days, $share, $scope] = self::CHANGES[$seed % count(self::CHANGES)];
            $submitted = CarbonImmutable::parse($project->computed_start_date ?? $this->today)->addDays(20 + $seed % 20)->min($this->today->subDays(3));
            $approved = $seed % 2 === 0 || $done;
            $cr = new ChangeRequest([
                'project_id' => $project->id, 'number' => 1, 'title' => $title, 'description' => $description, 'reason' => $reason,
                'schedule_impact_days' => $days, 'cost_impact' => round((float) $project->budget_amount * $share, -3), 'scope_impact' => $scope,
                'status' => $approved ? 'approved' : 'submitted', 'requested_by' => $project->leader_id,
                'decided_by' => $approved ? $approver : null, 'decided_at' => $approved ? $submitted->addDays(2)->setTime(10, 0) : null,
                'decision_note' => $approved ? 'Sesuaikan jadwal dan anggaran, lalu laporkan di rapat kemajuan berikutnya.' : null,
                'rebaselined' => $approved,
            ]);
            $cr->created_at = $cr->updated_at = $submitted->setTime(14, 0);
            $cr->save();
            if ($approved) {
                $project->forceFill(['baseline_number' => 2, 'baseline_at' => $cr->decided_at, 'baseline_by' => $approver])->saveQuietly();
            }
        }
    }
}
