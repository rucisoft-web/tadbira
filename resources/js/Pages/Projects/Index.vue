<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HealthBadge from '@/Components/HealthBadge.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { persen, rupiahRingkas, tanggal, warnaProyek } from '@/lib/format';

const props = defineProps({
    fiscalYears: Array,
    fiscalYear: Object,
    projects: Array,
    templates: Array,
    goals: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});
const terms = computed(() => usePage().props.terms);
const q = ref('');
const status = ref('');
const unit = ref(props.filters.unit ?? '');
const goal = ref(props.filters.goal ?? '');
const sort = ref('start');

const units = computed(() => [...new Set(props.projects.map((p) => p.category).filter(Boolean))].sort());
const sorters = {
    start: (a, b) => String(a.start).localeCompare(String(b.start)),
    gap: (a, b) => (a.progress - a.planned) - (b.progress - b.planned),
    budget: (a, b) => b.budget - a.budget,
    absorption: (a, b) => a.absorption - b.absorption,
};

const filtered = computed(() => props.projects.filter((p) =>
    (!q.value || `${p.name} ${p.code ?? ''} ${p.category ?? ''}`.toLowerCase().includes(q.value.toLowerCase()))
    && (!status.value || p.health === status.value)
    && (!unit.value || p.category === unit.value)
    && (!goal.value || String(p.goal_id) === String(goal.value))).sort(sorters[sort.value]));

// Daftar panjang ditampilkan bertahap.
const PAGE = 50;
const limit = ref(PAGE);
watch([q, status, unit, goal, sort], () => (limit.value = PAGE));
const visible = computed(() => filtered.value.slice(0, limit.value));
const sum = (key) => filtered.value.reduce((t, p) => t + (p[key] || 0), 0);

const selectFy = (ulid) => router.get(route('projects.index'), { ta: ulid });
</script>

<template>
    <Head :title="terms.project" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold text-zamrud-950">{{ terms.project }}</h1>
                <select class="masukan py-1.5" :value="fiscalYear?.ulid" @change="selectFy($event.target.value)">
                    <option v-for="fy in fiscalYears" :key="fy.ulid" :value="fy.ulid">{{ fy.code }}</option>
                </select>
            </div>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <input v-model="q" type="search" placeholder="Cari nama, kode, kategori…" class="masukan w-full sm:w-72">
            <select v-model="status" class="masukan">
                <option value="">Semua kesehatan jadwal</option>
                <option value="baik">Sesuai jadwal</option>
                <option value="waspada">Perlu perhatian</option>
                <option value="kritis">Terlambat</option>
            </select>
            <select v-if="units.length > 1" v-model="unit" class="masukan">
                <option value="">Semua {{ terms.work_unit?.toLowerCase() }}</option>
                <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
            </select>
            <select v-if="goals.length" v-model="goal" class="masukan max-w-xs">
                <option value="">Semua {{ terms.goal?.toLowerCase() }}</option>
                <option v-for="g in goals" :key="g.id" :value="String(g.id)">{{ g.code }} · {{ g.title }}</option>
            </select>
            <select v-model="sort" class="masukan">
                <option value="start">Urut: tanggal mulai</option>
                <option value="gap">Urut: paling tertinggal</option>
                <option value="budget">Urut: anggaran terbesar</option>
                <option value="absorption">Urut: serapan terendah</option>
            </select>
            <div class="ml-auto flex gap-2">
                <Link v-if="$page.props.auth.access?.can.import_budget" :href="route('budget.import', { ta: fiscalYear?.ulid })" class="tombol-sekunder">{{ terms.budget_import }}</Link>
                <Link v-if="$page.props.auth.access?.can.create_project" :href="route('projects.create', { ta: fiscalYear?.ulid })" class="tombol">+ {{ terms.project }} baru</Link>
            </div>
        </div>

        <p class="mb-2 text-sm text-slate-500">
            {{ filtered.length }} {{ terms.project?.toLowerCase() }} · anggaran {{ rupiahRingkas(sum('budget')) }} · {{ terms.realization?.toLowerCase() }} {{ rupiahRingkas(sum('realized')) }}
        </p>
        <div class="kartu overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">{{ terms.project }}</th>
                        <th class="px-4 py-3">Jadwal</th>
                        <th class="w-48 px-4 py-3">Kemajuan</th>
                        <th class="px-4 py-3">Kesehatan</th>
                        <th class="px-4 py-3 text-right">Anggaran</th>
                        <th class="px-4 py-3 text-right">{{ terms.realization }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <tr v-for="(p, i) in visible" :key="p.ulid" class="hover:bg-gading-50/60">
                        <td class="px-4 py-3">
                            <Link :href="route('projects.show', p.ulid)" class="flex items-start gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full" :style="{ background: warnaProyek(p, i) }" />
                                <span>
                                    <span class="font-semibold text-zamrud-950 hover:text-zamrud-600">{{ p.name }}</span>
                                    <span class="block text-xs text-slate-500">{{ [p.code, p.category, p.status_label, p.leader].filter(Boolean).join(' · ') }}</span>
                                </span>
                            </Link>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            {{ tanggal(p.start) }} – {{ tanggal(p.finish) }}
                            <span v-if="p.beyond_fiscal_year" class="ml-1 rounded bg-orange-100 px-1.5 text-[10px] font-semibold text-orange-700">lewat TA</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="mb-1 flex justify-between text-xs text-slate-500"><span>{{ persen(p.progress) }}</span><span>rencana {{ persen(p.planned) }}</span></div>
                            <ProgressBar :value="p.progress" :planned="p.planned" :color="warnaProyek(p, i)" />
                        </td>
                        <td class="px-4 py-3"><HealthBadge :health="p.health" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ rupiahRingkas(p.budget) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">
                            {{ rupiahRingkas(p.realized) }}
                            <span class="block text-xs text-slate-500">{{ persen(p.absorption, 1) }}</span>
                        </td>
                    </tr>
                    <tr v-if="!filtered.length">
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada {{ terms.project?.toLowerCase() }} di tahun anggaran ini.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="filtered.length > visible.length" class="border-t border-gading-100 p-3 text-center">
                <button type="button" class="tombol-sekunder" @click="limit += PAGE">
                    Tampilkan {{ Math.min(PAGE, filtered.length - visible.length) }} lagi ({{ filtered.length - visible.length }} tersisa)
                </button>
            </div>
        </div>

        <section class="mt-8">
            <h2 class="mb-1 text-base font-bold text-zamrud-950">Templat proyek</h2>
            <p class="mb-4 text-sm text-slate-500">Pilih templat, isi tanggal mulai, dan tanggal selesainya langsung terlihat.</p>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="t in templates"
                    :key="t.ulid"
                    :href="route('projects.create', { ta: fiscalYear?.ulid, templat: t.ulid })"
                    class="kartu group block p-5 transition hover:border-zamrud-300 hover:shadow-md"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-emas-700">{{ t.category }}</p>
                    <p class="mt-1 font-semibold text-zamrud-950 group-hover:text-zamrud-600">{{ t.name }}</p>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ t.description }}</p>
                    <p class="mt-3 text-xs text-slate-400">{{ t.tasks_count }} unsur SRK</p>
                </Link>
                <p v-if="!templates.length" class="text-sm text-slate-500">Belum ada templat. Buka sebuah proyek lalu pilih "Simpan sebagai templat".</p>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
