<?php

namespace Database\Seeders;

use App\Models\BudgetLine;
use App\Models\FiscalYear;
use App\Models\Goal;
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
use Illuminate\Support\Str;

/**
 * Demo pemantauan kegiatan sebuah PTKIN pada TA 2026: ±100 kegiatan dari belasan
 * fakultas/unit, sasaran program dengan IKU, serta jadwal, kemajuan, dan realisasi.
 *
 * Seluruh angka adalah karangan (lihat database/data/demo-ptkin-2026.php). Hasilnya
 * sama setiap kali dijalankan karena bilangan acaknya diturunkan dari nama kegiatan.
 */
class PtkinDemoSeeder extends Seeder
{
    private ProjectScheduler $scheduler;

    private PortfolioService $portfolio;

    private CarbonImmutable $asOf;

    private const PALETTE = ['#10B981', '#D4A72C', '#38BDF8', '#F97316', '#A78BFA', '#14B8A6', '#EC4899', '#84CC16'];

    /** Akun belanja per jenis kegiatan: [kode BLU, kode non-BLU, uraian, porsi]. */
    private const ACCOUNTS = [
        'acara' => [
            ['525112', '521211', 'Belanja bahan dan konsumsi', 0.30],
            ['525113', '521213', 'Honor narasumber dan panitia', 0.30],
            ['525115', '524111', 'Belanja perjalanan', 0.15],
            ['525119', '521219', 'Sewa tempat, perlengkapan, dan publikasi', 0.25],
        ],
        'kajian' => [
            ['525112', '521211', 'Belanja bahan', 0.15],
            ['525113', '521213', 'Honor tim pelaksana', 0.35],
            ['525113', '522151', 'Jasa profesi dan asesor', 0.30],
            ['525115', '524111', 'Belanja perjalanan', 0.20],
        ],
        'fisik' => [
            ['525113', '522191', 'Jasa perencanaan dan pengawasan', 0.08],
            ['537112', '533111', 'Belanja modal', 0.92],
        ],
        'rutin' => [
            ['525112', '521111', 'Keperluan perkantoran', 0.35],
            ['525113', '521115', 'Honor dan jasa layanan', 0.35],
            ['525121', '523111', 'Pemeliharaan', 0.15],
            ['525119', '522111', 'Langganan daya dan jasa', 0.15],
        ],
    ];

    public function run(): void
    {
        $this->scheduler = app(ProjectScheduler::class);
        $this->portfolio = app(PortfolioService::class);
        $data = require database_path('data/demo-ptkin-2026.php');
        $year = $data['fiscal_year']['year'];
        $this->asOf = CarbonImmutable::today()->min(CarbonImmutable::create($year, 12, 31));

        $org = Organization::create([
            'name' => $data['organization']['name'],
            'short_name' => $data['organization']['short_name'],
            'sector' => 'perguruan_tinggi',
            'code' => $data['organization']['code'],
        ]);
        $admin = User::firstOrCreate(['email' => 'admin@tadbira.test'], [
            'name' => 'Admin Tadbira', 'password' => Hash::make('password'), 'email_verified_at' => now(),
        ]);
        $org->users()->attach($admin->id, ['role' => 'admin']);
        $admin->update(['current_organization_id' => $org->id]);

        $fy = $org->fiscalYears()->create([
            'year' => $year, 'code' => "TA {$year}", 'start_date' => "{$year}-01-01", 'end_date' => "{$year}-12-31",
            'status' => 'active', 'ceiling_amount' => $data['fiscal_year']['ceiling'],
        ]);

        $goals = $this->goals($org, $fy, $data);

        $n = 0;
        foreach ($data['units'] as $unit => [$unitName, $activities]) {
            $operator = User::firstOrCreate(
                ['email' => Str::slug($unit).'@'.$data['organization']['email_domain']],
                ['name' => "Operator {$unit}", 'password' => Hash::make('password'), 'email_verified_at' => now()],
            );
            $org->users()->attach($operator->id, ['role' => 'anggota']);

            foreach ($activities as $i => [$name, $kind, $goalCode, $millions, $source]) {
                $this->kegiatan($org, $fy, [
                    'code' => sprintf('%s-%02d', strtoupper(Str::before(Str::slug($unit), '-')), $i + 1),
                    'name' => $name,
                    'unit' => $unit,
                    'unit_name' => $unitName,
                    'kind' => $kind,
                    'goal_id' => $goals[$goalCode] ?? null,
                    'budget' => $millions * 1_000_000,
                    'source' => $source,
                    'color' => self::PALETTE[$n++ % count(self::PALETTE)],
                ], $operator->id, $admin->id);
            }
        }
    }

