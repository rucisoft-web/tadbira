<?php

/*
 * Data demo Universitas Negeri Jakarta, TA 2026.
 *
 * Program Kerja 01–13 (kode PK-xx) diambil dari dokumen "Program Kerja Universitas Negeri Jakarta
 * Tahun 2026": nama, penanggung jawab, anggaran, sumber dana, dan jadwal pelaksanaannya sesuai
 * dokumen. Kegiatan fakultas dan unit lainnya, sasaran, IKU, kemajuan, dan realisasi adalah karangan.
 *
 * Kegiatan: [nama, jenis, kode sasaran, anggaran dalam juta rupiah, sumber dana, jadwal?, kode?]
 */

$janAgu = ['2026-01-05', '2026-08-31'];

return [
    'organization' => [
        'name' => 'Universitas Negeri Jakarta',
        'short_name' => 'UNJ',
        'code' => 'UNJ',
        'sector' => 'perguruan_tinggi',
        'email_domain' => 'demo.unj.test',
        'make_current' => false,
    ],

    'fiscal_year' => ['year' => 2026, 'ceiling' => 112_000_000_000],

    'goals' => [
        ['SP1', 'Meningkatnya kualitas lulusan pendidikan tinggi', '#0EA5E9', [
            ['IKU 1: Lulusan mendapat pekerjaan layak, studi lanjut, atau berwirausaha', '%', '60%', '57,4%', 96],
            ['IKU 2: Mahasiswa mendapat pengalaman di luar kampus', '%', '35%', '31,2%', 89],
        ]],
        ['SP2', 'Meningkatnya kualitas dosen pendidikan tinggi', '#A78BFA', [
            ['IKU 3: Dosen berkegiatan di luar kampus', '%', '40%', '42,5%', 106],
            ['IKU 4: Dosen bergelar doktor, bersertifikat kompetensi, atau praktisi mengajar', '%', '55%', '51,8%', 94],
        ]],
        ['SP3', 'Meningkatnya kualitas kurikulum dan pembelajaran', '#10B981', [
            ['IKU 7: Mata kuliah dengan metode kolaboratif dan partisipatif (OBE)', '%', '75%', '68,0%', 91],
            ['IKU 8: Program studi berstandar internasional', 'prodi', '12', '9', 90],
        ]],
        ['SP4', 'Meningkatnya riset dan publikasi bereputasi', '#D4A72C', [
            ['IKU 5: Publikasi internasional bereputasi kuartil 1 (Q1)', 'artikel', '180', '146', 97],
            ['Judul penelitian yang terlaksana sesuai rencana dan jadwal', '%', '95%', '91%', 96],
        ]],
        ['SP5', 'Meningkatnya kerja sama dan rekognisi internasional', '#14B8A6', [
            ['IKU 6: Program studi yang bekerja sama dengan mitra', '%', '85%', '81%', 95],
            ['Mitra kerja sama luar negeri yang aktif', 'mitra', '120', '104', 93],
        ]],
        ['SP6', 'Terwujudnya tata kelola yang akuntabel dan terdigitalisasi', '#F97316', [
            ['Nilai evaluasi SAKIP', 'nilai', 'A (≥ 80)', '78,2 (BB)', 98],
            ['Opini atas laporan keuangan', 'opini', 'WTP', 'WTP', 100],
            ['Layanan administrasi yang terintegrasi daring', '%', '90%', '82%', 91],
        ]],
        ['SP7', 'Terwujudnya kampus berkelanjutan (SDGs)', '#84CC16', [
            ['Peringkat UI GreenMetric nasional', 'peringkat', '≤ 15', '19', 79],
            ['Sampah kampus yang terpilah dan terkelola', '%', '70%', '45%', 64],
        ]],
    ],

    // Capaian s.d. Oktober 2026 (karangan): [realisasi, skor]
    'indicator_progress' => [
        'SP1' => [['58,1%', 97], ['33,6%', 96]],
        'SP2' => [['44,0%', 110], ['53,2%', 97]],
        'SP3' => [['71,5%', 95], ['10', 83]],
        'SP4' => [['121', 67], ['88%', 93]],
        'SP5' => [['86%', 101], ['112', 93]],
        'SP6' => [['80,4 (A)', 100], [null, null], ['87%', 97]],
        'SP7' => [['17', 88], ['58%', 83]],
    ],

    'units' => [
        // Program kerja universitas dari dokumen Program Kerja UNJ 2026.
        'WR Kerja Sama' => ['Wakil Rektor Bidang Kerja Sama dan Bisnis', [
            ['Penjajakan/Penguatan Kerja Sama (Optimalisasi Kerja Sama)', 'kajian', 'SP5', 803.796072, 'Selain APBN', $janAgu, 'PK-01'],
            ['Pengembangan unit bisnis dan aset komersial', 'kajian', 'SP6', 350, 'Selain APBN'],
        ], 'WRKB'],
        'WR Keuangan' => ['Wakil Rektor Bidang Keuangan dan Sumber Daya', [
            ['Waste Management (Manajemen Pengelolaan Sampah/Limbah)', 'fisik', 'SP7', 823.66159, 'Selain APBN', $janAgu, 'PK-02'],
            ['Rapat Kerja: Laporan Kerja 2025 dan Pemantapan Program Kerja 2026 (Penyusunan Perjanjian Kinerja)', 'acara', 'SP6', 58.23, 'Selain APBN', ['2026-02-02', '2026-02-27'], 'PK-05'],
            ['Rencana Kerja dan Anggaran Tahunan (RKAT) Tahun 2026', 'kajian', 'SP6', 41.8, 'Selain APBN', ['2026-04-01', '2026-04-30'], 'PK-08'],
            ['Penyusunan Rencana Strategis UNJ Tahun 2025–2029', 'kajian', 'SP6', 287.31982, 'Selain APBN', $janAgu, 'PK-09'],
            ['Evaluasi Implementasi Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)', 'kajian', 'SP6', 40, 'Selain APBN', ['2026-07-01', '2026-08-31'], 'PK-13'],
            ['Penyusunan laporan keuangan dan audit kantor akuntan publik', 'kajian', 'SP6', 380, 'Selain APBN'],
        ], 'WRKSD'],
        'WR Riset' => ['Wakil Rektor Bidang Riset, Inovasi dan Sistem Informasi', [
            ['Pengadaan Aplikasi Monitoring Media Informasi UNJ Tahun 2026', 'fisik', 'SP6', 445.8, 'Selain APBN', ['2026-07-01', '2026-07-31'], 'PK-03'],
            ['Integrasi sistem informasi akademik dan keuangan', 'fisik', 'SP6', 1250, 'Selain APBN'],
        ], 'WRRISI'],
        'Sekretariat' => ['Sekretaris Universitas', [
            ['Rapat Pimpinan Antar Organ UNJ (Pembahasan RPJP dan Renstra), Harris Sentul City Bogor', 'acara', 'SP6', 102.4, 'Selain APBN', ['2026-04-06', '2026-04-08'], 'PK-04'],
            ['Wisuda periode 116 dan 117', 'acara', 'SP1', 1600, 'Selain APBN'],
        ], 'SETUNIV'],
        'LPPM' => ['Lembaga Penelitian dan Pengabdian kepada Masyarakat', [
            ['Penelitian yang Didanai dan Dilaksanakan Sesuai Rencana', 'kajian', 'SP4', 46061.658813, 'Selain APBN', ['2026-01-05', '2026-12-18'], 'PK-06'],
            ['Pengabdian kepada masyarakat berbasis riset', 'kajian', 'SP2', 2800, 'Selain APBN'],
            ['KKN tematik dan Kampus Mengajar', 'acara', 'SP1', 1350, 'Selain APBN'],
        ]],
        'WR Akademik' => ['Wakil Rektor Bidang Akademik, Kemahasiswaan dan Alumni', [
            ['Penguatan Sistem Pembelajaran Digital dan LMS OBE (Outcome Based Education)', 'kajian', 'SP3', 828.182, 'Selain APBN', $janAgu, 'PK-07'],
            ['Seleksi mahasiswa baru jalur mandiri', 'acara', 'SP1', 2100, 'Selain APBN'],
            ['PKKMB mahasiswa baru', 'acara', 'SP1', 900, 'Selain APBN'],
            ['Tracer study lulusan universitas', 'kajian', 'SP1', 160, 'Selain APBN'],
        ], 'WRAKA'],
        'Lintas unit' => ['Program lintas unit (seluruh unit kerja)', [
            ['Implementasi Sustainable Development Goals (SDGs)', 'kajian', 'SP7', 273.0045, 'Selain APBN', $janAgu, 'PK-10'],
            ['World Class University: Akreditasi Internasional dan Pengembangan Kelas Internasional', 'kajian', 'SP3', 903.61, 'Selain APBN', $janAgu, 'PK-11'],
            ['Publikasi Q1 2026', 'kajian', 'SP4', 909, 'Selain APBN', $janAgu, 'PK-12'],
        ], 'LINTAS'],

        // Kegiatan fakultas dan unit (karangan).
        'FIP' => ['Fakultas Ilmu Pendidikan', [
            ['Akreditasi internasional prodi PGSD', 'kajian', 'SP3', 450, 'Selain APBN'],
            ['Pengenalan Lapangan Persekolahan (PLP)', 'acara', 'SP1', 600, 'Selain APBN'],
            ['Seminar internasional pendidikan dasar', 'acara', 'SP5', 180, 'Selain APBN'],
            ['Pengembangan laboratorium microteaching', 'fisik', 'SP3', 950, 'APBN'],
            ['Pelatihan pendidikan inklusif bagi guru sekolah mitra', 'acara', 'SP2', 120, 'Selain APBN'],
        ]],
        'FBS' => ['Fakultas Bahasa dan Seni', [
            ['Festival seni dan budaya Nusantara', 'acara', 'SP1', 350, 'Selain APBN'],
            ['Akreditasi internasional prodi Pendidikan Bahasa Inggris', 'kajian', 'SP3', 400, 'Selain APBN'],
            ['Pengadaan peralatan studio musik dan tari', 'fisik', 'SP3', 1100, 'APBN'],
            ['Kelas internasional BIPA', 'kajian', 'SP5', 250, 'Selain APBN'],
        ]],
        'FMIPA' => ['Fakultas Matematika dan Ilmu Pengetahuan Alam', [
            ['Renovasi laboratorium kimia terpadu', 'fisik', 'SP3', 2400, 'APBN'],
            ['Olimpiade sains nasional pelajar', 'acara', 'SP1', 220, 'Selain APBN'],
            ['Penelitian kolaboratif material maju', 'kajian', 'SP4', 750, 'Selain APBN'],
            ['Klinik penulisan artikel jurnal Q1', 'acara', 'SP4', 90, 'Selain APBN'],
        ]],
        'FISH' => ['Fakultas Ilmu Sosial dan Hukum', [
            ['Pusat studi hukum dan kebijakan pendidikan', 'kajian', 'SP4', 300, 'Selain APBN'],
            ['Seminar nasional ilmu sosial', 'acara', 'SP2', 150, 'Selain APBN'],
            ['Magang mahasiswa di lembaga pemerintah', 'acara', 'SP1', 280, 'Selain APBN'],
            ['Akreditasi prodi Ilmu Hukum', 'kajian', 'SP3', 180, 'Selain APBN'],
        ]],
        'FT' => ['Fakultas Teknik', [
            ['Pembangunan gedung workshop teknik mesin tahap II', 'fisik', 'SP3', 6500, 'APBN'],
            ['Teaching factory dan inkubator bisnis', 'kajian', 'SP1', 600, 'Selain APBN'],
            ['Sertifikasi kompetensi mahasiswa (BNSP)', 'acara', 'SP1', 420, 'Selain APBN'],
            ['Pengadaan peralatan laboratorium elektronika', 'fisik', 'SP3', 1800, 'APBN'],
        ]],
        'FIKK' => ['Fakultas Ilmu Keolahragaan dan Kesehatan', [
            ['Renovasi kolam renang dan gedung olahraga', 'fisik', 'SP3', 3200, 'APBN'],
            ['Pekan olahraga mahasiswa', 'acara', 'SP1', 260, 'Selain APBN'],
            ['Layanan klinik kesehatan olahraga', 'rutin', 'SP2', 300, 'Selain APBN'],
            ['Pelatihan pelatih olahraga bersertifikat', 'acara', 'SP2', 140, 'Selain APBN'],
        ]],
        'FEB' => ['Fakultas Ekonomi dan Bisnis', [
            ['Akreditasi internasional prodi Akuntansi', 'kajian', 'SP3', 520, 'Selain APBN'],
            ['Kompetisi rencana bisnis mahasiswa', 'acara', 'SP1', 120, 'Selain APBN'],
            ['Kelas internasional Manajemen', 'kajian', 'SP5', 300, 'Selain APBN'],
        ]],
        'FPsi' => ['Fakultas Psikologi', [
            ['Layanan konseling mahasiswa', 'rutin', 'SP1', 180, 'Selain APBN'],
            ['Penelitian kesehatan mental mahasiswa', 'kajian', 'SP4', 210, 'Selain APBN'],
            ['Pelatihan asesor psikologi', 'acara', 'SP2', 80, 'Selain APBN'],
        ]],
        'SPs' => ['Sekolah Pascasarjana', [
            ['Visiting professor dan joint supervision', 'kajian', 'SP5', 650, 'Selain APBN'],
            ['International Conference on Education', 'acara', 'SP5', 380, 'Selain APBN'],
            ['Pendampingan publikasi disertasi', 'kajian', 'SP4', 400, 'Selain APBN'],
        ]],
        'UPT TIK' => ['UPT Teknologi Informasi dan Komunikasi', [
            ['Pemeliharaan jaringan dan pusat data', 'rutin', 'SP6', 1800, 'Selain APBN'],
            ['Peningkatan bandwidth dan keamanan siber', 'fisik', 'SP6', 950, 'Selain APBN'],
        ], 'UPTTIK'],
        'Perpustakaan' => ['UPT Perpustakaan', [
            ['Langganan basis data jurnal internasional', 'rutin', 'SP4', 1500, 'Selain APBN'],
            ['Digitalisasi koleksi dan repositori', 'kajian', 'SP4', 220, 'Selain APBN'],
        ]],
        'BPM' => ['Badan Penjaminan Mutu', [
            ['Audit mutu internal', 'kajian', 'SP6', 120, 'Selain APBN'],
            ['Pendampingan akreditasi prodi', 'kajian', 'SP3', 260, 'Selain APBN'],
        ]],
    ],
];
