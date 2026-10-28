<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { tanggal } from '@/lib/format';

const props = defineProps({
    project: Object,
    risks: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    options: Object,
    editable: Boolean,
});

const levels = {
    rendah: { label: 'Rendah', cls: 'bg-zamrud-100 text-zamrud-800', cell: '#D1FAE5' },
    sedang: { label: 'Sedang', cls: 'bg-emas-100 text-emas-800', cell: '#FEF3C7' },
    tinggi: { label: 'Tinggi', cls: 'bg-orange-100 text-orange-800', cell: '#FED7AA' },
    ekstrem: { label: 'Ekstrem', cls: 'bg-red-100 text-red-800', cell: '#FECACA' },
};
const levelOf = (score) => (score >= 17 ? 'ekstrem' : score >= 10 ? 'tinggi' : score >= 5 ? 'sedang' : 'rendah');
const scale = ['', 'Sangat rendah', 'Rendah', 'Sedang', 'Tinggi', 'Sangat tinggi'];

const filter = ref('aktif');
const shown = computed(() => props.risks.filter((r) => (filter.value === 'aktif' ? r.status !== 'closed' : filter.value === 'semua' ? true : r.type === filter.value && r.status !== 'closed')));
const openRisks = computed(() => props.risks.filter((r) => r.type === 'risk' && r.status !== 'closed'));
const openIssues = computed(() => props.risks.filter((r) => r.type === 'issue' && r.status !== 'closed'));
const cell = (p, i) => openRisks.value.filter((r) => r.probability === p && r.impact === i);
const selectedCell = ref(null);
const pick = (p, i) => {
    selectedCell.value = selectedCell.value?.p === p && selectedCell.value?.i === i ? null : { p, i };
};
const listed = computed(() => (selectedCell.value ? cell(selectedCell.value.p, selectedCell.value.i) : shown.value));

const blank = { type: 'risk', title: '', description: '', category: '', probability: 3, impact: 3, strategy: 'mitigasi', response: '', owner_id: '', due_date: '', status: 'open', task: '' };
const form = useForm({ ...blank });
const editing = ref(null);
const showForm = ref(false);
const leaves = computed(() => props.tasks.filter((t) => !t.is_summary));

