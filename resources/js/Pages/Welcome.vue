<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DemoCards from '@/Components/DemoCards.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    canRegister: Boolean,
    demos: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
    guide: { type: Array, default: () => [] },
});

const step = (langkah) => `${props.links.solution}?langkah=${langkah}#mulai`;
const whatsapp = computed(
    () => `https://wa.me/${props.links.whatsapp}?text=${encodeURIComponent('Halo Rucisoft, saya tertarik dengan Tadbira.')}`,
);
const guestHref = computed(() => (props.demos.length ? '#data-demo' : route('login')));

const problems = [
    { title: 'Kegiatan menumpuk di akhir tahun', text: 'Rencana kegiatan disusun di awal tahun, lalu baru bergerak menjelang triwulan IV. Penyerapan anggaran terlambat dan mutu kegiatan turun.', color: '#F97316' },
    { title: 'Kemajuan tidak terlihat', text: 'Pimpinan baru tahu sebuah kegiatan terlambat ketika rapat evaluasi, saat waktu untuk memperbaikinya sudah habis.', color: '#D4A72C' },
    { title: 'Anggaran lepas dari jadwal', text: 'POK dan laporan realisasi hidup di SAKTI, jadwal hidup di Excel. Tidak ada yang menyandingkan keduanya per kegiatan.', color: '#38BDF8' },
    { title: 'Penanggung jawab kabur', text: 'Satu kegiatan melibatkan banyak unit. Ketika ada aktivitas tertunda, tidak jelas siapa yang harus bergerak lebih dulu.', color: '#A855F7' },
];

const causes = [
    { title: 'Jadwal tanpa urutan', text: 'Aktivitas ditulis sebagai daftar tanggal, tanpa ketergantungan. Satu keterlambatan tidak terlihat dampaknya ke aktivitas berikutnya.' },
    { title: 'Rencana tanpa garis dasar', text: 'Tidak ada pembanding antara rencana dan kenyataan per hari ini, sehingga "terlambat" menjadi soal perasaan.' },
    { title: 'Data tersebar', text: 'Kemajuan fisik dilaporkan unit, realisasi keuangan dicatat bagian keuangan, dan capaian IKU direkap perencana. Ketiganya jarang bertemu.' },
    { title: 'Pemantauan berkala yang berat', text: 'Rekap bulanan dikerjakan manual dari banyak berkas, sehingga sering terlambat atau dilewati.' },
];

const solutions = [
    { title: 'Dasbor portofolio per tahun anggaran', text: 'Semua kegiatan dalam satu layar: kesehatan jadwal, kurva S anggaran, kemajuan per fakultas atau unit, dan daftar kegiatan yang perlu perhatian.', answers: 'Kemajuan tidak terlihat', color: '#10B981' },
    { title: 'Gantt dan jalur kritis', text: 'Susun aktivitas dengan ketergantungan selesai-ke-mulai. Geser satu aktivitas, jadwal lain menyesuaikan, dan jalur kritis tersorot.', answers: 'Kegiatan menumpuk di akhir tahun', color: '#D4A72C' },
    { title: 'Impor POK dan laporan realisasi', text: 'Unggah POK RKA-K/L atau Laporan Ketersediaan Dana dari SAKTI/OM-SPAN. Setiap RO menjadi kegiatan dengan pagu dan realisasinya.', answers: 'Anggaran lepas dari jadwal', color: '#38BDF8' },
    { title: 'Penanggung jawab dan sasaran', text: 'Setiap kegiatan punya penanggung jawab, unit pelaksana, dan sasaran program beserta IKU yang didukungnya.', answers: 'Penanggung jawab kabur', color: '#A855F7' },
];

