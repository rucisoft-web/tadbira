<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import WorkshopNav from '@/Components/WorkshopNav.vue';

const props = defineProps({
    session: { type: Object, required: true },
    sessions: { type: Array, default: () => [] },
    links: { type: Object, default: () => ({}) },
    guide: { type: Array, default: () => [] },
});

// Alur dengan menit mulai dan selesai, untuk pengatur waktu instruktur.
const flow = computed(() => {
    let start = 0;
    return props.session.flow.map((f) => {
        const item = { ...f, start, end: start + f.minutes };
        start = item.end;
        return item;
    });
});

const elapsed = ref(0);
const running = ref(false);
let timer = null;

const toggle = () => {
    running.value = !running.value;
    if (running.value) {
        timer = setInterval(() => { elapsed.value += 1; }, 1000);
    } else {
        clearInterval(timer);
    }
};
const reset = () => {
    running.value = false;
    clearInterval(timer);
    elapsed.value = 0;
};
onBeforeUnmount(() => clearInterval(timer));

const clock = (seconds) => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
const total = computed(() => props.session.minutes * 60);
const over = computed(() => elapsed.value > total.value);
const activeIndex = computed(() => (elapsed.value === 0 ? -1 : flow.value.findIndex((f) => elapsed.value < f.end * 60)));
</script>

