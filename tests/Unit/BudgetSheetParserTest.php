<?php

namespace Tests\Unit;

use App\Services\Budget\BudgetSheetParser;
use App\Services\Budget\BudgetTemplate;
use Tests\TestCase;

class BudgetSheetParserTest extends TestCase
{
    public function test_pok_levels_are_recognised_from_codes(): void
    {
        $parsed = (new BudgetSheetParser)->parseRows(BudgetTemplate::pokSample());
        $levels = array_column($parsed['rows'], 'level', 'description');

        $this->assertSame('pok', $parsed['format']);
        $this->assertSame('program', $levels['Program Dukungan Manajemen']);
        $this->assertSame('kegiatan', $levels['Penyelenggaraan Layanan Pendidikan Tinggi']);
        $this->assertSame('kro', $levels['Fasilitasi dan Pembinaan Lembaga']);
        $this->assertSame('ro', $levels['Akreditasi Program Studi']);
        $this->assertSame('ro', $levels['Pembinaan Tata Kelola Perguruan Tinggi']);
        $this->assertSame('komponen', $levels['Penyusunan Dokumen Akreditasi']);
        $this->assertSame('subkomponen', $levels['Penyusunan Laporan Evaluasi Diri']);
        $this->assertSame('detail', $levels['Honor tim penyusun']);
    }

    public function test_pok_tree_and_totals(): void
    {
        $rows = (new BudgetSheetParser)->parseRows(BudgetTemplate::pokSample())['rows'];
        $byDesc = [];
        foreach ($rows as $r) {
            $byDesc[$r['description']] ??= $r;
        }

        // RO 001 = 3.000.000 + 3.000.000 + 11.250.000 + 10.000.000 + 3.000.000
        $this->assertEquals(30250000, $byDesc['Akreditasi Program Studi']['amount']);
        $this->assertEquals(22200000, $byDesc['Pembinaan Tata Kelola Perguruan Tinggi']['amount']);
        $this->assertEquals(52450000, $byDesc['Program Dukungan Manajemen']['amount']);

        // Komponen 052 berada di bawah RO 001, bukan di bawah komponen 051.
        $komponen052 = $byDesc['Pelaksanaan Asesmen Lapangan'];
        $this->assertSame($byDesc['Akreditasi Program Studi']['key'], $komponen052['parent_key']);
        $this->assertSame('PNBP', $byDesc['Transport asesor simulasi']['funding_source']);
    }

    public function test_rab_template_uses_level_column(): void
    {
        $parsed = (new BudgetSheetParser)->parseRows(BudgetTemplate::rabSample());

        $this->assertSame('rab', $parsed['format']);
        $this->assertEquals(410000000, $parsed['rows'][0]['amount']);
        $this->assertSame('pos', $parsed['rows'][1]['level']);
    }

    public function test_indonesian_number_format(): void
    {
        $p = new BudgetSheetParser;

        $this->assertSame(1500000.0, $p->number('1.500.000'));
        $this->assertSame(1500000.5, $p->number('1.500.000,50'));
        $this->assertSame(12.5, $p->number('12,5'));
        $this->assertNull($p->number('-'));
    }

    public function test_xlsx_round_trip(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pok').'.xlsx';
        BudgetTemplate::write(BudgetTemplate::pokSample(), $path, 'POK');

        $parsed = (new BudgetSheetParser)->parseFile($path);
        @unlink($path);

        $this->assertCount(2, array_filter($parsed['rows'], fn ($r) => $r['level'] === 'ro'));
    }

    /** Baris seperti hasil PdfReportReader dari Laporan Ketersediaan Dana Detail. */
    public static function realizationReport(string $period = 'Juni 2026', string $realized = '4,173,902,100'): array
    {
        return [
            ['LAPORAN KETERSEDIAAN DANA DETAIL TA 2026'],
            ["Periode {$period}"],
            ['Satuan Kerja : 423755 UNIVERSITAS CONTOH Hal 1 dari 1'],
            ['Uraian', 'Pagu Revisi', 'Lock Pagu', 'Periode Lalu', 'Periode Ini', 's.d. Periode', '%', 'Sisa Anggaran'],
            ['JUMLAH SELURUHNYA', '11,387,636,000', '0', null, null, '4,211,977,100', '36.99 %', null],
            ['DK Program Pendidikan Tinggi', '11,387,636,000', '0', null, null, '4,211,977,100', '36.99 %', null],
            ['DK.2132 Peningkatan Akses, Mutu, Relevansi, dan Daya Saing Pendidikan Tinggi Keagamaan Islam', '11,387,636,000', '0', null, null, '4,211,977,100', '36.99 %', null],
            ['BEA Bantuan Masyarakat', '11,387,636,000', '0', null, null, '4,211,977,100', '36.99 %', null],
            ['BEA.001 Tunjangan Profesi Dosen Non ASN', '11,243,336,000', '0', null, null, $realized, '37.12 %', null],
            ['004 Dukungan Operasional Penyelenggaraan Pendidikan', '11,243,336,000', '0', null, null, $realized, '37.12 %', null],
            ['BEA.008 Tunjangan Fungsional Dosen Non ASN', '144,300,000', '0', null, null, '38,075,000', '26.39 %', null],
            ['004 Dukungan Operasional Penyelenggaraan Pendidikan', '144,300,000', '0', null, null, '38,075,000', '26.39 %', null],
        ];
    }

    public function test_realization_report_with_new_codes(): void
    {
        $parsed = (new BudgetSheetParser)->parseRows(self::realizationReport());
        $rows = array_column($parsed['rows'], null, 'code');

        $this->assertSame('realisasi', $parsed['source']);
        $this->assertSame(['year' => 2026, 'period_end' => '2026-06-30', 'period_label' => 'Juni 2026', 'unit' => 'UNIVERSITAS CONTOH'], $parsed['meta']);
        $this->assertSame('program', $rows['DK']['level']);
        $this->assertSame('kegiatan', $rows['DK.2132']['level']);
        $this->assertSame('kro', $rows['BEA']['level']);
        $this->assertSame('ro', $rows['BEA.001']['level']);
        $this->assertSame('komponen', $parsed['rows'][4]['level']);
        $this->assertSame($rows['BEA.001']['key'], $parsed['rows'][4]['parent_key']);
        $this->assertEquals(11243336000, $rows['BEA.001']['amount']);
        $this->assertEquals(4173902100, $parsed['rows'][4]['realized']);
        $this->assertCount(7, $parsed['rows']); // baris JUMLAH dilewati
    }

    public function test_english_thousand_separators(): void
    {
        $p = new BudgetSheetParser;

        $this->assertSame(259915931000.0, $p->number('259,915,931,000'));
        $this->assertSame(1500.25, $p->number('1,500.25'));
    }
}