    /** @return array<string, int> kode sasaran → id */
    private function goals(Organization $org, FiscalYear $fy, array $data): array
    {
        $ids = [];
        foreach ($data['goals'] as $pos => [$code, $title, $color, $indicators]) {
            $goal = Goal::create([
                'organization_id' => $org->id, 'fiscal_year_id' => $fy->id,
                'code' => $code, 'title' => $title, 'color' => $color, 'position' => $pos,
            ]);
            foreach ($indicators as $i => [$name, $unit, $target, $baseline, $baselineScore]) {
                $progress = $data['indicator_progress'][$code][$i] ?? null;
                $goal->indicators()->create([
                    'name' => $name, 'unit' => $unit, 'target' => $target,
                    'realization' => $progress[0] ?? null, 'score' => $progress[1] ?? null,
                    'baseline_realization' => $baseline, 'baseline_score' => $baselineScore,
                    'position' => $i,
                ]);
            }
            $ids[$code] = $goal->id;
        }

        return $ids;
    }

    private function kegiatan(Organization $org, FiscalYear $fy, array $spec, int $leaderId, int $adminId): void
    {
        $rand = $this->random($spec['code'].$spec['name']);
        [$start, $plan] = $this->plan($spec, $fy, $rand);

        $project = Project::create([
            'organization_id' => $org->id,
            'fiscal_year_id' => $fy->id,
            'goal_id' => $spec['goal_id'],
            'leader_id' => $leaderId,
            'created_by' => $adminId,
            'code' => $spec['code'],
            'name' => $spec['name'],
            'description' => "Kegiatan {$spec['unit_name']} tahun anggaran {$fy->year}.",
            'category' => $spec['unit'],
            'color' => $spec['color'],
            'status' => 'planning',
            'scheduling_direction' => 'forward',
            'start_date' => $start->toDateString(),
            'budget_amount' => $spec['budget'],
        ]);
        $project->members()->attach($leaderId, ['role' => 'leader']);

        $tasks = [];
        foreach ($plan as $pos => [$title, $days, $pred]) {
            $tasks[$pos] = Task::create([
                'project_id' => $project->id,
                'kind' => $days === 0 ? 'milestone' : 'activity',
                'title' => $title,
                'duration_days' => $days,
                'position' => $pos + 1,
            ]);
            $tasks[$pos]->assignees()->attach($leaderId, ['role' => 'responsible']);
            if ($pred !== null) {
                TaskDependency::create(['project_id' => $project->id, 'predecessor_id' => $tasks[$pred]->id, 'successor_id' => $tasks[$pos]->id]);
            }
        }
        $this->scheduler->reschedule($project);

        // Kecepatan: sebagian besar sesuai jadwal, sebagian tertinggal.
        $roll = $rand();
        $pace = match (true) {
            $roll < 0.62 => 0.96 + $rand() * 0.08,
            $roll < 0.84 => 0.82 + $rand() * 0.12,
            default => 0.45 + $rand() * 0.33,
        };
        foreach ($tasks as $task) {
            $task->refresh();
            $planned = $this->portfolio->plannedPercent(collect([$task]), $this->asOf);
            $pct = (int) min(100, round($planned * $pace));
            if ($task->kind === 'milestone') {
                $pct = $planned >= 100 && $pace >= 0.9 ? 100 : 0;
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

        $project->update(['status' => match (true) {
            $project->percent_complete >= 100 => $rand() < 0.7 ? 'closed' : 'closing',
            $project->percent_complete > 0 => 'executing',
            default => 'planning',
        }]);

        $this->budget($project, $fy, $spec, $rand);
    }

    /**
     * Jadwal karangan menurut jenis kegiatan: [tanggal mulai, [judul, lama hari kerja, pendahulu]].
     */
    private function plan(array $spec, FiscalYear $fy, callable $rand): array
    {
        $y = $fy->year;
        $day = fn (int $fromMonth, int $toMonth) => CarbonImmutable::create($y, $fromMonth, 1)
            ->addDays((int) floor($rand() * CarbonImmutable::create($y, $fromMonth, 1)->diffInDays(CarbonImmutable::create($y, $toMonth, 28))));
        $len = fn (int $min, int $max) => $min + (int) floor($rand() * ($max - $min + 1));
        $name = Str::lower($spec['name']);

        if ($spec['kind'] === 'rutin') {
            $days = app(ProjectScheduler::class)->calendarFor($fy->organization_id)
                ->workingDaysBetween(CarbonImmutable::create($y, 1, 1), CarbonImmutable::create($y, 12, 31));

            return [CarbonImmutable::create($y, 1, 2), [
                ['Pelaksanaan layanan sepanjang tahun', $days, null],
            ]];
        }

        if ($spec['kind'] === 'fisik') {
            return [$day(1, 5), [
                ['Perencanaan dan penyusunan DED/spesifikasi', $len(15, 25), null],
                ['Pengadaan barang/jasa', $len(20, 30), 0],
                ['Kontrak ditandatangani', 0, 1],
                ['Pelaksanaan pekerjaan', $len(45, 90), 2],
                ['Pemeriksaan dan serah terima', $len(5, 8), 3],
                ['BAST diterbitkan', 0, 4],
            ]];
        }

        if ($spec['kind'] === 'kajian') {
            return [$day(1, 8), [
                ['Penyusunan TOR dan instrumen', $len(10, 15), null],
                ['Pelaksanaan', $len(30, 70), 0],
                ['Penyusunan laporan', $len(10, 15), 1],
                ['Laporan diterima', 0, 2],
            ]];
        }

        // Acara: beberapa jenis punya musim yang lazim di kampus.
        $start = match (true) {
            Str::contains($name, ['pbak', 'sospem', 'mahasiswa baru']) => CarbonImmutable::create($y, 7, 13)->addDays($len(0, 10)),
            Str::contains($name, ['seleksi mahasiswa baru']) => CarbonImmutable::create($y, 4, 6),
            Str::contains($name, ['promosi', 'roadshow']) => CarbonImmutable::create($y, 1, 12)->addDays($len(0, 20)),
            Str::contains($name, ['wisuda']) => CarbonImmutable::create($y, 10, 19),
            Str::contains($name, ['kkn']) => CarbonImmutable::create($y, 5, 11)->addDays($len(0, 10)),
            default => $day(1, 10),
        };
        $event = Str::contains($name, ['kkn', 'pertukaran', 'magang', 'plp', 'seleksi']) ? $len(20, 45) : $len(1, 3);

        return [$start, [
            ['Persiapan dan koordinasi panitia', $len(10, 20), null],
            ['Pelaksanaan kegiatan', $event, 0],
            ['Kegiatan terlaksana', 0, 1],
            ['Laporan dan SPJ', $len(5, 10), 2],
        ]];
    }

    /** Rincian anggaran per akun dan realisasi bulanan sebanding dengan kemajuan. */
    private function budget(Project $project, FiscalYear $fy, array $spec, callable $rand): void
    {
        $blu = $spec['source'] === 'BLU';
        $root = BudgetLine::create([
            'project_id' => $project->id, 'fiscal_year_id' => $fy->id, 'level' => 'komponen',
            'code' => $project->code, 'description' => $project->name, 'amount' => $spec['budget'],
            'funding_source' => $spec['source'], 'position' => 0,
        ]);

        // Serapan mengikuti kemajuan; kegiatan selesai menyisakan sedikit efisiensi.
        $progress = $project->percent_complete / 100;
        $absorb = match (true) {
            $progress >= 1 => 0.9 + $rand() * 0.1,
            default => min(1, $progress * (0.6 + $rand() * 0.45)),
        };

        $start = CarbonImmutable::parse($project->computed_start_date);
        $end = CarbonImmutable::parse($project->computed_finish_date)->min($this->asOf);
        $months = $end->lt($start) ? 0 : (int) $start->startOfMonth()->diffInMonths($end->startOfMonth()) + 1;

        foreach (self::ACCOUNTS[$spec['kind']] as $pos => [$bluCode, $code, $desc, $share]) {
            if ($spec['kind'] === 'fisik' && $share > 0.5 && Str::contains(Str::lower($spec['name']), ['peralatan', 'perlengkapan', 'jaringan', 'pengadaan', 'galeri'])) {
                [$bluCode, $code, $desc] = ['537111', '532111', 'Belanja modal peralatan dan mesin'];
            }
            $line = BudgetLine::create([
                'project_id' => $project->id, 'fiscal_year_id' => $fy->id, 'parent_id' => $root->id,
                'level' => 'akun', 'code' => $blu ? $bluCode : $code, 'description' => $desc,
                'amount' => round($spec['budget'] * $share), 'funding_source' => $spec['source'], 'position' => $pos + 1,
            ]);

            $total = round($line->amount * $absorb);
            for ($m = 0; $m < $months && $total > 0; $m++) {
                $date = $start->startOfMonth()->addMonths($m)->addDays(20)->min($this->asOf);
                $line->expenditures()->create([
                    'status' => 'paid',
                    'amount' => round($total / $months),
                    'transaction_date' => $date,
                    'reference' => sprintf('SP2D-%s-%05d', $date->format('ym'), $line->id * 7 + $m),
                ]);
            }
        }
    }

    /** Pembangkit acak deterministik per kegiatan. */
    private function random(string $seed): callable
    {
        $state = crc32($seed) ?: 1;

        return function () use (&$state): float {
            $state = ($state * 1103515245 + 12345) % 2147483648;

            return $state / 2147483648;
        };
    }
}