const steps = computed(() => [
    { title: 'Pahami masalahnya', text: 'Webinar gratis tentang pemantauan rencana kegiatan dan penyerapan anggaran.', cta: 'Ikuti webinar', href: props.links.academy },
    { title: 'Petakan kebutuhan', text: 'Asesmen singkat: berapa kegiatan, unit, dan sumber data anggaran yang Anda kelola.', cta: 'Isi asesmen', href: step('asesmen') },
    { title: 'Praktikkan', text: 'Workshop berbayar satu hari: menyusun jadwal kegiatan, Gantt, dan impor POK dengan data Anda.', cta: 'Lihat workshop', href: props.links.academy },
    { title: 'Pastikan', text: 'Klinik kebutuhan dan demo 30–45 menit bersama tim Rucisoft.', cta: 'Jadwalkan demo', href: step('demo') },
    { title: 'Buktikan', text: 'Uji coba terbatas di satu atau beberapa unit, didampingi selama satu periode pelaporan.', cta: 'Ajukan pilot', href: step('pilot') },
    { title: 'Terapkan', text: 'Proposal, pemasangan di server Anda, pelatihan operator, dan pendampingan.', cta: 'Minta proposal', href: step('proposal') },
]);

const phases = [
    { title: 'Inisiasi', text: 'Daftarkan kegiatan, unit pelaksana, penanggung jawab, dan sasaran program yang didukung.' },
    { title: 'Perencanaan', text: 'Susun SRK, durasi, dan ketergantungan; atau pakai templat dan cukup isi tanggal mulai.' },
    { title: 'Pelaksanaan', text: 'Unit memperbarui persen selesai aktivitas; realisasi anggaran dicatat per akun.' },
    { title: 'Pemantauan', text: 'Bandingkan rencana dan realisasi per hari ini, di tingkat kegiatan, unit, dan sasaran.' },
    { title: 'Penutupan', text: 'Tutup kegiatan, simpan sebagai templat, dan bawa pelajarannya ke tahun anggaran berikutnya.' },
];

const shots = [
    { src: '/images/demo/gantt.webp', title: 'Gantt dan jalur kritis' },
    { src: '/images/demo/kegiatan.webp', title: 'Daftar kegiatan per unit' },
    { src: '/images/demo/sasaran.webp', title: 'Sasaran program dan IKU' },
];

const cycle = [
    { day: 'Tgl 1–3', title: 'Pengingat', text: 'Operator unit diingatkan memperbarui kemajuan kegiatan.' },
    { day: 'Tgl 3–5', title: 'Isi kemajuan', text: 'Penanggung jawab mengisi persen selesai dan kendala.' },
    { day: 'Tgl 5–7', title: 'Unggah realisasi', text: 'Keuangan mengunggah laporan realisasi bulan lalu dari SAKTI.' },
    { day: 'Tgl 7–8', title: 'Verifikasi', text: 'Perencana memeriksa kegiatan berstatus waspada dan kritis.' },
    { day: 'Tgl 10', title: 'Rapat evaluasi', text: 'Pimpinan meninjau dasbor dan memutuskan tindak lanjut.' },
];

const roles = [
    { title: 'Pimpinan', text: 'Melihat dasbor portofolio, kegiatan kritis, dan capaian sasaran. Memimpin rapat evaluasi bulanan.' },
    { title: 'Perencana / Biro', text: 'Menyusun daftar kegiatan, mengimpor POK, mengaitkan kegiatan ke sasaran dan IKU, dan memverifikasi laporan.' },
    { title: 'Keuangan', text: 'Mengunggah Laporan Ketersediaan Dana atau mencatat realisasi per akun setiap bulan.' },
    { title: 'Penanggung jawab kegiatan', text: 'Menyusun jadwal aktivitas, menjaga ketergantungan, dan memastikan kegiatan selesai tepat waktu.' },
    { title: 'Operator unit', text: 'Memperbarui persen selesai aktivitas dan mencatat kendala di unitnya.' },
    { title: 'Admin', text: 'Mengatur organisasi, tahun anggaran, pengguna, hari libur, dan templat kegiatan.' },
];

