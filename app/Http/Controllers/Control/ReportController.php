<?php

namespace App\Http\Controllers\Control;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PortfolioService;
use App\Services\Reports\ProjectReport;
use App\Services\Reports\Workbook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Laporan status satu halaman, siap dicetak atau disimpan sebagai PDF dari peramban. */
    public function status(Request $request, Project $project, ProjectReport $report): View
    {
        $this->authorizeProject($request, $project);

        return view('reports.status', $report->build($project) + ['terms' => $project->organization->terminology()->all()]);
    }

    public function project(Request $request, Project $project, ProjectReport $report): StreamedResponse
    {
        $this->authorizeProject($request, $project);
        $d = $report->build($project);
        $s = $d['summary'];
        $evm = $s['evm'];
        $caption = "{$project->name} · per ".$d['today']->locale('id')->translatedFormat('j F Y');

        return (new Workbook)
            ->sheet('Ringkasan', ['Ukuran' => 'text', 'Nilai' => 'text'], [
                ['Kegiatan', $project->name],
                ['Kode', $project->code],
                ['Unit', $project->category],
                ['Tahun anggaran', $project->fiscalYear?->code],
                ['Penanggung jawab', $project->leader?->name],
                ['Status', $project->statusLabel()],
                ['Mulai', $s['start']],
                ['Perkiraan selesai', $s['finish']],
                ['Selesai baseline', $s['baseline_finish'] ?? '–'],
                ['Varians selesai (hari)', $s['finish_variance'] ?? '–'],
                ['Kemajuan (%)', $s['progress']],
                ['Rencana per hari ini (%)', $s['planned']],
                ['Kesehatan', ['baik' => 'Sesuai jadwal', 'waspada' => 'Perlu perhatian', 'kritis' => 'Terlambat'][$s['health']]],
                ['Aktivitas terlambat', $s['late_tasks']],
                ['BAC', $evm['bac']], ['PV', $evm['pv']], ['EV', $evm['ev']], ['AC', $evm['ac']],
                ['SV', $evm['sv']], ['CV', $evm['cv']], ['SPI', $evm['spi']], ['CPI', $evm['cpi']],
                ['EAC', $evm['eac']], ['ETC', $evm['etc']], ['VAC', $evm['vac']], ['TCPI', $evm['tcpi']],
            ], $caption)
            ->sheet('SRK & jadwal', [
                'No.' => 'text', 'Nama' => 'text', 'Lama (hari kerja)' => 'number', 'Mulai' => 'date', 'Selesai' => 'date',
                'Selesai baseline' => 'date', 'Varians (hari)' => 'number', '% selesai' => 'number', 'Kritis' => 'text',
                'Kelonggaran (hari)' => 'number', 'Penanggung jawab' => 'text',
            ], $d['tasks']->map(fn ($t) => [
                $t['wbs'], str_repeat('   ', substr_count((string) $t['wbs'], '.')).$t['title'], $t['duration'],
                Workbook::date($t['start']), Workbook::date($t['finish']), Workbook::date($t['baseline_finish']), $t['variance'],
                $t['progress'], $t['critical'] ? 'Ya' : '', $t['slack'], $t['responsible'],
            ]))
            ->sheet('Anggaran', [
                'Kode' => 'text', 'Uraian' => 'text', 'Volume' => 'decimal', 'Satuan' => 'text', 'Harga satuan' => 'money',
                'Jumlah' => 'money', 'Realisasi' => 'money', 'Sumber dana' => 'text',
            ], $d['budget']->map(fn ($b) => [
                $b['code'], str_repeat('   ', $b['depth']).$b['description'], $b['volume'], $b['unit'], $b['unit_price'],
                $b['amount'], $b['realized'], $b['funding'],
            ]))
            ->sheet('Risiko & isu', [
                'Jenis' => 'text', 'Judul' => 'text', 'Kategori' => 'text', 'Probabilitas' => 'number', 'Dampak' => 'number',
                'Skor' => 'number', 'Tingkat' => 'text', 'Strategi' => 'text', 'Respons' => 'text', 'Pemilik' => 'text',
                'Batas' => 'date', 'Status' => 'text', 'Aktivitas' => 'text',
            ], $d['risks']->map(fn ($r) => [
                $r['type'], $r['title'], $r['category'], $r['probability'], $r['impact'], $r['score'], $r['level'], $r['strategy'],
                $r['response'], $r['owner'], Workbook::date($r['due_date']), $r['status'], $r['task'],
            ]))
            ->sheet('Perubahan', [
                'Nomor' => 'text', 'Judul' => 'text', 'Alasan' => 'text', 'Dampak jadwal (hari)' => 'number', 'Dampak biaya' => 'money',
                'Status' => 'text', 'Pengaju' => 'text', 'Diajukan' => 'date', 'Pemutus' => 'text', 'Diputuskan' => 'date', 'Catatan' => 'text',
            ], $d['changes']->map(fn ($c) => [
                $c['code'], $c['title'], $c['reason'], $c['schedule'], $c['cost'], $c['status'], $c['requester'],
                Workbook::date($c['submitted']), $c['decider'], Workbook::date($c['decided']), $c['note'],
            ]))
            ->download('laporan-'.Str::limit(Str::slug($project->code ?: $project->name), 50, '').'-'.now()->format('Ymd').'.xlsx');
    }

    /** Seluruh kegiatan satu tahun anggaran beserta kinerja jadwal dan biaya. */
    public function portfolio(Request $request, PortfolioService $portfolio): StreamedResponse
    {
        $org = $this->organization($request);
        $fy = $this->fiscalYear($request, $org);
        abort_unless($fy, 404);
        $unit = $request->string('unit')->toString() ?: $this->access($request)->forcedCategory();
        $rows = collect($portfolio->summary($fy, null, $unit)['projects']);
        $health = ['baik' => 'Sesuai jadwal', 'waspada' => 'Perlu perhatian', 'kritis' => 'Terlambat'];

        return (new Workbook)
            ->sheet("Portofolio {$fy->code}", [
                'Kode' => 'text', 'Kegiatan' => 'text', 'Unit' => 'text', 'Penanggung jawab' => 'text', 'Status' => 'text',
                'Mulai' => 'date', 'Selesai' => 'date', 'Selesai baseline' => 'date', 'Varians (hari)' => 'number',
                'Kemajuan %' => 'percent', 'Rencana %' => 'percent', 'Kesehatan' => 'text', 'Aktivitas terlambat' => 'number',
                'Anggaran' => 'money', 'Realisasi' => 'money', 'Penyerapan %' => 'percent',
                'PV' => 'money', 'EV' => 'money', 'SPI' => 'decimal', 'CPI' => 'decimal', 'EAC' => 'money', 'VAC' => 'money',
            ], $rows->map(fn ($r) => [
                $r['code'], $r['name'], $r['category'], $r['leader'], $r['status_label'],
                Workbook::date($r['start']), Workbook::date($r['finish']), Workbook::date($r['baseline_finish']), $r['finish_variance'],
                $r['progress'], $r['planned'], $health[$r['health']], $r['late_tasks'],
                $r['budget'], $r['realized'], $r['absorption'],
                $r['evm']['pv'], $r['evm']['ev'], $r['evm']['spi'], $r['evm']['cpi'], $r['evm']['eac'], $r['evm']['vac'],
            ]), "{$org->name} · Tahun anggaran {$fy->code}".($unit ? " · {$unit}" : '').' · per '.now()->locale('id')->translatedFormat('j F Y'))
            ->download('portofolio-'.Str::slug($fy->code.($unit ? "-{$unit}" : '')).'-'.now()->format('Ymd').'.xlsx');
    }
}
