<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import { tanggal } from '@/lib/format';

const props = defineProps({
    fiscalYears: Array,
    fiscalYear: Object,
    weeks: Array,
    people: Array,
    idle: Array,
    units: Array,
    unit: String,
    unitLocked: Boolean,
    from: String,
});

const terms = computed(() => usePage().props.terms);
const expanded = ref(null);
const overOnly = ref(false);

const shown = computed(() => (overOnly.value ? props.people.filter((p) => p.overloaded_weeks) : props.people));
const overloaded = computed(() => props.people.filter((p) => p.overloaded_weeks).length);
const avgLoad = computed(() => {
    const cells = props.people.flatMap((p) => p.weeks.slice(0, 4).map((w) => w.percent));
    return cells.length ? Math.round(cells.reduce((a, b) => a + b, 0) / cells.length) : 0;
});

const go = (params) => router.get(route('workload'), { ta: props.fiscalYear?.ulid, unit: props.unit || undefined, mulai: props.from, ...params }, { preserveScroll: true });
const shift = (weeks) => {
    const d = new Date(`${props.from}T00:00:00`);
    d.setDate(d.getDate() + weeks * 7);
    go({ mulai: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}` });
};

const cellStyle = (p) => {
    if (!p) return { background: 'transparent', color: '#CBD5E1' };
    if (p > 150) return { background: '#FECACA', color: '#991B1B' };
    if (p > 100) return { background: '#FED7AA', color: '#9A3412' };
    if (p >= 70) return { background: '#6EE7B7', color: '#064E3B' };
    return { background: '#D1FAE5', color: '#065F46' };
};
const weekLabel = (w) => tanggal(w.start, { day: 'numeric', month: 'short' });
const thisWeek = (() => {
    const d = new Date();
    d.setDate(d.getDate() - ((d.getDay() + 6) % 7));
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
})();
</script>

<template>
    <Head title="Beban kerja" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold text-zamrud-950">Beban kerja</h1>
                <select v-if="fiscalYears.length" class="masukan py-1.5" :value="fiscalYear?.ulid" @change="go({ ta: $event.target.value })">
                    <option v-for="fy in fiscalYears" :key="fy.ulid" :value="fy.ulid">{{ fy.code }}</option>
                </select>
                <select v-if="!unitLocked && units.length > 1" class="masukan py-1.5" :value="unit ?? ''" @change="go({ unit: $event.target.value || undefined })">
                    <option value="">Semua {{ terms.work_unit?.toLowerCase() }}</option>
                    <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
                </select>
                <span v-else-if="unit" class="rounded-full bg-zamrud-100 px-3 py-1 text-xs font-semibold text-zamrud-800">{{ unit }}</span>
            </div>
        </template>

        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Orang bertugas" :value="people.length" :hint="`${idle.length} anggota tanpa aktivitas di periode ini`" accent="#10B981" />
            <StatCard label="Kelebihan beban" :value="overloaded" hint="Orang dengan minggu di atas 100%" accent="#F97316" />
            <StatCard label="Rata-rata 4 minggu ke depan" :value="`${avgLoad}%`" hint="Dari kapasitas hari kerja" accent="#D4A72C" />
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button type="button" class="tombol-sekunder px-3" title="4 minggu sebelumnya" @click="shift(-4)">‹</button>
                <span class="text-sm text-slate-600">{{ tanggal(weeks[0].start) }} – {{ tanggal(weeks[weeks.length - 1].end) }}</span>
                <button type="button" class="tombol-sekunder px-3" title="4 minggu berikutnya" @click="shift(4)">›</button>
                <button v-if="from !== thisWeek" type="button" class="text-xs font-semibold text-zamrud-700 hover:underline" @click="go({ mulai: undefined })">Minggu ini</button>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input v-model="overOnly" type="checkbox" class="rounded border-gading-200 text-orange-500 focus:ring-orange-400">
                Hanya yang kelebihan beban
            </label>
        </div>

        <div class="kartu mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gading-50 text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="sticky left-0 min-w-[14rem] bg-gading-50 px-4 py-3 text-left uppercase tracking-wide">Nama</th>
                        <th v-for="w in weeks" :key="w.start" class="px-1 py-3 text-center font-semibold" :class="w.start === thisWeek ? 'text-emas-700' : ''">
                            {{ weekLabel(w) }}
                            <span v-if="w.working < 5" class="block text-[10px] font-normal text-slate-400">{{ w.working }} hari kerja</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <template v-for="p in shown" :key="p.id">
                        <tr class="cursor-pointer hover:bg-gading-50/60" @click="expanded = expanded === p.id ? null : p.id">
                            <td class="sticky left-0 bg-white px-4 py-2">
                                <p class="font-medium text-zamrud-950">{{ expanded === p.id ? '▾' : '▸' }} {{ p.name }}</p>
                                <p class="text-xs text-slate-500">{{ p.unit ?? '–' }} · {{ p.tasks.length }} aktivitas</p>
                            </td>
                            <td v-for="(w, i) in p.weeks" :key="i" class="px-1 py-2">
                                <div
                                    class="mx-auto flex h-8 w-14 items-center justify-center rounded-md text-xs font-semibold tabular-nums"
                                    :style="cellStyle(w.percent)"
                                    :title="w.tasks.join('\n')"
                                >{{ w.percent ? `${w.percent}%` : '·' }}</div>
                            </td>
                        </tr>
                        <tr v-if="expanded === p.id">
                            <td :colspan="weeks.length + 1" class="bg-gading-50/60 px-4 py-3">
                                <ul class="space-y-1.5 text-sm">
                                    <li v-for="t in p.tasks" :key="t.ulid" class="flex flex-wrap items-center gap-x-3">
                                        <Link :href="route('projects.show', { project: t.project_ulid, tab: 'kisi' })" class="font-medium hover:underline" :class="t.critical ? 'text-orange-700' : 'text-zamrud-900'">{{ t.label }}</Link>
                                        <span class="text-xs text-slate-500">{{ t.project }} · {{ tanggal(t.start) }} – {{ tanggal(t.finish) }}{{ t.units !== 100 ? ` · ${t.units}%` : '' }}</span>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!shown.length">
                        <td :colspan="weeks.length + 1" class="px-4 py-10 text-center text-slate-500">
                            Tidak ada aktivitas yang ditugaskan pada periode ini. Tetapkan penanggung jawab di kisi SRK setiap {{ terms.project?.toLowerCase() }}.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded" :style="{ background: '#D1FAE5' }" /> di bawah 70%</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded" :style="{ background: '#6EE7B7' }" /> 70–100%</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded" :style="{ background: '#FED7AA' }" /> 101–150%</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded" :style="{ background: '#FECACA' }" /> di atas 150%</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            Dihitung dari aktivitas yang belum selesai dan penanggung jawabnya, per hari kerja (libur dan akhir pekan tidak dihitung). Aktivitas yang sudah lewat tenggat tidak masuk minggu mendatang; lihat tab Kinerja tiap {{ terms.project?.toLowerCase() }}.
        </p>

        <div v-if="idle.length" class="kartu mt-6 p-5">
            <h3 class="judul-kartu mb-2">Anggota tanpa aktivitas pada periode ini</h3>
            <p class="text-sm text-slate-600">
                <span v-for="(u, i) in idle" :key="i">{{ u.name }}<span v-if="u.unit" class="text-slate-400"> ({{ u.unit }})</span>{{ i < idle.length - 1 ? ', ' : '' }}</span>
            </p>
        </div>
    </AuthenticatedLayout>
</template>
