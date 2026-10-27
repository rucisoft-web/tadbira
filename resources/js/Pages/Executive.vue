<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DemoCards from '@/Components/DemoCards.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    demos: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
    guide: { type: Array, default: () => [] },
});

const step = (langkah) => `${props.links.solution}?langkah=${langkah}#mulai`;
const whatsapp = computed(
    () => `https://wa.me/${props.links.whatsapp}?text=${encodeURIComponent('Halo Rucisoft, saya ingin melihat Tadbira untuk pimpinan.')}`,
);
const guidePath = route('guide');

const questions = [
    {
        q: 'Kegiatan mana yang terlambat?',
        a: 'Setiap kegiatan diberi status kesehatan dari indeks kinerja jadwal (SPI): Sesuai jadwal, Perlu perhatian, atau Terlambat. Yang bermasalah naik ke daftar Perlu perhatian di dasbor.',
        where: 'Dasbor › Perlu perhatian',
        color: '#F97316',
    },
    {
        q: 'Apakah serapan anggaran sesuai rencana?',
        a: 'Kurva S menyandingkan rencana dan realisasi kumulatif per bulan, lengkap dengan serapan per unit. Penumpukan di triwulan IV terlihat sejak triwulan II.',
        where: 'Dasbor › Kurva penyerapan',
        color: '#38BDF8',
    },
    {
        q: 'Unit mana yang perlu didorong?',
        a: 'Kemajuan dan serapan diringkas per fakultas, direktorat, atau wilayah kerja, sehingga pembicaraan di rapat langsung tertuju ke unit yang tepat.',
        where: 'Dasbor › Kemajuan per unit',
        color: '#D4A72C',
    },
    {
        q: 'Apakah kegiatan mendukung sasaran strategis?',
        a: 'Kegiatan dikaitkan ke sasaran program dan IKU atau KPI. Pimpinan melihat kemajuan, serapan, dan capaian indikator per sasaran, bahan langsung untuk laporan kinerja.',
        where: 'Sasaran & IKU',
        color: '#10B981',
    },
    {
        q: 'Mana buktinya?',
        a: 'TOR, RAB, kontrak, BAST, notulen, dan laporan tersimpan di tab Dokumen setiap kegiatan, terkait ke tonggaknya. Klaim "sudah selesai" bisa dicek dalam dua klik.',
        where: 'Kegiatan › Dokumen',
        color: '#A855F7',
    },
    {
        q: 'Siapa yang harus bergerak?',
        a: 'Setiap kegiatan dan aktivitas punya penanggung jawab. Jalur kritis menunjukkan aktivitas mana yang, bila tertunda, menggeser seluruh kegiatan.',
        where: 'Kegiatan › Gantt & Ringkasan',
        color: '#EF4444',
    },
];

const benefits = [
    { title: 'Keputusan lebih awal', text: 'Masalah terlihat saat masih bisa diperbaiki, bukan saat rapat evaluasi akhir tahun.' },
    { title: 'Serapan lebih merata', text: 'Jadwal yang terhubung dengan anggaran mendorong kegiatan bergerak sejak awal tahun, bukan menumpuk di Desember.' },
    { title: 'Rapat lebih singkat', text: 'Semua peserta melihat angka yang sama. Rapat bulanan membahas keputusan, bukan menyamakan data.' },
    { title: 'Siap diperiksa', text: 'Kemajuan, realisasi, dokumen bukti, dan riwayat perubahan tersimpan rapi untuk SAKIP, auditor internal, BPK, atau komite audit.' },
    { title: 'Pengetahuan tidak hilang', text: 'Kegiatan yang selesai bisa disimpan sebagai templat. Pengganti pejabat atau staf mewarisi cara kerja, bukan sekadar berkas.' },
    { title: 'Data tetap milik Anda', text: 'Dipasang di server atau hosting instansi sendiri. Tidak ada data kegiatan dan anggaran yang dikirim ke pihak lain.' },
];

const routine = [
    { when: 'Setiap pagi', time: '5 menit', text: 'Buka dasbor di ponsel atau laptop. Lihat daftar Perlu perhatian dan kegiatan yang tonggaknya jatuh pekan ini.' },
    { when: 'Setiap bulan', time: '60 menit', text: 'Rapat evaluasi tanggal 10 dengan dasbor di layar. Putuskan tindak lanjut untuk kegiatan berstatus waspada dan terlambat.' },
    { when: 'Setiap triwulan', time: 'Setengah hari', text: 'Tinjau capaian sasaran dan IKU, geser anggaran bila perlu, dan siapkan bahan laporan kinerja.' },
];