const faqs = [
    { q: 'Apakah data demo adalah data resmi?', a: 'Bukan. Data demo, termasuk UIN Sunan Kalijaga TA 2026, adalah rekaan untuk belajar. Struktur unit dan jenis kegiatannya dibuat mirip aslinya, tetapi angka pagu, jadwal, kemajuan, dan realisasinya dikarang.' },
    { q: 'Apa yang bisa dilakukan tamu?', a: 'Tamu bisa membuka semua halaman: dasbor, daftar kegiatan, Gantt, anggaran, dan sasaran. Tamu tidak bisa mengubah, menambah, atau menghapus data.' },
    { q: 'Apakah cocok untuk kementerian, perguruan tinggi, BUMN, dan swasta?', a: 'Ya. Istilah menyesuaikan sektor: kementerian dan PTKIN melihat Pagu DIPA dan Kegiatan, BUMN melihat Anggaran RKAP dan Program Kerja, swasta melihat Anggaran dan Proyek. Satu akun bisa tergabung di beberapa organisasi.' },
    { q: 'Bagaimana memulai dengan data sendiri?', a: 'Admin mendaftarkan organisasi dan tahun anggaran, lalu mengimpor POK RKA-K/L atau RAB/RKAP dari Excel. Laporan realisasi dari SAKTI/OM-SPAN bisa diunggah setiap bulan dalam format PDF atau XLS.' },
    { q: 'Di mana aplikasi dipasang?', a: 'Di server atau hosting Anda sendiri, termasuk cPanel. Cukup PHP 8.3 dan MySQL/MariaDB; data tetap di tangan instansi Anda.' },
];
</script>

