<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-gading-50">
        <div class="relative hidden w-1/2 overflow-hidden bg-zamrud-900 bg-pola p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <Link href="/" class="h-12"><ApplicationLogo with-text light class="h-12" /></Link>
            <div class="relative z-10 max-w-md">
                <p class="font-arab text-3xl leading-relaxed text-emas-300" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
                <p class="mt-1 text-xs text-zamrud-100/70">"Dia mengatur segala urusan." (QS Yunus [10]: 3)</p>
                <h2 class="mt-8 text-3xl font-bold leading-tight">Rencanakan dengan cermat, tuntaskan dengan amanah.</h2>
                <p class="mt-4 text-zamrud-100/80">Pantau banyak proyek dalam satu tahun anggaran, dari POK RKA-K/L sampai RKAP, lengkap dengan Gantt dan jalur kritis.</p>
            </div>
            <div class="relative z-10 space-y-2">
                <span class="block h-2 w-40 rounded-full bg-emas-500" />
                <span class="ml-10 block h-2 w-56 rounded-full bg-zamrud-400" />
                <span class="ml-4 block h-2 w-44 rounded-full bg-sky-400" />
                <span class="ml-24 block h-2 w-36 rounded-full bg-orange-500" />
            </div>
            <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-emas-500/10" />
        </div>
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12">
            <Link href="/" class="mb-8 h-12 lg:hidden"><ApplicationLogo with-text class="h-12" /></Link>
            <div class="w-full max-w-md rounded-2xl border border-gading-200 bg-white p-8 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
