<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Demo portofolio proyek perusahaan energi (studi kasus MedcoEnergi) TA 2026: proyek migas,
 * ketenagalistrikan, pertambangan, korporat, dan pengembangan masyarakat, lengkap dengan
 * sasaran strategis dan KPI.
 *
 * Aset dan target diambil dari Laporan Tahunan 2025; anggaran per proyek, jadwal, kemajuan,
 * dan realisasi adalah karangan (lihat database/data/demo-medco-2026.php).
 */
class MedcoDemoSeeder extends PtkinDemoSeeder
{
    protected string $dataFile = 'demo-medco-2026.php';

    /** Pos biaya per jenis proyek: [kode, kode, uraian, porsi]. */
    protected const ACCOUNTS = [
        'pengeboran' => [
            ['7110', '7110', 'Sewa rig dan peralatan pengeboran', 0.38],
            ['7120', '7120', 'Casing, tubing, dan wellhead', 0.22],
            ['7130', '7130', 'Jasa pengeboran dan komplesi', 0.30],
            ['7140', '7140', 'Logistik dan dukungan laut/darat', 0.10],
        ],
        'epci' => [
            ['7210', '7210', 'Engineering dan FEED', 0.08],
            ['7220', '7220', 'Pengadaan material dan long lead item', 0.37],
            ['7230', '7230', 'Fabrikasi dan konstruksi', 0.35],
            ['7240', '7240', 'Instalasi dan commissioning', 0.15],
            ['7290', '7290', 'Manajemen proyek dan kontinjensi', 0.05],
        ],
        'pembangkit' => [
            ['7310', '7310', 'Kontrak EPC pembangkit', 0.78],
            ['7320', '7320', 'Interkoneksi dan transmisi', 0.10],
            ['7330', '7330', 'Pengadaan lahan dan perizinan', 0.04],
            ['7390', '7390', 'Owner\'s engineer dan manajemen proyek', 0.08],
        ],
        'regulasi' => [
            ['6510', '6510', 'Jasa konsultan teknis dan hukum', 0.60],
            ['6520', '6520', 'Kajian cadangan dan keekonomian', 0.30],
            ['6530', '6530', 'Perjalanan dan rapat koordinasi', 0.10],
        ],
        'sistem' => [
            ['7410', '7410', 'Lisensi perangkat lunak', 0.30],
            ['7420', '7420', 'Jasa implementasi', 0.50],
            ['7430', '7430', 'Infrastruktur dan migrasi data', 0.12],
            ['6410', '6410', 'Pelatihan pengguna', 0.08],
        ],
        'csr' => [
            ['6810', '6810', 'Bantuan sarana dan peralatan', 0.50],
            ['6820', '6820', 'Pendampingan dan pelatihan masyarakat', 0.35],
            ['6830', '6830', 'Monitoring, evaluasi, dan publikasi', 0.15],
        ],
        'kajian' => [
            ['6510', '6510', 'Jasa konsultan dan studi', 0.65],
            ['6520', '6520', 'Data, survei, dan laboratorium', 0.25],
            ['6530', '6530', 'Perjalanan dan rapat', 0.10],
        ],
        'acara' => [
            ['6610', '6610', 'Tempat dan akomodasi', 0.40],
            ['6620', '6620', 'Narasumber dan instruktur', 0.35],
            ['6630', '6630', 'Materi dan publikasi', 0.25],
        ],
        'fisik' => [
            ['7210', '7210', 'Engineering', 0.10],
            ['7220', '7220', 'Material dan peralatan', 0.45],
            ['7230', '7230', 'Pekerjaan konstruksi dan instalasi', 0.45],
        ],
        'rutin' => [
            ['6110', '6110', 'Tenaga kerja operasi', 0.35],
            ['6120', '6120', 'Pemeliharaan dan suku cadang', 0.30],
            ['6130', '6130', 'Sewa fasilitas produksi dan logistik', 0.25],
            ['6140', '6140', 'Asuransi dan HSE', 0.10],
        ],
    ];

