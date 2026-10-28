<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    sessions: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
    guide: { type: Array, default: () => [] },
});

// Rehat tidak punya halaman materi, tetapi tetap tampil agar instruktur melihat jadwal utuh.
const breaks = {
    1: [
        { time: '07.30–08.00', title: 'Registrasi dan pembagian akun latihan' },
        { time: '08.00–08.30', title: 'Pembukaan' },
        { time: '09.30–09.45', title: 'Rehat kopi' },
        { time: '12.00–13.00', title: 'Ishoma' },
        { time: '14.30–14.45', title: 'Rehat' },
    ],
    2: [
        { time: '08.00–08.15', title: 'Ulasan hari pertama' },
        { time: '09.45–10.00', title: 'Rehat kopi' },
        { time: '12.00–13.00', title: 'Ishoma' },
        { time: '15.00–15.15', title: 'Rehat' },
    ],
};

const days = computed(() => [1, 2].map((day) => ({
    day,
    title: day === 1 ? 'Merencanakan kegiatan' : 'Mengendalikan dan melaporkan',
    rows: [
        ...props.sessions.filter((s) => s.day === day),
        ...breaks[day].map((b) => ({ ...b, rest: true })),
    ].sort((a, b) => a.time.localeCompare(b.time)),
})));

const totals = computed(() => ({
    videos: props.sessions.filter((s) => s.hasVideo).length,
    images: props.sessions.reduce((n, s) => n + s.images, 0),
    imagesReady: props.sessions.reduce((n, s) => n + s.imagesReady, 0),
}));

const prep = [
    'Uji akun tamu data demo UIN Sunan Kalijaga dan akun admin organisasi latihan.',
    'Uji proyektor di resolusi 1366 × 768 dan perbesar peramban ke 110%.',
    'Siapkan berkas contoh: POK RKA-K/L, dua Laporan Ketersediaan Dana Detail (bulan berurutan), TOR, dan BAST.',
    'Siapkan Wi-Fi cadangan atau tangkapan layar untuk setiap demo.',
    'Cetak daftar hadir, formulir evaluasi, dan sertifikat.',
    'Bagi peserta menjadi meja per unit; tunjuk satu asisten untuk setiap 10 peserta.',
];
</script>

<template>
    <Head title="Materi instruktur workshop" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader :guide="guide" :site="links.site" />

        <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Workshop 2 hari mengenal Tadbira</p>
            <h1 class="mt-2 text-4xl font-bold text-zamrud-950">Materi instruktur</h1>
            <p class="mt-3 max-w-3xl text-lg text-slate-600">
                Satu halaman untuk setiap sesi: tujuan, alur penyampaian per menit, langkah demo di Tadbira, latihan peserta, pesan kunci, serta gambar dan video pendukung.
            </p>
            <div class="mt-5 flex flex-wrap gap-3 text-sm">
                <span class="rounded-full bg-white px-3 py-1 font-semibold text-zamrud-900 ring-1 ring-gading-200">{{ sessions.length }} sesi</span>
                <span class="rounded-full bg-white px-3 py-1 font-semibold text-zamrud-900 ring-1 ring-gading-200">Video {{ totals.videos }}/{{ sessions.length }}</span>
                <span class="rounded-full bg-white px-3 py-1 font-semibold text-zamrud-900 ring-1 ring-gading-200">Gambar {{ totals.imagesReady }}/{{ totals.images }}</span>
                <Link :href="route('workshop')" class="rounded-full px-3 py-1 font-semibold text-zamrud-700 underline">Informasi workshop</Link>
            </div>

            <div class="mt-10 grid items-start gap-6 lg:grid-cols-2">
                <section v-for="d in days" :key="d.day" class="overflow-hidden rounded-2xl border border-gading-200 bg-white">
                    <header class="bg-zamrud-900 bg-pola px-6 py-4 text-white">
                        <p class="text-xs font-bold uppercase tracking-wider text-emas-300">Hari {{ d.day }}</p>
                        <h2 class="text-xl font-bold">{{ d.title }}</h2>
                    </header>
                    <template v-for="r in d.rows" :key="r.time">
                        <div v-if="r.rest" class="grid gap-1 border-t border-gading-100 bg-gading-50 px-6 py-2 text-sm text-slate-500 sm:grid-cols-[6.5rem_1fr] sm:gap-4">
                            <span class="tabular-nums">{{ r.time }}</span><span>{{ r.title }}</span>
                        </div>
                        <Link v-else :href="route('workshop.material', r.slug)" class="group grid gap-1 border-t border-gading-100 px-6 py-4 hover:bg-zamrud-50 sm:grid-cols-[6.5rem_1fr] sm:gap-4">
                            <span class="text-sm font-semibold tabular-nums text-slate-500">{{ r.time }}</span>
                            <span class="min-w-0">
                                <span class="block text-[0.7rem] font-bold uppercase tracking-wider text-zamrud-600">{{ r.tag }} · {{ r.minutes }} menit</span>
                                <span class="font-semibold text-zamrud-950 group-hover:underline">{{ r.title }}</span>
                                <span class="block text-sm text-slate-500">{{ r.summary }}</span>
                                <span class="mt-2 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full px-2 py-0.5" :class="r.hasVideo ? 'bg-zamrud-100 text-zamrud-800' : 'bg-gading-100 text-slate-500'">{{ r.hasVideo ? 'Video siap' : 'Video menyusul' }}</span>
                                    <span class="rounded-full px-2 py-0.5" :class="r.imagesReady === r.images ? 'bg-zamrud-100 text-zamrud-800' : 'bg-gading-100 text-slate-500'">Gambar {{ r.imagesReady }}/{{ r.images }}</span>
                                </span>
                            </span>
                        </Link>
                    </template>
                </section>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2">
                <section class="kartu p-6">
                    <h2 class="text-lg font-bold text-zamrud-950">Persiapan instruktur</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li v-for="p in prep" :key="p" class="flex gap-3"><span class="mt-1.5 h-2.5 w-2.5 shrink-0 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" /><span>{{ p }}</span></li>
                    </ul>
                </section>
                <section class="rounded-2xl border border-dashed border-gading-300 bg-white p-6 text-sm">
                    <h2 class="text-lg font-bold text-zamrud-950">Menambah video dan gambar</h2>
                    <ol class="mt-3 list-decimal space-y-2 pl-5">
                        <li>Unggah video ke YouTube (boleh <em>Tidak publik</em>), salin tautannya.</li>
                        <li>Buka <code class="rounded bg-gading-100 px-1">resources/workshop/materi.php</code> di server, isi <code class="rounded bg-gading-100 px-1">'video' =&gt; 'https://youtu.be/…'</code> pada sesi yang sesuai.</li>
                        <li>Unggah gambar ke folder <code class="rounded bg-gading-100 px-1">images/workshop/</code> di folder web (<code class="rounded bg-gading-100 px-1">public_html</code> di cPanel) dengan nama yang tertera di kotak placeholder, mis. <code class="rounded bg-gading-100 px-1">sesi-01-a.webp</code> (boleh .jpg atau .png).</li>
                        <li>Jalankan <code class="rounded bg-gading-100 px-1">php artisan optimize:clear</code> bila perubahan belum tampil.</li>
                    </ol>
                </section>
            </div>
        </main>
    </div>
</template>
