<?php

namespace Database\Seeders;

use App\Models\BudgetLine;
use App\Models\FiscalYear;
use App\Models\Holiday;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\PortfolioService;
use App\Services\Scheduling\ProjectScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh fiktif: satu satuan kerja kementerian dan satu BUMN,
 * masing-masing dengan beberapa proyek dalam tahun anggaran berjalan.
 */
class DemoSeeder extends Seeder
{
    private ProjectScheduler $scheduler;

    private CarbonImmutable $today;

    public function run(): void
    {
        $this->scheduler = app(ProjectScheduler::class);
        $this->today = CarbonImmutable::today();
        $year = (int) $this->today->format('Y');

        $this->holidays([$year, $year + 1]);

        $admin = User::firstOrCreate(['email' => 'admin@tadbira.test'], [
            'name' => 'Admin Tadbira',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $staff = collect(['Aisyah Rahmawati', 'Budi Santoso', 'Fatimah Zahra', 'Hasan Basri', 'Nurul Hidayah'])
            ->map(fn ($name) => User::firstOrCreate(
                ['email' => strtolower(strtok($name, ' ')).'@tadbira.test'],
                ['name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now()],
            ));

        $satker = Organization::create([
            'name' => 'Direktorat Contoh, Kementerian Contoh',
            'short_name' => 'Dit. Contoh',
            'sector' => 'kementerian',
            'code' => '123456',
        ]);
        $bumn = Organization::create([
            'name' => 'PT Contoh Nusantara (Persero)',
            'short_name' => 'PT Contoh',
            'sector' => 'bumn',
        ]);

        foreach ([$satker, $bumn] as $org) {
            $org->users()->attach($admin->id, ['role' => 'admin']);
            $staff->each(fn ($u) => $org->users()->attach($u->id, ['role' => 'anggota']));
        }
        $admin->update(['current_organization_id' => $satker->id]);

        $ta = $satker->fiscalYears()->create([
            'year' => $year, 'code' => "TA {$year}", 'start_date' => "{$year}-01-01", 'end_date' => "{$year}-12-31",
            'status' => 'active', 'ceiling_amount' => 4_200_000_000,
        ]);
        $satker->fiscalYears()->create([
            'year' => $year + 1, 'code' => 'TA '.($year + 1), 'start_date' => ($year + 1).'-01-01', 'end_date' => ($year + 1).'-12-31',
            'status' => 'planning', 'ceiling_amount' => 4_800_000_000,
        ]);

        $people = $staff->pluck('id')->all();

        foreach ($this->satkerProjects($year) as $i => $spec) {
            $this->makeProject($satker, $ta, $spec, $admin->id, $people[$i % count($people)], $people);
        }

        $taBumn = $bumn->fiscalYears()->create([
            'year' => $year, 'code' => "RKAP {$year}", 'start_date' => "{$year}-01-01", 'end_date' => "{$year}-12-31",
            'status' => 'active', 'ceiling_amount' => 12_500_000_000,
        ]);
        foreach ($this->bumnProjects($year) as $i => $spec) {
            $this->makeProject($bumn, $taBumn, $spec, $admin->id, $people[($i + 2) % count($people)], $people);
        }

        // Templat
        foreach ($this->templates() as $spec) {
            $this->makeTemplate($satker, $spec, $admin->id);
        }
    }

    private function holidays(array $years): void
    {
        // Hanya libur nasional bertanggal tetap. Libur keagamaan dan cuti bersama
        // mengikuti SKB tiap tahun dan ditambahkan admin.
        foreach ($years as $y) {
            foreach ([
                '01-01' => 'Tahun Baru Masehi',
                '05-01' => 'Hari Buruh Internasional',
                '06-01' => 'Hari Lahir Pancasila',
                '08-17' => 'Hari Kemerdekaan RI',
                '12-25' => 'Hari Raya Natal',
            ] as $md => $name) {
                Holiday::firstOrCreate(['organization_id' => null, 'date' => "{$y}-{$md}"], ['name' => $name]);
            }
        }
    }

    /**
     * Spesifikasi proyek: tahap → aktivitas [judul, lama (hari kerja), pendahulu (indeks global, opsional)].
     * 'pace' = rasio kemajuan nyata terhadap rencana (1 = sesuai jadwal).
     */
    private function satkerProjects(int $y): array
    {
        return [
            [
                'code' => '4471.QDB.001', 'name' => 'Akreditasi Program Studi Informatika', 'category' => 'Penjaminan mutu',
                'start' => "{$y}-02-02", 'pace' => 1.05, 'budget' => 450_000_000, 'color' => '#10B981',
                'phases' => [
                    'Persiapan' => [['Pembentukan tim akreditasi', 5], ['Pelatihan penyusunan dokumen', 3, [0]]],
                    'Penyusunan Dokumen' => [['Penyusunan Laporan Evaluasi Diri', 30, [1]], ['Penyusunan Laporan Kinerja Program Studi', 35, [1]], ['Telaah internal dokumen', 10, [2, 3]]],
                    'Asesmen' => [['Unggah dokumen ke SIMAK', 2, [4]], ['Asesmen kecukupan', 20, [5]], ['Asesmen lapangan', 3, [6]], ['Keputusan akreditasi', 0, [7]]],
                    'Tindak Lanjut' => [['Penyusunan rencana perbaikan', 15, [7]]],
                ],
            ],
            [
                'code' => '4471.QDB.002', 'name' => 'Renovasi Laboratorium Komputer', 'category' => 'Sarana prasarana',
                'start' => "{$y}-04-06", 'pace' => 0.55, 'budget' => 1_150_000_000, 'color' => '#F97316',
                'phases' => [
                    'Perencanaan' => [['Survei kondisi ruang', 5], ['Penyusunan DED dan RAB', 15, [0]], ['Reviu APIP', 10, [1]]],
                    'Pengadaan' => [['Pengadaan jasa konstruksi', 30, [2]], ['Penandatanganan kontrak', 0, [3]]],
                    'Pelaksanaan' => [['Pembongkaran', 10, [4]], ['Pekerjaan sipil dan listrik', 45, [5]], ['Pemasangan jaringan', 15, [6]], ['Pengadaan dan pemasangan perangkat', 20, [6]]],
                    'Serah Terima' => [['Uji fungsi', 5, [7, 8]], ['Berita acara serah terima', 0, [9]]],
                ],
            ],
            [
                'code' => '4471.QDB.003', 'name' => 'Seminar Nasional Sains dan Teknologi', 'category' => 'Kegiatan ilmiah',
                'finish' => "{$y}-11-26", 'pace' => 1.0, 'budget' => 185_000_000, 'color' => '#38BDF8',
                'phases' => [
                    'Persiapan' => [['Pembentukan panitia', 5], ['Penetapan tema dan pembicara', 10, [0]], ['Publikasi dan undangan makalah', 25, [1]]],
                    'Seleksi Makalah' => [['Penerimaan abstrak', 20, [2]], ['Reviu makalah', 15, [3]], ['Pengumuman makalah diterima', 0, [4]]],
                    'Pelaksanaan' => [['Persiapan tempat dan logistik', 10, [5]], ['Hari pelaksanaan seminar', 2, [6]]],
                    'Pascakegiatan' => [['Penyusunan prosiding', 20, [7]], ['Laporan pertanggungjawaban', 10, [7]]],
                ],
            ],
            [
                'code' => '4471.QDB.004', 'name' => 'Pengadaan Perangkat Teknologi Informasi', 'category' => 'Pengadaan',
                'start' => "{$y}-01-12", 'pace' => 1.0, 'budget' => 890_000_000, 'color' => '#D4A72C', 'status' => 'closed',
                'phases' => [
                    'Perencanaan Pengadaan' => [['Identifikasi kebutuhan', 10], ['Penyusunan spesifikasi teknis', 10, [0]], ['Penyusunan HPS', 5, [1]]],
                    'Pemilihan Penyedia' => [['E-purchasing katalog elektronik', 15, [2]], ['Kontrak', 0, [3]]],
                    'Serah Terima' => [['Pengiriman barang', 20, [4]], ['Pemeriksaan barang', 3, [5]], ['Pembayaran', 5, [6]]],
                ],
            ],
            [
                'code' => '4471.QDB.005', 'name' => 'Penyusunan Rencana Strategis 2027–2031', 'category' => 'Perencanaan',
                'start' => "{$y}-07-13", 'pace' => 0.85, 'budget' => 240_000_000, 'color' => '#A78BFA',
                'phases' => [
                    'Analisis Lingkungan' => [['Evaluasi Renstra periode lalu', 15], ['Analisis SWOT', 10, [0]]],
                    'Perumusan' => [['Perumusan visi, misi, dan tujuan', 10, [1]], ['Penetapan sasaran dan indikator', 15, [2]], ['Uji publik', 5, [3]]],
                    'Penetapan' => [['Finalisasi dokumen', 10, [4]], ['Penetapan Renstra', 0, [5]]],
                ],
            ],
            [
                'code' => '4471.QDB.006', 'name' => 'Digitalisasi Arsip Kepegawaian', 'category' => 'Transformasi digital',
                'start' => "{$y}-09-01", 'pace' => 0.95, 'budget' => 310_000_000, 'color' => '#14B8A6',
                'phases' => [
                    'Persiapan' => [['Inventarisasi arsip', 15], ['Penyiapan perangkat pindai', 5]],
                    'Pelaksanaan' => [['Pemindaian dan pengindeksan', 50, [0, 1]], ['Kendali mutu hasil pindai', 45, [0, 1]]],
                    'Penyelesaian' => [['Integrasi ke aplikasi kepegawaian', 10, [2, 3]], ['Serah terima', 0, [4]]],
                ],
            ],
        ];
    }

    private function bumnProjects(int $y): array
    {
        return [
            [
                'code' => 'PRJ-01', 'name' => 'Implementasi ERP Keuangan', 'category' => 'Transformasi digital',
                'start' => "{$y}-02-02", 'pace' => 0.8, 'budget' => 6_500_000_000, 'color' => '#10B981',
                'phases' => [
                    'Inisiasi' => [['Pemilihan mitra implementasi', 30], ['Kick-off', 0, [0]]],
                    'Desain' => [['Business blueprint', 40, [1]], ['Desain integrasi', 20, [2]]],
                    'Realisasi' => [['Konfigurasi dan pengembangan', 60, [3]], ['Migrasi data', 30, [3]], ['Uji penerimaan pengguna', 20, [4, 5]]],
                    'Go-live' => [['Go-live', 0, [6]], ['Pendampingan pascaimplementasi', 30, [7]]],
                ],
            ],
            [
                'code' => 'PRJ-02', 'name' => 'Pembangunan Gudang Regional', 'category' => 'Investasi',
                'start' => "{$y}-03-02", 'pace' => 1.0, 'budget' => 5_200_000_000, 'color' => '#F97316',
                'phases' => [
                    'Perizinan' => [['Studi kelayakan', 20], ['Perizinan bangunan', 30, [0]]],
                    'Konstruksi' => [['Pekerjaan struktur', 80, [1]], ['Pekerjaan arsitektur dan MEP', 50, [2]]],
                    'Operasional' => [['Uji coba operasional', 10, [3]]],
                ],
            ],
        ];
    }

    private function templates(): array
    {
        return [
            [
                'name' => 'Seminar / Konferensi', 'category' => 'Kegiatan ilmiah',
                'description' => 'Persiapan, seleksi makalah, pelaksanaan, dan pertanggungjawaban seminar.',
                'phases' => $this->satkerProjects(2000)[2]['phases'],
            ],
            [
                'name' => 'Akreditasi Program Studi', 'category' => 'Penjaminan mutu',
                'description' => 'Penyusunan LED/LKPS, asesmen kecukupan, asesmen lapangan, dan tindak lanjut.',
                'phases' => $this->satkerProjects(2000)[0]['phases'],
            ],
            [
                'name' => 'Pengadaan Barang (E-purchasing)', 'category' => 'Pengadaan',
                'description' => 'Identifikasi kebutuhan sampai pembayaran.',
                'phases' => $this->satkerProjects(2000)[3]['phases'],
            ],
        ];
    }

    private function makeProject(Organization $org, FiscalYear $fy, array $spec, int $leaderId, int $mainPerson, array $people): Project
    {
        $project = Project::create([
            'organization_id' => $org->id,
            'fiscal_year_id' => $fy->id,
            'leader_id' => $mainPerson,
            'created_by' => $leaderId,
            'code' => $spec['code'],
            'name' => $spec['name'],
            'category' => $spec['category'],
            'color' => $spec['color'],
            'status' => $spec['status'] ?? 'executing',
            'scheduling_direction' => isset($spec['finish']) ? 'backward' : 'forward',
            'start_date' => $spec['start'] ?? null,
            'target_finish_date' => $spec['finish'] ?? null,
            'budget_amount' => $spec['budget'],
        ]);
        $project->members()->attach($mainPerson, ['role' => 'leader']);

        $leaves = $this->buildTasks($project, $spec['phases'], $people);
        $this->scheduler->reschedule($project);

        // Kemajuan: rencana per hari ini × kecepatan proyek.
        $portfolio = app(PortfolioService::class);
        foreach ($leaves as $task) {
            $task->refresh();
            $planned = $portfolio->plannedPercent(collect([$task]), $this->today);
            $pct = ($spec['status'] ?? null) === 'closed' ? 100 : (int) min(100, round($planned * $spec['pace']));
            if ($task->kind === 'milestone') {
                $pct = $planned >= 100 && $spec['pace'] >= 0.9 ? 100 : 0;
            }
            $task->update([
                'percent_complete' => $pct,
                'status' => $pct >= 100 ? 'completed' : ($pct > 0 ? 'in_progress' : 'not_started'),
                'actual_start' => $pct > 0 ? $task->start_date : null,
                'actual_finish' => $pct >= 100 ? $task->finish_date : null,
            ]);
        }
        $this->scheduler->reschedule($project);
        $project->refresh();

        if ($project->percent_complete >= 100 && $project->status !== 'closed') {
            $project->update(['status' => 'closing']);
        }

        $this->budget($project, $fy, $spec);

        return $project;
    }

    /** @return list<Task> */
    private function buildTasks(Project $project, array $phases, array $people = []): array
    {
        $position = 0;
        $leaves = [];
        foreach ($phases as $phaseTitle => $activities) {
            $phase = Task::create([
                'project_id' => $project->id, 'kind' => 'phase', 'title' => $phaseTitle, 'position' => ++$position, 'duration_days' => 1,
            ]);
            foreach ($activities as $a) {
                $task = Task::create([
                    'project_id' => $project->id,
                    'parent_id' => $phase->id,
                    'kind' => $a[1] === 0 ? 'milestone' : 'activity',
                    'title' => $a[0],
                    'duration_days' => $a[1],
                    'position' => ++$position,
                ]);
                foreach ($a[2] ?? [] as $predIndex) {
                    TaskDependency::create([
                        'project_id' => $project->id,
                        'predecessor_id' => $leaves[$predIndex]->id,
                        'successor_id' => $task->id,
                    ]);
                }
                if ($people !== []) {
                    $task->assignees()->attach($people[count($leaves) % count($people)], ['role' => 'responsible']);
                }
                $leaves[] = $task;
            }
        }

        return $leaves;
    }

    private function makeTemplate(Organization $org, array $spec, int $userId): void
    {
        $template = Project::create([
            'organization_id' => $org->id,
            'name' => $spec['name'],
            'category' => $spec['category'],
            'description' => $spec['description'],
            'is_template' => true,
            'created_by' => $userId,
        ]);
        $this->buildTasks($template, $spec['phases']);
        $this->scheduler->renumberAndSave($template);
    }

    /** Rincian anggaran ringkas berpola POK/RAB dan realisasi sesuai kemajuan. */
    private function budget(Project $project, FiscalYear $fy, array $spec): void
    {
        $gov = $project->organization->isGovernment();
        $root = BudgetLine::create([
            'project_id' => $project->id, 'fiscal_year_id' => $fy->id,
            'level' => $gov ? 'ro' : 'kelompok', 'code' => $project->code, 'description' => $project->name,
            'amount' => $spec['budget'], 'position' => 0,
        ]);

        $items = $gov
            ? [['521211', 'Belanja Bahan', 0.15], ['521213', 'Belanja Honor Output Kegiatan', 0.2], ['524111', 'Belanja Perjalanan Dinas Biasa', 0.1], ['522151', 'Belanja Jasa Profesi', 0.15], ['532111', 'Belanja Modal Peralatan dan Mesin', 0.4]]
            : [['1', 'Jasa konsultan', 0.3], ['2', 'Material dan perangkat', 0.5], ['3', 'Operasional proyek', 0.2]];

        $phases = $project->tasks()->whereNull('parent_id')->orderBy('position')->get();
        $pos = 0;
        foreach ($items as $i => [$code, $desc, $share]) {
            $line = BudgetLine::create([
                'project_id' => $project->id, 'fiscal_year_id' => $fy->id, 'parent_id' => $root->id,
                'task_id' => $phases[$i % max(1, $phases->count())]->id ?? null,
                'level' => $gov ? 'akun' : 'pos', 'code' => $code, 'description' => $desc,
                'amount' => round($spec['budget'] * $share), 'funding_source' => $gov ? 'RM' : 'Kas', 'position' => ++$pos,
            ]);

            // Realisasi bulanan sampai bulan lalu, sebanding dengan kemajuan proyek.
            $paidShare = min(1, $project->percent_complete / 100 * [0.95, 0.8, 1.05, 0.65, 0.9][$i % 5]);
            $start = CarbonImmutable::parse($project->computed_start_date ?? $fy->start_date);
            $end = min($this->today, CarbonImmutable::parse($project->computed_finish_date ?? $fy->end_date));
            $months = max(1, (int) $start->startOfMonth()->diffInMonths($end->startOfMonth()) + 1);
            $total = $line->amount * $paidShare;
            for ($m = 0; $m < $months && $total > 0; $m++) {
                $date = $start->startOfMonth()->addMonths($m)->addDays(24);
                if ($date->gt($this->today)) {
                    break;
                }
                $line->expenditures()->create([
                    'status' => 'paid', 'amount' => round($total / $months), 'transaction_date' => $date,
                    'reference' => sprintf('SP2D-%s-%03d', $date->format('ym'), $line->id * 10 + $m),
                ]);
            }
        }
    }
}
