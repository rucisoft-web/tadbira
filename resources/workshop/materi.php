<?php

/*
 * Materi instruktur Workshop 2 hari mengenal Tadbira (/workshop/materi).
 *
 * Menambah video: isi 'video' dengan tautan YouTube, mis.
 *   'video' => 'https://www.youtube.com/watch?v=XXXXXXXXXXX',
 *   (tautan youtu.be/... dan youtube.com/embed/... juga diterima)
 *
 * Menambah gambar: unggah berkas ke images/workshop/ di folder web (public_html
 * di cPanel, atau tadbira/public) dengan nama
 * sesuai 'file' (boleh .webp, .jpg, .jpeg, atau .png; nama tanpa ekstensi).
 * Selama berkas belum ada, halaman menampilkan kotak placeholder.
 *
 * Setelah mengubah berkas ini di server: php artisan optimize:clear
 */

return [
    [
        'slug' => 'sesi-1',
        'day' => 1,
        'time' => '08.30–09.30',
        'minutes' => 60,
        'tag' => 'Sesi 1 · konsep',
        'title' => 'Manajemen proyek di perguruan tinggi',
        'summary' => 'Kegiatan sebagai proyek, istilah PMBOK (SRK, tahap, tonggak, jalur kritis), dan tur Tadbira.',
        'goals' => [
            'Peserta melihat setiap RO/kegiatan kampus sebagai proyek: punya tujuan, batas waktu, anggaran, dan penanggung jawab.',
            'Peserta memahami istilah SRK, tahap, aktivitas, tonggak, pendahulu, dan jalur kritis.',
            'Peserta mengenal tata letak Tadbira: dasbor, daftar kegiatan, dan tab-tab kegiatan.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Pemantik', 'text' => 'Tanyakan: "Kegiatan apa di unit Anda yang tahun lalu baru selesai di Desember?" Kumpulkan 3–4 jawaban dan tulis di papan. Ini menjadi contoh sepanjang sesi.'],
            ['minutes' => 15, 'title' => 'Kegiatan sebagai proyek', 'text' => 'Jelaskan ciri proyek (sementara, hasil unik, dibatasi waktu dan biaya). Petakan istilah PMBOK ke istilah kampus: RO = kegiatan, Komponen = tahap, Subkomponen = aktivitas, BAST = tonggak.'],
            ['minutes' => 10, 'title' => 'Jalur kritis dengan contoh sederhana', 'text' => 'Gambar di papan seminar nasional: undangan, narasumber, tempat, cetak sertifikat. Tunjukkan rangkaian terpanjang. Terlambat sehari di rangkaian itu berarti acara mundur sehari.'],
            ['minutes' => 20, 'title' => 'Tur Tadbira (data demo UIN Suka)', 'text' => 'Masuk sebagai tamu. Dasbor → Perlu perhatian → buka satu kegiatan terlambat → tab Ringkasan, Kisi SRK, Gantt. Belum masuk ke detail; cukup tunjukkan "di mana apa".'],
            ['minutes' => 5, 'title' => 'Penutup sesi', 'text' => 'Kaitkan dengan akronim TADBIRA dan sebutkan sesi mana yang membahas tiap huruf.'],
        ],
        'demo' => [
            ['text' => 'Buka halaman depan, pilih kartu UIN Sunan Kalijaga, klik Masuk sebagai Tamu.', 'href' => '/'],
            ['text' => 'Di Dasbor, tunjukkan empat kartu angka (Pagu DIPA, Anggaran kegiatan, Realisasi, Aktivitas terlambat).', 'href' => '/dasbor'],
            ['text' => 'Klik satu kegiatan di Perlu perhatian. Tunjukkan tab Ringkasan, Kisi SRK, dan Gantt dengan jalur kritis.'],
            ['text' => 'Kembali ke menu Kegiatan dan saring kesehatan jadwal = Terlambat.', 'href' => '/proyek'],
        ],
        'exercise' => [
            'Tuliskan satu kegiatan unit Anda: nama, kode RO, tanggal acara/serah terima, anggaran, penanggung jawab.',
            'Sebutkan 3 tonggak (hasil yang bisa diperiksa) dari kegiatan itu.',
        ],
        'keyPoints' => [
            'Tonggak adalah hasil yang bisa diperiksa, bukan kesibukan.',
            'Jalur kritis menentukan tanggal selesai; perhatian pimpinan paling berharga di sana.',
        ],
        'questions' => [
            'Apa beda aktivitas dan tonggak?',
            'Mengapa aktivitas di luar jalur kritis boleh terlambat sedikit?',
        ],
        'notes' => 'Siapkan akun tamu sebelum sesi dimulai dan uji proyektor di resolusi 1366 px. Bila koneksi lambat, gunakan tangkapan layar cadangan di bawah.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-01-a', 'caption' => 'Peta istilah PMBOK ke istilah kampus (RO, komponen, subkomponen, BAST)'],
            ['file' => 'sesi-01-b', 'caption' => 'Contoh jalur kritis seminar nasional'],
            ['file' => 'sesi-01-c', 'caption' => 'Tur dasbor portofolio data demo UIN Suka'],
        ],
    ],
    [
        'slug' => 'sesi-2',
        'day' => 1,
        'time' => '09.45–10.45',
        'minutes' => 60,
        'tag' => 'Sesi 2 · praktik',
        'title' => 'Menyiapkan organisasi',
        'summary' => 'Unit kerja, tahun anggaran, hari libur, pengguna, dan peran.',
        'goals' => [
            'Peserta bisa mendaftarkan organisasi dan menjadi admin.',
            'Peserta menyiapkan unit kerja, tahun anggaran, dan hari libur kampus.',
            'Peserta memahami lima peran (admin, pimpinan, perencana, keuangan, operator unit) dan hak aksesnya.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Siapa melakukan apa', 'text' => 'Tampilkan tabel peran. Minta peserta menyebut siapa di unitnya yang cocok untuk tiap peran.'],
            ['minutes' => 10, 'title' => 'Demo: daftar organisasi', 'text' => 'Klik Daftarkan organisasi, isi data, tunjukkan tahun anggaran berjalan otomatis dibuat.'],
            ['minutes' => 25, 'title' => 'Praktik: Pengaturan', 'text' => 'Peserta mengisi Pengaturan > Organisasi, Unit kerja (minimal 3 unit), Tahun anggaran (pagu), dan Hari libur (dies natalis atau cuti bersama).'],
            ['minutes' => 10, 'title' => 'Praktik: pengguna', 'text' => 'Tambah dua pengguna: satu operator unit dan satu keuangan. Catat kata sandi awal yang tampil sekali.'],
            ['minutes' => 5, 'title' => 'Cek', 'text' => 'Masuk sebagai operator unit di jendela penyamaran; tunjukkan menu Pengaturan tidak tampil.'],
        ],
        'demo' => [
            ['text' => 'Halaman masuk → Daftarkan organisasi.', 'href' => '/register'],
            ['text' => 'Pengaturan > Organisasi: nama, kode satker, sektor Perguruan Tinggi.', 'href' => '/pengaturan/organisasi'],
            ['text' => 'Pengaturan > Unit kerja: tambah fakultas/biro.', 'href' => '/pengaturan/unit'],
            ['text' => 'Pengaturan > Tahun anggaran dan Hari libur.', 'href' => '/pengaturan/tahun-anggaran'],
            ['text' => 'Pengaturan > Pengguna: + Tambah pengguna, pilih peran dan unit.', 'href' => '/pengaturan/pengguna'],
        ],
        'exercise' => [
            'Daftarkan organisasi latihan dengan nama unit Anda.',
            'Buat 3 unit kerja, isi pagu tahun anggaran, dan tambahkan 1 hari libur kampus.',
            'Tambahkan 2 pengguna dengan peran berbeda.',
        ],
        'keyPoints' => [
            'Sektor organisasi menentukan istilah di seluruh aplikasi (Pagu DIPA, Kegiatan).',
            'Nonaktifkan, jangan hapus, pengguna yang mutasi: jejak kerjanya tetap tersimpan.',
        ],
        'questions' => [
            'Siapa yang boleh menetapkan ulang baseline?',
            'Operator unit bisa mengubah kegiatan apa saja?',
        ],
        'notes' => 'Peserta sering salah memilih sektor. Pastikan semua memilih Perguruan Tinggi sebelum lanjut ke impor POK.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-02-a', 'caption' => 'Formulir Daftarkan organisasi'],
            ['file' => 'sesi-02-b', 'caption' => 'Pengaturan > Unit kerja'],
            ['file' => 'sesi-02-c', 'caption' => 'Pengaturan > Pengguna dan pilihan peran'],
        ],
    ],
    [
        'slug' => 'sesi-3',
        'day' => 1,
        'time' => '10.45–12.00',
        'minutes' => 75,
        'tag' => 'Sesi 3 · praktik',
        'title' => 'Impor POK RKA-K/L dari SAKTI',
        'summary' => 'RO menjadi kegiatan, komponen menjadi SRK.',
        'goals' => [
            'Peserta bisa mengekspor POK dari SAKTI dan mengimpornya ke Tadbira.',
            'Peserta memahami bagaimana tingkat POK (Program sampai Detail) dipetakan menjadi kegiatan dan SRK.',
            'Peserta melengkapi kegiatan hasil impor: penanggung jawab, unit, sasaran, tanggal.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Struktur POK', 'text' => 'Tampilkan contoh POK dan tandai tingkatnya: Program, Kegiatan, KRO, RO, Komponen, Subkomponen, Akun, Detail.'],
            ['minutes' => 10, 'title' => 'Demo impor', 'text' => 'Menu Impor POK / realisasi → pilih berkas → Banyak kegiatan sekaligus → centang Bentuk struktur rincian kerja → Impor.'],
            ['minutes' => 30, 'title' => 'Praktik impor', 'text' => 'Peserta mengimpor contoh POK (atau POK unitnya). Instruktur dan asisten berkeliling.'],
            ['minutes' => 20, 'title' => 'Merapikan hasil', 'text' => 'Buka satu kegiatan hasil impor: isi penanggung jawab, unit, sasaran program, dan tanggal mulai. Tunjukkan tab Anggaran berisi akun dan detail.'],
            ['minutes' => 5, 'title' => 'Tanya jawab', 'text' => 'Bahas berkas yang gagal dibaca (biasanya hasil pindai atau kolom diubah manual).'],
        ],
        'demo' => [
            ['text' => 'Menu Impor POK / realisasi; unduh contoh POK dengan tombol Unduh contoh POK.', 'href' => '/anggaran/impor'],
            ['text' => 'Impor dengan Hasil impor = Banyak kegiatan sekaligus.'],
            ['text' => 'Buka menu Kegiatan; tunjukkan kode lengkap seperti 2132.BEA.001.', 'href' => '/proyek'],
            ['text' => 'Buka satu kegiatan → tab Kisi SRK (komponen menjadi tahap) dan tab Anggaran.'],
        ],
        'exercise' => [
            'Impor contoh POK ke organisasi latihan.',
            'Lengkapi satu kegiatan hasil impor dengan penanggung jawab, unit, dan sasaran program.',
        ],
        'keyPoints' => [
            'Pakai ekspor SAKTI apa adanya; jangan ubah judul kolom.',
            'Impor ulang berkas yang sama memperbarui kegiatan berkode sama, bukan menggandakan.',
        ],
        'questions' => [
            'Kapan memilih "Ke kegiatan yang ada" daripada "Banyak kegiatan sekaligus"?',
        ],
        'notes' => 'Siapkan berkas contoh POK di flashdisk atau tautan unduhan lokal untuk peserta yang tidak membawa POK sendiri.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-03-a', 'caption' => 'Contoh POK dengan tingkat yang ditandai'],
            ['file' => 'sesi-03-b', 'caption' => 'Halaman Impor POK / realisasi'],
            ['file' => 'sesi-03-c', 'caption' => 'Daftar kegiatan hasil impor'],
        ],
    ],
    [
        'slug' => 'sesi-4',
        'day' => 1,
        'time' => '13.00–14.30',
        'minutes' => 90,
        'tag' => 'Sesi 4 · praktik',
        'title' => 'Menyusun SRK dan jadwal',
        'summary' => 'Pendahulu dan jeda, tonggak, hitung mundur, Gantt, jalur kritis.',
        'goals' => [
            'Peserta menyusun SRK satu kegiatan: tahap, aktivitas, dan tonggak.',
            'Peserta menulis pendahulu FS, SS, FF dengan jeda positif dan negatif.',
            'Peserta memakai Hitung mundur untuk kegiatan bertanggal pasti dan membaca jalur kritis di Gantt.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Aturan menulis pendahulu', 'text' => 'Tampilkan tabel: 1.2, 1.2+3, 1.2SS+2, 1.2FF, 1.2-2. Latih lisan dengan contoh seminar.'],
            ['minutes' => 15, 'title' => 'Demo Kisi SRK', 'text' => 'Tambah tahap, aktivitas, dan tonggak. Ubah lama dan pendahulu langsung di kisi; tunjukkan tanggal dihitung ulang.'],
            ['minutes' => 35, 'title' => 'Praktik SRK', 'text' => 'Peserta menyusun SRK kegiatan unitnya: minimal 3 tahap, 8 aktivitas, 3 tonggak.'],
            ['minutes' => 10, 'title' => 'Hitung mundur', 'text' => 'Buat kegiatan baru dengan Penjadwalan = Hitung mundur dari tanggal acara. Bahas kapan pengadaan paling lambat dimulai.'],
            ['minutes' => 15, 'title' => 'Gantt dan jalur kritis', 'text' => 'Tab Gantt, centang Tampilkan jalur kritis. Seret batang dan tunjukkan penerus ikut bergeser.'],
            ['minutes' => 5, 'title' => 'Cek', 'text' => 'Setiap peserta menyebut aktivitas kritis di kegiatannya.'],
        ],
        'demo' => [
            ['text' => 'Buka kegiatan → tab Kisi SRK → kotak Tambah unsur SRK.'],
            ['text' => 'Isi Pendahulu dengan 1.2SS+2 dan tunjukkan panah di Gantt.'],
            ['text' => '+ Kegiatan baru → Penjadwalan: Hitung mundur.', 'href' => '/proyek/buat'],
            ['text' => 'Tab Gantt: skala Minggu, Tampilkan jalur kritis, garis akhir tahun anggaran.'],
        ],
        'exercise' => [
            'Susun SRK kegiatan unit Anda (3 tahap, 8 aktivitas, 3 tonggak).',
            'Pakai minimal satu pendahulu SS atau FF.',
            'Tuliskan aktivitas kritis dan perkiraan tanggal selesai.',
        ],
        'keyPoints' => [
            'Lama dihitung dalam hari kerja; akhir pekan dan hari libur dilewati otomatis.',
            'Bila perkiraan selesai melewati garis akhir tahun anggaran, putuskan sekarang: percepat, kurangi lingkup, atau revisi.',
        ],
        'questions' => [
            'Apa arti 1.2-2 di kolom Pendahulu?',
            'Mengapa batang oranye di Gantt perlu dipantau lebih sering?',
        ],
        'notes' => 'Sesi terpanjang hari pertama. Bila peserta tertinggal, beri SRK contoh untuk diketik ulang agar tetap bisa mengikuti bagian Gantt.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-04-a', 'caption' => 'Tabel penulisan pendahulu'],
            ['file' => 'sesi-04-b', 'caption' => 'Kisi SRK yang sudah tersusun'],
            ['file' => 'sesi-04-c', 'caption' => 'Gantt dengan jalur kritis'],
        ],
    ],
    [
        'slug' => 'sesi-5',
        'day' => 1,
        'time' => '14.45–15.45',
        'minutes' => 60,
        'tag' => 'Sesi 5 · praktik',
        'title' => 'Templat dan baseline',
        'summary' => 'Kegiatan rutin sebagai templat, lalu mengunci baseline jadwal dan anggaran.',
        'goals' => [
            'Peserta menyimpan kegiatan rutin sebagai templat dan membuat kegiatan baru darinya.',
            'Peserta menetapkan baseline dan memahami mengapa baseline diperlukan.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Mengapa templat', 'text' => 'Kegiatan rutin (wisuda, PKKMB, akreditasi) berulang tiap tahun. Templat menyimpan urutan dan lama yang sudah teruji.'],
            ['minutes' => 15, 'title' => 'Praktik templat', 'text' => 'Tab Ringkasan → Simpan sebagai templat. Lalu + Kegiatan baru → pilih templat → lihat Pratinjau jadwal.'],
            ['minutes' => 10, 'title' => 'Mengapa baseline', 'text' => 'Jadwal di Tadbira bergeser otomatis. Tanpa baseline, keterlambatan "hilang". Baseline adalah foto rencana yang disepakati.'],
            ['minutes' => 15, 'title' => 'Praktik baseline', 'text' => 'Tab Kinerja → Tetapkan baseline. Lalu ubah lama satu aktivitas dan tunjukkan kolom Baseline di Kisi SRK serta garis abu-abu di Gantt.'],
            ['minutes' => 10, 'title' => 'Diskusi', 'text' => 'Siapa di kampus yang berwenang menyetujui perubahan baseline? Kaitkan ke sesi 9 (kendali perubahan).'],
        ],
        'demo' => [
            ['text' => 'Tab Ringkasan → kotak Simpan sebagai templat.'],
            ['text' => '+ Kegiatan baru → pilih Templat → Pratinjau jadwal.', 'href' => '/proyek/buat'],
            ['text' => 'Tab Kinerja → Tetapkan baseline → kartu Baseline.'],
        ],
        'exercise' => [
            'Simpan kegiatan unit Anda sebagai templat.',
            'Tetapkan baseline kegiatan unit Anda.',
        ],
        'keyPoints' => [
            'Tetapkan baseline setelah SRK, jadwal, dan anggaran disepakati, bukan sebelumnya.',
            'Penetapan ulang baseline wajib beralasan dan sebaiknya lewat permintaan perubahan.',
        ],
        'questions' => [
            'Apa yang terjadi pada ukuran kemajuan rencana setelah baseline ditetapkan?',
        ],
        'notes' => 'Ingatkan peserta untuk menetapkan baseline pada kegiatan yang akan dipakai di hari kedua.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-05-a', 'caption' => 'Simpan sebagai templat di tab Ringkasan'],
            ['file' => 'sesi-05-b', 'caption' => 'Pratinjau jadwal dari templat'],
            ['file' => 'sesi-05-c', 'caption' => 'Kartu Baseline di tab Kinerja'],
        ],
    ],
    [
        'slug' => 'refleksi',
        'day' => 1,
        'time' => '15.45–16.15',
        'minutes' => 30,
        'tag' => 'Refleksi',
        'title' => 'Refleksi dan tugas',
        'summary' => 'Melengkapi SRK kegiatan unit sebagai bahan hari kedua.',
        'goals' => [
            'Peserta merangkum apa yang sudah dibangun hari ini.',
            'Peserta tahu tugas yang harus selesai sebelum hari kedua.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Satu kalimat', 'text' => 'Setiap meja menyampaikan satu hal yang paling berguna dan satu hal yang masih membingungkan.'],
            ['minutes' => 10, 'title' => 'Periksa kesiapan', 'text' => 'Cek bersama: kegiatan unit sudah punya SRK, jadwal, anggaran, dan baseline?'],
            ['minutes' => 10, 'title' => 'Tugas malam', 'text' => 'Lengkapi SRK dan siapkan Laporan Ketersediaan Dana Detail unit (bila ada) untuk Sesi 6.'],
        ],
        'demo' => [],
        'exercise' => [
            'Lengkapi SRK kegiatan unit Anda.',
            'Unduh Laporan Ketersediaan Dana Detail terakhir dari SAKTI/OM-SPAN (opsional).',
        ],
        'keyPoints' => [
            'Hari kedua dimulai dari kegiatan yang sudah ber-baseline.',
        ],
        'questions' => [],
        'notes' => 'Catat pertanyaan yang belum terjawab di papan dan jawab di Ulasan hari pertama besok pagi.',
        'video' => '',
        'images' => [
            ['file' => 'refleksi-a', 'caption' => 'Daftar periksa kesiapan hari kedua'],
        ],
    ],
    [
        'slug' => 'sesi-6',
        'day' => 2,
        'time' => '08.15–09.45',
        'minutes' => 90,
        'tag' => 'Sesi 6 · praktik',
        'title' => 'Anggaran dan realisasi',
        'summary' => 'Realisasi per akun dan unggah Laporan Ketersediaan Dana Detail bulanan.',
        'goals' => [
            'Peserta mencatat realisasi per akun dengan status komitmen, ditagih, dan dibayar.',
            'Peserta mengunggah Laporan Ketersediaan Dana Detail dan membaca kurva S.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Uang keluar bukan satu-satunya ukuran', 'text' => 'Contoh panitia membayar uang muka 2 juta dari pesanan 6 juta. Bedakan komitmen, ditagih, dan dibayar.'],
            ['minutes' => 20, 'title' => 'Praktik realisasi manual', 'text' => 'Tab Anggaran → + realisasi pada baris akun. Isi tanggal, jumlah, status, keterangan.'],
            ['minutes' => 15, 'title' => 'Demo Laporan Ketersediaan Dana Detail', 'text' => 'Impor POK / realisasi → unggah laporan PDF atau XLS. Tunjukkan pagu revisi dan realisasi s.d. periode diperbarui.'],
            ['minutes' => 25, 'title' => 'Praktik unggah bulanan', 'text' => 'Peserta mengunggah laporan contoh bulan Juni, lalu Juli. Tunjukkan realisasi dicatat sebagai selisih dan unggah ulang tidak menggandakan.'],
            ['minutes' => 20, 'title' => 'Membaca kurva S', 'text' => 'Dasbor → Penyerapan anggaran kumulatif. Diskusikan arti realisasi di bawah rencana dan di atas kemajuan fisik.'],
        ],
        'demo' => [
            ['text' => 'Tab Anggaran → + realisasi pada baris akun.'],
            ['text' => 'Impor POK / realisasi → unggah Laporan Ketersediaan Dana Detail.', 'href' => '/anggaran/impor'],
            ['text' => 'Dasbor → Serapan per fakultas/unit dan kurva S.', 'href' => '/dasbor'],
        ],
        'exercise' => [
            'Catat dua realisasi manual dengan status berbeda.',
            'Unggah dua laporan bulanan berurutan dan bandingkan kurva S.',
        ],
        'keyPoints' => [
            'Tahun anggaran laporan harus sama dengan tahun anggaran tujuan.',
            'Unggah ulang periode yang sama aman: angka tidak digandakan.',
        ],
        'questions' => [
            'Apa arti realisasi 80% tetapi kemajuan fisik 40%?',
        ],
        'notes' => 'Laporan hasil pindai (gambar) tidak bisa dibaca. Minta PDF unduhan langsung dari SAKTI/OM-SPAN.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-06-a', 'caption' => 'Tab Anggaran dengan realisasi per akun'],
            ['file' => 'sesi-06-b', 'caption' => 'Hasil unggah Laporan Ketersediaan Dana Detail'],
            ['file' => 'sesi-06-c', 'caption' => 'Kurva S rencana dan realisasi'],
        ],
    ],
    [
        'slug' => 'sesi-7',
        'day' => 2,
        'time' => '10.00–11.15',
        'minutes' => 75,
        'tag' => 'Sesi 7 · konsep dan praktik',
        'title' => 'Nilai hasil dan beban kerja',
        'summary' => 'SPI, CPI, EAC per kegiatan dan portofolio.',
        'goals' => [
            'Peserta memahami PV, EV, AC, SPI, CPI, dan EAC dengan satu contoh angka.',
            'Peserta membaca tab Kinerja dan kartu Kinerja nilai hasil di dasbor.',
            'Peserta membaca beban kerja per orang per minggu.',
        ],
        'flow' => [
            ['minutes' => 20, 'title' => 'Contoh hitung di papan', 'text' => 'Anggaran 100 juta, rencana per hari ini 50%, selesai 40%, sudah dibayar 50 juta. Hitung PV 50, EV 40, AC 50, SPI 0,80, CPI 0,80, EAC 125 juta.'],
            ['minutes' => 15, 'title' => 'Demo tab Kinerja', 'text' => 'Buka kegiatan demo yang terlambat. Cocokkan angka di layar dengan rumus.'],
            ['minutes' => 20, 'title' => 'Praktik', 'text' => 'Peserta memperbarui % selesai di kegiatannya, lalu membaca SPI dan CPI yang berubah.'],
            ['minutes' => 15, 'title' => 'Beban kerja', 'text' => 'Menu Beban kerja: siapa yang kelebihan beban minggu ini? Pindahkan penanggung jawab satu aktivitas dan lihat perubahannya.'],
            ['minutes' => 5, 'title' => 'Pesan kunci', 'text' => 'SPI di bawah 0,95 masuk Perlu perhatian; di bawah 0,80 Terlambat.'],
        ],
        'demo' => [
            ['text' => 'Buka kegiatan → tab Kinerja (indikator nilai hasil).'],
            ['text' => 'Dasbor → kartu Kinerja nilai hasil.', 'href' => '/dasbor'],
            ['text' => 'Menu Beban kerja.', 'href' => '/beban-kerja'],
        ],
        'exercise' => [
            'Hitung SPI dan CPI untuk kasus: anggaran 200 juta, rencana 60%, selesai 45%, dibayar 100 juta.',
            'Perbarui % selesai tiga aktivitas di kegiatan Anda dan catat SPI baru.',
        ],
        'keyPoints' => [
            'Serapan saja tidak cukup; nilai hasil menggabungkan jadwal, hasil fisik, dan biaya.',
            'Isi % selesai dengan jujur berdasarkan hasil nyata.',
        ],
        'questions' => [
            'CPI 1,20 selalu kabar baik? (Petunjuk: pembayaran termin yang tertinggal.)',
        ],
        'notes' => 'Jawaban latihan: PV 120 juta, EV 90 juta, AC 100 juta, SPI 0,75, CPI 0,90.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-07-a', 'caption' => 'Diagram PV, EV, AC'],
            ['file' => 'sesi-07-b', 'caption' => 'Tab Kinerja kegiatan'],
            ['file' => 'sesi-07-c', 'caption' => 'Halaman Beban kerja'],
        ],
    ],
    [
        'slug' => 'sesi-8',
        'day' => 2,
        'time' => '11.15–12.00',
        'minutes' => 45,
        'tag' => 'Sesi 8 · praktik',
        'title' => 'Sasaran program dan IKU',
        'summary' => 'Capaian IKU dan pantauan serapan per fakultas/unit.',
        'goals' => [
            'Peserta mengaitkan kegiatan ke sasaran program dan IKU.',
            'Peserta membaca capaian IKU (kategori KMA 94/2021) dan serapan per unit.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Dari kegiatan ke sasaran', 'text' => 'Tanyakan: kegiatan Anda mendukung IKU yang mana? Tunjukkan contoh di data demo UIN Suka.'],
            ['minutes' => 20, 'title' => 'Praktik', 'text' => 'Kaitkan kegiatan unit ke sasaran program; isi target dan capaian IKU.'],
            ['minutes' => 10, 'title' => 'Pantauan per unit', 'text' => 'Dasbor: saring per fakultas/unit dan per sasaran; urutkan kegiatan menurut ketertinggalan.'],
            ['minutes' => 5, 'title' => 'Penutup', 'text' => 'Bahan ini langsung dipakai untuk laporan kinerja (SAKIP).'],
        ],
        'demo' => [
            ['text' => 'Dasbor → bagian Sasaran program dan IKU.', 'href' => '/dasbor'],
            ['text' => 'Menu Kegiatan → saring per sasaran program.', 'href' => '/proyek'],
        ],
        'exercise' => [
            'Kaitkan kegiatan unit Anda ke satu sasaran program dan isi capaiannya.',
        ],
        'keyPoints' => [
            'Satu kegiatan boleh mendukung satu sasaran utama; pilih yang paling langsung.',
        ],
        'questions' => [
            'Unit mana yang serapannya rendah tetapi capaian IKU-nya tinggi, dan apa artinya?',
        ],
        'notes' => 'Bila waktu mepet menjelang Jumat, sesi ini bisa digeser ke sore setelah Sesi 9.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-08-a', 'caption' => 'Sasaran program dan IKU di dasbor'],
            ['file' => 'sesi-08-b', 'caption' => 'Serapan per fakultas/unit'],
        ],
    ],
    [
        'slug' => 'sesi-9',
        'day' => 2,
        'time' => '13.00–14.00',
        'minutes' => 60,
        'tag' => 'Sesi 9 · praktik',
        'title' => 'Risiko, perubahan, dan dokumen',
        'summary' => 'Matriks 5×5, permintaan perubahan, arsip dokumen kegiatan.',
        'goals' => [
            'Peserta membedakan risiko dan isu serta mengisi register risiko.',
            'Peserta mengajukan dan memutuskan permintaan perubahan.',
            'Peserta mengunggah dokumen bukti dan mengaitkannya ke tonggak.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Risiko atau isu?', 'text' => '"Kendaraan mungkin rusak" adalah risiko; "kendaraan sudah mogok" adalah isu. Latih dengan 4 contoh dari peserta.'],
            ['minutes' => 15, 'title' => 'Praktik register risiko', 'text' => 'Tab Risiko → + Risiko. Isi probabilitas, dampak, strategi, pemilik, batas respons. Lihat matriks 5×5.'],
            ['minutes' => 15, 'title' => 'Praktik kendali perubahan', 'text' => 'Tab Perubahan → + Ajukan perubahan (dampak jadwal dan biaya). Berpasangan: satu mengajukan, satu (peran perencana) memutuskan dan menetapkan ulang baseline.'],
            ['minutes' => 15, 'title' => 'Praktik dokumen', 'text' => 'Tab Dokumen → unggah TOR dan BAST contoh, pilih kategori, kaitkan ke tonggak "BAST diterbitkan".'],
            ['minutes' => 5, 'title' => 'Pesan kunci', 'text' => 'Mana buktinya? Pertanyaan auditor terjawab dalam dua klik.'],
        ],
        'demo' => [
            ['text' => 'Tab Risiko: matriks risiko dan daftar.'],
            ['text' => 'Tab Perubahan: + Ajukan perubahan, lalu Putuskan.'],
            ['text' => 'Tab Dokumen: unggah beberapa berkas sekaligus.'],
        ],
        'exercise' => [
            'Catat 2 risiko dan 1 isu di kegiatan Anda.',
            'Ajukan 1 permintaan perubahan dan minta rekan memutuskannya.',
            'Unggah minimal 2 dokumen dan kaitkan ke tonggak.',
        ],
        'keyPoints' => [
            'Setiap perubahan diajukan, ditimbang dampaknya, diputuskan, lalu dicatat.',
            'Skor risiko 17–25 ekstrem: bawa ke rapat pimpinan.',
        ],
        'questions' => [
            'Strategi apa yang cocok untuk risiko penyedia terlambat?',
        ],
        'notes' => 'Siapkan 3 berkas contoh (PDF kecil) untuk peserta yang tidak membawa dokumen.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-09-a', 'caption' => 'Matriks risiko 5×5'],
            ['file' => 'sesi-09-b', 'caption' => 'Permintaan perubahan dan keputusan'],
            ['file' => 'sesi-09-c', 'caption' => 'Tab Dokumen dengan kategori dan tonggak'],
        ],
    ],
    [
        'slug' => 'sesi-10',
        'day' => 2,
        'time' => '14.00–15.00',
        'minutes' => 60,
        'tag' => 'Sesi 10 · praktik',
        'title' => 'Dasbor dan laporan pimpinan',
        'summary' => 'Laporan status satu halaman, ekspor Excel, ritme rapat bulanan.',
        'goals' => [
            'Peserta menyajikan dasbor portofolio untuk rapat pimpinan.',
            'Peserta mencetak laporan status satu halaman dan mengekspor Excel.',
            'Peserta menyusun ritme pemantauan bulanan di unitnya.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Enam pertanyaan pimpinan', 'text' => 'Kegiatan mana terlambat? Serapan sesuai rencana? Unit mana perlu didorong? Mendukung sasaran? Mana buktinya? Siapa harus bergerak?'],
            ['minutes' => 15, 'title' => 'Demo dasbor untuk rapat', 'text' => 'Urutan presentasi: kartu angka → Perlu perhatian → Kinerja nilai hasil → Risiko utama → Perubahan menunggu keputusan.'],
            ['minutes' => 20, 'title' => 'Praktik laporan', 'text' => 'Laporan status kegiatan (siap cetak/PDF) dan Unduh Excel per kegiatan serta portofolio.'],
            ['minutes' => 15, 'title' => 'Siklus bulanan', 'text' => 'Isi jadwal unit: tanggal 1–3 pengingat, 3–5 kemajuan, 5–7 realisasi, 7–8 verifikasi, 10 rapat evaluasi.'],
        ],
        'demo' => [
            ['text' => 'Dasbor portofolio dengan filter unit.', 'href' => '/dasbor'],
            ['text' => 'Buka kegiatan → Laporan status → cetak/PDF.'],
            ['text' => 'Unduh Excel portofolio.', 'href' => '/portofolio/excel'],
        ],
        'exercise' => [
            'Cetak laporan status kegiatan Anda ke PDF.',
            'Tuliskan siklus bulanan unit Anda: siapa, kapan, apa.',
        ],
        'keyPoints' => [
            'Tim yang mengisi data; pimpinan cukup membaca dan memutuskan.',
            'Rapat bulanan membahas keputusan, bukan menyamakan data.',
        ],
        'questions' => [
            'Siapa di unit Anda yang mengunggah realisasi tiap bulan?',
        ],
        'notes' => 'Halaman Untuk eksekutif (/untuk-eksekutif) bisa dipakai sebagai bahan paparan singkat ke pimpinan.',
        'video' => '',
        'images' => [
            ['file' => 'sesi-10-a', 'caption' => 'Kartu kendali di dasbor'],
            ['file' => 'sesi-10-b', 'caption' => 'Laporan status satu halaman'],
            ['file' => 'sesi-10-c', 'caption' => 'Siklus pemantauan bulanan'],
        ],
    ],
    [
        'slug' => 'studi-kasus',
        'day' => 2,
        'time' => '15.15–15.45',
        'minutes' => 30,
        'tag' => 'Studi kasus',
        'title' => 'Presentasi kegiatan unit',
        'summary' => 'Perwakilan peserta menampilkan dasbor dan laporan statusnya.',
        'goals' => [
            'Peserta mempresentasikan kegiatan unit yang disusun selama dua hari.',
            'Peserta saling memberi masukan dengan sudut pandang pimpinan.',
        ],
        'flow' => [
            ['minutes' => 3, 'title' => 'Aturan main', 'text' => 'Tiga tim, masing-masing 7 menit presentasi dan 2 menit tanggapan.'],
            ['minutes' => 27, 'title' => 'Presentasi', 'text' => 'Tunjukkan: SRK dan jalur kritis, baseline, SPI/CPI, satu risiko utama, satu permintaan perubahan, dan laporan status.'],
        ],
        'demo' => [],
        'exercise' => [
            'Siapkan presentasi 7 menit dengan urutan di atas.',
        ],
        'keyPoints' => [
            'Nilai yang dicari: jujur pada angka, bukti tersedia, keputusan jelas.',
        ],
        'questions' => [
            'Bila Anda pimpinan, keputusan apa yang akan Anda ambil dari laporan ini?',
        ],
        'notes' => 'Pilih tim dari unit berbeda (fakultas, biro, lembaga) agar variasi kasusnya terlihat.',
        'video' => '',
        'images' => [
            ['file' => 'studi-kasus-a', 'caption' => 'Dokumentasi presentasi peserta'],
        ],
    ],
    [
        'slug' => 'penutupan',
        'day' => 2,
        'time' => '15.45–16.15',
        'minutes' => 30,
        'tag' => 'Penutupan',
        'title' => 'Penutupan dan sertifikat',
        'summary' => 'Rangkuman, rencana tindak lanjut, evaluasi, dan penyerahan sertifikat.',
        'goals' => [
            'Peserta pulang dengan rencana tindak lanjut 30 hari.',
            'Penyelenggara mendapat umpan balik workshop.',
        ],
        'flow' => [
            ['minutes' => 10, 'title' => 'Rangkuman TADBIRA', 'text' => 'Ulas tujuh huruf dan sesi yang membahasnya.'],
            ['minutes' => 5, 'title' => 'Rencana 30 hari', 'text' => 'Setiap peserta menulis: satu kegiatan yang akan dipantau di Tadbira dan siapa yang mengisi data.'],
            ['minutes' => 5, 'title' => 'Evaluasi', 'text' => 'Isi formulir evaluasi workshop.'],
            ['minutes' => 10, 'title' => 'Sertifikat', 'text' => 'Penyerahan sertifikat oleh Pusat Studi Manajemen Pendidikan Tinggi UIN Sunan Kalijaga dan foto bersama.'],
        ],
        'demo' => [],
        'exercise' => [
            'Tulis rencana 30 hari: kegiatan, penanggung jawab data, tanggal rapat evaluasi pertama.',
        ],
        'keyPoints' => [
            'Mulai kecil: satu unit, satu periode pelaporan, lalu perluas.',
        ],
        'questions' => [],
        'notes' => 'Bagikan tautan panduan pengguna (/panduan) dan kontak WhatsApp Rucisoft untuk pendampingan.',
        'video' => '',
        'images' => [
            ['file' => 'penutupan-a', 'caption' => 'Foto bersama dan penyerahan sertifikat'],
        ],
    ],
];
