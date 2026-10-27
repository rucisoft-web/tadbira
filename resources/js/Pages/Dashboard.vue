<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import HealthBadge from '@/Components/HealthBadge.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import PortfolioGantt from '@/Components/PortfolioGantt.vue';
import StatusDoughnut from '@/Components/Charts/StatusDoughnut.vue';
import ProgressBars from '@/Components/Charts/ProgressBars.vue';
import BudgetLine from '@/Components/Charts/BudgetLine.vue';
import { persen, rupiahRingkas, tanggal, warnaKesehatan, warnaProyek } from '@/lib/format';

const props = defineProps({
    fiscalYears: Array,
    fiscalYear: Object,
    portfolio: Object,
    today: String,
});

const terms = computed(() => usePage().props.terms);
const totals = computed(() => props.portfolio?.totals ?? {});
const projects = computed(() => props.portfolio?.projects ?? []);

const healthItems = computed(() => (props.portfolio?.by_health ?? []).map((h) => ({ ...h, color: warnaKesehatan[h.key].hex })));
const statusColors = { planning: '#38BDF8', executing: '#10B981', on_hold: '#A78BFA', closing: '#D4A72C', closed: '#064E3B', cancelled: '#94A3B8' };
const statusItems = computed(() => (props.portfolio?.by_status ?? []).filter((s) => s.count > 0).map((s) => ({ ...s, color: statusColors[s.key] })));

const absorption = computed(() => (totals.value.budget > 0 ? (totals.value.realized / totals.value.budget) * 100 : 0));
const ceilingUse = computed(() => (totals.value.ceiling > 0 ? (totals.value.budget / totals.value.ceiling) * 100 : null));

const attention = computed(() => [...projects.value]
    .filter((p) => p.health !== 'baik' || p.late_tasks > 0 || p.beyond_fiscal_year)
    .sort((a, b) => a.spi - b.spi)
    .slice(0, 5));

const selectFy = (ulid) => router.get(route('dashboard'), { ta: ulid }, { preserveState: false });

const showNewFy = ref(false);
const fyForm = useForm({ year: new Date().getFullYear() + 1, ceiling_amount: '' });
const createFy = () => fyForm.post(route('fiscal-years.store'), { onSuccess: () => (showNewFy.value = false) });
</script>

