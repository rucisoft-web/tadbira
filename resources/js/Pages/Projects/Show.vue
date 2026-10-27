<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import HealthBadge from '@/Components/HealthBadge.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import StatCard from '@/Components/StatCard.vue';
import ProjectGantt from '@/Components/ProjectGantt.vue';
import ProjectDocuments from '@/Components/ProjectDocuments.vue';
import { persen, rupiah, rupiahRingkas, tanggal, tanggalPanjang } from '@/lib/format';

const props = defineProps({
    project: Object,
    summary: Object,
    tasks: Array,
    budget: Array,
    budgetLevels: Object,
    activity: Array,
    users: Array,
    goals: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    documentRules: { type: Object, default: () => ({ max_kb: 20480, extensions: [], categories: [] }) },
});

const page = usePage();
const terms = computed(() => page.props.terms);
const errors = computed(() => page.props.errors ?? {});
const now = new Date();
const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

const tabs = ['ringkasan', 'kisi', 'gantt', 'anggaran', 'dokumen', 'riwayat'];
const initialTab = new URLSearchParams(window.location.search).get('tab');
const tab = ref(tabs.includes(initialTab) ? initialTab : 'gantt');
const setTab = (t) => {
    tab.value = t;
    const url = new URL(window.location.href);
    url.searchParams.set('tab', t);
    window.history.replaceState(window.history.state, '', url);
};

const leaves = computed(() => props.tasks.filter((t) => !t.is_summary));
const critical = computed(() => leaves.value.filter((t) => t.critical));
const upcoming = computed(() => leaves.value
    .filter((t) => t.percent_complete < 100 && t.start)
    .sort((a, b) => a.start.localeCompare(b.start))
    .slice(0, 6));
const milestones = computed(() => props.tasks.filter((t) => t.kind === 'milestone'));

// ---------- Kisi ----------
const save = (task, field, value) => {
    if (task[field] === value) return;
    router.patch(route('tasks.update', task.ulid), { [field]: value }, { preserveScroll: true, preserveState: true });
};
const remove = (task) => {
    if (confirm(`Hapus "${task.title}"${task.is_summary ? ' beserta isinya' : ''}?`)) {
        router.delete(route('tasks.destroy', task.ulid), { preserveScroll: true });
    }
};
const summaries = computed(() => props.tasks.filter((t) => t.kind === 'phase' || t.kind === 'work_package' || t.is_summary));

const newTask = useForm({ title: '', kind: 'activity', parent: '', duration_days: 5, predecessors: '' });
const addTask = () => newTask.post(route('tasks.store', props.project.ulid), {
    preserveScroll: true,
    onSuccess: () => newTask.reset('title', 'predecessors'),
});

// ---------- Pengaturan jadwal ----------
const settings = useForm({
    scheduling_direction: props.project.scheduling_direction,
    anchor_date: props.project.scheduling_direction === 'backward' ? props.project.target_finish_date : props.project.start_date,
    status: props.project.status,
    goal_id: props.project.goal_id,
});
const saveSettings = () => settings.patch(route('projects.update', props.project.ulid), { preserveScroll: true });

const templateForm = useForm({ name: `Templat ${props.project.name}`, use_actual_durations: false });
const saveTemplate = () => templateForm.post(route('templates.store', props.project.ulid), { preserveScroll: true });

// ---------- Anggaran ----------
const flatBudget = computed(() => {
    const out = [];
    const walk = (nodes) => nodes.forEach((n) => { out.push(n); walk(n.children); });
    walk(props.budget);
    return out;
});
const collapsed = ref(new Set());
const visibleBudget = computed(() => {
    const out = [];
    const walk = (nodes, hidden) => nodes.forEach((n) => {
        if (!hidden) out.push(n);
        walk(n.children, hidden || collapsed.value.has(n.id));
    });
    walk(props.budget, false);
    return out;
});
const toggle = (id) => {
    const s = new Set(collapsed.value);
    s.has(id) ? s.delete(id) : s.add(id);
    collapsed.value = s;
};
const budgetTotal = computed(() => props.budget.reduce((a, n) => a + n.amount, 0));
const realizedTotal = computed(() => props.budget.reduce((a, n) => a + n.realized_total, 0));

const expenseFor = ref(null);
const expense = useForm({ amount: '', transaction_date: today, status: 'paid', reference: '' });
const saveExpense = () => expense.post(route('expenditures.store', expenseFor.value.id), {
    preserveScroll: true,
    onSuccess: () => { expense.reset(); expenseFor.value = null; },
});

