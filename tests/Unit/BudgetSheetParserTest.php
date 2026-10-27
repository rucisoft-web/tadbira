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
}