const shots = [
    { src: '/images/panduan/02b-dasbor-grafik.webp', title: 'Serapan per unit dan kurva S', w: 1110, h: 740 },
    { src: '/images/panduan/02c-sasaran.webp', title: 'Sasaran program dan IKU', w: 1110, h: 1360 },
    { src: '/images/panduan/02d-kemajuan.webp', title: 'Kemajuan kegiatan dibanding rencana', w: 1110, h: 940 },
    { src: '/images/panduan/14-dokumen.webp', title: 'Dokumen bukti per kegiatan', w: 1110, h: 860 },
];

const sectors = [
    { title: 'Kementerian, lembaga, PTKIN, dan pemda', text: 'Pagu DIPA, POK RKA-K/L, laporan realisasi SAKTI/OM-SPAN, dan IKU untuk SAKIP. Tadbira melengkapi SAKTI, tidak menggantikannya.' },
    { title: 'BUMN dan BUMD', text: 'Anggaran RKAP, program kerja per direktorat atau wilayah kerja, dan KPI korporat. Cocok untuk portofolio proyek investasi dan TJSL.' },
    { title: 'Swasta dan yayasan', text: 'Portofolio proyek dengan anggaran, jadwal, dan penanggung jawab yang jelas, dari kantor pusat sampai cabang.' },
];
</script>

