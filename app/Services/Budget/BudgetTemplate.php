<?php

namespace App\Services\Budget;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Templat XLSX yang bisa diunduh pengguna: tata letak POK RKA-K/L (ekspor SAKTI)
 * untuk pemerintah pusat, dan RAB/RKAP untuk pemda, BUMN, BUMD, dan swasta.
 * Isinya contoh fiktif.
 */
class BudgetTemplate
{
    public const POK_HEADER = ['KODE', 'PROGRAM/ KEGIATAN/ KRO/ RO/ KOMPONEN/ SUBKOMPONEN/ AKUN/ DETIL', 'VOLUME', 'SATUAN', 'HARGA SATUAN', 'JUMLAH BIAYA', 'SD/CP'];

    public const RAB_HEADER = ['KODE', 'URAIAN', 'LEVEL', 'VOLUME', 'SATUAN', 'HARGA SATUAN', 'JUMLAH', 'SUMBER DANA'];

    /** @return list<list<mixed>> */
    public static function pokSample(int $year = 2027): array
    {
        return [
            ['PETUNJUK OPERASIONAL KEGIATAN (POK)'],
            ["TAHUN ANGGARAN {$year}"],
            ['SATKER : 123456 SATUAN KERJA CONTOH'],
            [],
            self::POK_HEADER,
            [1, 2, 3, 4, 5, 6, 7],
            ['025.04.DL', 'Program Dukungan Manajemen', null, null, null, null, null],
            ['4471', 'Penyelenggaraan Layanan Pendidikan Tinggi', null, null, null, null, null],
            ['QDB', 'Fasilitasi dan Pembinaan Lembaga', null, null, null, null, null],
            ['001', 'Akreditasi Program Studi', 2, 'Prodi', null, null, null],
            ['051', 'Penyusunan Dokumen Akreditasi', null, null, null, null, null],
            ['A', 'Penyusunan Laporan Evaluasi Diri', null, null, null, null, null],
            ['521211', 'Belanja Bahan', null, null, null, null, null],
            [null, '- Penggandaan dokumen LED', 20, 'Eks', 150000, 3000000, 'RM'],
            [null, '- Konsumsi rapat penyusunan', 60, 'OK', 50000, 3000000, 'RM'],
            ['521213', 'Belanja Honor Output Kegiatan', null, null, null, null, null],
            [null, '- Honor tim penyusun', 15, 'OB', 750000, 11250000, 'RM'],
            ['B', 'Simulasi Asesmen Lapangan', null, null, null, null, null],
            ['524111', 'Belanja Perjalanan Dinas Biasa', null, null, null, null, null],
            [null, '- Transport asesor simulasi', 4, 'OK', 2500000, 10000000, 'PNBP'],
            ['052', 'Pelaksanaan Asesmen Lapangan', null, null, null, null, null],
            ['A', 'Penyambutan Asesor', null, null, null, null, null],
            ['521211', 'Belanja Bahan', null, null, null, null, null],
            [null, '- Konsumsi asesmen', 40, 'OK', 75000, 3000000, 'PNBP'],
            ['002', 'Pembinaan Tata Kelola Perguruan Tinggi', 1, 'Lembaga', null, null, null],
            ['051', 'Workshop Tata Kelola', null, null, null, null, null],
            ['A', 'Persiapan Workshop', null, null, null, null, null],
            ['521211', 'Belanja Bahan', null, null, null, null, null],
            [null, '- Seminar kit', 100, 'Paket', 120000, 12000000, 'RM'],
            ['B', 'Pelaksanaan Workshop', null, null, null, null, null],
            ['522151', 'Belanja Jasa Profesi', null, null, null, null, null],
            [null, '- Honor narasumber', 6, 'OJ', 1700000, 10200000, 'RM'],
        ];
    }

    /** @return list<list<mixed>> */
    public static function rabSample(): array
    {
        return [
            ['RENCANA ANGGARAN BIAYA (RAB) / RKAP'],
            [],
            self::RAB_HEADER,
            ['1', 'Pengembangan Sistem Informasi', 'kelompok', null, null, null, null, null],
            ['1.1', 'Analisis Kebutuhan', 'pos', null, null, null, null, null],
            [null, 'Konsultan analis', 'detail', 2, 'OB', 25000000, 50000000, 'Kas'],
            ['1.2', 'Pengembangan Aplikasi', 'pos', null, null, null, null, null],
            [null, 'Tim pengembang', 'detail', 12, 'OB', 30000000, 360000000, 'Kas'],
            ['2', 'Sosialisasi', 'kelompok', null, null, null, null, null],
            ['2.1', 'Pelatihan Pengguna', 'pos', null, null, null, null, null],
            [null, 'Sewa ruang pelatihan', 'detail', 3, 'Hari', 5000000, 15000000, 'Kas'],
        ];
    }

    public static function write(array $rows, string $path, string $title): void
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setTitle($title);
        $sheet->fromArray($rows, null, 'A1', true);

        foreach ($rows as $i => $row) {
            if (in_array($row[0] ?? null, ['KODE'], true)) {
                $r = $i + 1;
                $sheet->getStyle("A{$r}:H{$r}")->getFont()->setBold(true);
                $sheet->getStyle("A{$r}:H{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');
            }
        }
        foreach (['A' => 14, 'B' => 60, 'C' => 12, 'D' => 12, 'E' => 16, 'F' => 18, 'G' => 18, 'H' => 14] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        (new Xlsx($book))->save($path);
    }
}