const startNew = (type = 'risk') => {
    editing.value = null;
    form.defaults({ ...blank, type });
    form.reset();
    form.clearErrors();
    showForm.value = true;
};
const edit = (r) => {
    editing.value = r.ulid;
    form.defaults({ ...blank, ...r, owner_id: r.owner_id ?? '', due_date: r.due_date ?? '', task: r.task?.ulid ?? '', category: r.category ?? '', strategy: r.strategy ?? '' });
    form.reset();
    form.clearErrors();
    showForm.value = true;
};
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    form.transform((d) => ({ ...d, owner_id: d.owner_id || null, due_date: d.due_date || null, category: d.category || null, strategy: d.strategy || null, probability: d.type === 'issue' ? null : d.probability }));
    editing.value ? form.patch(route('risks.update', editing.value), opts) : form.post(route('risks.store', props.project.ulid), opts);
};
const setStatus = (r, status) => router.patch(route('risks.update', r.ulid), { status }, { preserveScroll: true });
const remove = (r) => confirm(`Hapus "${r.title}" dari register?`) && router.delete(route('risks.destroy', r.ulid), { preserveScroll: true });
const formScore = computed(() => (form.type === 'issue' ? 5 : Number(form.probability)) * Number(form.impact));
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <div class="kartu p-5">
                <h3 class="judul-kartu">Matriks risiko</h3>
                <p class="mb-4 text-xs text-slate-500">Risiko terbuka menurut probabilitas × dampak. Klik sel untuk menyaring.</p>
                <div class="flex">
                    <div class="flex w-6 items-center justify-center">
                        <span class="-rotate-90 whitespace-nowrap text-[11px] font-semibold uppercase tracking-wide text-slate-500">Probabilitas</span>
                    </div>
                    <div class="flex-1">
                        <div v-for="p in [5, 4, 3, 2, 1]" :key="p" class="grid grid-cols-[1.25rem_repeat(5,1fr)] gap-1 pb-1">
                            <span class="self-center text-center text-xs tabular-nums text-slate-400">{{ p }}</span>
                            <button
                                v-for="i in [1, 2, 3, 4, 5]"
                                :key="i"
                                type="button"
                                class="flex aspect-square items-center justify-center rounded-md text-sm font-bold text-slate-800 ring-zamrud-700 transition"
                                :class="selectedCell?.p === p && selectedCell?.i === i ? 'ring-2' : ''"
                                :style="{ background: levels[levelOf(p * i)].cell }"
                                :title="`Probabilitas ${p} × dampak ${i} = ${p * i}`"
                                @click="pick(p, i)"
                            >{{ cell(p, i).length || '' }}</button>
                        </div>
                        <div class="grid grid-cols-[1.25rem_repeat(5,1fr)] gap-1">
                            <span />
                            <span v-for="i in 5" :key="i" class="text-center text-xs tabular-nums text-slate-400">{{ i }}</span>
                        </div>
                        <p class="mt-1 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">Dampak</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span v-for="(l, k) in levels" :key="k" class="rounded-full px-2 py-0.5 font-semibold" :class="l.cls">{{ l.label }}</span>
                </div>
            </div>

            <div class="kartu grid grid-cols-2 divide-x divide-gading-100 text-center">
                <div class="p-4"><p class="text-2xl font-bold text-zamrud-950">{{ openRisks.length }}</p><p class="text-xs text-slate-500">risiko terbuka</p></div>
                <div class="p-4"><p class="text-2xl font-bold" :class="openIssues.length ? 'text-orange-600' : 'text-zamrud-950'">{{ openIssues.length }}</p><p class="text-xs text-slate-500">isu belum selesai</p></div>
            </div>

            <div class="kartu p-5 text-sm text-slate-600">
                <h3 class="judul-kartu mb-2">Risiko atau isu?</h3>
                <p><b>Risiko</b> adalah hal yang <i>mungkin</i> terjadi dan mengganggu kegiatan, misalnya penyedia terlambat. <b>Isu</b> adalah masalah yang <i>sudah</i> terjadi dan perlu ditangani sekarang.</p>
                <p class="mt-2">Setiap risiko punya pemilik, strategi (hindari, mitigasi, alihkan, terima, eskalasi), dan batas waktu respons.</p>
            </div>
        </div>

        <div class="space-y-4 xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gading-200 bg-white p-0.5 text-xs font-semibold">
                    <button
                        v-for="(label, k) in { aktif: 'Aktif', risk: 'Risiko', issue: 'Isu', semua: 'Semua' }"
                        :key="k"
                        type="button"
                        class="rounded-md px-3 py-1.5"
                        :class="filter === k && !selectedCell ? 'bg-zamrud-800 text-white' : 'text-slate-600 hover:bg-gading-100'"
                        @click="filter = k; selectedCell = null"
                    >{{ label }}</button>
                </div>
                <div v-if="editable" class="flex gap-2">
                    <button type="button" class="tombol-sekunder" @click="startNew('issue')">+ Isu</button>
                    <button type="button" class="tombol" @click="startNew('risk')">+ Risiko</button>
                </div>
            </div>

            <form v-if="showForm" class="kartu grid gap-3 border-l-4 border-l-emas-400 p-5 sm:grid-cols-6" @submit.prevent="submit">
                <div class="sm:col-span-6 flex items-center justify-between">
                    <h3 class="judul-kartu">{{ editing ? 'Ubah' : 'Catat' }} {{ form.type === 'issue' ? 'isu' : 'risiko' }}</h3>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="levels[levelOf(formScore)].cls">Skor {{ formScore }} · {{ levels[levelOf(formScore)].label }}</span>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Jenis</label>
                    <select v-model="form.type" class="masukan w-full"><option value="risk">Risiko</option><option value="issue">Isu</option></select>
                </div>
                <div class="sm:col-span-4">
                    <label class="label">Judul</label>
                    <input v-model="form.title" type="text" class="masukan w-full" required :placeholder="form.type === 'issue' ? 'mis. Narasumber utama membatalkan kehadiran' : 'mis. Penyedia terlambat mengirim barang'">
                    <p v-if="form.errors.title" class="mt-1 text-xs text-orange-600">{{ form.errors.title }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Kategori</label>
                    <select v-model="form.category" class="masukan w-full"><option value="">–</option><option v-for="c in options.categories" :key="c">{{ c }}</option></select>
                </div>
                <div v-if="form.type === 'risk'" class="sm:col-span-2">
                    <label class="label">Probabilitas</label>
                    <select v-model.number="form.probability" class="masukan w-full"><option v-for="n in 5" :key="n" :value="n">{{ n }} · {{ scale[n] }}</option></select>
                </div>
                <div :class="form.type === 'risk' ? 'sm:col-span-2' : 'sm:col-span-4'">
                    <label class="label">Dampak</label>
                    <select v-model.number="form.impact" class="masukan w-full"><option v-for="n in 5" :key="n" :value="n">{{ n }} · {{ scale[n] }}</option></select>
                </div>
                <div class="sm:col-span-6">
                    <label class="label">Uraian</label>
                    <textarea v-model="form.description" rows="2" class="masukan w-full" placeholder="Penyebab dan akibatnya bagi kegiatan" />
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Strategi</label>
                    <select v-model="form.strategy" class="masukan w-full"><option value="">–</option><option v-for="(l, k) in options.strategies" :key="k" :value="k">{{ l }}</option></select>
                </div>
                <div class="sm:col-span-4">
                    <label class="label">Rencana respons</label>
                    <input v-model="form.response" type="text" class="masukan w-full" placeholder="Langkah yang diambil dan oleh siapa">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Pemilik</label>
                    <select v-model="form.owner_id" class="masukan w-full"><option value="">–</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Batas respons</label>
                    <input v-model="form.due_date" type="date" class="masukan w-full">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Status</label>
                    <select v-model="form.status" class="masukan w-full"><option v-for="(l, k) in options.statuses" :key="k" :value="k">{{ l }}</option></select>
                </div>
                <div class="sm:col-span-6">
                    <label class="label">Terkait aktivitas</label>
                    <select v-model="form.task" class="masukan w-full"><option value="">(seluruh kegiatan)</option><option v-for="t in leaves" :key="t.ulid" :value="t.ulid">{{ t.wbs }} {{ t.title }}</option></select>
                </div>
                <div class="flex gap-2 sm:col-span-6">
                    <button class="tombol" :disabled="form.processing">Simpan</button>
                    <button type="button" class="tombol-sekunder" @click="showForm = false">Batal</button>
                </div>
            </form>

            <p v-if="selectedCell" class="text-sm text-slate-600">
                Menampilkan risiko dengan probabilitas {{ selectedCell.p }} dan dampak {{ selectedCell.i }}.
                <button type="button" class="font-semibold text-zamrud-700 hover:underline" @click="selectedCell = null">Tampilkan semua</button>
            </p>

            <ul class="space-y-3">
                <li v-for="r in listed" :key="r.ulid" class="kartu p-4" :class="r.status === 'closed' ? 'opacity-60' : ''">
                    <div class="flex flex-wrap items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold" :class="levels[r.level].cls" :title="`Skor ${r.score}`">{{ r.score }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-zamrud-950">
                                <span class="mr-1 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase" :class="r.type === 'issue' ? 'bg-orange-100 text-orange-800' : 'bg-zamrud-50 text-zamrud-700'">{{ r.type === 'issue' ? 'Isu' : 'Risiko' }}</span>
                                {{ r.title }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ r.category ?? 'Tanpa kategori' }}
                                <template v-if="r.type === 'risk'"> · P{{ r.probability }} × D{{ r.impact }}</template>
                                <template v-else> · dampak {{ r.impact }}</template>
                                · {{ options.statuses[r.status] }}
                                <template v-if="r.task"> · {{ r.task.label }}</template>
                            </p>
                            <p v-if="r.description" class="mt-2 text-sm text-slate-600">{{ r.description }}</p>
                            <p v-if="r.response" class="mt-2 text-sm">
                                <span class="font-semibold text-zamrud-800">{{ options.strategies[r.strategy] ?? 'Respons' }}:</span> {{ r.response }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Pemilik: {{ r.owner ?? '–' }}
                                <template v-if="r.due_date"> · batas <span :class="r.status !== 'closed' && r.due_date < new Date().toISOString().slice(0, 10) ? 'font-semibold text-orange-600' : ''">{{ tanggal(r.due_date) }}</span></template>
                            </p>
                        </div>
                        <div v-if="editable" class="flex shrink-0 flex-col items-end gap-1 text-xs font-semibold">
                            <button type="button" class="text-zamrud-700 hover:underline" @click="edit(r)">Ubah</button>
                            <button v-if="r.status !== 'closed'" type="button" class="text-slate-500 hover:underline" @click="setStatus(r, 'closed')">Tutup</button>
                            <button v-else type="button" class="text-slate-500 hover:underline" @click="setStatus(r, 'open')">Buka lagi</button>
                            <button type="button" class="text-orange-600 hover:underline" @click="remove(r)">Hapus</button>
                        </div>
                    </div>
                </li>
                <li v-if="!listed.length" class="kartu p-8 text-center text-sm text-slate-500">
                    Belum ada {{ filter === 'issue' ? 'isu' : 'risiko' }} yang dicatat{{ editable ? '. Mulai dengan tiga hal yang paling mungkin menggagalkan kegiatan ini.' : '.' }}
                </li>
            </ul>
        </div>
    </div>
</template>