<template>
    <Head :title="`${session.title} · Materi workshop`" />
    <div class="min-h-screen bg-gading-50 bg-pola-terang text-slate-700">
        <SiteHeader :guide="guide" :site="links.site" />

        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[16rem_1fr]">
            <aside class="hidden lg:block">
                <div class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto pr-2">
                    <WorkshopNav :sessions="sessions" :current="session.slug" />
                </div>
            </aside>

            <main class="min-w-0">
                <Link :href="route('workshop.materials')" class="text-sm font-semibold text-zamrud-700 lg:hidden">← Semua materi</Link>

                <!-- Kepala sesi -->
                <header class="mt-2 lg:mt-0">
                    <p class="text-sm font-semibold uppercase tracking-wider text-emas-600">Hari {{ session.day }} · {{ session.time }} · {{ session.minutes }} menit</p>
                    <p class="mt-2 text-xs font-bold uppercase tracking-wider text-zamrud-600">{{ session.tag }}</p>
                    <h1 class="mt-1 text-3xl font-bold text-zamrud-950 sm:text-4xl">{{ session.title }}</h1>
                    <p class="mt-3 max-w-3xl text-lg text-slate-600">{{ session.summary }}</p>
                </header>

                <!-- Video -->
                <section class="mt-8" aria-label="Video">
                    <div v-if="session.videoId" class="aspect-video w-full overflow-hidden rounded-2xl border border-gading-200 bg-black shadow">
                        <iframe
                            class="h-full w-full"
                            :src="`https://www.youtube-nocookie.com/embed/${session.videoId}?rel=0`"
                            :title="`Video: ${session.title}`"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                            allowfullscreen
                            loading="lazy"
                        />
                    </div>
                    <div v-else class="flex aspect-video w-full flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gading-300 bg-white p-6 text-center">
                        <svg class="h-12 w-12 text-emas-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2a2.5 2.5 0 00-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 002.4 7.2 26 26 0 002 12a26 26 0 00.4 4.8 2.5 2.5 0 001.8 1.8c1.6.4 7.8.4 7.8.4s6.2 0 7.8-.4a2.5 2.5 0 001.8-1.8A26 26 0 0022 12a26 26 0 00-.4-4.8zM10 15V9l5.2 3L10 15z" /></svg>
                        <p class="font-semibold text-zamrud-950">Video YouTube menyusul</p>
                        <p class="max-w-md text-xs text-slate-500">Isi <code class="rounded bg-gading-100 px-1">'video'</code> untuk <code class="rounded bg-gading-100 px-1">{{ session.slug }}</code> di resources/workshop/materi.php dengan tautan YouTube.</p>
                    </div>
                </section>

                <div class="mt-8 grid gap-8 xl:grid-cols-[1fr_20rem]">
                    <div class="min-w-0 space-y-8">
                        <!-- Tujuan -->
                        <section>
                            <h2 class="text-xl font-bold text-zamrud-950">Tujuan sesi</h2>
                            <ul class="mt-3 space-y-2">
                                <li v-for="g in session.goals" :key="g" class="flex gap-3"><span class="mt-1.5 h-2.5 w-2.5 shrink-0 rotate-45 rounded-sm bg-emas-500" aria-hidden="true" /><span>{{ g }}</span></li>
                            </ul>
                        </section>

                        <!-- Alur penyampaian -->
                        <section>
                            <h2 class="text-xl font-bold text-zamrud-950">Alur penyampaian</h2>
                            <ol class="mt-4 space-y-3">
                                <li
                                    v-for="(f, i) in flow"
                                    :key="f.title"
                                    class="grid gap-3 rounded-2xl border bg-white p-4 sm:grid-cols-[5.5rem_1fr]"
                                    :class="i === activeIndex ? 'border-zamrud-500 ring-2 ring-zamrud-200' : 'border-gading-200'"
                                >
                                    <div class="text-sm tabular-nums">
                                        <span class="block font-bold text-zamrud-800">{{ f.minutes }} menit</span>
                                        <span class="text-xs text-slate-400">menit {{ f.start }}–{{ f.end }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-zamrud-950">{{ f.title }}</h3>
                                        <p class="mt-1 text-sm text-slate-600">{{ f.text }}</p>
                                    </div>
                                </li>
                            </ol>
                        </section>

                        <!-- Demo -->
                        <section v-if="session.demo.length">
                            <h2 class="text-xl font-bold text-zamrud-950">Langkah demo di Tadbira</h2>
                            <ol class="mt-3 space-y-2">
                                <li v-for="(d, i) in session.demo" :key="d.text" class="flex gap-3 rounded-xl bg-white px-4 py-3 ring-1 ring-gading-200">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zamrud-900 text-xs font-bold text-white">{{ i + 1 }}</span>
                                    <span class="min-w-0 flex-1 text-sm">{{ d.text }}</span>
                                    <a v-if="d.href" :href="d.href" target="_blank" rel="noopener" class="shrink-0 text-sm font-semibold text-zamrud-700 hover:text-zamrud-950">Buka ↗</a>
                                </li>
                            </ol>
                        </section>

                        <!-- Gambar -->
                        <section v-if="session.images.length">
                            <h2 class="text-xl font-bold text-zamrud-950">Gambar pendukung</h2>
                            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                                <figure v-for="img in session.images" :key="img.file">
                                    <a v-if="img.src" :href="img.src" target="_blank" rel="noopener">
                                        <img :src="img.src" :alt="img.caption" loading="lazy" class="aspect-[16/10] w-full rounded-xl border border-gading-200 bg-white object-cover object-top shadow" />
                                    </a>
                                    <div v-else class="flex aspect-[16/10] w-full flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gading-300 bg-white p-4 text-center">
                                        <svg class="h-10 w-10 text-gading-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="9" cy="10" r="2" /><path d="M21 16l-5-5-8 9" /></svg>
                                        <p class="text-sm font-semibold text-slate-500">Gambar menyusul</p>
                                        <code class="break-all rounded bg-gading-100 px-1.5 py-0.5 text-xs text-slate-500">{{ img.path }}</code>
                                    </div>
                                    <figcaption class="mt-2 text-sm font-medium text-zamrud-900">{{ img.caption }}</figcaption>
                                </figure>
                            </div>
                        </section>

                        <!-- Latihan -->
                        <section v-if="session.exercise.length" class="rounded-2xl bg-zamrud-900 bg-pola p-6 text-white">
                            <h2 class="text-xl font-bold">Latihan peserta</h2>
                            <ol class="mt-3 list-decimal space-y-2 pl-5 text-zamrud-50">
                                <li v-for="e in session.exercise" :key="e">{{ e }}</li>
                            </ol>
                        </section>
                    </div>

                    <!-- Kolom kanan: pengatur waktu dan catatan -->
                    <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">
                        <section class="kartu p-5">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-emas-600">Pengatur waktu</h2>
                            <p class="mt-2 text-4xl font-bold tabular-nums" :class="over ? 'text-red-600' : 'text-zamrud-950'">{{ clock(elapsed) }}</p>
                            <p class="text-sm text-slate-500">dari {{ clock(total) }}<span v-if="activeIndex >= 0"> · {{ flow[activeIndex].title }}</span><span v-else-if="over" class="font-semibold text-red-600"> · lewat waktu</span></p>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gading-100">
                                <div class="h-full rounded-full transition-all" :class="over ? 'bg-red-500' : 'bg-zamrud-500'" :style="{ width: `${Math.min(100, (elapsed / total) * 100)}%` }" />
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button type="button" class="tombol" @click="toggle">{{ running ? 'Jeda' : (elapsed ? 'Lanjut' : 'Mulai') }}</button>
                                <button type="button" class="tombol-sekunder" :disabled="!elapsed" @click="reset">Ulang</button>
                            </div>
                        </section>

                        <section v-if="session.keyPoints.length" class="rounded-2xl border border-emas-200 bg-emas-50 p-5">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-emas-800">Pesan kunci</h2>
                            <ul class="mt-2 space-y-2 text-sm text-slate-700">
                                <li v-for="k in session.keyPoints" :key="k">{{ k }}</li>
                            </ul>
                        </section>

                        <section v-if="session.questions.length" class="kartu p-5">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-emas-600">Cek pemahaman</h2>
                            <ul class="mt-2 space-y-2 text-sm">
                                <li v-for="q in session.questions" :key="q">{{ q }}</li>
                            </ul>
                        </section>

                        <section v-if="session.notes" class="rounded-2xl border border-dashed border-gading-300 p-5">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Catatan instruktur</h2>
                            <p class="mt-2 text-sm">{{ session.notes }}</p>
                        </section>
                    </aside>
                </div>

                <!-- Sebelum / sesudah -->
                <nav class="mt-12 grid gap-4 border-t border-gading-200 pt-6 sm:grid-cols-2" aria-label="Sesi lain">
                    <Link v-if="session.prev" :href="route('workshop.material', session.prev.slug)" class="rounded-2xl border border-gading-200 bg-white p-4 hover:border-zamrud-400">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">← Sebelumnya</span>
                        <span class="block font-semibold text-zamrud-950">{{ session.prev.title }}</span>
                    </Link>
                    <span v-else />
                    <Link v-if="session.next" :href="route('workshop.material', session.next.slug)" class="rounded-2xl border border-gading-200 bg-white p-4 text-right hover:border-zamrud-400">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Berikutnya →</span>
                        <span class="block font-semibold text-zamrud-950">{{ session.next.title }}</span>
                    </Link>
                </nav>
            </main>
        </div>
    </div>
</template>