<template>
    <Head title="Tadbira untuk eksekutif" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader :guide="guide" :site="links.site" />

        <main>
            <!-- Pembuka -->
            <section class="mx-auto max-w-6xl px-4 pb-16 pt-12 sm:px-6">
                <div class="grid items-center gap-10 lg:grid-cols-[1fr_1.1fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Untuk rektor, eselon I dan II, direksi, dan kepala unit</p>
                        <h1 class="mt-3 text-4xl font-bold leading-tight text-zamrud-950 sm:text-5xl">Tahu kegiatan mana yang perlu diputuskan, sebelum terlambat.</h1>
                        <p class="mt-5 text-lg text-slate-600">
                            Pimpinan tidak perlu membuka Gantt atau berkas Excel. Tadbira merangkum seluruh kegiatan satu tahun anggaran menjadi satu layar: mana yang sehat, mana yang tersendat, berapa yang sudah terserap, dan apa buktinya.
                        </p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="#coba" class="tombol">Lihat dasbor demo</a>
                            <a :href="step('demo')" class="tombol-sekunder">Jadwalkan demo untuk pimpinan</a>
                        </div>
                        <p class="mt-3 text-sm text-slate-500">Demo tanpa akun, hanya untuk melihat. Sekitar lima menit.</p>
                    </div>
                    <figure>
                        <img src="/images/panduan/02a-dasbor-atas.webp" alt="Bagian atas dasbor: pagu, realisasi, kesehatan kegiatan, dan daftar Perlu perhatian" class="w-full rounded-2xl border border-gading-200 shadow-xl" width="1110" height="960" />
                        <figcaption class="mt-2 text-center text-xs text-slate-500">Ringkasan pimpinan di dasbor portofolio (data demo)</figcaption>
                    </figure>
                </div>
            </section>

            <!-- Pertanyaan pimpinan -->
            <section id="pertanyaan" class="scroll-mt-20 border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Pertanyaan pimpinan</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Enam pertanyaan yang biasanya butuh rapat, kini terjawab di dasbor.</h2>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="item in questions" :key="item.q" class="kartu flex flex-col p-6">
                            <span class="block h-1.5 w-12 rounded-full" :style="{ background: item.color }" />
                            <h3 class="mt-4 text-lg font-semibold text-zamrud-950">{{ item.q }}</h3>
                            <p class="mt-2 flex-1 text-sm">{{ item.a }}</p>
                            <p class="mt-4 text-xs font-semibold text-zamrud-700">{{ item.where }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Manfaat -->
            <section id="manfaat" class="scroll-mt-20 bg-zamrud-900 bg-pola py-16 text-white">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-300">Manfaat bagi organisasi</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold">Dari laporan yang menceritakan masa lalu, ke dasbor yang membantu memutuskan.</h2>
                    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="(b, i) in benefits" :key="b.title" class="rounded-2xl border border-white/10 bg-white/5 p-6">
                            <span class="text-sm font-bold text-emas-300">{{ String(i + 1).padStart(2, '0') }}</span>
                            <h3 class="mt-2 font-semibold">{{ b.title }}</h3>
                            <p class="mt-2 text-sm text-zamrud-100/85">{{ b.text }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Waktu pimpinan -->
            <section class="py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Waktu yang dibutuhkan pimpinan</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-bold text-zamrud-950">Tim yang mengisi data. Pimpinan cukup membaca dan memutuskan.</h2>
                    <p class="mt-3 max-w-3xl text-slate-600">Operator unit memperbarui kemajuan, bagian keuangan mengunggah realisasi dari SAKTI atau sistem keuangan, dan perencana memverifikasi. Ritme pimpinan sederhana:</p>
                    <ol class="mt-8 grid gap-5 md:grid-cols-3">
                        <li v-for="r in routine" :key="r.when" class="kartu p-6">
                            <div class="flex items-baseline justify-between gap-3">
                                <h3 class="font-semibold text-zamrud-950">{{ r.when }}</h3>
                                <span class="rounded-full bg-zamrud-50 px-3 py-1 text-xs font-bold text-zamrud-800">{{ r.time }}</span>
                            </div>
                            <p class="mt-3 text-sm">{{ r.text }}</p>
                        </li>
                    </ol>
                    <p class="mt-6 text-sm text-slate-600">
                        Rincian peran setiap orang ada di
                        <a :href="`${guidePath}#peran-dan-tugas-admin`" class="font-semibold text-zamrud-800 underline">panduan pengguna</a>.
                    </p>
                </div>
            </section>

            <!-- Tangkapan layar -->
            <section class="border-y border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <h2 class="text-3xl font-bold text-zamrud-950">Yang dilihat pimpinan</h2>
                    <p class="mt-2 text-slate-600">Semua tangkapan layar diambil dari data demo yang bisa Anda buka sendiri.</p>
                    <div class="mt-8 grid gap-6 md:grid-cols-2">
                        <figure v-for="s in shots" :key="s.src">
                            <img :src="s.src" :alt="s.title" :width="s.w" :height="s.h" loading="lazy" class="aspect-[16/10] w-full rounded-xl border border-gading-200 object-cover object-top shadow" />
                            <figcaption class="mt-2 text-sm font-semibold text-zamrud-900">{{ s.title }}</figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <!-- Sektor dan kepercayaan -->
            <section class="py-16">
                <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.2fr_1fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Sesuai sektor Anda</p>
                        <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Istilahnya mengikuti cara organisasi Anda bekerja.</h2>
                        <div class="mt-6 space-y-4">
                            <div v-for="s in sectors" :key="s.title" class="rounded-2xl border border-gading-200 bg-white p-5">
                                <h3 class="font-semibold text-zamrud-950">{{ s.title }}</h3>
                                <p class="mt-1 text-sm">{{ s.text }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-zamrud-50 p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-zamrud-950">Pertimbangan tata kelola</h2>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li><span class="font-semibold text-zamrud-900">Data di server sendiri.</span> Cukup PHP dan MySQL/MariaDB, termasuk hosting cPanel.</li>
                            <li><span class="font-semibold text-zamrud-900">Hak akses per peran.</span> Pimpinan, perencana, keuangan, operator unit, dan admin melihat sesuai tugasnya.</li>
                            <li><span class="font-semibold text-zamrud-900">Jejak perubahan.</span> Setiap perubahan kemajuan, anggaran, dan dokumen tercatat di riwayat kegiatan.</li>
                            <li><span class="font-semibold text-zamrud-900">Melengkapi sistem yang ada.</span> Data anggaran diimpor dari POK, RKAP, dan laporan realisasi; tidak ada entri ganda.</li>
                            <li><span class="font-semibold text-zamrud-900">Mulai kecil.</span> Uji coba di satu atau beberapa unit selama satu periode pelaporan sebelum diterapkan menyeluruh.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Coba -->
            <section id="coba" class="scroll-mt-20 border-t border-gading-200 bg-white py-16">
                <div class="mx-auto max-w-6xl px-4 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Lihat sendiri</p>
                    <h2 class="mt-2 text-3xl font-bold text-zamrud-950">Buka dasbor demo sebagai tamu</h2>
                    <p class="mt-2 max-w-3xl text-slate-600">
                        Pilih organisasi yang paling mirip dengan instansi Anda. Seluruh angka pada data demo adalah rekaan untuk belajar.
                    </p>
                    <DemoCards v-if="demos.length" :demos="demos" class="mt-8" />
                    <div v-else class="mt-8 rounded-2xl border border-dashed border-gading-300 p-6 text-sm text-slate-600">
                        Mode tamu belum diaktifkan di server ini.
                        <Link :href="route('login')" class="font-semibold text-zamrud-700 underline">Masuk dengan akun</Link>.
                    </div>
                </div>
            </section>

            <!-- Ajakan penutup -->
            <section class="px-4 py-16 sm:px-6">
                <div class="mx-auto max-w-6xl rounded-3xl bg-zamrud-900 bg-pola px-8 py-12 text-center text-white sm:px-14">
                    <p class="font-arab text-2xl text-emas-300" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
                    <h2 class="mt-3 text-3xl font-bold">Diskusikan dengan tim Rucisoft, 30–45 menit.</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-zamrud-100">Kami tunjukkan dasbor dengan contoh kegiatan sejenis organisasi Anda, lalu memetakan langkah uji coba.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a :href="step('demo')" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">Jadwalkan demo</a>
                        <a :href="whatsapp" class="tombol-sekunder border-white/20 bg-white/10 text-white hover:bg-white/20">Tanya via WhatsApp</a>
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
