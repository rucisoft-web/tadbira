<script setup>
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { tanggal, tanggalPanjang } from '@/lib/format';

const props = defineProps({
    fiscalYears: Array,
    selectedFiscalYear: String,
    templates: Array,
    selectedTemplate: String,
    users: Array,
    units: { type: Array, default: () => [] },
    forcedCategory: { type: String, default: null },
});

const terms = computed(() => usePage().props.terms);
const fy0 = props.fiscalYears.find((f) => f.ulid === props.selectedFiscalYear) ?? props.fiscalYears[0];
const tpl0 = props.templates.find((t) => t.ulid === props.selectedTemplate);

const form = useForm({
    name: tpl0 ? tpl0.name : '',
    code: '',
    category: props.forcedCategory ?? tpl0?.category ?? '',
    description: '',
    fiscal_year: fy0?.ulid ?? '',
    template: props.selectedTemplate ?? '',
    scheduling_direction: 'forward',
    anchor_date: fy0 ? fy0.start_date : new Date().toISOString().slice(0, 10),
    budget_amount: '',
    leader_id: usePage().props.auth.user.id,
    goal_id: '',
});

const fy = computed(() => props.fiscalYears.find((f) => f.ulid === form.fiscal_year));

watch(() => form.template, (ulid) => {
    const t = props.templates.find((x) => x.ulid === ulid);
    if (t && (!form.name || props.templates.some((x) => x.name === form.name))) {
        form.name = t.name;
        form.category = props.forcedCategory ?? t.category ?? '';
    }
});

// Pratinjau tanggal selesai dari templat
const preview = ref(null);
const loading = ref(false);
let timer;
watch(() => [form.template, form.anchor_date, form.scheduling_direction, form.fiscal_year], () => {
    clearTimeout(timer);
    if (!form.template || !form.anchor_date) {
        preview.value = null;
        return;
    }
    timer = setTimeout(async () => {
        loading.value = true;
        try {
            const { data } = await axios.post(route('templates.preview', form.template), {
                direction: form.scheduling_direction,
                date: form.anchor_date,
                fiscal_year: form.fiscal_year,
            });
            preview.value = data;
        } finally {
            loading.value = false;
        }
    }, 250);
}, { immediate: true });

const span = computed(() => {
    if (!preview.value) return null;
    const s = new Date(`${preview.value.start_date}T00:00:00`).getTime();
    const f = new Date(`${preview.value.finish_date}T00:00:00`).getTime() + 86400000;
    return { s, f, len: f - s };
});
const pos = (d) => ((new Date(`${d}T00:00:00`).getTime() - span.value.s) / span.value.len) * 100;
const len = (a, b) => ((new Date(`${b}T00:00:00`).getTime() + 86400000 - new Date(`${a}T00:00:00`).getTime()) / span.value.len) * 100;
const colors = ['#10B981', '#D4A72C', '#38BDF8', '#F97316', '#A78BFA', '#14B8A6'];

const submit = () => form.post(route('projects.store'));
</script>

