<?php

/*
|--------------------------------------------------------------------------
| Terminologi Tadbira
|--------------------------------------------------------------------------
|
| Istilah inti mengikuti "Manajemen Proyek Bernilai Kenabian" (9 bidang,
| PMBOK edisi ke-4). Istilah yang berbeda antar-sektor (pemerintah, BUMN,
| BUMD, swasta) diatur per sektor di bawah, sehingga satu aplikasi bisa
| dipakai kementerian, lembaga, badan, pemda, maupun korporasi.
|
*/

return [

    // Tombol "Masuk sebagai tamu" di halaman masuk. Tamu hanya bisa melihat (hanya-baca).
    'guest' => [
        'enabled' => (bool) env('TADBIRA_GUEST_LOGIN', false),
        'email' => env('TADBIRA_GUEST_EMAIL', 'tamu@tadbira.test'),
    ],

    // Peran dalam organisasi dan hak aksesnya. true = semua kegiatan; 'unit' = hanya kegiatan di
    // unit kerjanya atau yang ia pimpin/ikuti. Peran lama "anggota" diperlakukan sebagai perencana.
    'roles' => [
        'admin' => [
            'label' => 'Admin',
            'description' => 'Mengatur organisasi, pengguna, unit kerja, tahun anggaran, dan hari libur. Bisa melakukan semua hal.',
            'can' => ['settings' => true, 'fiscal_years' => true, 'create_project' => true, 'edit_project' => true, 'delete_project' => true, 'documents' => true, 'import_budget' => true, 'record_expenditure' => true],
        ],
        'pimpinan' => [
            'label' => 'Pimpinan',
            'description' => 'Melihat dasbor, semua kegiatan, anggaran, dan dokumen. Tidak mengubah data.',
            'can' => [],
        ],
        'perencana' => [
            'label' => 'Perencana',
            'description' => 'Menyusun dan mengubah semua kegiatan, membuat tahun anggaran, mengimpor POK/RAB, dan memverifikasi.',
            'can' => ['fiscal_years' => true, 'create_project' => true, 'edit_project' => true, 'delete_project' => true, 'documents' => true, 'import_budget' => true, 'record_expenditure' => true],
        ],
        'keuangan' => [
            'label' => 'Keuangan',
            'description' => 'Mengimpor anggaran dan laporan realisasi, mencatat realisasi, dan mengunggah dokumen keuangan.',
            'can' => ['documents' => true, 'import_budget' => true, 'record_expenditure' => true],
        ],
        'operator' => [
            'label' => 'Operator unit',
            'description' => 'Membuat dan memperbarui kegiatan di unit kerjanya: aktivitas, kemajuan, dan dokumen.',
            'can' => ['create_project' => 'unit', 'edit_project' => 'unit', 'documents' => 'unit'],
        ],
    ],

    // Hak akses yang ditampilkan di halaman Pengaturan > Peran.
    'abilities' => [
        'view' => 'Melihat dasbor, kegiatan, anggaran, dan dokumen',
        'create_project' => 'Membuat kegiatan baru',
        'edit_project' => 'Mengubah jadwal, aktivitas, dan kemajuan',
        'documents' => 'Mengunggah dokumen kegiatan',
        'delete_project' => 'Menghapus kegiatan',
        'import_budget' => 'Mengimpor POK/RAB dan laporan realisasi',
        'record_expenditure' => 'Mencatat realisasi per akun',
        'fiscal_years' => 'Membuat tahun anggaran',
        'settings' => 'Membuka menu Pengaturan',
    ],

    // Tautan di halaman depan (program Rucisoft untuk Tadbira).
    'landing' => [
        'site' => 'https://rucisoft.id',
        'academy' => env('TADBIRA_URL_ACADEMY', 'https://rucisoft.id/academy?produk=tadbira'),
        'solution' => env('TADBIRA_URL_SOLUTION', 'https://rucisoft.id/solusi/tadbira'),
        'whatsapp' => env('TADBIRA_WHATSAPP', '6281199892939'),
    ],

    'sectors' => [
        'kementerian' => 'Kementerian',
        'lembaga' => 'Lembaga',
        'badan' => 'Badan',
        'pemda' => 'Pemerintah Daerah',
        'perguruan_tinggi' => 'Perguruan Tinggi',
        'bumn' => 'BUMN',
        'bumd' => 'BUMD',
        'swasta' => 'Swasta',
        'nirlaba' => 'Organisasi Nirlaba',
    ],

    // Sektor yang memakai struktur anggaran RKA-K/L / POK (Program → Kegiatan → KRO → RO → ...).
    'government_sectors' => ['kementerian', 'lembaga', 'badan', 'perguruan_tinggi'],

    // Istilah umum (dipakai bila sektor tidak menimpanya).
    'terms' => [
        'organization' => 'Organisasi',
        'unit' => 'Unit kerja',
        'fiscal_year' => 'Tahun anggaran',
        'budget_ceiling' => 'Pagu anggaran',
        'budget_document' => 'Rencana anggaran',
        'budget_import' => 'Impor rencana anggaran (XLS)',
        'realization' => 'Realisasi',
        'project' => 'Proyek',
        'portfolio' => 'Portofolio',
        'phase' => 'Tahap',
        'work_package' => 'Paket kerja',
        'activity' => 'Aktivitas',
        'milestone' => 'Tonggak',
        'wbs' => 'Struktur rincian kerja',
        'duration' => 'Lama pengerjaan',
        'predecessor' => 'Pendahulu',
        'critical_path' => 'Jalur kritis',
        'slack' => 'Kelonggaran waktu',
        'baseline' => 'Jadwal acuan',
        'responsible' => 'Penanggung jawab',
        'leader' => 'Pemimpin proyek',
        'goal' => 'Sasaran',
        'indicator' => 'Indikator kinerja',
        'work_unit' => 'Unit kerja',
    ],

    'sector_terms' => [
        'kementerian' => [
            'unit' => 'Satuan kerja',
            'budget_ceiling' => 'Pagu DIPA',
            'budget_document' => 'POK RKA-K/L',
            'budget_import' => 'Impor POK / realisasi',
            'project' => 'Kegiatan',
            'goal' => 'Sasaran program',
            'indicator' => 'IKU',
        ],
        'lembaga' => [
            'unit' => 'Satuan kerja',
            'budget_ceiling' => 'Pagu DIPA',
            'budget_document' => 'POK RKA-K/L',
            'budget_import' => 'Impor POK / realisasi',
            'project' => 'Kegiatan',
            'goal' => 'Sasaran program',
            'indicator' => 'IKU',
        ],
        'badan' => [
            'unit' => 'Satuan kerja',
            'budget_ceiling' => 'Pagu DIPA',
            'budget_document' => 'POK RKA-K/L',
            'budget_import' => 'Impor POK / realisasi',
            'project' => 'Kegiatan',
            'goal' => 'Sasaran program',
            'indicator' => 'IKU',
        ],
        'perguruan_tinggi' => [
            'unit' => 'Satuan kerja',
            'budget_ceiling' => 'Pagu DIPA',
            'budget_document' => 'POK RKA-K/L',
            'budget_import' => 'Impor POK / realisasi',
            'goal' => 'Sasaran program',
            'indicator' => 'IKU',
            'project' => 'Kegiatan',
            'work_unit' => 'Fakultas / unit',
            'leader' => 'Penanggung jawab kegiatan',
        ],
        'pemda' => [
            'unit' => 'Perangkat daerah',
            'budget_ceiling' => 'Pagu DPA',
            'budget_document' => 'RKA/DPA SKPD',
            'budget_import' => 'Impor RKA/DPA (XLS)',
            'project' => 'Kegiatan',
            'goal' => 'Sasaran program',
            'indicator' => 'IKU',
        ],
        'bumn' => [
            'unit' => 'Divisi',
            'budget_ceiling' => 'Anggaran RKAP',
            'budget_document' => 'RKAP',
            'budget_import' => 'Impor RAB/RKAP (XLS)',
            'goal' => 'Sasaran strategis',
            'indicator' => 'KPI',
        ],
        'bumd' => [
            'unit' => 'Divisi',
            'budget_ceiling' => 'Anggaran RKAP',
            'budget_document' => 'RKAP',
            'budget_import' => 'Impor RAB/RKAP (XLS)',
            'goal' => 'Sasaran strategis',
            'indicator' => 'KPI',
        ],
        'swasta' => [
            'unit' => 'Divisi',
            'budget_ceiling' => 'Anggaran',
            'budget_document' => 'RAB',
            'budget_import' => 'Impor RAB (XLS)',
            'goal' => 'Sasaran strategis',
            'indicator' => 'KPI',
            'work_unit' => 'Unit bisnis / aset',
        ],
        'nirlaba' => [
            'unit' => 'Divisi',
            'budget_ceiling' => 'Anggaran',
            'budget_document' => 'RAB',
            'budget_import' => 'Impor RAB (XLS)',
            'project' => 'Program',
        ],
    ],

    // Label tingkat anggaran. Tingkat POK RKA-K/L dipakai pemerintah pusat;
    // tingkat RAB umum dipakai sektor lain (Kelompok → Pos → Rincian).
    'budget_levels' => [
        'program' => 'Program',
        'kegiatan' => 'Kegiatan',
        'kro' => 'KRO',
        'ro' => 'RO',
        'komponen' => 'Komponen',
        'subkomponen' => 'Subkomponen',
        'akun' => 'Akun',
        'detail' => 'Detail',
        'kelompok' => 'Kelompok',
        'pos' => 'Pos anggaran',
    ],

    // Berkas digital kegiatan. Batas ukuran juga dibatasi oleh upload_max_filesize dan
    // post_max_size PHP di server (cPanel > Select PHP Version > Options).
    'documents' => [
        'max_kb' => (int) env('TADBIRA_DOCUMENT_MAX_KB', 20480),
        'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf', 'csv', 'txt',
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'zip', 'rar', '7z', 'dwg', 'mp4'],
        'categories' => ['TOR / KAK', 'RAB', 'Kontrak / SPK', 'BAST', 'Laporan', 'Notulen', 'Foto / dokumentasi', 'Surat', 'Lainnya'],
    ],

    // Nilai yang menjiwai tiap bidang manajemen (ditampilkan sebagai pengingat).
    'values' => [
        'integrasi' => ['nilai' => 'Istikamah', 'pesan' => 'Menjaga keselarasan tujuan, keputusan, dan tindakan.'],
        'lingkup' => ['nilai' => 'Sidik', 'pesan' => 'Memperjelas kesepakatan dan memenuhi janji.'],
        'waktu' => ['nilai' => 'Sidik', 'pesan' => 'Menghargai waktu dan menepati janji.'],
        'biaya' => ['nilai' => 'Amanah', 'pesan' => 'Menggunakan dana secara wajar dan bertanggung jawab.'],
        'mutu' => ['nilai' => 'Amanah', 'pesan' => 'Memenuhi ukuran yang dijanjikan dan membuktikan kesesuaiannya.'],
        'sdm' => ['nilai' => 'Tabligh', 'pesan' => 'Memimpin melalui arahan, pembinaan, dan musyawarah.'],
        'komunikasi' => ['nilai' => 'Tabligh', 'pesan' => 'Menyampaikan kebenaran dan membangun pemahaman bersama.'],
        'risiko' => ['nilai' => 'Fathanah', 'pesan' => 'Membaca kemungkinan dan menyiapkan tindakan.'],
        'pengadaan' => ['nilai' => 'Fathanah', 'pesan' => 'Cermat memilih penyedia dan memenuhi kesepakatan.'],
    ],

];
