## Pengantar

Tadbira adalah aplikasi web untuk merencanakan, memantau, dan melaporkan portofolio kegiatan atau proyek sebuah organisasi dalam satu tahun anggaran. Panduan ini ditulis untuk pimpinan, manajer kegiatan, operator unit, dan admin. Setiap bab memadukan dua hal: teori manajemen proyek dari buku *Catatan Kecil Manajer Proyek* (Jarot Wahyudi & Jaya Martha) dan langkah praktis di Tadbira, lengkap dengan tangkapan layar.

Buku tersebut menyusun sembilan bidang pengetahuan manajemen proyek (mengikuti PMBOK) di atas empat sifat kenabian, diikat oleh istikamah. Tadbira mengikuti peta yang sama, sehingga setiap menu punya alasan nilai di baliknya.

| Nilai | Bidang pengetahuan | Fitur Tadbira |
| --- | --- | --- |
| Istikamah (konsisten) | Integrasi | Dasbor portofolio, siklus pemantauan bulanan |
| Sidik (benar) | Lingkup, Waktu | Daftar kegiatan, templat SRK, Gantt, jalur kritis |
| Amanah (dapat dipercaya) | Biaya, Mutu | Anggaran, impor POK, realisasi, kurva S, tonggak BAST, dokumen kegiatan |
| Tabligh (menyampaikan) | SDM, Komunikasi | Penanggung jawab, akun operator, sasaran & IKU, laporan per unit |
| Fathanah (cerdas) | Risiko, Pengadaan | Kesehatan kegiatan (SPI), daftar Perlu perhatian, tahapan pengadaan |

Cara membaca panduan ini: mulai dari bab Memulai untuk masuk ke aplikasi, lalu baca bab per bidang sesuai peran Anda. Pengguna baru bisa mencoba semua langkah tanpa akun lewat Mode tamu dengan tiga data demo (UIN Sunan Kalijaga, MedcoEnergi, UNJ).

Pimpinan yang hanya butuh gambaran manfaatnya bisa mulai dari halaman [Tadbira untuk eksekutif](/untuk-eksekutif): ringkas, tanpa langkah teknis.

## 1. Memulai

Buka alamat Tadbira organisasi Anda (misalnya tadbira.rucisoft.id), lalu klik **Masuk**. Ada dua cara masuk: dengan akun sendiri, atau sebagai tamu untuk melihat data demo.

![Halaman masuk dengan tiga kartu data demo](/images/panduan/01-login.webp)

### Masuk dengan akun

1. Isi **Surel** dan **Kata sandi** yang diberikan admin.
2. Centang **Ingat saya** bila memakai komputer pribadi.
3. Klik **Masuk**. Anda akan dibawa ke Dasbor portofolio organisasi aktif.

Lupa kata sandi? Klik **Lupa kata sandi?**, isi surel, lalu buka tautan yang dikirim ke kotak masuk Anda. Bila surel tidak datang, minta admin mengatur ulang (lihat bab 8).

### Mode tamu dan kartu data demo

Di bawah formulir masuk ada bagian **Coba data demo tanpa akun** berisi tiga kartu: UIN Suka (99 kegiatan, pagu Rp68,8 M), MedcoEnergi (66 proyek, pagu Rp19,4 T), dan UNJ (62 kegiatan, pagu Rp92,1 M). Klik **Masuk sebagai Tamu** pada salah satu kartu untuk langsung melihat dasbornya.

Mode tamu hanya untuk melihat. Pita kuning di atas layar mengingatkan hal ini, dan setiap upaya menyimpan perubahan akan ditolak dengan pesan merah.

![Perubahan ditolak dalam mode tamu](/images/panduan/08-tamu-tolak.webp)

### Berpindah organisasi

Nama organisasi aktif tampil di kiri atas. Jika akun Anda tergabung di beberapa organisasi (atau Anda sedang menjadi tamu), pilih organisasi lain dari menu tarik-turun di bawah nama tersebut. Semua halaman langsung beralih ke data organisasi itu.

### Profil dan kata sandi

Klik nama Anda di kanan atas, lalu **Profil**. Di sini Anda bisa mengubah nama, surel, dan kata sandi. Gunakan kata sandi minimal 8 karakter dan jangan memakai ulang kata sandi layanan lain.

![Halaman profil](/images/panduan/07-profil.webp)

## 2. Integrasi: dasbor portofolio

### Teori: istikamah menjaga keselarasan

Buku menggambarkan kegiatan yang bagian-bagiannya beres tetapi hasilnya mengecewakan: "Panitia makanan sudah siap, tetapi tempat acara belum tersedia." Integrasi berarti menyatukan tujuan, lingkup, waktu, biaya, mutu, manusia, komunikasi, risiko, dan pengadaan agar bergerak menuju hasil yang disepakati. Nilainya adalah istikamah, keteguhan yang merajut sidik, amanah, tabligh, dan fathanah sejak gagasan dibicarakan sampai hasil diserahkan.

