<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({ canRegister: Boolean });

const features = [
    { title: 'Portofolio per tahun anggaran', text: 'Pantau banyak proyek sekaligus: kemajuan, kesehatan jadwal, pagu, dan realisasi.', color: '#10B981' },
    { title: 'Gantt dan jalur kritis', text: 'Urutan Selesai-ke-Mulai, penjadwalan ulang otomatis, dan hitung mundur dari tanggal acara.', color: '#D4A72C' },
    { title: 'Impor POK RKA-K/L', text: 'Unggah POK dari SAKTI; setiap RO menjadi proyek lengkap dengan rincian anggarannya. RAB/RKAP juga didukung.', color: '#38BDF8' },
    { title: 'Templat proyek', text: 'Pilih templat, isi tanggal mulai, dan tanggal selesai langsung terlihat.', color: '#F97316' },
];
</script>

<template>
    <Head title="Kelola proyek dengan amanah" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <ApplicationLogo with-text class="h-11" />
            <nav class="flex gap-3">
                <Link :href="route('login')" class="tombol-sekunder">Masuk</Link>
                <Link v-if="canRegister" :href="route('register')" class="tombol">Daftar</Link>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-6 pb-20 pt-10">
            <section class="relative overflow-hidden rounded-3xl bg-zamrud-900 bg-pola px-8 py-14 text-white shadow-xl sm:px-14">
                <div class="absolute -right-16 -top-16 h-72 w-72 rounded-full bg-emas-500/10" />
                <p class="font-arab text-3xl text-emas-300" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
                <h1 class="mt-4 max-w-2xl text-4xl font-bold leading-tight sm:text-5xl">Kelola proyek dengan amanah.</h1>
                <p class="mt-4 max-w-2xl text-lg text-zamrud-100/85">
                    Tadbira membantu kementerian, lembaga, badan, pemda, BUMN, BUMD, dan swasta merencanakan, memantau, dan menuntaskan rencana kegiatan dalam satu tahun anggaran.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link v-if="canRegister" :href="route('register')" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">Mulai sekarang</Link>
                    <Link :href="route('login')" class="tombol-sekunder border-white/20 bg-white/10 text-white hover:bg-white/20">Masuk</Link>
                </div>
                <div class="mt-12 max-w-md space-y-2">
                    <span class="block h-2.5 w-1/2 rounded-full bg-emas-500" />
                    <span class="ml-12 block h-2.5 w-2/3 rounded-full bg-zamrud-400" />
                    <span class="ml-4 block h-2.5 w-1/2 rounded-full bg-sky-400" />
                    <span class="ml-28 block h-2.5 w-1/3 rounded-full bg-orange-500" />
                </div>
            </section>

            <section class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="f in features" :key="f.title" class="kartu p-6">
                    <span class="block h-1.5 w-12 rounded-full" :style="{ background: f.color }" />
                    <h3 class="mt-4 font-semibold text-zamrud-950">{{ f.title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ f.text }}</p>
                </div>
            </section>

            <p class="mt-14 text-center text-sm text-slate-500">
                Nilai kerja: <span class="font-semibold text-zamrud-800">sidik</span> · <span class="font-semibold text-zamrud-800">amanah</span> ·
                <span class="font-semibold text-zamrud-800">tabligh</span> · <span class="font-semibold text-zamrud-800">fathanah</span> · <span class="font-semibold text-zamrud-800">istikamah</span>
            </p>
        </main>
    </div>
</template>