<template>
    <Head title="Kelola proyek dengan amanah" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader on-home :guide="guide" :site="links.site" />

        <main>
            <!-- Pembuka -->
            <section class="mx-auto max-w-6xl px-4 pb-16 pt-12 sm:px-6">
                <div class="grid items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Untuk pimpinan, perencana, dan pengelola kegiatan</p>
                        <h1 class="mt-3 text-4xl font-bold leading-tight text-zamrud-950 sm:text-5xl">Rencana kegiatan yang terpantau, anggaran yang terserap tepat waktu.</h1>
                        <p class="mt-5 text-lg text-slate-600">
                            Tadbira adalah aplikasi manajemen proyek untuk memantau seluruh kegiatan dalam satu tahun anggaran: dari jadwal dan jalur kritis, POK dan realisasi, sampai capaian sasaran dan IKU. Untuk kementerian, lembaga, perguruan tinggi, pemda, BUMN, BUMD, dan swasta.
                        </p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a :href="guestHref" class="tombol">Coba data demo 2026</a>
                            <a :href="route('guide')" class="tombol-sekunder">Baca panduannya</a>
                        </div>
                        <p class="mt-3 text-sm text-slate-500">Tanpa akun. Mode tamu hanya untuk melihat.</p>
                    </div>
                    <figure>
                        <img src="/images/demo/dasbor.webp" alt="Dasbor portofolio Tadbira dengan data demo" class="w-full rounded-2xl border border-gading-200 shadow-xl" width="1200" height="750" />
                        <figcaption class="mt-2 text-center text-xs text-slate-500">Dasbor portofolio Tadbira dengan data demo</figcaption>
                    </figure>
                </div>
            </section>

            <!-- Masalah -->
            <section id="kenapa" class="scroll-mt-20 border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Masalah yang sering dihadapi</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Rencana kegiatan yang baik sering berhenti di dokumen POK.</h2>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="p in problems" :key="p.title" class="kartu p-6">
                            <span class="block h-1.5 w-12 rounded-full" :style="{ background: p.color }" />
                            <h3 class="mt-4 font-semibold text-zamrud-950">{{ p.title }}</h3>
                            <p class="mt-2 text-sm">{{ p.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Penyebab -->
            <section class="py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Mengapa masalah ini terjadi</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Akar masalahnya ada pada cara rencana dijadwalkan dan dipantau.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Praktik manajemen proyek (PMBOK) menekankan jadwal yang saling terhubung, garis dasar yang jelas, dan pemantauan rutin. Di banyak instansi, empat hal ini yang hilang:</p>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(c, i) in causes" :key="c.title" class="rounded-2xl border border-gading-200 bg-gading-100/60 p-6">
                            <span class="text-sm font-bold text-emas-600">{{ String(i + 1).padStart(2, '0') }}</span>
                            <h3 class="mt-2 font-semibold text-zamrud-950">{{ c.title }}</h3>
                            <p class="mt-2 text-sm">{{ c.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Solusi -->
            <section id="fitur" class="scroll-mt-20 bg-zamrud-900 bg-pola py-16 text-white">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-300">Solusi Rucisoft</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold">Tadbira menerjemahkan POK menjadi kegiatan yang dipantau setiap bulan.</h2>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="s in solutions" :key="s.title" class="flex flex-col rounded-2xl border border-white/10 bg-white/5 p-6">
                            <span class="block h-1.5 w-12 rounded-full" :style="{ background: s.color }" />
                            <h3 class="mt-4 font-semibold">{{ s.title }}</h3>
                            <p class="mt-2 flex-1 text-sm text-zamrud-100/85">{{ s.text }}</p>
                            <p class="mt-4 text-xs text-emas-300">Menjawab: {{ s.answers }}</p>
                        </div>
                    </div>
                    <a :href="route('executive')" class="mt-8 flex flex-col gap-2 rounded-2xl border border-emas-300/30 bg-white/5 p-5 hover:bg-white/10 sm:flex-row sm:items-center sm:justify-between">
                        <span><span class="font-semibold text-emas-300">Untuk pimpinan:</span> enam pertanyaan yang terjawab di dasbor, manfaat bagi organisasi, dan waktu yang dibutuhkan.</span>
                        <span class="shrink-0 font-semibold">Tadbira untuk eksekutif →</span>
                    </a>
                </div>
            </section>

            <!-- Cara memperoleh -->
            <section class="py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Cara memperoleh Tadbira</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Mulai dari tahap mana pun yang sesuai dengan kesiapan Anda.</h2>
                    <ol class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <li v-for="(s, i) in steps" :key="s.title" class="kartu flex flex-col p-6">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-zamrud-100 font-bold text-zamrud-800">{{ i + 1 }}</span>
                            <h3 class="mt-3 font-semibold text-zamrud-950">{{ s.title }}</h3>
                            <p class="mt-1 flex-1 text-sm">{{ s.text }}</p>
                            <a :href="s.href" class="mt-4 text-sm font-semibold text-zamrud-700 hover:text-zamrud-900">{{ s.cta }} →</a>
                        </li>
                    </ol>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a :href="step('demo')" class="tombol">Jadwalkan klinik &amp; demo</a>
                        <a :href="links.academy" class="tombol-sekunder">Ikuti webinar gratis</a>
                        <a :href="whatsapp" class="tombol-sekunder">Tanya via WhatsApp</a>
                        <a :href="guestHref" class="tombol-sekunder">Coba data demo</a>
                    </div>
                </div>
            </section>

            <!-- Kerangka -->
            <section class="border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2 class="text-3xl font-bold text-zamrud-950">Kerangka lengkap, bukan sekadar dasbor</h2>
                    <p class="mt-2 text-slate-600">Lima tahap siklus hidup kegiatan, dari inisiasi sampai penutupan.</p>
                    <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <li v-for="(p, i) in phases" :key="p.title" class="rounded-2xl border border-gading-200 p-5">
                            <span class="text-sm font-bold text-emas-600">{{ i + 1 }}</span>
                            <h3 class="mt-1 font-semibold text-zamrud-950">{{ p.title }}</h3>
                            <p class="mt-2 text-sm">{{ p.text }}</p>
                        </li>
                    </ol>
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        <figure v-for="s in shots" :key="s.src">
                            <img :src="s.src" :alt="s.title" loading="lazy" class="aspect-[16/10] w-full rounded-xl border border-gading-200 object-cover object-top shadow" />
                            <figcaption class="mt-2 text-sm text-slate-600"><span class="font-semibold text-zamrud-900">Data demo:</span> {{ s.title }}</figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <!-- Data demo -->
            <section id="data-demo" class="scroll-mt-20 py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Data demo tahun berjalan 2026</p>
                    <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Coba sendiri, tanpa akun</h2>
                    <p class="mt-2 max-w-3xl text-slate-600">
                        Pilih organisasi lalu masuk sebagai tamu. Data demo dibuat untuk belajar dan seluruh angkanya rekaan, bukan data resmi instansi mana pun.
                    </p>
                    <DemoCards v-if="demos.length" :demos="demos" class="mt-8" />
                    <div v-else class="mt-8 rounded-2xl border border-dashed border-gading-300 p-6 text-sm text-slate-600">
                        Mode tamu belum diaktifkan di server ini.
                        <Link :href="route('login')" class="font-semibold text-zamrud-700 underline">Masuk dengan akun</Link>.
                    </div>
                </div>
            </section>

            <!-- Panduan -->
            <section id="panduan" class="scroll-mt-20 border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Panduan Tadbira</p>
                    <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Siapa mengerjakan apa, dan kapan.</h2>
                    <p class="mt-2 text-slate-600">Siklus bulanan yang disarankan:</p>
                    <ol class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <li v-for="c in cycle" :key="c.title" class="rounded-xl bg-zamrud-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-zamrud-700">{{ c.day }}</p>
                            <p class="mt-1 font-semibold text-zamrud-950">{{ c.title }}</p>
                            <p class="mt-1 text-sm">{{ c.text }}</p>
                        </li>
                    </ol>
                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="r in roles" :key="r.title" class="rounded-2xl border border-gading-200 p-5">
                            <h3 class="font-semibold text-zamrud-950">{{ r.title }}</h3>
                            <p class="mt-2 text-sm">{{ r.text }}</p>
                        </div>
                    </div>
                    <div v-if="guide.length" class="mt-10 rounded-2xl bg-zamrud-900 p-6 text-white sm:p-8">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div>
                                <h3 class="text-xl font-bold">Panduan pengguna lengkap</h3>
                                <p class="mt-1 max-w-2xl text-sm text-zamrud-100">Teori manajemen proyek dari buku <em>Catatan Kecil Manajer Proyek</em>, langkah demi langkah di Tadbira, dan tangkapan layar.</p>
                            </div>
                            <a :href="route('guide')" class="tombol shrink-0 bg-emas-500 text-zamrud-950 hover:bg-emas-400">Buka panduan</a>
                        </div>
                        <ol class="mt-6 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3">
                            <li v-for="s in guide" :key="s.id">
                                <a :href="`${route('guide')}#${s.id}`" class="block rounded-lg bg-white/5 px-3 py-2 hover:bg-white/10">{{ s.title }}</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Workshop -->
            <section class="py-16">
                <div class="mx-auto flex max-w-6xl flex-col items-start gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Workshop</p>
                        <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Kenali Tadbira dalam satu hari.</h2>
                        <p class="mt-2 max-w-2xl text-slate-600">Bawa POK dan daftar kegiatan unit Anda. Pulang dengan jadwal kegiatan, Gantt, dan dasbor pemantauan yang siap dipakai.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a :href="links.academy" class="tombol">Daftar workshop</a>
                        <a :href="whatsapp" class="tombol-sekunder">Tanya via WhatsApp</a>
                    </div>
                </div>
            </section>

            <!-- Tanya jawab -->
            <section id="tanya" class="scroll-mt-20 border-t border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-3xl px-4 sm:px-6">
                    <h2 class="text-3xl font-bold text-zamrud-950">Tanya jawab</h2>
                    <div class="mt-6 divide-y divide-gading-200">
                        <details v-for="f in faqs" :key="f.q" class="group py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-zamrud-950">
                                {{ f.q }}
                                <span class="text-emas-600 transition group-open:rotate-45">+</span>
                            </summary>
                            <p class="mt-2 text-sm">{{ f.a }}</p>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Ajakan penutup -->
            <section class="px-4 py-16 sm:px-6">
                <div class="mx-auto max-w-6xl rounded-3xl bg-zamrud-900 bg-pola px-8 py-12 text-center text-white sm:px-14">
                    <p class="font-arab text-2xl text-emas-300" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
                    <h2 class="mt-3 text-3xl font-bold">Lihat sendiri dalam lima menit.</h2>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a :href="guestHref" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">Coba sebagai tamu</a>
                        <Link :href="route('login')" class="tombol-sekunder border-white/20 bg-white/10 text-white hover:bg-white/20">Masuk dengan akun</Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gading-200 py-8">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 text-sm text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between">
                <p>
                    <span class="font-semibold text-zamrud-900">Tadbira</span> oleh
                    <a :href="links.site" class="underline hover:text-zamrud-800">Rucisoft</a>.
                    Kelola proyek dengan amanah.
                </p>
                <p class="max-w-xl text-xs">Nama instansi pada data demo dipakai sebagai studi kasus, bukan kemitraan atau dukungan resmi. Seluruh angka pada data demo adalah rekaan.</p>
            </div>
        </footer>
    </div>
</template>
