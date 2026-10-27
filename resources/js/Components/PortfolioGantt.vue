<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { tanggal, warnaKesehatan, warnaProyek } from '@/lib/format';

const props = defineProps({
    projects: { type: Array, required: true },
    start: { type: String, required: true }, // awal tahun anggaran
    end: { type: String, required: true },
    today: String,
});

const day = (s) => new Date(`${String(s).slice(0, 10)}T00:00:00`).getTime();
const from = computed(() => day(props.start));
const to = computed(() => day(props.end) + 86400000);
const span = computed(() => to.value - from.value);
const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
const pct = (s) => Math.max(0, Math.min(100, ((day(s) - from.value) / span.value) * 100));

const months = computed(() => {
    const out = [];
    const d = new Date(from.value);
    while (d.getTime() < to.value) {
        out.push({ label: d.toLocaleDateString('id-ID', { month: 'short' }), left: pct(iso(d)) });
        d.setMonth(d.getMonth() + 1);
    }
    return out;
});

const todayPct = computed(() => (props.today ? pct(props.today) : null));

const rows = computed(() => props.projects.map((p, i) => {
    const left = p.start ? pct(p.start) : 0;
    const right = p.finish ? Math.max(left + 0.6, pct(p.finish) + (100 / 365)) : left + 0.6;
    return { ...p, color: warnaProyek(p, i), left, width: Math.min(100, right) - left, overflow: p.beyond_fiscal_year };
}));
</script>

<template>
    <div class="overflow-x-auto">
        <div class="min-w-[720px]">
            <div class="grid grid-cols-[minmax(180px,260px)_1fr] border-b border-gading-200 pb-2 text-xs font-semibold text-slate-500">
                <div>Proyek</div>
                <div class="relative h-4">
                    <span v-for="m in months" :key="m.left" class="absolute" :style="{ left: `${m.left}%` }">{{ m.label }}</span>
                </div>
            </div>
            <div class="relative">
                <div v-for="p in rows" :key="p.ulid" class="grid grid-cols-[minmax(180px,260px)_1fr] items-center border-b border-gading-100 py-2.5">
                    <Link :href="route('projects.show', p.ulid)" class="pr-3 text-sm font-medium text-zamrud-950 hover:text-zamrud-600">
                        <span class="mr-2 inline-block h-2 w-2 rounded-full" :class="warnaKesehatan[p.health]?.dot" />
                        <span class="line-clamp-1">{{ p.name }}</span>
                        <span class="block pl-4 text-xs font-normal text-slate-400">{{ p.code }}</span>
                    </Link>
                    <div class="relative h-7">
                        <span v-for="m in months" :key="m.left" class="absolute inset-y-0 border-l border-dashed border-gading-200" :style="{ left: `${m.left}%` }" />
                        <span v-if="todayPct !== null" class="absolute -inset-y-2 z-10 w-0.5 bg-emas-500" :style="{ left: `${todayPct}%` }" />
                        <div
                            class="absolute top-1 h-5 overflow-hidden rounded-full"
                            :style="{ left: `${p.left}%`, width: `${p.width}%`, background: `${p.color}33` }"
                            :title="`${p.name}\n${tanggal(p.start)} – ${tanggal(p.finish)}\nKemajuan ${p.progress}% (rencana ${p.planned}%)`"
                        >
                            <div class="h-full rounded-full" :style="{ width: `${p.progress}%`, background: p.color }" />
                        </div>
                        <span
                            v-for="ms in p.milestones"
                            :key="ms.title"
                            class="absolute top-2 z-10 h-3 w-3 -translate-x-1/2 rotate-45 border-2 border-white bg-zamrud-900"
                            :style="{ left: `${pct(ms.date)}%` }"
                            :title="`${ms.title} · ${tanggal(ms.date)}`"
                        />
                        <span v-if="p.overflow" class="absolute right-0 top-1 rounded bg-orange-100 px-1.5 text-[10px] font-semibold text-orange-700">lewat TA</span>
                    </div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-0.5 bg-emas-500" /> Hari ini</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rotate-45 bg-zamrud-900" /> Tonggak</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-6 rounded-full bg-zamrud-500/30"><span class="block h-2 w-3 rounded-full bg-zamrud-500" /></span> Isian = kemajuan</span>
            </div>
        </div>
    </div>
</template>
