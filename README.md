<p align="center"><img src="public/images/tadbira-logo.svg" alt="Tadbira" width="260"></p>

# Tadbira

**Kelola proyek dengan amanah.** Tadbira adalah aplikasi manajemen proyek berbasis web untuk memantau banyak kegiatan dalam satu tahun anggaran, di kementerian, lembaga, badan, pemerintah daerah, perguruan tinggi, BUMN, BUMD, swasta, maupun organisasi nirlaba. Nama ini diambil dari *tadbīr* (يُدَبِّرُ الْأَمْرَ, "Dia mengatur urusan", QS. Yunus: 3).

Istilah mengikuti PMBOK edisi ke-4 (SRK/WBS, tahap, tonggak, jalur kritis, pemangku kepentingan) dan menyesuaikan sektor organisasi: kementerian melihat *Pagu DIPA* dan *Kegiatan*, BUMN melihat *Anggaran RKAP* dan *Program Kerja*, swasta melihat *Anggaran* dan *Proyek*.

## Fitur

- **Dasbor portofolio** per tahun anggaran: kartu ringkasan, donat status, kesehatan proyek (berdasarkan SPI), batang kemajuan rencana vs realisasi, kurva S anggaran kumulatif per bulan, dan Gantt portofolio.
- **Banyak proyek dalam satu tahun anggaran**, dengan pemilih tahun anggaran dan pemindah organisasi (satu pengguna bisa tergabung di beberapa organisasi).
- **Penjadwalan dinamis (CPM)** pada hari kerja: ketergantungan selesai-ke-mulai dengan jeda, tonggak, rollup tahap, deteksi siklus, hari libur nasional dan organisasi, serta jalur kritis dan *slack*.
- **Hitung mundur**: isi tanggal acara, aplikasi menghitung kapan harus mulai.
- **Gantt chart** interaktif: skala hari/minggu/bulan, panah ketergantungan, sorot jalur kritis, geser batang untuk memindah, tarik ujung untuk mengubah durasi, garis hari ini dan batas tahun anggaran.
- **Kisi SRK** yang bisa disunting langsung: durasi, pendahulu (`1.2, 1.3+2`), persen selesai, penanggung jawab.
- **Templat proyek**: simpan proyek sebagai templat, lalu buat proyek baru cukup dengan memilih tanggal mulai. Pratinjau menampilkan tanggal selesai, tiap tahap, tonggak, dan peringatan bila melewati tahun anggaran.
- **Impor POK RKA-K/L** (ekspor SAKTI), **Laporan Ketersediaan Dana Detail** (PDF atau XLS dari SAKTI/OM-SPAN, termasuk realisasi s.d. periode), dan **RAB/RKAP**: setiap RO menjadi satu kegiatan, dan struktur Komponen/Subkomponen bisa langsung dibentuk menjadi SRK. Unggah laporan realisasi tiap bulan untuk memperbarui pagu dan realisasi.
- **Anggaran dan realisasi** per baris (akun/pos) dengan status komitmen/tagihan/dibayar.
- **Sasaran program & IKU** per tahun anggaran: setiap kegiatan dikaitkan ke sasaran, dasbor menampilkan target, capaian (kategori KMA 94/2021), serta kemajuan dan serapan kegiatan pendukungnya.
- **Pantauan per unit kerja**: grafik serapan per fakultas/unit, filter unit dan sasaran, serta daftar kegiatan yang bisa diurutkan menurut ketertinggalan atau serapan.
- **Riwayat** perubahan per proyek.

## Teknologi

Laravel 12 (PHP 8.3+), MySQL 8 / MariaDB 10.6+, Inertia.js + Vue 3, Tailwind CSS, Chart.js, PhpSpreadsheet, smalot/pdfparser.

## Instalasi

```bash
git clone https://github.com/rucisoft-web/tadbira.git
cd tadbira
composer install
cp .env.example .env
php artisan key:generate
```