    protected function plan(array $spec, FiscalYear $fy, callable $rand): array
    {
        $y = $fy->year;
        $len = fn (int $min, int $max) => $min + (int) floor($rand() * ($max - $min + 1));
        $day = fn (int $fromMonth, int $toMonth) => CarbonImmutable::create($y, $fromMonth, 1)
            ->addDays((int) floor($rand() * CarbonImmutable::create($y, $fromMonth, 1)->diffInDays(CarbonImmutable::create($y, $toMonth, 28))));
        $name = Str::lower($spec['name']);

        return match ($spec['kind']) {
            'pengeboran' => [
                Str::contains($name, 'tarakan') ? CarbonImmutable::create($y, 6, 1) : $day(1, 4),
                [
                    ['Desain sumur dan persetujuan AFE', $len(10, 15), null],
                    ['Pengadaan rig dan long lead item', $len(20, 35), 0],
                    ['Mobilisasi rig', $len(5, 10), 1],
                    ['Spud-in', 0, 2],
                    ['Pengeboran', $len(35, 70), 3],
                    ['Komplesi dan uji sumur', $len(10, 15), 4],
                    ['Put on production', 0, 5],
                ],
            ],
            'epci' => Str::contains($name, 'bualuang') ? [
                CarbonImmutable::create($y, 1, 5),
                [
                    ['Fabrikasi modul topside', 45, null],
                    ['Instalasi offshore dan hook-up', 25, 0],
                    ['Commissioning', 15, 1],
                    ['Mulai berproduksi (onstream)', 0, 2],
                    ['Uji kinerja dan serah terima ke operasi', 20, 3],
                ],
            ] : [
                $day(1, 3),
                [
                    ['Desain rinci (detail engineering)', $len(30, 45), null],
                    ['Pengadaan long lead item', $len(45, 60), 0],
                    ['Fabrikasi dan konstruksi', $len(60, 90), 1],
                    ['Instalasi dan hook-up', $len(25, 40), 2],
                    ['Commissioning', $len(15, 25), 3],
                    ['Mulai beroperasi', 0, 4],
                ],
            ],
            'pembangkit' => [
                $day(1, 3),
                [
                    ['Finalisasi kontrak EPC dan pendanaan', $len(20, 30), null],
                    ['Kontrak EPC efektif', 0, 0],
                    ['Pekerjaan sipil dan fondasi', $len(40, 60), 1],
                    ['Pemasangan mekanikal dan elektrikal', $len(50, 80), 2],
                    ['Interkoneksi jaringan', $len(15, 25), 3],
                    ['Commissioning dan uji keandalan', $len(15, 25), 4],
                    ['Commercial Operation Date (COD)', 0, 5],
                ],
            ],
            'regulasi' => [
                $day(1, 6),
                [
                    ['Penyusunan dokumen dan kajian pendukung', $len(15, 25), null],
                    ['Pengajuan ke regulator', 0, 0],
                    ['Evaluasi dan pembahasan dengan regulator', $len(40, 80), 1],
                    ['Persetujuan diterima', 0, 2],
                ],
            ],
            'sistem' => [
                CarbonImmutable::create($y, 1, 5),
                [
                    ['Desain proses bisnis (blueprint)', $len(25, 35), null],
                    ['Konfigurasi dan pengembangan', $len(60, 80), 0],
                    ['Migrasi data', $len(20, 30), 1],
                    ['Uji integrasi dan UAT', $len(25, 35), 2],
                    ['Pelatihan pengguna', $len(10, 15), 3],
                    ['Go-live', 0, 4],
                ],
            ],
            'csr' => [
                $day(1, 5),
                [
                    ['Pemetaan sosial dan penyusunan program', $len(10, 20), null],
                    ['Pelaksanaan program bersama mitra', $len(60, 110), 0],
                    ['Monitoring dan evaluasi', $len(10, 15), 1],
                    ['Laporan dampak sosial', 0, 2],
                ],
            ],
            default => parent::plan($spec, $fy, $rand),
        };
    }
}