<template>
    <Head title="Dasbor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-lg font-bold text-zamrud-950">Dasbor {{ terms.portfolio?.toLowerCase() }}</h1>
                <select
                    v-if="fiscalYears.length"
                    class="masukan py-1.5"
                    :value="fiscalYear?.ulid"
                    @change="selectFy($event.target.value)"
                >
                    <option v-for="fy in fiscalYears" :key="fy.ulid" :value="fy.ulid">{{ fy.code }}</option>
                </select>
                <button type="button" class="text-xs font-semibold text-zamrud-700 hover:underline" @click="showNewFy = !showNewFy">
                    + {{ terms.fiscal_year }}
                </button>
            </div>
        </template>

        <form v-if="showNewFy || !fiscalYear" class="kartu mb-6 flex flex-wrap items-end gap-4 p-5" @submit.prevent="createFy">
            <div>
                <label class="label">{{ terms.fiscal_year }}</label>
                <input v-model="fyForm.year" type="number" class="masukan w-32">
            </div>
            <div>
                <label class="label">{{ terms.budget_ceiling }} (Rp)</label>
                <input v-model="fyForm.ceiling_amount" type="number" min="0" class="masukan w-56">
            </div>
            <button class="tombol" :disabled="fyForm.processing">Buat {{ terms.fiscal_year?.toLowerCase() }}</button>
            <p v-if="fyForm.errors.year" class="w-full text-sm text-orange-600">{{ fyForm.errors.year }}</p>
        </form>

        <template v-if="fiscalYear && portfolio">
            <!-- Ringkasan -->
            <section class="relative mb-6 overflow-hidden rounded-2xl bg-zamrud-900 bg-pola p-6 text-white shadow-lg">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-emas-500/10" />
                <div class="relative flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emas-300">{{ fiscalYear.code }}</p>
                        <h2 class="mt-1 text-2xl font-bold">{{ totals.count }} {{ terms.project?.toLowerCase() }} dipantau</h2>
                        <p class="mt-1 text-sm text-zamrud-100/80">
                            Kemajuan rata-rata {{ persen(totals.avg_progress, 1) }} dari rencana {{ persen(totals.avg_planned, 1) }} per {{ tanggal(today) }}.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('projects.create', { ta: fiscalYear.ulid })" class="tombol bg-emas-500 text-zamrud-950 hover:bg-emas-400">+ {{ terms.project }} baru</Link>
                        <Link :href="route('budget.import', { ta: fiscalYear.ulid })" class="tombol-sekunder border-white/20 bg-white/10 text-white hover:bg-white/20">{{ terms.budget_import }}</Link>
                    </div>
                </div>
                <div class="relative mt-5 space-y-1.5">
                    <div class="flex h-2 overflow-hidden rounded-full bg-white/10">
                        <div v-for="(p, i) in projects" :key="p.ulid" class="h-full" :style="{ width: `${100 / Math.max(1, projects.length)}%`, background: warnaProyek(p, i), opacity: 0.35 + (p.progress / 100) * 0.65 }" :title="`${p.name}: ${p.progress}%`" />
                    </div>
                </div>
            </section>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard :label="terms.budget_ceiling" :value="rupiahRingkas(totals.ceiling)" :hint="ceilingUse !== null ? `${persen(ceilingUse, 1)} sudah dialokasikan ke ${terms.project?.toLowerCase()}` : 'Belum diisi'" accent="#D4A72C" />
                <StatCard label="Anggaran proyek" :value="rupiahRingkas(totals.budget)" :hint="`${totals.count} ${terms.project?.toLowerCase()}`" accent="#10B981" />
                <StatCard :label="terms.realization" :value="rupiahRingkas(totals.realized)" :hint="`Penyerapan ${persen(absorption, 1)}`" accent="#38BDF8">
                    <ProgressBar class="mt-3" :value="Math.round(absorption)" color="#38BDF8" />
                </StatCard>
                <StatCard label="Aktivitas terlambat" :value="totals.late_tasks" :hint="totals.beyond_fiscal_year ? `${totals.beyond_fiscal_year} ${terms.project?.toLowerCase()} melewati akhir TA` : 'Semua dalam tahun anggaran'" accent="#F97316" />
            </div>

            <!-- Grafik -->
            <div class="mt-6 grid gap-6 xl:grid-cols-3">
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Kesehatan jadwal</h3>
                    <p class="mb-3 text-xs text-slate-500">Kemajuan aktual dibanding rencana per hari ini</p>
                    <StatusDoughnut :items="healthItems" :center-value="totals.count" :center-label="terms.project" />
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Status {{ terms.project?.toLowerCase() }}</h3>
                    <p class="mb-3 text-xs text-slate-500">Tahap siklus hidup proyek</p>
                    <StatusDoughnut :items="statusItems" :center-value="persen(totals.avg_progress)" center-label="rata-rata selesai" />
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Perlu perhatian</h3>
                    <p class="mb-3 text-xs text-slate-500">Diurutkan dari indeks kinerja jadwal terendah</p>
                    <ul v-if="attention.length" class="divide-y divide-gading-100">
                        <li v-for="p in attention" :key="p.ulid" class="py-3">
                            <Link :href="route('projects.show', p.ulid)" class="block">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-zamrud-950">{{ p.name }}</p>
                                    <HealthBadge :health="p.health" />
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ persen(p.progress) }} dari rencana {{ persen(p.planned) }}
                                    <template v-if="p.late_tasks"> · {{ p.late_tasks }} aktivitas terlambat</template>
                                    <template v-if="p.beyond_fiscal_year"> · melewati akhir TA</template>
                                </p>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="rounded-xl bg-zamrud-50 p-4 text-sm text-zamrud-800">Alhamdulillah, semua {{ terms.project?.toLowerCase() }} sesuai jadwal.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Kemajuan per {{ terms.project?.toLowerCase() }}</h3>
                    <p class="mb-3 text-xs text-slate-500">Batang berwarna = aktual, batang pudar = rencana per hari ini</p>
                    <ProgressBars :projects="projects" />
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Penyerapan anggaran kumulatif</h3>
                    <p class="mb-3 text-xs text-slate-500">Rencana disebar sepanjang jadwal tiap {{ terms.project?.toLowerCase() }}; realisasi dari pembayaran tercatat</p>
                    <BudgetLine :monthly="portfolio.monthly" :ceiling="totals.ceiling" :ceiling-label="terms.budget_ceiling" />
                </div>
            </div>

            <div class="kartu mt-6 p-5">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="judul-kartu">Garis waktu {{ terms.portfolio?.toLowerCase() }} {{ fiscalYear.code }}</h3>
                        <p class="text-xs text-slate-500">{{ tanggal(fiscalYear.start_date) }} – {{ tanggal(fiscalYear.end_date) }}</p>
                    </div>
                    <Link :href="route('projects.index', { ta: fiscalYear.ulid })" class="text-sm font-semibold text-zamrud-700 hover:underline">Lihat semua →</Link>
                </div>
                <PortfolioGantt :projects="projects" :start="fiscalYear.start_date" :end="fiscalYear.end_date" :today="today" />
            </div>
        </template>

        <div v-else-if="fiscalYear" class="kartu p-10 text-center text-slate-500">Belum ada data.</div>
    </AuthenticatedLayout>
</template>