const kindLabel = { phase: terms.value.phase, work_package: terms.value.work_package, activity: terms.value.activity, milestone: terms.value.milestone };
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link :href="route('projects.index', { ta: project.fiscal_year?.ulid })" class="text-slate-500 hover:text-zamrud-700">{{ terms.project }}</Link>
                <span class="text-slate-300">/</span>
                <span class="truncate font-semibold text-zamrud-950">{{ project.name }}</span>
            </div>
        </template>

        <!-- Kepala proyek -->
        <section class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emas-700">{{ project.code }} · {{ project.fiscal_year?.code }} · {{ project.category }}</p>
                <h1 class="mt-1 text-2xl font-bold text-zamrud-950">{{ project.name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ terms.leader }}: {{ project.leader ?? '–' }} · {{ project.status_label }} ·
                    {{ tanggal(project.computed_start_date) }} – <span class="font-semibold text-zamrud-900">{{ tanggal(project.computed_finish_date) }}</span>
                </p>
            </div>
            <HealthBadge :health="summary.health" />
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Kemajuan" :value="persen(summary.progress, 1)" :hint="`Rencana per hari ini ${persen(summary.planned, 1)}`" accent="#10B981">
                <ProgressBar class="mt-3" :value="summary.progress" :planned="summary.planned" />
            </StatCard>
            <StatCard label="Selesai" :value="tanggal(project.computed_finish_date)" :hint="summary.beyond_fiscal_year ? 'Melewati akhir tahun anggaran' : `Mulai ${tanggal(project.computed_start_date)}`" accent="#D4A72C" />
            <StatCard :label="terms.critical_path" :value="`${summary.critical_tasks} aktivitas`" :hint="`${summary.late_tasks} aktivitas terlambat`" accent="#F97316" />
            <StatCard label="Anggaran" :value="rupiahRingkas(summary.budget)" :hint="`${terms.realization} ${rupiahRingkas(summary.realized)} (${persen(summary.absorption, 1)})`" accent="#38BDF8" />
        </div>

        <!-- Tab -->
        <nav class="mt-8 flex gap-6 overflow-x-auto border-b border-gading-200">
            <button v-for="t in tabs" :key="t" type="button" class="tab capitalize" :class="{ 'tab-aktif': tab === t }" @click="setTab(t)">
                {{ { ringkasan: 'Ringkasan', kisi: 'Kisi SRK', gantt: 'Gantt', anggaran: 'Anggaran', dokumen: 'Dokumen', riwayat: 'Riwayat' }[t] }}
                <span v-if="t === 'dokumen' && documents.length" class="ml-1 rounded-full bg-zamrud-100 px-1.5 text-[11px] text-zamrud-800">{{ documents.length }}</span>
            </button>
        </nav>

        <!-- Ringkasan -->
        <section v-if="tab === 'ringkasan'" class="mt-6 grid gap-6 xl:grid-cols-3">
            <div class="kartu p-5 xl:col-span-2">
                <h3 class="judul-kartu mb-3">Aktivitas berikutnya</h3>
                <ul class="divide-y divide-gading-100">
                    <li v-for="t in upcoming" :key="t.id" class="flex items-center gap-3 py-3">
                        <span class="w-10 text-xs tabular-nums text-slate-400">{{ t.wbs }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium" :class="t.critical ? 'text-orange-700' : 'text-zamrud-950'">{{ t.title }}</p>
                            <p class="text-xs text-slate-500">{{ tanggal(t.start) }} – {{ tanggal(t.finish) }} · {{ t.assignees.join(', ') || 'belum ada penanggung jawab' }}</p>
                        </div>
                        <div class="w-28"><ProgressBar :value="t.percent_complete" :color="t.critical ? '#F97316' : '#10B981'" /></div>
                    </li>
                    <li v-if="!upcoming.length" class="py-6 text-center text-sm text-slate-500">Semua aktivitas sudah selesai.</li>
                </ul>
            </div>
            <div class="space-y-6">
                <div v-if="project.goal" class="kartu border-l-4 border-l-emas-400 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emas-700">{{ terms.goal }} {{ project.goal.code }}</p>
                    <h3 class="mt-1 font-semibold text-zamrud-950">{{ project.goal.title }}</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li v-for="(i, n) in project.goal.indicators" :key="n" class="flex justify-between gap-3">
                            <span class="text-slate-600">{{ i.name }}</span>
                            <span class="shrink-0 font-semibold text-zamrud-900">{{ i.target }}</span>
                        </li>
                    </ul>
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu mb-3">{{ terms.milestone }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li v-for="m in milestones" :key="m.id" class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rotate-45" :class="m.percent_complete >= 100 ? 'bg-zamrud-500' : 'bg-zamrud-900'" />
                            <span class="flex-1">{{ m.title }}</span>
                            <span class="text-slate-500">{{ tanggal(m.start) }}</span>
                        </li>
                        <li v-if="!milestones.length" class="text-slate-500">Belum ada tonggak.</li>
                    </ul>
                </div>
                <form class="kartu space-y-3 p-5" @submit.prevent="saveSettings">
                    <h3 class="judul-kartu">Jadwal &amp; status</h3>
                    <select v-model="settings.scheduling_direction" class="masukan w-full">
                        <option value="forward">Dari tanggal mulai</option>
                        <option value="backward">Hitung mundur dari tanggal selesai</option>
                    </select>
                    <div>
                        <label class="label">{{ settings.scheduling_direction === 'forward' ? 'Tanggal mulai' : 'Tanggal selesai / acara' }}</label>
                        <input v-model="settings.anchor_date" type="date" class="masukan w-full">
                        <p class="mt-1 text-xs text-slate-500">Ubah tanggal ini dan seluruh aktivitas otomatis bergeser. Selesai saat ini: {{ tanggalPanjang(project.computed_finish_date) }}.</p>
                    </div>
                    <select v-model="settings.status" class="masukan w-full">
                        <option value="planning">Perencanaan</option>
                        <option value="executing">Pelaksanaan</option>
                        <option value="on_hold">Ditunda</option>
                        <option value="closing">Penutupan</option>
                        <option value="closed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                    <div v-if="goals.length">
                        <label class="label">{{ terms.goal }}</label>
                        <select v-model="settings.goal_id" class="masukan w-full">
                            <option :value="null">Belum dikaitkan</option>
                            <option v-for="g in goals" :key="g.id" :value="g.id">{{ g.code }} · {{ g.title }}</option>
                        </select>
                    </div>
                    <button class="tombol w-full justify-center" :disabled="settings.processing">Simpan</button>
                </form>
                <form class="kartu space-y-3 p-5" @submit.prevent="saveTemplate">
                    <h3 class="judul-kartu">Simpan sebagai templat</h3>
                    <input v-model="templateForm.name" type="text" class="masukan w-full">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="templateForm.use_actual_durations" type="checkbox" class="rounded border-gading-200 text-zamrud-600">
                        Pakai lama pengerjaan aktual sebagai pembelajaran
                    </label>
                    <button class="tombol-sekunder w-full justify-center" :disabled="templateForm.processing">Simpan templat</button>
                </form>
            </div>
        </section>

        <!-- Kisi -->
        <section v-if="tab === 'kisi'" class="mt-6">
            <p v-if="errors.predecessors" class="mb-3 rounded-lg bg-orange-50 p-3 text-sm text-orange-800">{{ errors.predecessors }}</p>
            <div class="kartu overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-14 px-3 py-3">No.</th>
                            <th class="min-w-[18rem] px-3 py-3">Nama</th>
                            <th class="px-3 py-3">Jenis</th>
                            <th class="w-24 px-3 py-3" :title="terms.duration">Lama (hari)</th>
                            <th class="px-3 py-3">Mulai</th>
                            <th class="px-3 py-3">Selesai</th>
                            <th class="w-32 px-3 py-3">{{ terms.predecessor }}</th>
                            <th class="w-24 px-3 py-3">% Selesai</th>
                            <th class="px-3 py-3">{{ terms.responsible }}</th>
                            <th class="w-10" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gading-100">
                        <tr v-for="t in tasks" :key="t.id" :class="t.is_summary ? 'bg-gading-50/70 font-semibold' : ''">
                            <td class="px-3 py-1.5 text-xs tabular-nums text-slate-400">{{ t.wbs }}</td>
                            <td class="px-3 py-1.5" :style="{ paddingLeft: `${12 + t.depth * 18}px` }">
                                <div class="flex items-center gap-2">
                                    <span v-if="t.kind === 'milestone'" class="h-2 w-2 shrink-0 rotate-45 bg-zamrud-900" />
                                    <span v-else-if="t.critical && !t.is_summary" class="h-2 w-2 shrink-0 rounded-full bg-orange-500" title="Jalur kritis" />
                                    <input
                                        :value="t.title"
                                        class="w-full rounded border-transparent bg-transparent px-1 py-1 text-sm hover:border-gading-200 focus:border-zamrud-500 focus:ring-zamrud-500"
                                        :class="t.is_summary ? 'font-semibold text-zamrud-950' : ''"
                                        @change="save(t, 'title', $event.target.value)"
                                    >
                                </div>
                            </td>
                            <td class="px-3 py-1.5 text-xs text-slate-500">{{ kindLabel[t.kind] }}</td>
                            <td class="px-3 py-1.5">
                                <span v-if="t.is_summary" class="tabular-nums">{{ t.duration_days }}</span>
                                <input v-else type="number" min="0" :value="t.duration_days" class="w-20 rounded border-gading-200 px-2 py-1 text-sm tabular-nums" @change="save(t, 'duration_days', Number($event.target.value))">
                            </td>
                            <td class="whitespace-nowrap px-3 py-1.5 tabular-nums">{{ tanggal(t.start) }}</td>
                            <td class="whitespace-nowrap px-3 py-1.5 tabular-nums" :class="!t.is_summary && t.finish < today && t.percent_complete < 100 ? 'text-orange-600' : ''">{{ tanggal(t.finish) }}</td>
                            <td class="px-3 py-1.5">
                                <input v-if="!t.is_summary" :value="t.predecessors" placeholder="mis. 1.2, 1.3+2" class="w-28 rounded border-gading-200 px-2 py-1 text-sm" @change="save(t, 'predecessors', $event.target.value)">
                            </td>
                            <td class="px-3 py-1.5">
                                <span v-if="t.is_summary" class="tabular-nums">{{ t.percent_complete }}%</span>
                                <input v-else type="number" min="0" max="100" step="5" :value="t.percent_complete" class="w-20 rounded border-gading-200 px-2 py-1 text-sm tabular-nums" @change="save(t, 'percent_complete', Number($event.target.value))">
                            </td>
                            <td class="px-3 py-1.5">
                                <select
                                    v-if="!t.is_summary"
                                    class="w-40 rounded border-gading-200 py-1 text-xs"
                                    :value="users.find((u) => u.name === t.assignees[0])?.id ?? ''"
                                    @change="router.patch(route('tasks.update', t.ulid), { assignee_ids: $event.target.value ? [Number($event.target.value)] : [] }, { preserveScroll: true })"
                                >
                                    <option value="">–</option>
                                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </td>
                            <td class="px-2">
                                <button type="button" class="rounded p-1 text-slate-400 hover:bg-orange-50 hover:text-orange-600" title="Hapus" @click="remove(t)">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form class="kartu mt-4 grid gap-3 p-4 sm:grid-cols-12 sm:items-end" @submit.prevent="addTask">
                <div class="sm:col-span-4">
                    <label class="label">Tambah unsur SRK</label>
                    <input v-model="newTask.title" type="text" class="masukan w-full" placeholder="Nama aktivitas" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Jenis</label>
                    <select v-model="newTask.kind" class="masukan w-full">
                        <option value="activity">{{ terms.activity }}</option>
                        <option value="milestone">{{ terms.milestone }}</option>
                        <option value="phase">{{ terms.phase }}</option>
                        <option value="work_package">{{ terms.work_package }}</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Di bawah</label>
                    <select v-model="newTask.parent" class="masukan w-full">
                        <option value="">(tingkat teratas)</option>
                        <option v-for="s in summaries" :key="s.ulid" :value="s.ulid">{{ s.wbs }} {{ s.title }}</option>
                    </select>
                </div>
                <div class="sm:col-span-1">
                    <label class="label">Lama</label>
                    <input v-model="newTask.duration_days" type="number" min="0" class="masukan w-full">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">{{ terms.predecessor }}</label>
                    <input v-model="newTask.predecessors" type="text" class="masukan w-full" placeholder="1.2+2">
                </div>
                <button class="tombol justify-center sm:col-span-1" :disabled="newTask.processing">Tambah</button>
                <p v-if="newTask.errors.predecessors || newTask.errors.title" class="text-sm text-orange-600 sm:col-span-12">{{ newTask.errors.predecessors || newTask.errors.title }}</p>
            </form>
            <p class="mt-2 text-xs text-slate-500">Pendahulu ditulis dengan nomor SRK: "1.2" berarti mulai setelah 1.2 selesai, "1.2+3" dengan jeda 3 hari kerja.</p>
        </section>

        <!-- Gantt -->
        <section v-if="tab === 'gantt'" class="mt-6">
            <ProjectGantt :tasks="tasks" :today="today" :fiscal-year-end="project.fiscal_year?.end_date" />
        </section>

        <!-- Anggaran -->
        <section v-if="tab === 'anggaran'" class="mt-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">
                    {{ terms.budget_document }}: <span class="font-semibold text-zamrud-950">{{ rupiah(budgetTotal) }}</span> ·
                    {{ terms.realization }}: <span class="font-semibold text-zamrud-950">{{ rupiah(realizedTotal) }}</span>
                </p>
                <Link :href="route('budget.import')" class="tombol-sekunder">{{ terms.budget_import }}</Link>
            </div>
            <div class="kartu overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Kode</th>
                            <th class="min-w-[18rem] px-3 py-3">Uraian</th>
                            <th class="px-3 py-3 text-right">Volume</th>
                            <th class="px-3 py-3 text-right">Harga satuan</th>
                            <th class="px-3 py-3 text-right">Jumlah</th>
                            <th class="px-3 py-3">SD</th>
                            <th class="px-3 py-3 text-right">{{ terms.realization }}</th>
                            <th class="w-28" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gading-100">
                        <template v-for="l in visibleBudget" :key="l.id">
                            <tr :class="l.children.length ? 'bg-gading-50/60' : ''">
                                <td class="whitespace-nowrap px-3 py-2 text-xs tabular-nums text-slate-500">{{ l.code }}</td>
                                <td class="px-3 py-2" :style="{ paddingLeft: `${12 + l.depth * 18}px` }">
                                    <button v-if="l.children.length" type="button" class="mr-1 text-slate-400" @click="toggle(l.id)">{{ collapsed.has(l.id) ? '▸' : '▾' }}</button>
                                    <span class="mr-2 rounded bg-zamrud-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-zamrud-700">{{ budgetLevels[l.level] ?? l.level }}</span>
                                    <span :class="l.children.length ? 'font-semibold text-zamrud-950' : ''">{{ l.description }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">{{ l.volume ? `${l.volume.toLocaleString('id-ID')} ${l.unit ?? ''}` : '' }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">{{ l.unit_price ? rupiah(l.unit_price) : '' }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right font-medium tabular-nums">{{ rupiah(l.amount) }}</td>
                                <td class="px-3 py-2 text-xs">{{ l.funding_source }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right tabular-nums">
                                    {{ rupiah(l.realized_total) }}
                                    <span v-if="l.amount" class="block text-xs text-slate-500">{{ persen((l.realized_total / l.amount) * 100, 1) }}</span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button v-if="!l.children.length" type="button" class="text-xs font-semibold text-zamrud-700 hover:underline" @click="expenseFor = expenseFor?.id === l.id ? null : l">+ realisasi</button>
                                </td>
                            </tr>
                            <tr v-if="expenseFor?.id === l.id">
                                <td colspan="8" class="bg-zamrud-50/60 px-3 py-3">
                                    <form class="flex flex-wrap items-end gap-3" @submit.prevent="saveExpense">
                                        <div><label class="label">Jumlah (Rp)</label><input v-model="expense.amount" type="number" min="0" class="masukan w-40" required></div>
                                        <div><label class="label">Tanggal</label><input v-model="expense.transaction_date" type="date" class="masukan" required></div>
                                        <div>
                                            <label class="label">Status</label>
                                            <select v-model="expense.status" class="masukan">
                                                <option value="committed">Dipesan / kontrak</option>
                                                <option value="invoiced">Ditagih</option>
                                                <option value="paid">Dibayar</option>
                                            </select>
                                        </div>
                                        <div><label class="label">Referensi</label><input v-model="expense.reference" type="text" class="masukan w-40" placeholder="No. SP2D / kuitansi"></div>
                                        <button class="tombol" :disabled="expense.processing">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="!flatBudget.length">
                            <td colspan="8" class="px-3 py-10 text-center text-slate-500">Belum ada rincian anggaran. Impor dari {{ terms.budget_document }}.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Dokumen -->
        <section v-if="tab === 'dokumen'" class="mt-6">
            <ProjectDocuments :project="project" :documents="documents" :rules="documentRules" :tasks="tasks" :read-only="!!page.props.auth?.is_guest" />
        </section>

        <!-- Riwayat -->
        <section v-if="tab === 'riwayat'" class="kartu mt-6 p-5">
            <ul class="space-y-3">
                <li v-for="(a, i) in activity" :key="i" class="flex gap-3 text-sm">
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emas-500" />
                    <div>
                        <p class="text-slate-700">{{ a.description }}</p>
                        <p class="text-xs text-slate-400">{{ new Date(a.at).toLocaleString('id-ID') }}</p>
                    </div>
                </li>
                <li v-if="!activity.length" class="text-sm text-slate-500">Belum ada catatan aktivitas.</li>
            </ul>
        </section>
    </AuthenticatedLayout>
</template>
