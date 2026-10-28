<?php

/*
 * Data karangan untuk demo pemantauan kegiatan perguruan tinggi keagamaan Islam negeri, TA 2026.
 *
 * Nama unit dan jenis kegiatan meniru pola daftar kegiatan sebuah PTKIN, sedangkan anggaran,
 * jadwal, kemajuan, dan realisasi semuanya rekaan. Sasaran program dan IKU mengikuti
 * Perjanjian Kinerja PTKIN yang lazim; realisasi tahun lalu dipakai sebagai pembanding.
 *
 * Kegiatan: [nama, jenis, kode sasaran, anggaran dalam juta rupiah, sumber dana]
 * Jenis: acara (persiapan → pelaksanaan → laporan), kajian (penyusunan → pelaksanaan → laporan),
 *        fisik (perencanaan → pengadaan → pelaksanaan → serah terima), rutin (sepanjang tahun).
 */

return [
    'organization' => [
        'name' => 'UIN Sunan Kalijaga Yogyakarta',
        'short_name' => 'UIN Suka',
        'code' => '423755',
        'email_domain' => 'demo.uin-suka.test',
    ],

    'fiscal_year' => ['year' => 2026, 'ceiling' => 78_500_000_000],

    'goals' => [
        ['SP1', 'Meningkatnya partisipasi peserta didik pada pendidikan tinggi', '#0EA5E9', [
            ['Persentase peningkatan mahasiswa', '%', '20%', '36,8%', 120],
            ['Persentase peningkatan mahasiswa berkebutuhan khusus', '%', '7%', '-45,5%', 51],
        ]],
        ['SP2', 'Meningkatnya kualitas standar dan sistem penjaminan mutu', '#10B981', [
            ['Persentase program studi terakreditasi unggul / terakreditasi', '%', '65%', '72%', 111],
            ['Persentase prodi dan lembaga yang melaksanakan prosedur SPM dan manajemen kelembagaan', '%', '90%', '94,7%', 105],
        ]],
        ['SP3', 'Meningkatnya kepedulian PTKN terhadap literasi keagamaan', '#14B8A6', [
            ['Persentase prodi yang mengembangkan literasi keagamaan', '%', '90%', '94,7%', 105],
        ]],
        ['SP4', 'Meningkatnya dosen dan tenaga kependidikan yang berkualitas', '#A78BFA', [
            ['Persentase dosen yang memperoleh sertifikasi peningkatan kompetensi', '%', '26%', '78,5%', 120],
            ['Persentase tenaga kependidikan yang memperoleh sertifikasi peningkatan kompetensi', '%', '20%', '38,9%', 120],
        ]],
        ['SP5', 'Meningkatnya kualitas lulusan yang diterima di dunia kerja', '#D4A72C', [
            ['Persentase lulusan yang bekerja atau berwiraswasta', '%', '19%', '26,1%', 120],
            ['Persentase lulusan bersertifikat kompetensi dari lembaga bereputasi', '%', '1%', '8,3%', 120],
        ]],
        ['SP6', 'Meningkatnya kerja sama perguruan tinggi', '#F97316', [
            ['Persentase kerja sama yang ditindaklanjuti dalam tridarma', '%', '50%', '57,1%', 114],
        ]],
        ['SP7', 'Meningkatnya produktivitas dan daya saing pendidikan tinggi', '#EC4899', [
            ['Persentase artikel jurnal bereputasi nasional yang disitasi', '%', '50%', '71,6%', 120],
            ['Persentase karya ilmiah yang berkontribusi pada pencapaian SDGs', '%', '45%', '59,4%', 120],
        ]],
        ['SP8', 'Meningkatnya tata kelola organisasi yang efektif dan akuntabel', '#064E3B', [
            ['Predikat opini laporan keuangan', 'predikat', 'WTP', 'WTP', 100],
            ['Nilai SAKIP', 'nilai', '70-80 (BB)', '91,4', 114],
            ['Nilai Kinerja Anggaran', '%', '83%', '93,2%', 112],
        ]],
        ['SP9', 'Terwujudnya transformasi kelembagaan', '#84CC16', []],
    ],

    // Realisasi IKU tengah tahun (rekaan): kode sasaran => [realisasi, capaian %] sesuai urutan indikator.
    'indicator_progress' => [
        'SP1' => [['24,2%', 120], ['3,1%', 44]],
        'SP2' => [['68%', 105], ['92%', 102]],
        'SP3' => [['86%', 96]],
        'SP4' => [['18,4%', 71], ['15,0%', 75]],
        'SP5' => [null, ['4,7%', 120]],
        'SP6' => [['41,2%', 82]],
        'SP7' => [['47,5%', 95], ['38,0%', 84]],
        'SP8' => [null, null, ['88,6%', 107]],
    ],

    'units' => [
        'FITK' => ['Fakultas Ilmu Tarbiyah dan Keguruan', [
            ['Pengenalan Budaya Akademik dan Kemahasiswaan (PBAK)', 'acara', 'SP1', 210, 'BLU'],
            ['Akreditasi Internasional Prodi PAI dan PGMI', 'kajian', 'SP2', 385, 'BLU'],
            ['Peninjauan Kurikulum OBE Prodi S1', 'kajian', 'SP2', 96, 'BLU'],
            ['Sertifikasi Kompetensi Dosen', 'acara', 'SP4', 75, 'BOPTN'],
            ['Kalijaga International Conference on Education', 'acara', 'SP6', 168, 'BLU'],
            ['Praktik Lapangan Persekolahan (PLP) Internasional', 'acara', 'SP5', 142, 'BLU'],
            ['Sertifikasi Kompetensi Calon Lulusan', 'acara', 'SP5', 64, 'BOPTN'],
            ['Insentif Publikasi Mahasiswa S1, S2, dan S3', 'kajian', 'SP7', 120, 'BLU'],
            ['Penataan Laboratorium Microteaching', 'fisik', 'SP8', 420, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 1_480, 'BLU'],
        ]],
        'FUPI' => ['Fakultas Ushuluddin dan Pemikiran Islam', [
            ['Sosialisasi Pembelajaran Mahasiswa Baru', 'acara', 'SP1', 92, 'BOPTN'],
            ['Pelatihan Pemrograman Hadis Prodi Ilmu Hadis', 'acara', 'SP3', 38, 'BLU'],
            ['Kajian Tafsir Tematik dan Moderasi Beragama', 'acara', 'SP3', 54, 'BLU'],
            ['Akreditasi Prodi Studi Agama-Agama', 'kajian', 'SP2', 145, 'BLU'],
            ['Ujian Pendahuluan dan Promosi Doktor', 'rutin', 'SP7', 260, 'BLU'],
            ['Pembekalan Calon Wisudawan', 'acara', 'SP5', 27, 'BOPTN'],
            ['Renovasi Gedung Kuliah Teori', 'fisik', 'SP8', 680, 'BLU'],
            ['Pelayanan Penyelenggaraan BLU Fakultas', 'rutin', 'SP8', 2_340, 'BLU'],
        ]],
        'FISHUM' => ['Fakultas Ilmu Sosial dan Humaniora', [
            ['Pengenalan Budaya Akademik dan Kemahasiswaan (PBAK)', 'acara', 'SP1', 63, 'BOPTN'],
            ['Seminar Penguatan Moderasi Beragama Mahasiswa', 'acara', 'SP3', 28, 'BOPTN'],
            ['Academic Writing for Students', 'acara', 'SP7', 24, 'BOPTN'],
            ['Pendampingan Desa Wisata Berbasis Ekologi Komunikasi', 'kajian', 'SP7', 86, 'BLU'],
            ['Kerja Sama Riset Sosial dengan Pemerintah Daerah', 'kajian', 'SP6', 110, 'BLU'],
            ['Tracer Study dan Pemetaan Karier Alumni', 'kajian', 'SP5', 45, 'BLU'],
            ['Renovasi Ruang Dosen, Tata Usaha, dan Kelas', 'fisik', 'SP8', 199, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 960, 'BLU'],
        ]],
        'FEBI' => ['Fakultas Ekonomi dan Bisnis Islam', [
            ['Honorarium Dosen Luar Biasa', 'rutin', 'SP4', 90, 'BOPTN'],
            ['Akreditasi Prodi Perbankan Syariah dan Akuntansi Syariah', 'kajian', 'SP2', 230, 'BLU'],
            ['Konferensi Mahasiswa dan Kompetisi Ekonomi Syariah', 'acara', 'SP5', 65, 'BLU'],
            ['Sertifikasi Profesi Keuangan Syariah Mahasiswa', 'acara', 'SP5', 140, 'BLU'],
            ['Pengembangan Galeri Investasi dan Laboratorium Bisnis', 'fisik', 'SP8', 455, 'BLU'],
            ['Workshop Pengembangan Tim Media Fakultas', 'acara', 'SP8', 9, 'BLU'],
            ['Kerja Sama Magang dengan Industri Keuangan Syariah', 'acara', 'SP6', 72, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 1_120, 'BLU'],
        ]],
        'FADIB' => ['Fakultas Adab dan Ilmu Budaya', [
            ['Penyusunan Borang Pembukaan Prodi Baru', 'kajian', 'SP9', 39, 'BOPTN'],
            ['Seminar Kebudayaan', 'acara', 'SP3', 33, 'BOPTN'],
            ['Magang Mahasiswa di ANRI dan Perpusnas', 'acara', 'SP5', 20, 'BOPTN'],
            ['Pelatihan Karier Mahasiswa', 'acara', 'SP5', 8, 'BLU'],
            ['Festival Sastra dan Bahasa Arab', 'acara', 'SP3', 58, 'BLU'],
            ['Penerbitan Jurnal Terakreditasi SINTA', 'kajian', 'SP7', 74, 'BLU'],
            ['Pembangunan Gedung Laboratorium Bahasa', 'fisik', 'SP8', 1_280, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 780, 'BLU'],
        ]],
        'FST' => ['Fakultas Sains dan Teknologi', [
            ['Praktikum Laboratorium', 'rutin', 'SP2', 194, 'BOPTN'],
            ['Akreditasi Internasional Prodi Teknik Informatika', 'kajian', 'SP2', 410, 'BLU'],
            ['Olimpiade Sains dan Matematika Tingkat Nasional', 'acara', 'SP5', 88, 'BLU'],
            ['Riset Kolaboratif Energi Terbarukan untuk Pesantren', 'kajian', 'SP7', 175, 'BLU'],
            ['Pengadaan Peralatan Laboratorium Kimia dan Fisika', 'fisik', 'SP8', 2_150, 'BLU'],
            ['Sertifikasi Kompetensi Mahasiswa Teknik Industri', 'acara', 'SP5', 66, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 1_050, 'BLU'],
        ]],
        'FSH' => ['Fakultas Syariah dan Hukum', [
            ['Sosialisasi Pembelajaran Mahasiswa Baru (SOSPEM)', 'acara', 'SP1', 137, 'BOPTN'],
            ['Pelatihan Mediator Bersertifikat untuk Mahasiswa', 'acara', 'SP5', 96, 'BLU'],
            ['Peradilan Semu dan Kompetisi Debat Hukum', 'acara', 'SP5', 54, 'BOPTN'],
            ['Klinik Bantuan Hukum Masyarakat', 'rutin', 'SP7', 120, 'BLU'],
            ['Renovasi Toilet dan Penataan Ruang Kelas', 'fisik', 'SP8', 746, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 2_040, 'BLU'],
        ]],
        'FDK' => ['Fakultas Dakwah dan Komunikasi', [
            ['Sosialisasi Pembelajaran Mahasiswa Baru', 'acara', 'SP1', 91, 'BOPTN'],
            ['Peninjauan Kurikulum Prodi', 'kajian', 'SP2', 8, 'BOPTN'],
            ['Pemetaan Karier Alumni dan Relevansi Fakultas', 'kajian', 'SP5', 15, 'BOPTN'],
            ['Pengembangan Laboratorium Produksi Siaran', 'fisik', 'SP8', 166, 'BLU'],
            ['Dakwah Digital dan Literasi Keagamaan Generasi Muda', 'acara', 'SP3', 62, 'BLU'],
            ['Layanan Operasional Perkantoran Fakultas', 'rutin', 'SP8', 870, 'BLU'],
        ]],
        'FK' => ['Fakultas Kedokteran (dalam pendirian)', [
            ['Dukungan Manajemen Pendirian Fakultas dan Prodi Baru', 'rutin', 'SP9', 4_780, 'BLU'],
            ['Klinik Unggulan Muskuloskeletal', 'fisik', 'SP9', 1_042, 'BLU'],
            ['Penyusunan Dokumen Izin Prodi Pendidikan Dokter', 'kajian', 'SP9', 310, 'BLU'],
            ['Kerja Sama Rumah Sakit Pendidikan', 'kajian', 'SP6', 145, 'BLU'],
        ]],
        'Pascasarjana' => ['Sekolah Pascasarjana', [
            ['Penyelenggaraan Ujian Magister dan Doktor', 'rutin', 'SP7', 2_145, 'BLU'],
            ['Studium Generale Pascasarjana', 'acara', 'SP3', 24, 'BLU'],
            ['Visiting Professor dan Kuliah Tamu Internasional', 'acara', 'SP6', 260, 'BLU'],
            ['Renovasi Gedung dan Ruang Seminar', 'fisik', 'SP8', 308, 'BLU'],
        ]],
        'LPPM' => ['Lembaga Penelitian dan Pengabdian kepada Masyarakat', [
            ['Penelitian Kompetitif BOPTN', 'kajian', 'SP7', 2_800, 'BOPTN'],
            ['KKN Reguler dan Tematik', 'acara', 'SP7', 1_350, 'BLU'],
            ['KKN Luar Negeri', 'acara', 'SP6', 150, 'BLU'],
            ['Layanan Mahasiswa dan Dosen Difabel', 'rutin', 'SP1', 180, 'BLU'],
            ['Perlengkapan Green Campus (SDGs 6, 7, 12)', 'fisik', 'SP7', 40, 'BLU'],
            ['Pengelolaan Jurnal dan Insentif Publikasi Bereputasi', 'kajian', 'SP7', 640, 'BLU'],
        ]],
        'LPM' => ['Lembaga Penjaminan Mutu', [
            ['Audit Mutu Internal Prodi dan Unit', 'kajian', 'SP2', 145, 'BLU'],
            ['Pendampingan Akreditasi Prodi', 'kajian', 'SP2', 220, 'BLU'],
            ['Survei Kepuasan Pemangku Kepentingan', 'kajian', 'SP8', 65, 'BLU'],
        ]],
        'Kemahasiswaan' => ['Bagian Kemahasiswaan dan Alumni', [
            ['PBAK Tingkat Universitas', 'acara', 'SP1', 198, 'BLU'],
            ['Kalijaga Changemakers Competition', 'acara', 'SP5', 74, 'BLU'],
            ['Pembinaan Unit Kegiatan Mahasiswa', 'rutin', 'SP5', 860, 'BLU'],
            ['Beasiswa Prestasi dan Afirmasi', 'rutin', 'SP1', 1_120, 'BLU'],
            ['Pusat Karier dan Bursa Kerja', 'acara', 'SP5', 96, 'BLU'],
        ]],
        'Perpustakaan' => ['UPT Perpustakaan', [
            ['Langganan e-Jurnal dan Turnitin', 'rutin', 'SP7', 2_115, 'BLU'],
            ['Renovasi Ruang Layanan Perpustakaan', 'fisik', 'SP8', 179, 'BLU'],
            ['Pengembangan Repositori dan Data Informasi', 'kajian', 'SP7', 49, 'BLU'],
        ]],
        'PTIPD' => ['Pusat Teknologi Informasi dan Pangkalan Data', [
            ['Pengembangan Sistem Informasi Akademik Terpadu', 'kajian', 'SP8', 540, 'BLU'],
            ['Peningkatan Jaringan dan Pusat Data', 'fisik', 'SP8', 1_860, 'BLU'],
            ['Langganan Layanan Awan dan Lisensi Perangkat Lunak', 'rutin', 'SP8', 920, 'BLU'],
        ]],
        'Admisi' => ['Pusat Admisi', [
            ['Seleksi Mahasiswa Baru Jalur Mandiri', 'acara', 'SP1', 640, 'BLU'],
            ['Promosi dan Roadshow Penerimaan Mahasiswa Baru', 'acara', 'SP1', 210, 'BLU'],
        ]],
        'International Office' => ['Kantor Urusan Internasional', [
            ['Penguatan Kerja Sama Luar Negeri', 'acara', 'SP6', 230, 'BLU'],
            ['Program Pertukaran Mahasiswa Internasional', 'acara', 'SP6', 315, 'BLU'],
        ]],
        'Bagian Umum' => ['Bagian Umum, Pusat Administrasi Universitas', [
            ['Operasional dan Pemeliharaan Perkantoran', 'rutin', 'SP8', 9_940, 'RM'],
            ['Pengerasan Tanah Kampus 2', 'fisik', 'SP9', 7_998, 'BLU'],
            ['Pembuatan Jaringan Listrik dan Air Bersih Kampus 2', 'fisik', 'SP9', 3_026, 'BLU'],
            ['Pembuatan Sarana Parkir Kampus Timur', 'fisik', 'SP8', 273, 'BLU'],
            ['Wisuda Universitas', 'acara', 'SP5', 2_092, 'BLU'],
            ['Penyusunan Laporan Keuangan dan SAKIP', 'kajian', 'SP8', 185, 'BLU'],
        ]],
    ],
];