Buat basis data MySQL lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tadbira
DB_USERNAME=root
DB_PASSWORD=
```

Lanjutkan:

```bash
php artisan migrate --seed   # --seed mengisi data contoh
npm install
npm run build                # atau `npm run dev` saat pengembangan
php artisan serve
```

Buka http://127.0.0.1:8000 dan masuk dengan akun contoh **admin@tadbira.test** / **password**. Data contoh (seluruhnya karangan) berisi tiga organisasi:

- **UIN Sunan Kalijaga Yogyakarta, TA 2026** (dibuka pertama): 99 kegiatan di 18 fakultas/unit, sembilan sasaran program dengan IKU, serta jadwal, kemajuan, anggaran per akun, dan realisasi bulanan. Tiap unit punya akun operator, mis. `fitk@demo.uin-suka.test` / `password`. Datanya ada di `database/data/demo-ptkin-2026.php`.
- Sebuah direktorat kementerian dan sebuah BUMN dengan beberapa proyek dan tiga templat. Pindah organisasi lewat pemilih di bilah samping. Untuk organisasi sendiri, daftar lewat halaman *Daftar*: pilih sektor organisasi, dan tahun anggaran berjalan dibuat otomatis.

### Pemasangan di server

- Arahkan *document root* web server ke folder `public/`.
- Jalankan `npm run build` sebelum mengunggah (folder `public/build` hasilnya ikut diunggah) bila server tidak memiliki Node.js.
- Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server.
- Di produksi, set `APP_ENV=production`, `APP_DEBUG=false`, lalu jalankan `php artisan config:cache route:cache view:cache`.
- Hari libur nasional di data contoh hanya yang bertanggal tetap. Tambahkan cuti bersama dan hari besar Islam per tahun ke tabel `holidays` (`organization_id` kosong untuk libur nasional).

## Format impor anggaran

### POK RKA-K/L

Gunakan ekspor POK dari SAKTI apa adanya. Aplikasi mencari baris judul berisi `KODE` dan `URAIAN`, lalu membaca kolom `VOLUME`, `SATUAN`, `HARGA SATUAN`, `JUMLAH BIAYA`, dan `SD/CP` bila ada. Tingkat dikenali dari pola kodenya:

| Tingkat | Contoh kode |
| --- | --- |
| Program | `025.04.DL` |
| Kegiatan | `4471` |
| KRO | `QDB` atau `4471.QDB` |
| RO | `001` (baris dengan volume dan satuan) atau `4471.QDB.001` |
| Komponen | `051` |
| Subkomponen | `A` |
| Akun | `521211` |
| Detail | tanpa kode, diawali `-` atau `>` |

Baris jumlah/total dilewati, dan jumlah induk yang kosong dihitung dari anak-anaknya. Contoh berkas bisa diunduh dari halaman *Impor* di aplikasi.

### Laporan Ketersediaan Dana Detail (realisasi)

Unggah laporan *Ketersediaan Dana Detail* per Program/Kegiatan/Output/SubOutput/Komponen dari SAKTI atau OM-SPAN, baik PDF hasil unduhan (bukan hasil pindai) maupun XLS. Kode menyatu dengan uraian dan memakai kode baru (Program `DK`, Kegiatan `DK.2132`, KRO `BEA`, RO `BEA.001`, Komponen `004`).

- *Pagu Revisi* menjadi anggaran, dan *Realisasi s.d. Periode* dicatat sebagai realisasi per komponen pada akhir bulan periode laporan.
- Setiap RO menjadi satu kegiatan berkode lengkap, mis. `2132.BEA.001`. Komponen menjadi aktivitas sepanjang tahun anggaran, dengan kemajuan mengikuti persentase realisasi anggaran.
- Unggah laporan bulan berikutnya ke tahun anggaran yang sama: kegiatan berkode sama diperbarui pagu dan realisasinya. Realisasi dicatat sebagai selisih per bulan, sehingga kurva S terbentuk, dan unggah ulang periode yang sama tidak menggandakan angka.
- Tahun anggaran laporan harus sama dengan tahun anggaran tujuan.

### RAB / RKAP

Untuk pemda, BUMN, BUMD, swasta, dan nirlaba. Kolom: `KODE`, `URAIAN`, `LEVEL` (`kelompok`, `pos`, atau `detail`), `VOLUME`, `SATUAN`, `HARGA SATUAN`, `JUMLAH`, `SUMBER DANA`. Setiap *kelompok* menjadi satu proyek.

## Pengujian

```bash
php artisan test
```

Tes mencakup mesin penjadwalan (maju, mundur, tonggak, jeda, siklus), pembaca POK/RAB, pembuatan proyek dari templat, impor POK lewat HTTP, dan pembatasan akses antarorganisasi.

## Struktur penting

| Lokasi | Isi |
| --- | --- |
| `config/tadbira.php` | Sektor organisasi, istilah per sektor, tingkat anggaran |
| `app/Services/Scheduling` | Kalender kerja, mesin CPM, penjadwal proyek, pengurai pendahulu |
| `app/Services/Budget` | Pembaca dan pengimpor POK/RAB, contoh berkas |
| `app/Services/PortfolioService.php` | Ringkasan dasbor, kesehatan, kurva S |
| `app/Services/TemplateService.php` | Pratinjau dan pembuatan proyek dari templat |
| `resources/js/Pages` | Halaman Vue (Dasbor, Kegiatan, Impor) |
| `resources/js/Components/ProjectGantt.vue` | Gantt interaktif |