> "Maka, tetaplah (di jalan yang benar), sebagaimana engkau (Nabi Muhammad) telah diperintahkan." (QS Hud [11]:112)
>
> "Tolong-menolonglah kamu dalam (mengerjakan) kebajikan dan takwa ..." (QS Al-Ma'idah [5]:2)

Enam proses integrasi menurut buku: menyusun piagam proyek, menyusun rencana manajemen proyek, mengarahkan pelaksanaan, memantau dan mengendalikan pekerjaan, mengendalikan perubahan terpadu, dan menutup proyek. Untuk pemantauan, buku menekankan "gambaran menyeluruh": pencapaian hasil, penggunaan dana, kemajuan waktu, mutu, dan hambatan dilihat bersama, dan setiap masalah punya penanggung jawab serta waktu tindak lanjut.

### Di Tadbira: Dasbor portofolio

Dasbor adalah halaman pertama setelah masuk. Ia menyajikan gambaran menyeluruh itu untuk seluruh kegiatan dalam satu tahun anggaran.

![Bagian atas dasbor: pagu, anggaran, realisasi, kesehatan, dan Perlu perhatian](/images/panduan/02a-dasbor-atas.webp)

1. **Pilih tahun anggaran dan unit** di bilah atas (misalnya TA 2026, Semua fakultas/unit).
2. **Baca empat kartu angka**: Pagu DIPA, Anggaran kegiatan, Realisasi, dan Aktivitas terlambat.
3. **Kesehatan jadwal** membagi kegiatan menjadi Sesuai jadwal, Perlu perhatian, dan Terlambat. **Status kegiatan** menunjukkan tahap Perencanaan, Pelaksanaan, atau Selesai.
4. **Perlu perhatian** mendaftar kegiatan dengan indeks kinerja jadwal terendah. Klik namanya untuk langsung membuka kegiatan.

![Serapan per unit dan kurva penyerapan anggaran kumulatif](/images/panduan/02b-dasbor-grafik.webp)

5. **Serapan per fakultas/unit** membandingkan realisasi dengan anggaran tiap unit. **Penyerapan anggaran kumulatif** (kurva S) membandingkan rencana penyerapan, realisasi, dan pagu.
6. **Kemajuan per kegiatan** menampilkan batang kemajuan aktual dengan garis rencana per hari ini. Kegiatan dengan selisih terbesar ada di atas.

![Kemajuan per kegiatan dibanding rencana](/images/panduan/02d-kemajuan.webp)

7. **Garis waktu portofolio** menampilkan seluruh kegiatan dalam satu Gantt ringkas, lengkap dengan tonggak dan garis hari ini. Klik **Lihat semua** untuk daftar penuh.

![Garis waktu portofolio](/images/panduan/02e-garis-waktu.webp)

Tampilan yang sama dipakai untuk korporasi. Pada sektor swasta, istilahnya menyesuaikan: Sasaran strategis, KPI, dan Unit bisnis/aset.

![Dasbor demo MedcoEnergi](/images/panduan/09-dasbor-medco.webp)

## 3. Lingkup: kegiatan dan SRK

### Teori: sidik memperjelas janji

Menurut buku, banyak perselisihan bermula dari kalimat "Saya kira sudah termasuk." Lingkup adalah batas pekerjaan: apa yang dikerjakan, apa yang dihasilkan, dan apa yang tidak termasuk. Nilainya sidik, yaitu benar dan jujur sejak pembicaraan pertama.

> "Wahai orang-orang yang beriman, penuhilah janji-janji!" (QS Al-Ma'idah [5]:1)
>
> "... dan penuhilah janji (karena) sesungguhnya janji itu pasti diminta pertanggungjawabannya." (QS Al-Isra' [17]:34)

Lima proses lingkup: mengumpulkan kebutuhan, mendefinisikan lingkup, menyusun struktur rincian kerja (SRK), memverifikasi lingkup, dan mengendalikan lingkup. Tentang SRK, buku menulis: "Pekerjaan besar biasanya terasa sulit karena masih dibayangkan sebagai satu kesatuan." Dengan menguraikannya, setiap bagian mudah diberikan kepada penanggung jawab dan diperiksa penyelesaiannya.

### Di Tadbira: daftar kegiatan

Menu **Kegiatan** memuat semua kegiatan dalam tahun anggaran terpilih, dengan jadwal, kemajuan dibanding rencana, kesehatan, anggaran, dan realisasi.

![Daftar kegiatan dengan penyaring](/images/panduan/03-proyek.webp)

- Cari berdasarkan nama, kode, atau kategori.
- Saring berdasarkan kesehatan jadwal, fakultas/unit, atau sasaran program.
- Urutkan berdasarkan tanggal mulai, kemajuan, atau anggaran.
- Klik nama kegiatan untuk membuka rinciannya.

### Membuat kegiatan baru

Klik **+ Kegiatan baru**.

![Formulir kegiatan baru dengan pilihan Hitung mundur](/images/panduan/05-buat.webp)

1. Pilih **Templat** bila ada kegiatan serupa sebelumnya, atau biarkan Tanpa templat.
2. Isi **Nama kegiatan** dan **Kode** (misalnya kode RO dari POK).
3. Pilih **Tahun anggaran** dan **Penanggung jawab kegiatan**.
4. Pilih cara **Penjadwalan**. *Dari tanggal mulai* menghitung kapan selesai. *Hitung mundur* dipakai untuk acara bertanggal pasti (wisuda, seminar): isi tanggal acara, aplikasi menghitung kapan harus mulai.
5. Isi **Anggaran**, **Fakultas/unit/kategori**, **Sasaran program**, dan **Uraian** singkat tentang hasil yang dijanjikan.
6. Klik **Buat kegiatan**.

Saat templat dipilih, kotak **Pratinjau jadwal** di kanan menunjukkan kapan kegiatan akan selesai sebelum Anda menyimpannya.

### Kisi SRK: menguraikan pekerjaan

Buka kegiatan, lalu tab **Kisi SRK**. Setiap baris adalah unsur SRK dengan jenis Tahap, Aktivitas, atau Tonggak.

![Kisi SRK kegiatan Pembangunan Gedung Laboratorium Bahasa](/images/panduan/11-kisi-srk.webp)

1. Di kotak **Tambah unsur SRK**, isi nama, pilih jenis, pilih induknya di **Di bawah**, isi **Lama** (hari kerja) dan **Pendahulu**.
2. Klik **Tambah**. Nomor SRK (1, 1.1, 1.2, ...) dibuat otomatis.
3. Ubah lama, pendahulu, % selesai, atau penanggung jawab langsung di kisi. Tanggal mulai dan selesai dihitung ulang seketika.

Tips sidik: tuliskan hasil yang bisa diperiksa sebagai tonggak (misalnya "Kontrak ditandatangani", "BAST diterbitkan"), bukan hanya kesibukan.

### Templat: belajar dari kegiatan lalu

Di tab **Ringkasan**, kotak **Simpan sebagai templat** menyimpan SRK kegiatan ini untuk dipakai ulang. Centang **Pakai lama pengerjaan aktual sebagai pembelajaran** agar templat memakai durasi nyata, bukan rencana awal.

## 4. Waktu: Gantt dan jalur kritis

### Teori: sidik dalam menepati waktu

"Besok selesai." Buku mengingatkan bahwa kalimat ini mudah diucapkan, tetapi janji waktu sebaiknya dibuat setelah menghitung pekerjaan, tenaga, dan bahan. Mengelola waktu adalah bentuk penghormatan terhadap kepentingan orang lain.

> "Sesungguhnya dia adalah orang yang benar janjinya, rasul, dan nabi." (QS Maryam [19]:54)
>
> "Demi masa, ... kecuali orang-orang yang beriman dan beramal saleh serta saling menasihati untuk kebenaran dan kesabaran." (QS Al-'Asr [103]:1-3)

Enam proses waktu: menentukan aktivitas, mengurutkan aktivitas, memperkirakan tenaga/peralatan/bahan, memperkirakan lama pengerjaan, menyusun jadwal, dan mengendalikan jadwal. Pada pengurutan, buku memberi contoh mengecat kamar: dinding lembap harus ditangani dulu, "memaksa mengecat permukaan yang belum siap dapat merusak hasil." Pada pengendalian, pemeriksaan didasarkan pada hasil nyata, bukan banyaknya orang yang sibuk.

### Di Tadbira: urutan, lama, dan pendahulu

Di Kisi SRK, kolom **Pendahulu** menyatakan pekerjaan yang harus ditunggu. Tulis nomor SRK-nya:

| Isi Pendahulu | Artinya |
| --- | --- |
| `1.2` | Mulai setelah 1.2 selesai |
| `1.2+3` | Mulai 3 hari kerja setelah 1.2 selesai |
| `1.2, 1.3+2` | Menunggu keduanya; yang paling akhir menentukan |

Lama dihitung dalam hari kerja. Tadbira melewati akhir pekan dan menghitung tanggal mulai serta selesai secara otomatis.

### Gantt dan jalur kritis

Buka tab **Gantt** pada kegiatan.

![Gantt tampilan Bulan dengan jalur kritis menyala oranye](/images/panduan/12-gantt.webp)

1. Pilih skala **Hari**, **Minggu**, atau **Bulan**.
2. Centang **Tampilkan jalur kritis**. Batang oranye adalah aktivitas yang tidak punya kelonggaran: terlambat sehari di sini berarti kegiatan selesai sehari lebih lambat.
3. Seret batang untuk menggeser jadwal, tarik ujung kanan untuk mengubah lama. Aktivitas penerusnya ikut bergeser.
4. Belah ketupat adalah tonggak, garis kuning adalah hari ini, dan garis putus-putus adalah akhir tahun anggaran.

Kartu **Jalur kritis** di atas tab menunjukkan jumlah aktivitas kritis dan berapa yang terlambat. Kartu **Selesai** menunjukkan perkiraan tanggal selesai terbaru.

### Menggeser seluruh jadwal

Di tab **Ringkasan**, kotak **Jadwal & status** memungkinkan Anda mengubah tanggal mulai (atau tanggal acara untuk hitung mundur). Semua aktivitas bergeser bersama, dan tanggal selesai baru langsung tampil sebelum disimpan.

### Mengendalikan jadwal tiap bulan

Perbarui **% selesai** tiap aktivitas berdasarkan hasil nyata. Tadbira membandingkannya dengan rencana per hari ini dan memberi label kesehatan: **Sesuai jadwal**, **Perlu perhatian**, atau **Terlambat**. Jujurlah pada angka ini; seperti pesan buku, laporan yang terlalu yakin membuat keputusan dibangun di atas anggapan.

## 5. Biaya: anggaran dan realisasi

### Teori: amanah dalam setiap rupiah

"Uangnya sudah habis, tetapi pekerjaannya belum selesai." Buku menegaskan bahwa persoalan biaya menyangkut kepercayaan: setiap rupiah punya asal, tujuan, dan pihak yang berhak memperoleh penjelasan. Amanah diwujudkan melalui perhitungan yang masuk akal, penggunaan sesuai peruntukan, pencatatan yang benar, dan keterbukaan.

> "Dan, orang-orang yang apabila berinfak tidak berlebihan dan tidak (pula) kikir." (QS Al-Furqan [25]:67)
>
> "Wahai orang-orang yang beriman, apabila kamu berutang piutang untuk waktu yang ditentukan, hendaklah kamu mencatatnya." (QS Al-Baqarah [2]:282)

Tiga proses biaya: memperkirakan biaya, menetapkan anggaran, dan mengendalikan biaya. Pada pengendalian, buku memberi contoh panitia yang baru membayar uang muka dua juta rupiah dari pesanan enam juta. Jika hanya mencatat uang yang keluar, laporan akan terlihat aman, padahal kewajibannya sudah penuh.

### Di Tadbira: impor POK atau RAB

Cara tercepat menyusun anggaran adalah mengimpor berkas yang sudah ada. Buka menu **Impor POK / realisasi**.

![Halaman Impor POK / realisasi](/images/panduan/06-impor.webp)

1. Klik **Choose File** dan pilih berkas XLS, XLSX, atau PDF.
2. Pilih **Hasil impor**. *Banyak kegiatan sekaligus*: setiap RO menjadi satu kegiatan. *Ke kegiatan yang ada*: seluruh isi berkas menjadi rincian anggaran satu kegiatan.
3. Pilih **Tahun anggaran**.
4. Biarkan **Bentuk struktur rincian kerja dari struktur anggaran** tercentang bila ingin Komponen menjadi tahap dan Subkomponen menjadi aktivitas.
5. Klik **Impor**.

Format yang dikenali:

| Sumber | Untuk | Catatan |
| --- | --- | --- |
| POK RKA-K/L (ekspor SAKTI) | Kementerian, lembaga, PTN, PTKIN | Tingkat dikenali dari kode: Program, Kegiatan, KRO, RO, Komponen, Subkomponen, Akun, Detail |
| RAB / RKAP | Pemda, BUMN, BUMD, swasta, nirlaba | Tambahkan kolom LEVEL berisi kelompok, pos, atau detail |
| Laporan Ketersediaan Dana Detail (SAKTI / OM-SPAN) | Pembaruan bulanan | Pagu Revisi menjadi anggaran; Realisasi s.d. Periode langsung tercatat |

Unduh contoh berkasnya dengan tombol **Unduh contoh POK** atau **Unduh templat RAB/RKAP**. Kegiatan yang kodenya sama akan diperbarui, bukan dibuat ulang.

### Tab Anggaran dan pencatatan realisasi

![Tab Anggaran: rincian per akun dengan realisasi](/images/panduan/13-anggaran.webp)

Tab **Anggaran** pada kegiatan menampilkan pohon anggaran (komponen, akun, detail) dengan jumlah, sumber dana, dan realisasi. Untuk mencatat pembayaran di luar impor bulanan, klik **+ realisasi** pada baris akun, isi tanggal, jumlah, dan keterangan.

### Membaca serapan

Kartu **Anggaran** di atas tab menampilkan realisasi dan persentasenya. Di dasbor, kurva **Penyerapan anggaran kumulatif** membandingkan rencana penyerapan (mengikuti jadwal), realisasi, dan pagu. Realisasi yang jauh di bawah rencana menandakan pekerjaan atau pembayaran tertunda; realisasi di atas kemajuan fisik perlu ditanyakan.

## 6. Mutu, SDM, dan Komunikasi

### Mutu: amanah memenuhi ukuran yang dijanjikan

Buku menulis: "Atap sudah dipasang, namun bocor ketika hujan." Mutu berarti kesesuaian dengan kebutuhan dan persyaratan, termasuk bagian yang tidak terlihat oleh penerima. Tiga prosesnya: merencanakan mutu, melaksanakan penjaminan mutu, dan melaksanakan pengendalian mutu.

> "Sempurnakanlah takaran apabila kamu menakar dan timbanglah dengan timbangan yang benar." (QS Al-Isra' [17]:35)
>
> "... untuk menguji kamu, siapa di antara kamu yang lebih baik amalnya." (QS Al-Mulk [67]:2)

**Di Tadbira**, mutu dicatat lewat tonggak serah terima. Tambahkan tonggak seperti "Pemeriksaan dan serah terima" dan "BAST diterbitkan" di Kisi SRK. Tonggak tampil di tab **Ringkasan** beserta tanggalnya, dan baru diisi 100% setelah hasil diperiksa sesuai spesifikasi. Status kegiatan (Perencanaan, Pelaksanaan, Selesai) diubah di kotak **Jadwal & status**; ubah ke Selesai hanya setelah BAST terbit.

![Tab Ringkasan: aktivitas berikutnya, sasaran program, tonggak, dan jadwal & status](/images/panduan/10-ringkasan.webp)

### Dokumen kegiatan: bukti yang tersimpan rapi

Mutu perlu dibuktikan, bukan hanya diakui. Tab **Dokumen** pada setiap kegiatan menyimpan berkas digitalnya: TOR/KAK, RAB, kontrak, BAST, laporan, notulen, surat, dan foto dokumentasi.

![Tab Dokumen: daftar berkas dan kotak unggah](/images/panduan/14-dokumen.webp)

1. Buka kegiatan, lalu tab **Dokumen**.
2. Seret berkas ke kotak **Unggah dokumen**, atau klik untuk memilih. Bisa beberapa berkas sekaligus, dengan jenis berbeda (PDF, Word, Excel, PowerPoint, gambar, ZIP, dan lainnya).
3. Pilih **Kategori** (misalnya BAST) dan, bila perlu, aktivitas atau tonggak terkait di Kisi SRK (misalnya "BAST diterbitkan").
4. Klik **Unggah**.

Setiap berkas punya tombol **Lihat** (PDF dan gambar dibuka di peramban), **Unduh**, **Ubah** (judul, kategori, aktivitas terkait), dan **Hapus**. Ubah dan hapus hanya bisa dilakukan oleh pengunggah, penanggung jawab kegiatan, atau admin. Berkas disimpan di luar folder web dan hanya bisa diunduh oleh pengguna organisasi yang sama.

**Coba di data demo.** Setiap kegiatan di tiga data demo (UIN Sunan Kalijaga, MedcoEnergi, dan UNJ) sudah berisi contoh dokumen, sehingga fitur ini bisa dicoba tanpa mengunggah apa pun. Polanya mengikuti siklus kegiatan yang wajar:

| Kapan | Contoh dokumen | Kategori | Terkait tonggak |
|---|---|---|---|
| Sebelum mulai | Kerangka acuan kerja (TOR/KAK), RAB | TOR / KAK, RAB | |
| Kontrak diteken | Kontrak / SPK pelaksana | Kontrak / SPK | Kontrak ditandatangani |
| Selama berjalan | Foto progres lapangan, laporan kemajuan, bahan paparan | Foto, Laporan, Lainnya | |
| Acara (seminar, wisuda, rapat) | Surat undangan, notulen dan daftar hadir, dokumentasi | Surat, Notulen, Foto | Kegiatan terlaksana |
| Serah terima dan penutupan | BAST, laporan akhir | BAST, Laporan | BAST diterbitkan, Laporan diterima |

Masuk sebagai tamu dari halaman depan, buka kegiatan mana pun, lalu klik tab **Dokumen**. Tamu bisa melihat dan mengunduh, tetapi tidak bisa mengunggah, mengubah, atau menghapus. Isi berkas contoh itu sama untuk semua kegiatan dan diberi tanda "contoh dokumen data demo"; pada pemakaian nyata, berkasnya adalah dokumen asli kegiatan Anda.

Bagi pimpinan, tab ini menjawab pertanyaan auditor yang paling sering: "mana buktinya?" Dokumen yang terkait tonggak membuat klaim kemajuan bisa diperiksa langsung, misalnya tonggak "BAST diterbitkan" yang selesai disertai berkas BAST-nya.

### SDM: tabligh dalam memimpin tim

Menurut buku, kegiatan bisa kaya dana dan rencana tetapi tersendat "ketika orang-orang yang menjalankannya tidak memahami tugas masing-masing." Empat prosesnya: menyusun rencana SDM, memperoleh tim, mengembangkan tim, dan mengelola tim.

> "Sesungguhnya sebaik-baik orang yang engkau pekerjakan adalah orang yang kuat lagi dapat dipercaya." (QS Al-Qasas [28]:26)
>
> "... dan bermusyawarahlah dengan mereka dalam segala urusan (penting)." (QS Ali 'Imran [3]:159)

**Di Tadbira**, setiap kegiatan punya **Penanggung jawab kegiatan**, dan setiap aktivitas di Kisi SRK punya kolom **Penanggung jawab** sendiri. Dengan begitu jelas siapa mengerjakan apa. Bagian **Aktivitas berikutnya** di tab Ringkasan menampilkan pekerjaan yang segera jatuh tempo beserta penanggung jawabnya, sehingga mudah dibahas dalam rapat koordinasi.

### Komunikasi: tabligh menyampaikan kebenaran

"Saya sudah menyampaikan." Buku mengingatkan bahwa pesan yang disampaikan belum tentu membentuk pemahaman bersama. Lima prosesnya: mengidentifikasi pemangku kepentingan, merencanakan komunikasi, mendistribusikan informasi, mengelola harapan, dan melaporkan kinerja.

> "Wahai orang-orang yang beriman, bertakwalah kamu kepada Allah dan ucapkanlah perkataan yang benar." (QS Al-Ahzab [33]:70)
>
> "... maka telitilah kebenarannya ..." (QS Al-Hujurat [49]:6)

**Di Tadbira**, pelaporan kinerja tersusun otomatis dari data yang sama:

- **Sasaran program & IKU** di dasbor mengelompokkan kegiatan per sasaran, lengkap dengan kemajuan, serapan, dan capaian tiap indikator terhadap target. Hubungkan setiap kegiatan ke sasarannya lewat kolom **Sasaran program** agar muncul di sini.
- Saring dasbor per **fakultas/unit** untuk bahan rapat unit.
- Satu sumber data untuk semua pihak: pimpinan, unit, dan SPI melihat angka yang sama, sehingga tidak perlu rekap ulang di lembar kerja.

![Sasaran program & IKU dengan kemajuan, serapan, dan capaian indikator](/images/panduan/02c-sasaran.webp)

## 7. Risiko dan Pengadaan

### Risiko: fathanah membaca kemungkinan

Buku mencontohkan syukuran di halaman rumah yang tiba-tiba mendung: keluarga yang sudah menyiapkan tempat alternatif bisa segera menata ulang. Risiko adalah ketidakpastian yang dapat memengaruhi tujuan, berupa ancaman maupun peluang. "Kemungkinan kendaraan rusak merupakan risiko; kendaraan yang telah mogok merupakan masalah." Enam prosesnya: merencanakan manajemen risiko, mengidentifikasi, analisis kualitatif, analisis kuantitatif, merencanakan respons, serta memantau dan mengendalikan risiko.

> "Kemudian apa yang kamu tuai, biarkanlah di tangkainya, kecuali sedikit untuk kamu makan. ... Kemudian, sesudah itu akan datang tujuh (tahun) yang sangat sulit (paceklik) ..." (QS Yusuf [12]:47-48)
>
> "... dan bermusyawarahlah dengan mereka dalam segala urusan (penting)." (QS Ali 'Imran [3]:159)

**Di Tadbira**, tanda bahaya dini dihitung otomatis dari indeks kinerja jadwal (SPI), yaitu kemajuan aktual dibagi kemajuan rencana per hari ini:

| Label kesehatan | Syarat | Tindakan yang disarankan |
| --- | --- | --- |
| Sesuai jadwal | SPI 0,95 atau lebih | Pantau seperti biasa |
| Perlu perhatian | SPI 0,80 sampai di bawah 0,95 | Bahas di rapat bulanan, cari penyebab |
| Terlambat | SPI di bawah 0,80 | Tetapkan respons, penanggung jawab, dan tenggat |

Gunakan fitur-fitur ini sebagai daftar risiko yang hidup:

1. **Perlu perhatian** di dasbor: lima kegiatan dengan SPI terendah. Buka satu per satu.
2. **Aktivitas terlambat**: aktivitas yang tanggal selesainya lewat tetapi belum 100%.
3. **Jalur kritis**: aktivitas tanpa kelonggaran. Risiko pada aktivitas ini langsung menggeser tanggal selesai.
4. **Akhir tahun anggaran** di Gantt: bila perkiraan selesai melewati garis putus-putus, anggaran berisiko tidak terserap. Putuskan sejak dini: percepat, kurangi lingkup, atau ajukan revisi.
5. Saring daftar kegiatan dengan **Semua kesehatan jadwal** → Terlambat untuk bahan musyawarah pimpinan.

### Pengadaan: cermat memilih penyedia

Buku mengisahkan keluarga yang memilih penawaran renovasi termurah lalu dikejutkan biaya tambahan. Pengadaan mencakup keputusan tentang apa yang dibutuhkan, siapa yang mampu menyediakannya, bagaimana pekerjaan dipantau, dan bagaimana penyelesaiannya dipastikan. Empat prosesnya: merencanakan, melaksanakan, mengadministrasikan, dan menutup pengadaan.

> "Wahai orang-orang yang beriman, penuhilah janji-janji!" (QS Al-Ma'idah [5]:1)
>
> "Berlakulah adil karena (adil) itu lebih dekat pada takwa." (QS Al-Ma'idah [5]:8)

**Di Tadbira**, tahapan pengadaan dimodelkan sebagai aktivitas dan tonggak di Kisi SRK. Pola yang dipakai data demo untuk pekerjaan fisik:

1. Perencanaan dan penyusunan DED/spesifikasi (merencanakan pengadaan)
2. Pengadaan barang/jasa (melaksanakan pengadaan)
3. Tonggak: Kontrak ditandatangani
4. Pelaksanaan pekerjaan (mengadministrasikan kontrak)
5. Pemeriksaan dan serah terima
6. Tonggak: BAST diterbitkan (menutup pengadaan)

Simpan pola ini sebagai templat agar unit lain memakai urutan yang sama. Untuk kegiatan bertanggal pasti, pakai **Hitung mundur** supaya terlihat kapan proses pengadaan paling lambat harus dimulai.

## 8. Peran dan tugas admin

### Siapa melakukan apa

Setiap pengguna punya satu peran di organisasinya. Peran menentukan tugas sekaligus hak aksesnya di aplikasi; tombol yang tidak sesuai peran tidak ditampilkan.

| Peran | Tugas di Tadbira | Hak akses |
| --- | --- | --- |
| Pimpinan | Melihat dasbor portofolio, kegiatan kritis, dan capaian sasaran. Memimpin rapat evaluasi bulanan. | Melihat semua; tidak mengubah data. |
| Perencana / Biro | Menyusun daftar kegiatan, mengimpor POK, mengaitkan kegiatan ke sasaran dan IKU, dan memverifikasi laporan. | Mengubah semua kegiatan, membuat tahun anggaran, impor anggaran. |
| Keuangan | Mengunggah Laporan Ketersediaan Dana atau mencatat realisasi per akun setiap bulan. | Impor anggaran dan realisasi, mencatat realisasi, mengunggah dokumen. |
| Operator unit | Memperbarui persen selesai aktivitas, dokumen, dan kendala di unitnya. | Membuat dan mengubah kegiatan di unit kerjanya atau yang ia pimpin. |
| Admin | Mengatur organisasi, pengguna, unit kerja, tahun anggaran, dan hari libur. | Semua, termasuk menu **Pengaturan**. |

Penanggung jawab kegiatan bukan peran tersendiri: siapa pun yang ditunjuk memimpin sebuah kegiatan bisa mengubah kegiatan itu, walaupun kegiatannya ada di luar unitnya.

![Pengaturan > Peran & hak akses](/images/panduan/16-peran.webp)

### Siklus pemantauan bulanan

Istikamah dalam praktik berarti pemantauan yang berkesinambungan dan diikuti tindakan. Siklus yang disarankan:

1. **Tanggal 1-3, Pengingat**: operator unit diingatkan memperbarui kemajuan kegiatan.
2. **Tanggal 3-5, Isi kemajuan**: penanggung jawab mengisi % selesai di Kisi SRK berdasarkan hasil nyata.
3. **Tanggal 5-7, Unggah realisasi**: keuangan mengimpor Laporan Ketersediaan Dana Detail bulan lalu dari SAKTI/OM-SPAN.
4. **Tanggal 7-8, Verifikasi**: perencana memeriksa kegiatan berlabel Perlu perhatian dan Terlambat, serta menanyakan bukti.
5. **Tanggal 10, Rapat evaluasi**: pimpinan meninjau dasbor dan menetapkan tindak lanjut, penanggung jawab, serta tenggatnya.

### Tugas admin

**Mendaftarkan organisasi.** Di halaman masuk, klik **Daftarkan organisasi**. Isi nama Anda, nama organisasi, surel, dan kata sandi. Akun ini otomatis menjadi admin organisasi tersebut, dan tahun anggaran berjalan langsung dibuat.

Semua pengaturan berikut ada di menu **Pengaturan** di bilah samping, yang hanya tampil untuk admin.

**Profil organisasi.** Di **Pengaturan > Organisasi**, ubah nama, nama singkat, kode satker atau entitas, sektor, dan zona waktu. Sektor menentukan istilah yang dipakai di seluruh aplikasi.

**Menambah pengguna.** Buka **Pengaturan > Pengguna**, klik **+ Tambah pengguna**, lalu isi nama, surel, peran, dan unit kerja. Kata sandi awal boleh dikosongkan; Tadbira membuatnya dan menampilkannya sekali. Sampaikan kata sandi itu langsung kepada yang bersangkutan. Bila surelnya sudah terdaftar (misalnya di organisasi lain), akun itu langsung ditambahkan dengan kata sandinya sendiri.

![Pengaturan > Pengguna: peran, unit kerja, dan tindakan per akun](/images/panduan/15-pengguna.webp)

**Mengubah peran, unit, dan status.** Di daftar pengguna, ganti peran atau unit langsung dari kotak pilihannya. **Nonaktifkan** menutup akses tanpa menghapus jejaknya, cocok untuk pegawai yang mutasi atau pensiun; **Keluarkan** mencabut keanggotaannya. Kegiatan dan riwayat yang pernah dibuatnya tetap tersimpan. Organisasi selalu harus punya minimal satu admin aktif, dan admin tidak bisa menurunkan perannya sendiri.

**Setel ulang kata sandi.** Klik **Setel ulang sandi** pada baris pengguna. Kata sandi baru tampil sekali dan sesi lamanya ditutup. Untuk akun yang juga tergabung di organisasi lain, kata sandi hanya bisa diganti pemiliknya atau lewat terminal (lihat di bawah).

**Unit kerja.** Di **Pengaturan > Unit kerja**, daftarkan fakultas, biro, direktorat, atau wilayah kerja. Nama singkat unit dipakai sebagai kategori kegiatan dan filter dasbor. Mengganti nama unit ikut memperbarui kategori semua kegiatannya. Operator unit hanya bisa mengubah kegiatan di unitnya.

**Tahun anggaran.** Tambahkan tahun anggaran di **Pengaturan > Tahun anggaran** (atau **+ Tahun anggaran** di dasbor), lalu atur pagu, rentang tanggal, dan statusnya: Perencanaan, Berjalan, atau Ditutup. Tahun yang ditutup tidak menerima pencatatan realisasi lagi.

**Hari libur.** Di **Pengaturan > Hari libur**, tambahkan libur khusus organisasi (misalnya dies natalis) atau hari kerja pengganti. Kegiatan berjalan yang melewati tanggal itu langsung dijadwalkan ulang.

**Mengaktifkan mode tamu.** Di berkas `.env` server, isi `TADBIRA_GUEST_LOGIN=true`, lalu jalankan `php artisan optimize:clear`. Kartu data demo akan muncul di halaman masuk dan beranda. Isi `false` untuk menyembunyikannya.

**Melihat daftar pengguna lewat terminal** (di server, dalam folder aplikasi), misalnya saat tidak ada admin yang bisa masuk:

```bash
php artisan tadbira:pengguna
```

### Lupa kata sandi

Ada empat jalan, dari yang paling mudah:

1. **Lewat surel.** Pengguna klik **Lupa kata sandi?** di halaman masuk. Cara ini membutuhkan pengaturan SMTP di `.env` (MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS).
2. **Disetel ulang admin di aplikasi.** Buka **Pengaturan > Pengguna**, lalu klik **Setel ulang sandi** pada baris pengguna tersebut.
3. **Diatur ulang lewat terminal.** Jika kata sandi tidak ditulis, aplikasi membuat kata sandi acak 12 karakter dan menampilkannya.

```bash
php artisan tadbira:reset-sandi pengguna@instansi.ac.id
php artisan tadbira:reset-sandi pengguna@instansi.ac.id KataSandiBaru123
```

4. **Versi lama tanpa perintah tadbira.** Gunakan tinker:

```bash
php artisan tinker --execute="App\Models\User::where('email','pengguna@instansi.ac.id')->update(['password'=>bcrypt('KataSandiBaru123')]);"
```

Sampaikan kata sandi baru lewat saluran pribadi, lalu minta pengguna menggantinya di halaman **Profil**.

## Rujukan

- Jarot Wahyudi & Jaya Martha. *Catatan Kecil Manajer Proyek*. Sumber seluruh uraian teori dan peta nilai dalam panduan ini.
- Project Management Institute. 2008. *A Guide to the Project Management Body of Knowledge*, edisi keempat. Sembilan bidang ilmu dan 42 proses manajemen proyek.
- Petikan terjemahan ayat dari Quran NU Online: [Hud 11:112](https://quran.nu.or.id/hud/112) · [Al-Ma'idah 5:1](https://quran.nu.or.id/al-maidah/1) · [Al-Ma'idah 5:2](https://quran.nu.or.id/al-maidah/2) · [Al-Ma'idah 5:8](https://quran.nu.or.id/al-maidah/8) · [Al-Isra' 17:34](https://quran.nu.or.id/al-isra/34) · [Al-Isra' 17:35](https://quran.nu.or.id/al-isra/35) · [Maryam 19:54](https://quran.nu.or.id/maryam/54) · [Al-'Asr 103:1-3](https://quran.nu.or.id/al-ashr) · [Al-Furqan 25:67](https://quran.nu.or.id/al-furqan/67) · [Al-Baqarah 2:282](https://quran.nu.or.id/al-baqarah/282) · [Al-Mulk 67:2](https://quran.nu.or.id/al-mulk/2) · [Al-Qasas 28:26](https://quran.nu.or.id/al-qashash/26) · [Ali 'Imran 3:159](https://quran.nu.or.id/ali-imran/159) · [Al-Ahzab 33:70](https://quran.nu.or.id/al-ahzab/70) · [Al-Hujurat 49:6](https://quran.nu.or.id/al-hujurat/6) · [Yusuf 12:47-48](https://quran.nu.or.id/yusuf/47)

Tangkapan layar diambil dari data demo UIN Sunan Kalijaga TA 2026 dan MedcoEnergi TA 2026 dalam mode tamu. Angka pada data demo adalah rekaan untuk belajar, bukan data resmi.
