<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ProgressBar from '@/Components/ProgressBar.vue';
import { persen, rupiahRingkas } from '@/lib/format';

const props = defineProps({ goals: { type: Array, required: true }, fiscalYear: Object });
const terms = computed(() => usePage().props.terms);

// Kategori capaian KMA 94/2021.
const kategori = {
    sangat_baik: { label: 'Sangat baik', cls: 'bg-sky-100 text-sky-800' },
    baik: { label: 'Baik', cls: 'bg-zamrud-100 text-zamrud-800' },
    cukup: { label: 'Cukup', cls: 'bg-emas-100 text-emas-800' },
    kurang: { label: 'Kurang', cls: 'bg-red-100 text-red-800' },
};
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
        <article v-for="g in goals" :key="g.ulid" class="rounded-xl border border-gading-200 bg-white p-4">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 rounded-md px-2 py-0.5 text-xs font-bold text-white" :style="{ background: g.color || '#065F46' }">{{ g.code }}</span>
                <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-semibold leading-snug text-zamrud-950">{{ g.title }}</h4>
                    <Link
                        :href="route('projects.index', { ta: fiscalYear.ulid, sasaran: g.id })"
                        class="mt-0.5 inline-block text-xs text-slate-500 hover:text-zamrud-700 hover:underline"
                    >
                        {{ g.project_count }} {{ terms.project?.toLowerCase() }} · {{ rupiahRingkas(g.budget) }}
                        <template v-if="g.kritis"> · <span class="text-orange-600">{{ g.kritis }} terlambat</span></template>
                    </Link>
                </div>
            </div>

            <div v-if="g.project_count" class="mt-3">
                <div class="mb-1 flex justify-between text-xs text-slate-500">
                    <span>Kemajuan {{ persen(g.progress) }}</span>
                    <span>Serapan {{ persen(g.budget ? (g.realized / g.budget) * 100 : 0) }}</span>
                </div>
                <ProgressBar :value="g.progress" :planned="g.planned" :color="g.color || '#10B981'" />
            </div>

            <table v-if="g.indicators.length" class="mt-3 w-full text-xs">
                <thead class="text-left text-[10px] uppercase tracking-wide text-slate-400">
                    <tr><th class="pb-1 font-semibold">{{ terms.indicator }}</th><th class="pb-1 text-right font-semibold">Target</th><th class="pb-1 text-right font-semibold">Capaian</th></tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <tr v-for="(i, n) in g.indicators" :key="n" class="align-top">
                        <td class="py-1.5 pr-2 text-slate-600">
                            {{ i.name }}
                            <span v-if="i.baseline_realization" class="block text-[10px] text-slate-400">tahun lalu {{ i.baseline_realization }}</span>
                        </td>
                        <td class="whitespace-nowrap py-1.5 text-right font-semibold text-zamrud-950">{{ i.target }}</td>
                        <td class="whitespace-nowrap py-1.5 pl-2 text-right">
                            <template v-if="i.realization">
                                <span class="font-semibold text-zamrud-950">{{ i.realization }}</span>
                                <span v-if="i.category" class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-semibold" :class="kategori[i.category].cls">{{ persen(i.score) }}</span>
                            </template>
                            <span v-else class="text-slate-400">belum diukur</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>
</template>
