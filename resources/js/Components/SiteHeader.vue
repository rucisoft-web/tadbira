<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    // Di beranda tautan cukup berupa jangkar; di halaman lain diawali '/'.
    onHome: { type: Boolean, default: false },
    guide: { type: Array, default: () => [] },
    site: { type: String, default: 'https://rucisoft.id' },
});

const base = computed(() => (props.onHome ? '' : '/'));
const menu = [
    { label: 'Kenapa perlu', href: '#kenapa' },
    { label: 'Fitur', href: '#fitur' },
    { label: 'Data demo', href: '#data-demo' },
];
const open = ref(false);
const guidePath = route('guide');
const executivePath = route('executive');
const workshopPath = route('workshop');
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-gading-200 bg-gading-50/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <Link href="/" class="h-10 shrink-0"><ApplicationLogo with-text class="h-10" /></Link>
            <nav class="hidden items-center gap-4 whitespace-nowrap text-sm font-medium text-slate-600 lg:flex xl:gap-5">
                <a v-for="m in menu" :key="m.href" :href="base + m.href" class="hover:text-zamrud-800">{{ m.label }}</a>
                <a :href="executivePath" class="hover:text-zamrud-800">Untuk eksekutif</a>
                <a :href="workshopPath" class="hover:text-zamrud-800">Workshop</a>
                <div class="relative" @mouseenter="open = true" @mouseleave="open = false">
                    <button type="button" class="flex items-center gap-1 hover:text-zamrud-800" :aria-expanded="open" @click="open = !open">
                        Panduan
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                    </button>
                    <div v-show="open" class="absolute left-1/2 top-full w-72 -translate-x-1/2 pt-3">
                        <div class="rounded-xl border border-gading-200 bg-white p-2 shadow-lg">
                            <a :href="guidePath" class="block rounded-lg px-3 py-2 font-semibold text-zamrud-900 hover:bg-zamrud-50">Panduan pengguna lengkap</a>
                            <a v-for="s in guide" :key="s.id" :href="`${guidePath}#${s.id}`" class="block rounded-lg px-3 py-1.5 text-slate-600 hover:bg-zamrud-50 hover:text-zamrud-800">{{ s.title }}</a>
                            <a :href="base + '#panduan'" class="mt-1 block border-t border-gading-200 px-3 pb-1 pt-2 text-slate-600 hover:text-zamrud-800">Siklus bulanan dan peran</a>
                        </div>
                    </div>
                </div>
                <a :href="base + '#tanya'" class="hover:text-zamrud-800">Tanya jawab</a>
                <a :href="site" class="hidden hover:text-zamrud-800 xl:inline">Rucisoft.id</a>
            </nav>
            <div class="flex items-center gap-3">
                <a :href="executivePath" class="hidden text-sm font-medium text-slate-600 hover:text-zamrud-800 sm:inline lg:hidden">Eksekutif</a>
                <a :href="workshopPath" class="hidden text-sm font-medium text-slate-600 hover:text-zamrud-800 sm:inline lg:hidden">Workshop</a>
                <a :href="guidePath" class="hidden text-sm font-medium text-slate-600 hover:text-zamrud-800 sm:inline lg:hidden">Panduan</a>
                <Link :href="route('login')" class="tombol">Masuk</Link>
            </div>
        </div>
    </header>
</template>
