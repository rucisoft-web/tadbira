<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { tanggal } from '@/lib/format';

const props = defineProps({
    tasks: { type: Array, required: true },
    today: String,
    fiscalYearEnd: String,
    editable: { type: Boolean, default: true },
});

const scales = { hari: 28, minggu: 12, bulan: 4 };
const scale = ref('minggu');
const showCritical = ref(true);
const rowH = 36;
const DAY = 86400000;

const day = (s) => new Date(`${String(s).slice(0, 10)}T00:00:00`).getTime();
const iso = (t) => {
    const d = new Date(t);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

const dated = computed(() => props.tasks.filter((t) => t.start && t.finish));
const range = computed(() => {
    if (!dated.value.length) return null;
    const min = Math.min(...dated.value.map((t) => day(t.start))) - 7 * DAY;
    const max = Math.max(...dated.value.map((t) => day(t.finish))) + 14 * DAY;
    const start = new Date(min);
    start.setDate(start.getDate() - ((start.getDay() + 6) % 7)); // mulai Senin
    return { from: start.getTime(), to: max };
});

const px = computed(() => scales[scale.value]);
const x = (t) => ((t - range.value.from) / DAY) * px.value;
const width = computed(() => (range.value ? x(range.value.to) : 0));
const height = computed(() => props.tasks.length * rowH);

const headerTicks = computed(() => {
    if (!range.value) return [];
    const out = [];
    const d = new Date(range.value.from);
    if (scale.value === 'bulan') d.setDate(1);
    while (d.getTime() <= range.value.to) {
        const label = scale.value === 'hari'
            ? d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
            : scale.value === 'minggu'
                ? d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
                : d.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
        out.push({ x: x(d.getTime()), label, weekend: scale.value === 'hari' && (d.getDay() === 0 || d.getDay() === 6) });
        if (scale.value === 'hari') d.setDate(d.getDate() + 1);
        else if (scale.value === 'minggu') d.setDate(d.getDate() + 7);
        else d.setMonth(d.getMonth() + 1);
    }
    return out;
});

const rowIndex = computed(() => Object.fromEntries(props.tasks.map((t, i) => [t.id, i])));

const bars = computed(() => props.tasks.map((t, i) => {
    if (!t.start || !t.finish) return null;
    const left = x(day(t.start));
    const right = x(day(t.finish) + DAY);
    return { ...t, i, left, w: Math.max(px.value * 0.6, right - left), y: i * rowH };
}));

const arrows = computed(() => {
    const out = [];
    for (const b of bars.value) {
        if (!b) continue;
        for (const pid of b.predecessor_ids || []) {
            const p = bars.value[rowIndex.value[pid]];
            if (!p) continue;
            const x1 = p.kind === 'milestone' ? p.left + 6 : p.left + p.w;
            const y1 = p.y + rowH / 2;
            const x2 = b.kind === 'milestone' ? b.left - 6 : b.left;
            const y2 = b.y + rowH / 2;
            const mid = Math.max(x1 + 8, Math.min(x2 - 8, x1 + 8));
            out.push({
                d: `M${x1},${y1} H${mid} V${y2} H${x2 - 2}`,
                critical: showCritical.value && p.critical && b.critical,
            });
        }
    }
    return out;
});

const todayX = computed(() => (props.today && range.value ? x(day(props.today)) : null));
const fyEndX = computed(() => (props.fiscalYearEnd && range.value ? x(day(props.fiscalYearEnd) + DAY) : null));

const barColor = (b) => {
    if (b.is_summary) return '#064E3B';
    if (showCritical.value && b.critical) return '#F97316';
    return '#10B981';
};

// Seret batang = ubah tanggal mulai (kendala "mulai tidak lebih awal dari");
// tarik ujung kanan = ubah lama pengerjaan.
const drag = ref(null);

function weekdaysBetween(a, b) {
    let n = 0;
    for (let t = a; t <= b; t += DAY) {
        const d = new Date(t).getDay();
        if (d !== 0 && d !== 6) n++;
    }
    return n;
}

function startDrag(e, b, mode) {
    if (!props.editable || b.is_summary) return;
    e.preventDefault();
    drag.value = { id: b.ulid, mode, x0: e.clientX, dx: 0, bar: b };
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', onUp, { once: true });
}

function onMove(e) {
    if (drag.value) drag.value.dx = e.clientX - drag.value.x0;
}

function onUp() {
    window.removeEventListener('pointermove', onMove);
    const d = drag.value;
    drag.value = null;
    if (!d) return;
    const days = Math.round(d.dx / px.value);
    if (days === 0) return;

    if (d.mode === 'move') {
        const start = iso(day(d.bar.start) + days * DAY);
        router.patch(route('tasks.update', d.id), { constraint_type: 'snet', constraint_date: start }, { preserveScroll: true });
    } else {
        const finish = day(d.bar.finish) + days * DAY;
        const duration = Math.max(1, weekdaysBetween(day(d.bar.start), finish));
        router.patch(route('tasks.update', d.id), { duration_days: duration }, { preserveScroll: true });
    }
}

const offset = (b) => (drag.value && drag.value.id === b.ulid ? drag.value : null);
const shownLeft = (b) => b.left + (offset(b)?.mode === 'move' ? offset(b).dx : 0);
const shownW = (b) => Math.max(px.value * 0.6, b.w + (offset(b)?.mode === 'resize' ? offset(b).dx : 0));
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-lg border border-gading-200 bg-white p-0.5 text-xs font-semibold">
                <button
                    v-for="(v, k) in scales"
                    :key="k"
                    type="button"
                    class="rounded-md px-3 py-1.5 capitalize"
                    :class="scale === k ? 'bg-zamrud-800 text-white' : 'text-slate-600 hover:bg-gading-100'"
                    @click="scale = k"
                >{{ k }}</button>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input v-model="showCritical" type="checkbox" class="rounded border-gading-200 text-orange-500 focus:ring-orange-400">
                Tampilkan jalur kritis
            </label>
            <span v-if="editable" class="text-xs text-slate-400">Seret batang untuk menggeser, tarik ujung kanan untuk mengubah lama pengerjaan.</span>
        </div>

        <div v-if="!range" class="rounded-xl border border-dashed border-gading-200 p-10 text-center text-sm text-slate-500">
            Belum ada aktivitas berjadwal. Tambahkan aktivitas di tab Kisi.
        </div>

        <div v-else class="flex overflow-hidden rounded-xl border border-gading-200 bg-white">
            <!-- Panel SRK -->
            <div class="w-72 shrink-0 border-r border-gading-200">
                <div class="flex h-10 items-center border-b border-gading-200 bg-gading-50 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Struktur rincian kerja
                </div>
                <div
                    v-for="t in tasks"
                    :key="t.id"
                    class="flex items-center gap-2 border-b border-gading-100 px-3 text-sm"
                    :style="{ height: `${rowH}px`, paddingLeft: `${12 + t.depth * 14}px` }"
                >
                    <span class="w-9 shrink-0 text-xs tabular-nums text-slate-400">{{ t.wbs }}</span>
                    <span class="truncate" :class="t.is_summary ? 'font-semibold text-zamrud-950' : 'text-slate-700'" :title="t.title">{{ t.title }}</span>
                </div>
            </div>

            <!-- Garis waktu -->
            <div class="flex-1 overflow-x-auto">
                <div :style="{ width: `${width}px` }">
                    <div class="relative h-10 border-b border-gading-200 bg-gading-50 text-[11px] font-semibold text-slate-500">
                        <span v-for="tk in headerTicks" :key="tk.x" class="absolute top-3 whitespace-nowrap pl-1" :style="{ left: `${tk.x}px` }">{{ tk.label }}</span>
                    </div>
                    <svg :width="width" :height="height" class="block select-none">
                        <defs>
                            <marker id="panah" viewBox="0 0 8 8" refX="7" refY="4" markerWidth="6" markerHeight="6" orient="auto">
                                <path d="M0,0 L8,4 L0,8 z" fill="#64748B" />
                            </marker>
                            <marker id="panah-kritis" viewBox="0 0 8 8" refX="7" refY="4" markerWidth="6" markerHeight="6" orient="auto">
                                <path d="M0,0 L8,4 L0,8 z" fill="#F97316" />
                            </marker>
                        </defs>

                        <g>
                            <rect v-for="(t, i) in tasks" :key="`r${t.id}`" x="0" :y="i * rowH" :width="width" :height="rowH" :fill="i % 2 ? '#FFFFFF' : '#FDFBF6'" />
                            <line v-for="tk in headerTicks" :key="`g${tk.x}`" :x1="tk.x" :x2="tk.x" y1="0" :y2="height" stroke="#F5EFE0" />
                            <rect v-for="tk in headerTicks.filter((t) => t.weekend)" :key="`w${tk.x}`" :x="tk.x" y="0" :width="px" :height="height" fill="#F5EFE0" opacity="0.6" />
                        </g>

                        <line v-if="fyEndX !== null && fyEndX < width" :x1="fyEndX" :x2="fyEndX" y1="0" :y2="height" stroke="#F97316" stroke-dasharray="4 4" />
                        <line v-if="todayX !== null" :x1="todayX" :x2="todayX" y1="0" :y2="height" stroke="#D4A72C" stroke-width="2" />

                        <path
                            v-for="(a, i) in arrows"
                            :key="`a${i}`"
                            :d="a.d"
                            fill="none"
                            :stroke="a.critical ? '#F97316' : '#94A3B8'"
                            :stroke-width="a.critical ? 1.6 : 1.2"
                            :marker-end="a.critical ? 'url(#panah-kritis)' : 'url(#panah)'"
                        />

                        <g v-for="b in bars.filter(Boolean)" :key="`b${b.id}`">
                            <title>{{ b.wbs }} {{ b.title }}&#10;{{ tanggal(b.start) }} – {{ tanggal(b.finish) }} · {{ b.duration_days }} hari kerja&#10;Kemajuan {{ b.percent_complete }}%{{ b.slack !== null ? ` · Kelonggaran ${b.slack} hari` : '' }}</title>
                            <!-- Tonggak -->
                            <rect
                                v-if="b.kind === 'milestone'"
                                :x="shownLeft(b) - 7" :y="b.y + rowH / 2 - 7" width="14" height="14"
                                :transform="`rotate(45 ${shownLeft(b)} ${b.y + rowH / 2})`"
                                :fill="showCritical && b.critical ? '#F97316' : '#064E3B'"
                                class="cursor-grab"
                                @pointerdown="startDrag($event, b, 'move')"
                            />
                            <!-- Tahap / paket kerja -->
                            <g v-else-if="b.is_summary">
                                <rect :x="b.left" :y="b.y + 12" :width="b.w" height="8" rx="2" fill="#064E3B" />
                                <rect :x="b.left" :y="b.y + 12" :width="(b.w * b.percent_complete) / 100" height="8" rx="2" fill="#D4A72C" />
                                <path :d="`M${b.left},${b.y + 20} l0,6 l6,-6 z M${b.left + b.w},${b.y + 20} l0,6 l-6,-6 z`" fill="#064E3B" />
                            </g>
                            <!-- Aktivitas -->
                            <g v-else>
                                <rect
                                    :x="shownLeft(b)" :y="b.y + 8" :width="shownW(b)" height="20" rx="6"
                                    :fill="`${barColor(b)}40`" :stroke="barColor(b)" stroke-width="1.2"
                                    class="cursor-grab"
                                    @pointerdown="startDrag($event, b, 'move')"
                                />
                                <rect
                                    :x="shownLeft(b)" :y="b.y + 8" :width="(shownW(b) * b.percent_complete) / 100" height="20" rx="6"
                                    :fill="barColor(b)" class="pointer-events-none"
                                />
                                <rect
                                    v-if="editable"
                                    :x="shownLeft(b) + shownW(b) - 6" :y="b.y + 8" width="8" height="20"
                                    fill="transparent" class="cursor-ew-resize"
                                    @pointerdown="startDrag($event, b, 'resize')"
                                />
                                <text
                                    :x="shownLeft(b) + shownW(b) + 6" :y="b.y + rowH / 2 + 4"
                                    class="pointer-events-none fill-slate-500 text-[11px]"
                                >{{ b.assignees?.[0] ?? '' }}{{ b.assignees?.length ? ' · ' : '' }}{{ b.percent_complete }}%</text>
                            </g>
                        </g>
                    </svg>
                </div>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded bg-zamrud-500" /> Aktivitas</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-5 rounded bg-orange-500" /> Jalur kritis</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2 w-5 rounded-sm bg-zamrud-900" /> Tahap</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rotate-45 bg-zamrud-900" /> Tonggak</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-3 w-0.5 bg-emas-500" /> Hari ini</span>
            <span v-if="fyEndX !== null" class="inline-flex items-center gap-1.5"><span class="h-3 w-0 border-l border-dashed border-orange-500" /> Akhir tahun anggaran</span>
        </div>
    </div>
</template>
