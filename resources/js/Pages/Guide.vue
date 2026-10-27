<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';

defineProps({
    html: { type: String, required: true },
    sections: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
});

// Sorot bab yang sedang dibaca di daftar isi.
const active = ref('');
let observer;
onMounted(() => {
    observer = new IntersectionObserver(
        (entries) => entries.filter((e) => e.isIntersecting).forEach((e) => (active.value = e.target.id)),
        { rootMargin: '-80px 0px -70% 0px' },
    );
    document.querySelectorAll('.panduan h2[id]').forEach((h) => observer.observe(h));
    if (location.hash) document.getElementById(location.hash.slice(1))?.scrollIntoView();
});
onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <Head title="Panduan pengguna" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader :guide="sections" :site="links.site" />

        <div class="mx-auto max-w-6xl px-4 pb-16 pt-10 sm:px-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Panduan Tadbira</p>
            <h1 class="mt-2 text-3xl font-bold text-zamrud-950 sm:text-4xl">Panduan pengguna</h1>
            <p class="mt-3 max-w-3xl text-slate-600">
                Teori manajemen proyek dari buku <em>Catatan Kecil Manajer Proyek</em> karya Jarot Wahyudi dan Jaya Martha, dipadukan dengan langkah praktis di Tadbira.
                Coba setiap langkah tanpa akun lewat <Link :href="route('login')" class="font-semibold text-zamrud-800 underline">mode tamu</Link>.
            </p>

            <details class="kartu mt-6 p-4 lg:hidden">
                <summary class="cursor-pointer font-semibold text-zamrud-900">Daftar isi</summary>
                <ol class="mt-3 space-y-1 text-sm">
                    <li v-for="s in sections" :key="s.id"><a :href="`#${s.id}`" class="hover:text-zamrud-800">{{ s.title }}</a></li>
                </ol>
            </details>

            <div class="mt-8 grid gap-10 lg:grid-cols-[15rem_1fr]">
                <aside class="hidden lg:block">
                    <nav class="sticky top-24 space-y-1 text-sm">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Daftar isi</p>
                        <a
                            v-for="s in sections"
                            :key="s.id"
                            :href="`#${s.id}`"
                            class="block rounded-lg border-l-2 px-3 py-1.5"
                            :class="active === s.id ? 'border-zamrud-600 bg-zamrud-50 font-semibold text-zamrud-900' : 'border-transparent text-slate-600 hover:text-zamrud-800'"
                        >{{ s.title }}</a>
                    </nav>
                </aside>
                <!-- Isi berasal dari resources/docs/panduan.md milik aplikasi, bukan dari masukan pengguna. -->
                <article class="panduan kartu min-w-0 p-6 sm:p-10" v-html="html" />
            </div>
        </div>

        <footer class="border-t border-gading-200 py-8">
            <div class="mx-auto max-w-6xl px-4 text-sm text-slate-500 sm:px-6">
                <span class="font-semibold text-zamrud-900">Tadbira</span> oleh <a :href="links.site" class="underline hover:text-zamrud-800">Rucisoft</a>. Kelola proyek dengan amanah.
            </div>
        </footer>
    </div>
</template>

<style scoped>
.panduan :deep(h2) { @apply mt-14 scroll-mt-24 border-t border-gading-200 pt-10 text-2xl font-bold text-zamrud-950 first:mt-0 first:border-0 first:pt-0; }
.panduan :deep(h3) { @apply mt-8 scroll-mt-24 text-lg font-semibold text-zamrud-900; }
.panduan :deep(p) { @apply mt-4 leading-relaxed; }
.panduan :deep(ul) { @apply mt-4 list-disc space-y-1.5 pl-6; }
.panduan :deep(ol) { @apply mt-4 list-decimal space-y-1.5 pl-6; }
.panduan :deep(strong) { @apply font-semibold text-zamrud-950; }
.panduan :deep(a) { @apply text-zamrud-700 underline hover:text-zamrud-900; }
.panduan :deep(blockquote) { @apply mt-5 rounded-r-xl border-l-4 border-emas-500 bg-emas-50 px-5 py-3 italic text-slate-700; }
.panduan :deep(blockquote p) { @apply mt-2 first:mt-0; }
.panduan :deep(img) { @apply mt-5 h-auto w-full rounded-xl border border-gading-200 shadow-md; }
.panduan :deep(table) { @apply mt-5 block w-full overflow-x-auto text-sm; }
.panduan :deep(th) { @apply border-b-2 border-gading-200 bg-gading-50 px-3 py-2 text-left font-semibold text-zamrud-950; }
.panduan :deep(td) { @apply border-b border-gading-200 px-3 py-2 align-top; }
.panduan :deep(code) { @apply rounded bg-gading-100 px-1.5 py-0.5 text-[0.9em] text-zamrud-900; }
.panduan :deep(pre) { @apply mt-4 overflow-x-auto rounded-xl bg-zamrud-950 p-4 text-sm text-zamrud-50; }
.panduan :deep(pre code) { @apply bg-transparent p-0 text-zamrud-50; }
</style>