<template>
    <Head :title="`${terms.project} baru`" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-zamrud-950">{{ terms.project }} baru</h1>
        </template>

        <form class="grid gap-6 xl:grid-cols-5" @submit.prevent="submit">
            <div class="kartu space-y-5 p-6 xl:col-span-3">
                <div>
                    <label class="label">Templat</label>
                    <select v-model="form.template" class="masukan w-full">
                        <option value="">Tanpa templat (proyek kosong)</option>
                        <option v-for="t in templates" :key="t.ulid" :value="t.ulid">{{ t.name }} · {{ t.tasks_count }} unsur SRK</option>
                    </select>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="label">Nama {{ terms.project?.toLowerCase() }}</label>
                        <input v-model="form.name" type="text" class="masukan w-full" required>
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <label class="label">Kode</label>
                        <input v-model="form.code" type="text" class="masukan w-full" placeholder="mis. 4471.QDB.001">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">{{ terms.fiscal_year }}</label>
                        <select v-model="form.fiscal_year" class="masukan w-full">
                            <option v-for="f in fiscalYears" :key="f.ulid" :value="f.ulid">{{ f.code }}</option>
                        </select>
                        <InputError :message="form.errors.fiscal_year" class="mt-1" />
                    </div>
                    <div>
                        <label class="label">{{ terms.leader }}</label>
                        <select v-model="form.leader_id" class="masukan w-full">
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend class="label">Penjadwalan</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4" :class="form.scheduling_direction === 'forward' ? 'border-zamrud-500 bg-zamrud-50' : 'border-gading-200'">
                            <input v-model="form.scheduling_direction" type="radio" value="forward" class="mt-0.5 text-zamrud-600 focus:ring-zamrud-500">
                            <span><span class="block text-sm font-semibold text-zamrud-950">Dari tanggal mulai</span><span class="text-xs text-slate-500">Isi tanggal mulai, aplikasi menghitung kapan selesai.</span></span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4" :class="form.scheduling_direction === 'backward' ? 'border-zamrud-500 bg-zamrud-50' : 'border-gading-200'">
                            <input v-model="form.scheduling_direction" type="radio" value="backward" class="mt-0.5 text-zamrud-600 focus:ring-zamrud-500">
                            <span><span class="block text-sm font-semibold text-zamrud-950">Hitung mundur</span><span class="text-xs text-slate-500">Isi tanggal acara/selesai, aplikasi menghitung kapan harus mulai.</span></span>
                        </label>
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">{{ form.scheduling_direction === 'forward' ? 'Tanggal mulai' : 'Tanggal selesai / acara' }}</label>
                        <input v-model="form.anchor_date" type="date" class="masukan w-full" required>
                        <InputError :message="form.errors.anchor_date" class="mt-1" />
                    </div>
                    <div>
                        <label class="label">Anggaran (Rp)</label>
                        <input v-model="form.budget_amount" type="number" min="0" class="masukan w-full" placeholder="opsional">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label">{{ terms.work_unit }} / kategori</label>
                        <input v-if="forcedCategory" :value="forcedCategory" type="text" class="masukan w-full bg-gading-50" disabled>
                        <template v-else>
                            <input v-model="form.category" type="text" class="masukan w-full" list="daftar-unit">
                            <datalist id="daftar-unit">
                                <option v-for="u in units" :key="u.name" :value="u.name">{{ u.full_name }}</option>
                            </datalist>
                        </template>
                        <p v-if="forcedCategory" class="mt-1 text-xs text-slate-500">Operator unit membuat kegiatan untuk unitnya sendiri.</p>
                    </div>
                    <div v-if="fy?.goals?.length">
                        <label class="label">{{ terms.goal }}</label>
                        <select v-model="form.goal_id" class="masukan w-full">
                            <option value="">Belum dikaitkan</option>
                            <option v-for="g in fy.goals" :key="g.id" :value="g.id">{{ g.code }} · {{ g.title }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Uraian</label>
                    <textarea v-model="form.description" rows="3" class="masukan w-full" />
                </div>

                <div class="flex justify-end">
                    <button class="tombol" :disabled="form.processing">Buat {{ terms.project?.toLowerCase() }}</button>
                </div>
            </div>

            <!-- Pratinjau -->
            <div class="xl:col-span-2">
                <div class="kartu sticky top-24 overflow-hidden">
                    <div class="bg-zamrud-900 bg-pola p-5 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emas-300">Pratinjau jadwal</p>
                        <template v-if="preview">
                            <div class="mt-3 grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-zamrud-100/70">Mulai</p>
                                    <p class="font-semibold">{{ tanggalPanjang(preview.start_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zamrud-100/70">Selesai</p>
                                    <p class="text-lg font-bold text-emas-300">{{ tanggalPanjang(preview.finish_date) }}</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-zamrud-100/80">
                                {{ preview.working_days }} hari kerja ({{ preview.calendar_days }} hari kalender)<template v-if="preview.holidays">, melewati {{ preview.holidays }} hari libur</template>.
                            </p>
                        </template>
                        <p v-else class="mt-3 text-sm text-zamrud-100/80">
                            {{ form.template ? (loading ? 'Menghitung…' : '') : 'Pilih templat untuk melihat kapan proyek selesai sebelum dibuat.' }}
                        </p>
                    </div>

                    <div v-if="preview" class="space-y-4 p-5">
                        <div v-if="preview.within_fiscal_year === false" class="rounded-lg bg-orange-50 p-3 text-sm text-orange-800">
                            Jadwal melewati batas {{ fy?.code }}. Pertimbangkan memajukan tanggal mulai atau membagi anggaran ke tahun berikutnya.
                        </div>
                        <div v-else-if="preview.within_fiscal_year" class="rounded-lg bg-zamrud-50 p-3 text-sm text-zamrud-800">
                            Seluruh jadwal berada di dalam {{ fy?.code }}.
                        </div>

                        <div>
                            <p class="label">{{ terms.phase }}</p>
                            <div class="space-y-2">
                                <div v-for="(ph, i) in preview.phases" :key="ph.title">
                                    <div class="flex justify-between text-xs"><span class="font-medium text-zamrud-950">{{ ph.title }}</span><span class="text-slate-500">{{ tanggal(ph.start) }} – {{ tanggal(ph.finish) }}</span></div>
                                    <div class="relative mt-1 h-2 rounded-full bg-gading-100">
                                        <div class="absolute h-2 rounded-full" :style="{ left: `${pos(ph.start)}%`, width: `${Math.max(1, len(ph.start, ph.finish))}%`, background: colors[i % colors.length] }" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="preview.milestones.length">
                            <p class="label">{{ terms.milestone }}</p>
                            <ul class="space-y-1 text-sm">
                                <li v-for="m in preview.milestones" :key="m.title" class="flex items-center gap-2">
                                    <span class="h-2 w-2 rotate-45 bg-zamrud-900" />
                                    <span class="flex-1">{{ m.title }}</span>
                                    <span class="text-slate-500">{{ tanggal(m.date) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
