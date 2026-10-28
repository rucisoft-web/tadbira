<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DemoCards from '@/Components/DemoCards.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    demos: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
    guide: { type: Array, default: () => [] },
    workshop: { type: Object, default: () => ({}) },
});

const soon = 'Segera diumumkan';
const info = (key) => props.workshop[key] || soon;
const dates = computed(() => {
    const { day1, day2 } = props.workshop;
    if (day1 && day2) return `${day1} dan ${day2}`;
    return day1 || day2 || soon;
});
const wa = (text) => `https://wa.me/${props.links.whatsapp}?text=${encodeURIComponent(text)}`;
const register = computed(() => props.workshop.form || wa('Assalamu\'alaikum, saya ingin mendaftar Workshop 2 Hari Tadbira.'));
const inHouse = computed(() => wa('Assalamu\'alaikum, saya ingin menanyakan workshop Tadbira untuk instansi kami.'));
const waDisplay = computed(() => String(props.links.whatsapp || '').replace(/^62/, '0').replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3'));
const brochure = '/files/brosur-workshop-tadbira.pdf';
const guidePath = route('guide');
const executivePath = route('executive');

const problems = [
    { title: 'Jadwal tersebar di banyak berkas', text: 'Setiap unit menyimpan rencananya sendiri. Tidak ada yang tahu kegiatan mana yang bergantung pada kegiatan lain.' },
    { title: 'Realisasi baru terlihat saat ditarik dari SAKTI', text: 'Angka serapan muncul sebulan sekali dan tidak terhubung dengan kemajuan pekerjaan di lapangan.' },
    { title: 'Keterlambatan baru diketahui di akhir', text: 'Tanpa jalur kritis dan baseline, kegiatan yang tergelincir sepekan baru terasa ketika sudah tergelincir sebulan.' },
    { title: 'Kegiatan tidak terkait ke IKU', text: 'Sulit menjawab kegiatan mana yang menopang sasaran program, dan berapa capaiannya sekarang.' },
    { title: 'Risiko dan perubahan tidak tercatat', text: 'Perubahan lingkup dan jadwal terjadi lewat pesan singkat, tanpa jejak keputusan dan dampaknya.' },
    { title: 'Laporan pimpinan disusun ulang tiap bulan', text: 'Tim menyalin data ke presentasi, padahal pimpinan hanya butuh satu layar yang jujur.' },
];

const outcomes = [
    { title: 'Kegiatan dari POK', text: 'RO menjadi kegiatan, komponen dan subkomponen menjadi struktur rincian kerja (SRK).' },
    { title: 'Jadwal dengan baseline', text: 'Ketergantungan, tonggak, jalur kritis, dan baseline jadwal serta anggaran yang terkunci.' },
    { title: 'Rencana pemantauan bulanan', text: 'Alur unggah realisasi, register risiko awal, dan siapa memperbarui apa setiap bulan.' },
    { title: 'Laporan status satu halaman', text: 'Siap dibawa ke rapat pimpinan, beserta dasbor portofolio unit.' },
];

const acronym = [
    { letter: 'T', word: 'Task', text: 'Aktivitas SRK dengan penanggung jawab, durasi, dan beban kerja per orang.', session: 'Sesi 4 dan 7' },
    { letter: 'A', word: 'Activity', text: 'Kegiatan dari POK atau templat, lengkap dengan tahap dan tonggak.', session: 'Sesi 3 dan 5' },
    { letter: 'D', word: 'Delivery', text: 'Jadwal, jalur kritis, baseline, dan nilai hasil untuk memastikan kegiatan tuntas tepat waktu.', session: 'Sesi 4, 5, dan 7' },
    { letter: 'B', word: 'Budget', text: 'Pagu, realisasi per akun, dan kurva S dari laporan SAKTI atau OM-SPAN.', session: 'Sesi 6' },
    { letter: 'I', word: 'Integration', text: 'Satu portofolio yang menghubungkan kegiatan, anggaran, unit kerja, dan sasaran IKU.', session: 'Sesi 3 dan 8' },
    { letter: 'R', word: 'Risk', text: 'Register risiko dan isu, matriks 5×5, serta kendali perubahan.', session: 'Sesi 9' },
    { letter: 'A', word: 'Accountability', text: 'Peran yang jelas, dokumen bukti, riwayat perubahan, dan laporan untuk pimpinan.', session: 'Sesi 2, 9, dan 10' },
];

const modules = [
    {
        session: 'Hari 1 · Sesi 3', title: 'Impor POK RKA-K/L dari SAKTI', text: 'Tidak perlu mengetik ulang anggaran. Ekspor POK dari SAKTI dibaca apa adanya.',
        points: ['Setiap RO menjadi satu kegiatan berkode lengkap.', 'Komponen dan subkomponen langsung dibentuk menjadi SRK.', 'Juga mendukung RAB/RKAP untuk unit usaha dan BLU.'],
        img: '/images/panduan/06-impor.webp', w: 1200, h: 918,
    },
    {
        session: 'Hari 1 · Sesi 4', title: 'SRK, Gantt, dan jalur kritis', text: 'Penjadwalan pada hari kerja, lengkap dengan hari libur nasional dan kampus.',
        points: ['Pendahulu FS, SS, FF, SF dengan jeda, ditulis seperti 1.2, 2.1SS+1.', 'Hitung mundur: isi tanggal acara, Tadbira menghitung kapan harus mulai.', 'Gantt interaktif dengan sorotan jalur kritis dan garis hari ini.'],
        img: '/images/panduan/12-gantt.webp', w: 1110, h: 440,
    },
    {
        session: 'Hari 2 · Sesi 6', title: 'Realisasi anggaran tiap bulan', text: 'Unggah Laporan Ketersediaan Dana Detail (PDF atau XLS) dari SAKTI atau OM-SPAN.',
        points: ['Pagu revisi dan realisasi s.d. periode diperbarui otomatis.', 'Unggah ulang periode yang sama tidak menggandakan angka.', 'Kurva S rencana dan realisasi terbentuk dengan sendirinya.'],
        img: '/images/panduan/13-anggaran.webp', w: 1110, h: 640,
    },
    {
        session: 'Hari 2 · Sesi 7', title: 'Nilai hasil (earned value)', text: 'Satu angka untuk kesehatan jadwal dan satu untuk biaya, per kegiatan dan seluruh kampus.',
        points: ['PV, EV, AC, SPI, CPI, EAC, ETC, VAC, dan TCPI.', 'Varians terhadap baseline per aktivitas.', 'Beban kerja per orang per minggu, dengan sorotan kelebihan beban.'],
        img: '/images/panduan/17-kinerja.webp', w: 1110, h: 920,
    },
    {
        session: 'Hari 2 · Sesi 8', title: 'Sasaran program dan IKU', text: 'Setiap kegiatan dikaitkan ke sasaran, sehingga capaian dan serapan terbaca bersama.',
        points: ['Target dan capaian IKU dengan kategori KMA 94/2021.', 'Grafik serapan per fakultas dan unit kerja.', 'Daftar kegiatan diurutkan menurut ketertinggalan.'],
        img: '/images/panduan/02c-sasaran.webp', w: 1110, h: 1360,
    },
    {
        session: 'Hari 2 · Sesi 9', title: 'Risiko, perubahan, dan dokumen', text: 'Keputusan penting tercatat beserta dampaknya.',
        points: ['Register risiko dan isu dengan matriks 5×5.', 'Permintaan perubahan bernomor, diputuskan pimpinan, dapat menetapkan ulang baseline.', 'Arsip TOR, kontrak, BAST, dan laporan per kegiatan.'],
        img: '/images/panduan/18-risiko.webp', w: 1110, h: 740,
    },
    {
        session: 'Hari 2 · Sesi 10', title: 'Dasbor dan laporan pimpinan', text: 'Satu layar menjawab mana yang sehat, mana yang tersendat, dan apa yang menunggu keputusan.',
        points: ['Laporan status satu halaman siap cetak atau PDF.', 'Ekspor Excel per kegiatan maupun portofolio.', 'Ritme pemantauan bulanan untuk rapat pimpinan.'],
        img: '/images/panduan/22-dasbor-kendali.webp', w: 1110, h: 590,
    },
];

const days = [
    {
        label: 'Hari 1', date: 'day1', title: 'Merencanakan kegiatan', text: 'Dari pagu DIPA menjadi jadwal yang bisa dipantau.',
        slots: [
            { time: '07.30–08.00', title: 'Registrasi dan pembagian akun latihan', rest: true },
            { time: '08.00–08.30', title: 'Pembukaan', text: 'Sambutan Pusat Studi Manajemen Pendidikan Tinggi UIN Sunan Kalijaga.' },
            { time: '08.30–09.30', tag: 'Sesi 1 · konsep', title: 'Manajemen proyek di perguruan tinggi', text: 'Kegiatan sebagai proyek, istilah PMBOK (SRK, tahap, tonggak, jalur kritis), dan tur Tadbira.' },
            { time: '09.30–09.45', title: 'Rehat kopi', rest: true },
            { time: '09.45–10.45', tag: 'Sesi 2 · praktik', title: 'Menyiapkan organisasi', text: 'Unit kerja, tahun anggaran, hari libur, pengguna, dan peran.' },
            { time: '10.45–12.00', tag: 'Sesi 3 · praktik', title: 'Impor POK RKA-K/L dari SAKTI', text: 'RO menjadi kegiatan, komponen menjadi SRK.' },
            { time: '12.00–13.00', title: 'Ishoma', rest: true },
            { time: '13.00–14.30', tag: 'Sesi 4 · praktik', title: 'Menyusun SRK dan jadwal', text: 'Pendahulu dan jeda, tonggak, hitung mundur, Gantt, jalur kritis.' },
            { time: '14.30–14.45', title: 'Rehat', rest: true },
            { time: '14.45–15.45', tag: 'Sesi 5 · praktik', title: 'Templat dan baseline', text: 'Kegiatan rutin sebagai templat, lalu mengunci baseline jadwal dan anggaran.' },
            { time: '15.45–16.15', title: 'Refleksi dan tugas', text: 'Melengkapi SRK kegiatan unit sebagai bahan hari kedua.' },
        ],
    },
    {
        label: 'Hari 2', date: 'day2', title: 'Mengendalikan dan melaporkan', text: 'Dari realisasi bulanan menjadi keputusan pimpinan.',
        slots: [
            { time: '08.00–08.15', title: 'Ulasan hari pertama' },
            { time: '08.15–09.45', tag: 'Sesi 6 · praktik', title: 'Anggaran dan realisasi', text: 'Realisasi per akun dan unggah Laporan Ketersediaan Dana Detail bulanan.' },
            { time: '09.45–10.00', title: 'Rehat kopi', rest: true },
            { time: '10.00–11.15', tag: 'Sesi 7 · konsep dan praktik', title: 'Nilai hasil dan beban kerja', text: 'SPI, CPI, EAC per kegiatan dan portofolio.' },
            { time: '11.15–12.00', tag: 'Sesi 8 · praktik', title: 'Sasaran program dan IKU', text: 'Capaian IKU dan pantauan serapan per fakultas/unit.' },
            { time: '12.00–13.00', title: 'Ishoma', rest: true },
            { time: '13.00–14.00', tag: 'Sesi 9 · praktik', title: 'Risiko, perubahan, dan dokumen', text: 'Matriks 5×5, permintaan perubahan, arsip dokumen kegiatan.' },
            { time: '14.00–15.00', tag: 'Sesi 10 · praktik', title: 'Dasbor dan laporan pimpinan', text: 'Laporan status satu halaman, ekspor Excel, ritme rapat bulanan.' },
            { time: '15.00–15.15', title: 'Rehat', rest: true },
            { time: '15.15–15.45', tag: 'Studi kasus', title: 'Presentasi kegiatan unit', text: 'Perwakilan peserta menampilkan dasbor dan laporan statusnya.' },
            { time: '15.45–16.15', title: 'Penutupan dan sertifikat' },
        ],
    },
];

const roles = [
    { title: 'Pimpinan', text: 'Rektor, wakil rektor, dekan, kepala biro dan lembaga.' },
    { title: 'Perencana', text: 'Bagian perencanaan dan tim penyusun RKA-K/L.' },
    { title: 'Keuangan', text: 'PPK, bendahara, dan staf yang mencatat realisasi.' },
    { title: 'Operator unit', text: 'Pengelola kegiatan di fakultas, lembaga, dan unit.' },
    { title: 'Penjaminan mutu', text: 'LPM, SPI, dan auditor internal.' },
];

const facilities = [
    'Akun latihan Tadbira dengan data demo perguruan tinggi.',
    'Modul workshop dan panduan pengguna Tadbira.',
    'Contoh berkas POK dan laporan realisasi untuk latihan.',
    'Sertifikat dari Pusat Studi Manajemen Pendidikan Tinggi UIN Sunan Kalijaga.',
    'Konsumsi selama dua hari.',
];

const bring = [
    'Laptop dengan peramban terbaru (Chrome, Edge, atau Firefox).',
    'TOR atau RAB satu kegiatan unit yang akan disusun.',
    'Opsional: ekspor POK SAKTI dan Laporan Ketersediaan Dana Detail unit Anda.',
];

const faq = [
    { q: 'Apakah peserta perlu memahami manajemen proyek lebih dulu?', a: 'Tidak. Sesi pertama membahas konsep dasar (SRK, tahap, tonggak, jalur kritis) dengan contoh kegiatan kampus. Cukup terbiasa memakai peramban dan Excel.' },
    { q: 'Apa peran Pusat Studi Manajemen Pendidikan Tinggi?', a: 'Pusat Studi Manajemen Pendidikan Tinggi UIN Sunan Kalijaga Yogyakarta menjadi penyelenggara bersama dan menerbitkan sertifikat. Materi aplikasi dibawakan oleh tim Rucisoft.' },
    { q: 'Apakah Tadbira hanya untuk PTKIN?', a: 'Tidak. Tadbira menyesuaikan istilah sektor: kementerian dan perguruan tinggi negeri melihat Pagu DIPA dan Kegiatan, BUMN melihat Anggaran RKAP dan Program Kerja, swasta melihat Anggaran dan Proyek.' },
    { q: 'Apakah data unit kami aman dibawa ke workshop?', a: 'Data unit hanya dimasukkan ke akun latihan peserta dan tidak dibagikan ke peserta lain. Bila ragu, gunakan data demo yang sudah disediakan.' },
    { q: 'Bagaimana jika kampus kami ingin memakai Tadbira setelah workshop?', a: 'Tadbira dipasang di server atau hosting milik instansi sendiri (cukup PHP dan MySQL/MariaDB, termasuk cPanel). Mulai dengan uji coba di satu atau beberapa unit selama satu periode pelaporan, lalu perluas.' },
];
</script>

<template>
    <Head title="Workshop 2 hari mengenal Tadbira" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader :guide="guide" :site="links.site" />

        <main>
            <!-- Pembuka -->
            <section class="bg-zamrud-900 bg-pola text-white">
                <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_1fr]">
                    <div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-zamrud-100">
                            <img src="/images/mitra/uin-sunan-kalijaga.png" alt="Logo UIN Sunan Kalijaga" class="h-16 w-auto rounded-xl bg-white px-2 py-1.5" width="326" height="400" />
                            <p>Kerja sama <span class="font-semibold text-white">Pusat Studi Manajemen Pendidikan Tinggi</span><br />UIN Sunan Kalijaga Yogyakarta dan <span class="font-semibold text-white">Rucisoft</span></p>
                        </div>
                        <p class="mt-6 text-sm font-semibold uppercase tracking-wider text-emas-300">Workshop 2 hari · mengenal Tadbira</p>
                        <h1 class="mt-3 text-4xl font-bold leading-tight sm:text-5xl">Dari pagu DIPA menjadi kegiatan yang <span class="text-emas-300">terjadwal, terpantau, dan terlaporkan.</span></h1>
                        <p class="mt-5 text-lg text-zamrud-100">
                            Belajar langsung mengelola seluruh kegiatan perguruan tinggi dalam satu tahun anggaran dengan Tadbira: impor POK dari SAKTI, menyusun jadwal dan jalur kritis, memantau realisasi bulanan, sampai menyajikan laporan untuk pimpinan.
                        </p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a :href="register" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">Daftar sekarang</a>
                            <a :href="brochure" target="_blank" rel="noopener" class="tombol-sekunder border-white/30 bg-white/10 text-white hover:bg-white/20">Unduh brosur (PDF)</a>
                        </div>
                        <dl class="mt-8 grid grid-cols-2 overflow-hidden rounded-2xl border border-white/15 sm:grid-cols-3">
                            <div class="border-b border-r border-white/15 px-4 py-3 sm:border-b-0">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-zamrud-200">Tanggal</dt>
                                <dd class="mt-0.5 font-semibold">{{ dates }}</dd>
                            </div>
                            <div class="border-b border-white/15 px-4 py-3 sm:border-b-0 sm:border-r">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-zamrud-200">Waktu</dt>
                                <dd class="mt-0.5 font-semibold">08.00–16.15 WIB</dd>
                            </div>
                            <div class="col-span-2 px-4 py-3 sm:col-span-1">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-zamrud-200">Tempat</dt>
                                <dd class="mt-0.5 font-semibold">{{ info('place') }}</dd>
                            </div>
                        </dl>
                    </div>
                    <figure class="relative">
                        <img src="/images/panduan/02a-dasbor-atas.webp" alt="Dasbor portofolio Tadbira untuk data demo UIN Sunan Kalijaga TA 2026" class="w-full rounded-2xl border border-white/15 shadow-2xl" width="1110" height="960" />
                        <figcaption class="absolute -bottom-5 left-3 rounded-2xl bg-emas-500 px-4 py-3 font-bold leading-tight text-zamrud-950 shadow-lg sm:-left-4">
                            Pulang dengan 1 kegiatan
                            <span class="block text-xs font-semibold">unit Anda tersusun lengkap di Tadbira</span>
                        </figcaption>
                    </figure>
                </div>
                <div class="h-1.5 bg-[linear-gradient(90deg,#D4A72C_0_22%,#34D399_22%_55%,#38BDF8_55%_80%,#F97316_80%)]" aria-hidden="true" />
            </section>

            <!-- Masalah -->
            <section id="masalah" class="scroll-mt-20 py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Masalah yang sering dihadapi</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Kegiatan berjalan, tetapi baru ketahuan terlambat saat serapan menumpuk di akhir tahun.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Perguruan tinggi menjalankan puluhan sampai ratusan kegiatan per tahun anggaran. Tanpa satu tempat untuk memantau, pimpinan bekerja dengan laporan yang datang terlambat.</p>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="p in problems" :key="p.title" class="kartu p-6">
                            <span class="block h-3 w-3 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" />
                            <h3 class="mt-4 font-semibold text-zamrud-950">{{ p.title }}</h3>
                            <p class="mt-2 text-sm">{{ p.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Hasil -->
            <section class="bg-zamrud-900 bg-pola py-16 text-white">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-300">Yang dibawa pulang</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold">Bukan sekadar mengenal menu. Peserta pulang dengan hasil kerja.</h2>
                    <p class="mt-3 max-w-3xl text-zamrud-100">Setiap peserta membawa satu kegiatan riil unitnya dan menyelesaikannya di Tadbira selama dua hari.</p>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(o, i) in outcomes" :key="o.title" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                            <span class="text-3xl font-bold text-emas-300">{{ i + 1 }}</span>
                            <h3 class="mt-2 font-semibold">{{ o.title }}</h3>
                            <p class="mt-2 text-sm text-zamrud-100/85">{{ o.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Akronim TADBIRA -->
            <section id="tadbira" class="scroll-mt-20 border-b border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Tujuh hal yang dikelola</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">TADBIRA: Task, Activity, Delivery, Budget, Integration, Risk, Accountability.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Setiap huruf dibahas dan dipraktikkan dalam workshop.</p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(a, i) in acronym" :key="i" class="flex gap-4 rounded-2xl border border-gading-200 bg-gading-50 p-5">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-zamrud-900 text-2xl font-bold text-emas-300">{{ a.letter }}</span>
                            <div class="min-w-0">
                                <h3 class="font-bold text-zamrud-950">{{ a.word }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ a.text }}</p>
                                <p class="mt-2 text-xs font-semibold text-zamrud-700">{{ a.session }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Materi -->
            <section id="materi" class="scroll-mt-20 py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Materi workshop</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Satu alur kerja dari anggaran sampai laporan.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Tangkapan layar diambil dari data demo UIN Sunan Kalijaga TA 2026, yang juga dipakai sebagai bahan latihan. Angkanya rekaan.</p>
                    <div class="mt-10 space-y-14">
                        <div v-for="(m, i) in modules" :key="m.title" class="grid items-center gap-8 md:grid-cols-[1fr_1.2fr]">
                            <div :class="i % 2 ? 'md:order-2' : ''">
                                <span class="inline-block rounded-full bg-emas-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emas-800">{{ m.session }}</span>
                                <h3 class="mt-3 text-2xl font-bold text-zamrud-950">{{ m.title }}</h3>
                                <p class="mt-2 text-slate-600">{{ m.text }}</p>
                                <ul class="mt-4 space-y-2 text-sm">
                                    <li v-for="pt in m.points" :key="pt" class="flex gap-3">
                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" />
                                        <span>{{ pt }}</span>
                                    </li>
                                </ul>
                            </div>
                            <img :src="m.img" :alt="m.title" :width="m.w" :height="m.h" loading="lazy" class="w-full rounded-xl border border-gading-200 bg-white shadow-lg" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Jadwal -->
            <section id="jadwal" class="scroll-mt-20 border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Jadwal</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Hari pertama merencanakan, hari kedua mengendalikan.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Sepuluh sesi, sebagian besar praktik langsung di laptop peserta.</p>
                    <div class="mt-8 grid items-start gap-6 lg:grid-cols-2">
                        <article v-for="d in days" :key="d.label" class="overflow-hidden rounded-2xl border border-gading-200">
                            <header class="bg-zamrud-900 bg-pola px-6 py-4 text-white">
                                <p class="text-xs font-bold uppercase tracking-wider text-emas-300">{{ d.label }} · {{ info(d.date) }}</p>
                                <h3 class="mt-1 text-xl font-bold">{{ d.title }}</h3>
                                <p class="text-sm text-zamrud-100">{{ d.text }}</p>
                            </header>
                            <div v-for="s in d.slots" :key="s.time" class="grid gap-1 border-t border-gading-100 px-6 sm:grid-cols-[6.5rem_1fr] sm:gap-4" :class="s.rest ? 'bg-gading-50 py-2' : 'py-3'">
                                <span class="text-sm font-semibold tabular-nums text-slate-500">{{ s.time }}</span>
                                <div :class="s.rest ? 'text-sm text-slate-500' : ''">
                                    <span v-if="s.tag" class="block text-[0.7rem] font-bold uppercase tracking-wider text-zamrud-600">{{ s.tag }}</span>
                                    <span :class="s.rest ? '' : 'font-semibold text-zamrud-950'">{{ s.title }}</span>
                                    <p v-if="s.text" class="text-sm text-slate-500">{{ s.text }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Bila salah satu hari jatuh pada Jumat, istirahat siang menjadi 11.30–13.00 dan Sesi 8 digeser ke sore.</p>
                </div>
            </section>

            <!-- Peserta -->
            <section id="peserta" class="scroll-mt-20 py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Untuk siapa</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Paling efektif bila satu unit mengirim tim kecil.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Satu perencana, satu dari keuangan, dan satu operator kegiatan. Setiap peran berikut punya hak akses sendiri di Tadbira.</p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div v-for="r in roles" :key="r.title" class="rounded-2xl border border-gading-200 bg-white p-5">
                            <h3 class="font-semibold text-zamrud-950">{{ r.title }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ r.text }}</p>
                        </div>
                    </div>
                    <div class="mt-10 grid gap-8 md:grid-cols-2">
                        <div>
                            <h3 class="text-lg font-bold text-zamrud-950">Fasilitas peserta</h3>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li v-for="f in facilities" :key="f" class="flex gap-3"><span class="mt-1.5 h-2.5 w-2.5 shrink-0 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" /><span>{{ f }}</span></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zamrud-950">Yang perlu dibawa</h3>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li v-for="b in bring" :key="b" class="flex gap-3"><span class="mt-1.5 h-2.5 w-2.5 shrink-0 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" /><span>{{ b }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Coba -->
            <section id="coba" class="scroll-mt-20 border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Coba sendiri, tanpa akun</p>
                    <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Lihat Tadbira sebelum workshop.</h2>
                    <p class="mt-2 max-w-3xl text-slate-600">Buka data demo sebagai tamu, hanya untuk melihat. Seluruh angka rekaan untuk belajar.</p>
                    <DemoCards v-if="demos.length" :demos="demos" class="mt-8" />
                    <div v-else class="mt-8 rounded-2xl border border-dashed border-gading-300 p-6 text-sm text-slate-600">
                        Mode tamu belum diaktifkan di server ini.
                        <Link :href="route('login')" class="font-semibold text-zamrud-700 underline">Masuk dengan akun</Link>.
                    </div>
                    <p class="mt-6 text-sm text-slate-600">
                        Ingin membaca dulu? Buka <a :href="guidePath" class="font-semibold text-zamrud-800 underline">panduan pengguna</a>
                        atau ringkasan <a :href="executivePath" class="font-semibold text-zamrud-800 underline">untuk eksekutif</a>.
                    </p>
                </div>
            </section>

            <!-- Pendaftaran -->
            <section id="daftar" class="scroll-mt-20 bg-zamrud-900 bg-pola py-16 text-white">
                <div class="mx-auto grid max-w-6xl items-start gap-10 px-4 sm:px-6 lg:grid-cols-[1.1fr_1fr]">
                    <div class="rounded-2xl bg-white p-6 text-slate-700 sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Pendaftaran</p>
                        <h2 class="mt-2 text-2xl font-bold text-zamrud-950">Kursi terbatas, daftar per tim unit.</h2>
                        <dl class="mt-6 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-[auto_1fr]">
                            <dt class="text-slate-500">Tanggal</dt><dd class="font-semibold text-zamrud-950">{{ dates }}</dd>
                            <dt class="text-slate-500">Waktu</dt><dd class="font-semibold text-zamrud-950">08.00–16.15 WIB</dd>
                            <dt class="text-slate-500">Tempat</dt><dd class="font-semibold text-zamrud-950">{{ info('place') }}</dd>
                            <dt class="text-slate-500">Investasi</dt><dd class="font-semibold text-zamrud-950">{{ info('fee') }}</dd>
                            <dt class="text-slate-500">Kuota</dt><dd class="font-semibold text-zamrud-950">{{ workshop.quota ? `${workshop.quota} peserta` : soon }}</dd>
                            <dt class="text-slate-500">Batas daftar</dt><dd class="font-semibold text-zamrud-950">{{ info('deadline') }}</dd>
                        </dl>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a :href="register" class="tombol">Daftar sekarang</a>
                            <a :href="brochure" target="_blank" rel="noopener" class="tombol-sekunder">Unduh brosur (PDF)</a>
                        </div>
                    </div>
                    <div>
                        <p class="font-arab text-2xl text-emas-300" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
                        <h2 class="mt-3 text-2xl font-bold">Ingin workshop di kampus atau instansi Anda?</h2>
                        <p class="mt-3 text-zamrud-100">Workshop yang sama dapat diselenggarakan khusus untuk satu perguruan tinggi, kementerian, atau pemerintah daerah, dengan data POK instansi sendiri.</p>
                        <p class="mt-4 text-sm text-zamrud-100">
                            <span class="font-semibold text-white">WhatsApp</span> {{ waDisplay }}<br />
                            <span class="font-semibold text-white">Surel</span> {{ links.email }}
                        </p>
                        <a :href="inHouse" class="tombol mt-6 bg-emas-500 text-zamrud-950 hover:bg-emas-400">Tanya via WhatsApp</a>
                    </div>
                </div>
            </section>

            <!-- Tanya jawab -->
            <section id="tanya" class="scroll-mt-20 py-16">
                <div class="mx-auto max-w-3xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Tanya jawab</p>
                    <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Yang sering ditanyakan.</h2>
                    <div class="mt-8 space-y-3">
                        <details v-for="f in faq" :key="f.q" class="group rounded-2xl border border-gading-200 bg-white px-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 font-semibold text-zamrud-950">
                                {{ f.q }}
                                <span class="text-xl text-emas-500 group-open:rotate-45" aria-hidden="true">+</span>
                            </summary>
                            <p class="pb-4 text-sm text-slate-600">{{ f.a }}</p>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Ajakan penutup -->
            <section class="px-4 pb-16 sm:px-6">
                <div class="mx-auto max-w-6xl rounded-3xl bg-zamrud-900 bg-pola px-8 py-12 text-center text-white sm:px-14">
                    <h2 class="text-3xl font-bold">Dua hari, satu kegiatan tersusun, satu cara kerja baru.</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-zamrud-100">Ajak perencana, keuangan, dan operator unit Anda. Kursi terbatas.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a :href="register" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">Daftar sekarang</a>
                        <a href="#coba" class="tombol-sekunder border-white/20 bg-white/10 text-white hover:bg-white/20">Coba data demo</a>
                    </div>
                </div>
            </section>
        </main>
        <footer class="border-t border-gading-200 py-8">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between">
                <p>
                    <span class="font-semibold text-zamrud-900">Tadbira</span> oleh
                    <a :href="links.site" class="underline hover:text-zamrud-800">Rucisoft</a>.
                    Diselenggarakan bersama Pusat Studi Manajemen Pendidikan Tinggi UIN Sunan Kalijaga Yogyakarta.
                </p>
                <p class="max-w-xl text-xs">Nama instansi pada data demo dipakai sebagai studi kasus. Seluruh angka pada data demo adalah rekaan.</p>
            </div>
        </footer>
    </div>
</template>
