@php
    $s = $summary;
    $e = $s['evm'];
    $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j M Y') : '–';
    $idx = fn ($v) => $v === null ? '–' : number_format($v, 2, ',', '.');
    $tone = fn ($v) => $v === null ? '' : ($v >= 0.95 ? 'baik' : ($v >= 0.8 ? 'waspada' : 'kritis'));
    $health = ['baik' => 'Sesuai jadwal', 'waspada' => 'Perlu perhatian', 'kritis' => 'Terlambat'][$s['health']];
    $openRisks = $risks->where('open', true);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan status · {{ $project->name }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        @page { size: A4; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: "Plus Jakarta Sans", "Segoe UI", Arial, sans-serif; color: #1e293b; font-size: 11.5px; line-height: 1.45; margin: 0; background: #FBF8F1; }
        .halaman { max-width: 210mm; margin: 0 auto; background: #fff; padding: 14mm 12mm; }
        .alat { max-width: 210mm; margin: 12px auto; display: flex; gap: 8px; justify-content: flex-end; }
        .alat button, .alat a { font: inherit; font-weight: 600; padding: 7px 14px; border-radius: 8px; border: 1px solid #0B6449; background: #0B6449; color: #fff; cursor: pointer; text-decoration: none; }
        .alat a { background: #fff; color: #0B6449; }
        header { display: flex; justify-content: space-between; gap: 16px; border-bottom: 3px solid #064E3B; padding-bottom: 10px; }
        .meta { font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: #8F631C; font-weight: 700; }
        h1 { font-size: 19px; margin: 4px 0 2px; color: #022C22; }
        h2 { font-size: 12.5px; text-transform: uppercase; letter-spacing: .06em; color: #064E3B; margin: 18px 0 6px; border-bottom: 1px solid #EADFC4; padding-bottom: 3px; }
        .muted { color: #64748b; }
        .lencana { display: inline-block; padding: 3px 10px; border-radius: 999px; font-weight: 700; font-size: 11px; }
        .baik { color: #0B6449; } .waspada { color: #8F631C; } .kritis { color: #C2410C; }
        .lencana.baik { background: #D1FAE5; } .lencana.waspada { background: #FAF0CC; } .lencana.kritis { background: #FFEDD5; }
        .kpi { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 12px; }
        .kpi div { border: 1px solid #EADFC4; border-radius: 8px; padding: 8px 10px; }
        .kpi b { display: block; font-size: 16px; color: #022C22; }
        .kpi span { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9.5px; text-transform: uppercase; color: #64748b; background: #FBF8F1; padding: 5px 6px; border-bottom: 1px solid #EADFC4; }
        td { padding: 4px 6px; border-bottom: 1px solid #F5EFE0; vertical-align: top; }
        .r { text-align: right; font-variant-numeric: tabular-nums; }
        .dua { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .bar { height: 7px; background: #F5EFE0; border-radius: 4px; position: relative; margin-top: 6px; }
        .bar i { position: absolute; left: 0; top: 0; bottom: 0; background: #10B981; border-radius: 4px; }
        .bar u { position: absolute; top: -3px; width: 2px; height: 13px; background: #022C22; }
        .ttd { margin-top: 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; text-align: center; }
        .ttd p { margin: 0; }
        .ttd .ruang { height: 54px; }
        footer { margin-top: 18px; font-size: 9.5px; color: #94a3b8; display: flex; justify-content: space-between; }
        @media print {
            body { background: #fff; }
            .alat { display: none; }
            .halaman { padding: 0; max-width: none; }
            h2 { break-after: avoid; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="alat">
    <a href="{{ route('projects.show', $project) }}">Kembali</a>
    <a href="{{ route('projects.export', $project) }}">Unduh Excel</a>
    <button type="button" onclick="window.print()">Cetak / simpan PDF</button>
</div>
<div class="halaman">
    <header>
        <div>
            <div class="meta">Laporan status {{ strtolower($terms['project'] ?? 'kegiatan') }} · {{ $project->organization->name }}</div>
            <h1>{{ $project->name }}</h1>
            <div class="muted">
                {{ $project->code ? $project->code.' · ' : '' }}{{ $project->category ? $project->category.' · ' : '' }}{{ $project->fiscalYear?->code }}
                · {{ $terms['leader'] ?? 'Penanggung jawab' }}: {{ $project->leader?->name ?? '–' }} · {{ $project->statusLabel() }}
            </div>
        </div>
        <div style="text-align:right">
            <span class="lencana {{ $s['health'] }}">{{ $health }}</span>
            <div class="muted" style="margin-top:6px">Per {{ $today->locale('id')->translatedFormat('j F Y') }}</div>
        </div>
    </header>

    <div class="kpi">
        <div><span>Kemajuan</span><b>{{ number_format($s['progress'], 1, ',', '.') }}%</b>
            <div class="bar"><i style="width: {{ min(100, $s['progress']) }}%"></i><u style="left: {{ min(100, $s['planned']) }}%"></u></div>
            <span style="text-transform:none">rencana {{ number_format($s['planned'], 1, ',', '.') }}%</span></div>
        <div><span>Perkiraan selesai</span><b>{{ $tgl($s['finish']) }}</b>
            <span style="text-transform:none">@if ($s['has_baseline']) baseline {{ $tgl($s['baseline_finish']) }}@if ($s['finish_variance']) ({{ $s['finish_variance'] > 0 ? '+' : '' }}{{ $s['finish_variance'] }} hari)@endif @else belum ada baseline @endif</span></div>
        <div><span>Anggaran / {{ strtolower($terms['realization'] ?? 'realisasi') }}</span><b>{{ number_format($s['absorption'], 1, ',', '.') }}%</b>
            <span style="text-transform:none">{{ $rp($s['realized']) }} dari {{ $rp($s['budget']) }}</span></div>
        <div><span>SPI · CPI</span><b><span class="{{ $tone($e['spi']) }}" style="font-size:16px;text-transform:none;color:inherit">{{ $idx($e['spi']) }}</span> · <span class="{{ $tone($e['cpi']) }}" style="font-size:16px;text-transform:none;color:inherit">{{ $idx($e['cpi']) }}</span></b>
            <span style="text-transform:none">EAC {{ $rp($e['eac']) }}</span></div>
    </div>

    <div class="dua">
        <div>
            <h2>{{ $terms['milestone'] ?? 'Tonggak' }}</h2>
            <table>
                <tr><th>Tonggak</th><th class="r">Rencana</th><th class="r">Baseline</th><th class="r">Status</th></tr>
                @forelse ($milestones as $m)
                    <tr><td>{{ $m['title'] }}</td><td class="r">{{ $tgl($m['start']) }}</td><td class="r">{{ $tgl($m['baseline_finish']) }}</td>
                        <td class="r {{ $m['progress'] >= 100 ? 'baik' : ($m['late'] ? 'kritis' : '') }}">{{ $m['progress'] >= 100 ? 'Tercapai' : ($m['late'] ? 'Terlewat' : 'Menunggu') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="muted">Tidak ada tonggak.</td></tr>
                @endforelse
            </table>
        </div>
        <div>
            <h2>Nilai hasil</h2>
            <table>
                <tr><td>Nilai rencana (PV)</td><td class="r">{{ $rp($e['pv']) }}</td></tr>
                <tr><td>Nilai hasil (EV)</td><td class="r">{{ $rp($e['ev']) }}</td></tr>
                <tr><td>Biaya aktual (AC)</td><td class="r">{{ $rp($e['ac']) }}</td></tr>
                <tr><td>Varians jadwal (SV)</td><td class="r {{ $e['sv'] < 0 ? 'kritis' : '' }}">{{ $rp($e['sv']) }}</td></tr>
                <tr><td>Varians biaya (CV)</td><td class="r {{ $e['cv'] < 0 ? 'kritis' : '' }}">{{ $rp($e['cv']) }}</td></tr>
                <tr><td>Perkiraan biaya akhir (EAC)</td><td class="r">{{ $rp($e['eac']) }}</td></tr>
                <tr><td>Varians saat selesai (VAC)</td><td class="r {{ $e['vac'] < 0 ? 'kritis' : '' }}">{{ $rp($e['vac']) }}</td></tr>
            </table>
        </div>
    </div>

    <h2>Aktivitas terlambat ({{ $late->count() }})</h2>
    <table>
        <tr><th style="width:44px">No.</th><th>Aktivitas</th><th>Penanggung jawab</th><th class="r">Selesai</th><th class="r">Kemajuan</th></tr>
        @forelse ($late->take(12) as $t)
            <tr><td class="muted">{{ $t['wbs'] }}</td><td class="{{ $t['critical'] ? 'kritis' : '' }}">{{ $t['title'] }}</td><td>{{ $t['responsible'] ?: '–' }}</td><td class="r kritis">{{ $tgl($t['finish']) }}</td><td class="r">{{ $t['progress'] }}%</td></tr>
        @empty
            <tr><td colspan="5" class="muted">Tidak ada aktivitas yang terlambat.</td></tr>
        @endforelse
    </table>

    <h2>Rencana 30 hari ke depan</h2>
    <table>
        <tr><th style="width:44px">No.</th><th>Aktivitas</th><th>Penanggung jawab</th><th class="r">Mulai</th><th class="r">Selesai</th></tr>
        @forelse ($upcoming as $t)
            <tr><td class="muted">{{ $t['wbs'] }}</td><td>{{ $t['title'] }}</td><td>{{ $t['responsible'] ?: '–' }}</td><td class="r">{{ $tgl($t['start']) }}</td><td class="r">{{ $tgl($t['finish']) }}</td></tr>
        @empty
            <tr><td colspan="5" class="muted">Tidak ada aktivitas terjadwal.</td></tr>
        @endforelse
    </table>

    <h2>Risiko dan isu terbuka ({{ $openRisks->count() }})</h2>
    <table>
        <tr><th>Jenis</th><th>Uraian</th><th class="r">Skor</th><th>Respons</th><th>Pemilik</th><th class="r">Batas</th></tr>
        @forelse ($openRisks->take(10) as $r)
            <tr><td>{{ $r['type'] }}</td><td>{{ $r['title'] }}</td><td class="r {{ $r['score'] >= 10 ? 'kritis' : '' }}">{{ $r['score'] }} · {{ $r['level'] }}</td>
                <td>{{ $r['strategy'] ? $r['strategy'].': ' : '' }}{{ $r['response'] ?: '–' }}</td><td>{{ $r['owner'] ?? '–' }}</td><td class="r">{{ $tgl($r['due_date']) }}</td></tr>
        @empty
            <tr><td colspan="6" class="muted">Tidak ada risiko atau isu terbuka.</td></tr>
        @endforelse
    </table>

    @if ($changes->isNotEmpty())
        <h2>Permintaan perubahan</h2>
        <table>
            <tr><th>Nomor</th><th>Perubahan</th><th class="r">Jadwal</th><th class="r">Biaya</th><th>Status</th></tr>
            @foreach ($changes->take(8) as $c)
                <tr><td class="muted">{{ $c['code'] }}</td><td>{{ $c['title'] }}</td><td class="r">{{ $c['schedule'] ? ($c['schedule'] > 0 ? '+' : '').$c['schedule'].' hari' : '–' }}</td><td class="r">{{ $c['cost'] ? $rp($c['cost']) : '–' }}</td><td>{{ $c['status'] }}{{ $c['decider'] ? ' · '.$c['decider'] : '' }}</td></tr>
            @endforeach
        </table>
    @endif

    <div class="ttd">
        <div><p>Disusun oleh,</p><p class="muted">{{ $terms['leader'] ?? 'Penanggung jawab' }}</p><div class="ruang"></div><p><b>{{ $project->leader?->name ?? '..............................' }}</b></p></div>
        <div><p>Mengetahui,</p><p class="muted">Pimpinan</p><div class="ruang"></div><p><b>..............................</b></p></div>
    </div>

    <footer>
        <span>Dibuat dengan Tadbira · {{ now()->locale('id')->translatedFormat('j F Y H:i') }}</span>
        <span>{{ $project->ulid }}</span>
    </footer>
</div>
</body>
</html>
