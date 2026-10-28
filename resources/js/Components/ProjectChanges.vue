<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { rupiah } from '@/lib/format';

const props = defineProps({
    project: Object,
    changes: { type: Array, default: () => [] },
    canSubmit: Boolean,
    canDecide: Boolean,
});

const tone = {
    submitted: 'bg-emas-100 text-emas-800',
    approved: 'bg-zamrud-100 text-zamrud-800',
    rejected: 'bg-orange-100 text-orange-800',
    withdrawn: 'bg-gading-100 text-slate-600',
};
const pending = computed(() => props.changes.filter((c) => c.status === 'submitted').length);
const approvedCost = computed(() => props.changes.filter((c) => c.status === 'approved').reduce((a, c) => a + Number(c.cost_impact || 0), 0));
const approvedDays = computed(() => props.changes.filter((c) => c.status === 'approved').reduce((a, c) => a + Number(c.schedule_impact_days || 0), 0));

const showForm = ref(false);
const form = useForm({ title: '', description: '', reason: '', schedule_impact_days: 0, cost_impact: 0, scope_impact: '' });
const submit = () => form.post(route('changes.store', props.project.ulid), {
    preserveScroll: true,
    onSuccess: () => { form.reset(); showForm.value = false; },
});

const deciding = ref(null);
const decision = useForm({ decision: 'approved', decision_note: '', rebaseline: true });
const openDecision = (c) => {
    deciding.value = c.ulid;
    decision.reset();
    decision.clearErrors();
};
const decide = () => decision.post(route('changes.decide', deciding.value), {
    preserveScroll: true,
    onSuccess: () => { deciding.value = null; },
});
const withdraw = (c) => confirm(`Tarik ${c.code}?`) && router.post(route('changes.withdraw', c.ulid), {}, { preserveScroll: true });
const when = (iso) => (iso ? new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '');
const signed = (n, unit) => (n > 0 ? `+${n} ${unit}` : n < 0 ? `${n} ${unit}` : 'tidak ada');
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-slate-600">{{ changes.length }} permintaan · <b>{{ pending }}</b> menunggu keputusan</p>
                <button v-if="canSubmit" type="button" class="tombol" @click="showForm = !showForm">+ Ajukan perubahan</button>
            </div>

            <form v-if="showForm" class="kartu grid gap-3 border-l-4 border-l-emas-400 p-5 sm:grid-cols-2" @submit.prevent="submit">
                <h3 class="judul-kartu sm:col-span-2">Permintaan perubahan baru</h3>
                <div class="sm:col-span-2">
                    <label class="label">Judul</label>
                    <input v-model="form.title" type="text" class="masukan w-full" required placeholder="mis. Penambahan peserta dari 100 menjadi 150 orang">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Apa yang berubah</label>
                    <textarea v-model="form.description" rows="3" class="masukan w-full" required />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-orange-600">{{ form.errors.description }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Alasan</label>
                    <textarea v-model="form.reason" rows="2" class="masukan w-full" placeholder="mis. arahan pimpinan, revisi DIPA, kondisi lapangan" />
                </div>
                <div>
                    <label class="label">Dampak jadwal (hari kerja)</label>
                    <input v-model.number="form.schedule_impact_days" type="number" class="masukan w-full">
                    <p class="mt-1 text-xs text-slate-500">Positif = mundur, negatif = maju.</p>
                </div>
                <div>
                    <label class="label">Dampak biaya (Rp)</label>
                    <input v-model.number="form.cost_impact" type="number" step="1000" class="masukan w-full">
                    <p class="mt-1 text-xs text-slate-500">Positif = tambahan, negatif = penghematan.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Dampak lingkup / mutu</label>
                    <input v-model="form.scope_impact" type="text" class="masukan w-full" placeholder="mis. tambahan satu sesi kelas paralel">
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <button class="tombol" :disabled="form.processing">Ajukan</button>
                    <button type="button" class="tombol-sekunder" @click="showForm = false">Batal</button>
                </div>
            </form>

            <ul class="space-y-3">
                <li v-for="c in changes" :key="c.ulid" class="kartu p-4">
                    <div class="flex flex-wrap items-start gap-3">
                        <span class="font-mono text-xs font-semibold text-emas-700">{{ c.code }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-zamrud-950">{{ c.title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">Diajukan {{ c.requester ?? '–' }}, {{ when(c.created_at) }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ c.description }}</p>
                            <p v-if="c.reason" class="mt-1 text-sm text-slate-600"><span class="font-semibold">Alasan:</span> {{ c.reason }}</p>
                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-600">
                                <span>Jadwal: <b :class="c.schedule_impact_days > 0 ? 'text-orange-600' : ''">{{ signed(c.schedule_impact_days, 'hari') }}</b></span>
                                <span>Biaya: <b :class="c.cost_impact > 0 ? 'text-orange-600' : ''">{{ c.cost_impact ? rupiah(c.cost_impact) : 'tidak ada' }}</b></span>
                                <span v-if="c.scope_impact">Lingkup: <b>{{ c.scope_impact }}</b></span>
                            </div>
                            <p v-if="c.decided_at" class="mt-2 rounded-lg bg-gading-50 px-3 py-2 text-xs text-slate-600">
                                {{ c.status_label }} oleh {{ c.decider ?? '–' }}, {{ when(c.decided_at) }}{{ c.rebaselined ? ' · baseline ditetapkan ulang' : '' }}{{ c.decision_note ? `. ${c.decision_note}` : '' }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="tone[c.status]">{{ c.status_label }}</span>
                            <button v-if="c.status === 'submitted' && canDecide" type="button" class="text-xs font-semibold text-zamrud-700 hover:underline" @click="openDecision(c)">Putuskan</button>
                            <button v-if="c.status === 'submitted' && canSubmit" type="button" class="text-xs font-semibold text-slate-500 hover:underline" @click="withdraw(c)">Tarik</button>
                        </div>
                    </div>

                    <form v-if="deciding === c.ulid" class="mt-4 space-y-3 border-t border-gading-100 pt-4" @submit.prevent="decide">
                        <div class="flex gap-4 text-sm">
                            <label class="inline-flex items-center gap-2"><input v-model="decision.decision" type="radio" value="approved" class="text-zamrud-600"> Setujui</label>
                            <label class="inline-flex items-center gap-2"><input v-model="decision.decision" type="radio" value="rejected" class="text-orange-600"> Tolak</label>
                        </div>
                        <textarea v-model="decision.decision_note" rows="2" class="masukan w-full" :placeholder="decision.decision === 'rejected' ? 'Alasan penolakan (wajib)' : 'Catatan keputusan (opsional)'" />
                        <p v-if="decision.errors.decision_note" class="text-xs text-orange-600">{{ decision.errors.decision_note }}</p>
                        <label v-if="decision.decision === 'approved'" class="flex items-start gap-2 text-sm text-slate-600">
                            <input v-model="decision.rebaseline" type="checkbox" class="mt-0.5 rounded border-gading-200 text-zamrud-600">
                            <span>Tetapkan ulang baseline dengan jadwal dan anggaran saat ini. Sesuaikan jadwal/anggaran lebih dulu bila perubahan belum diterapkan.</span>
                        </label>
                        <div class="flex gap-2">
                            <button class="tombol" :disabled="decision.processing">Simpan keputusan</button>
                            <button type="button" class="tombol-sekunder" @click="deciding = null">Batal</button>
                        </div>
                    </form>
                </li>
                <li v-if="!changes.length" class="kartu p-8 text-center text-sm text-slate-500">Belum ada permintaan perubahan.</li>
            </ul>
        </div>

        <div class="space-y-6">
            <div class="kartu grid grid-cols-2 divide-x divide-gading-100 text-center">
                <div class="p-4"><p class="text-xl font-bold" :class="approvedDays > 0 ? 'text-orange-600' : 'text-zamrud-950'">{{ signed(approvedDays, 'hari') }}</p><p class="text-xs text-slate-500">dampak jadwal disetujui</p></div>
                <div class="p-4"><p class="text-xl font-bold" :class="approvedCost > 0 ? 'text-orange-600' : 'text-zamrud-950'">{{ approvedCost ? rupiah(approvedCost) : 'Rp 0' }}</p><p class="text-xs text-slate-500">dampak biaya disetujui</p></div>
            </div>
            <div class="kartu p-5 text-sm text-slate-600">
                <h3 class="judul-kartu mb-2">Alur kendali perubahan</h3>
                <ol class="list-decimal space-y-1.5 pl-4">
                    <li>Penanggung jawab kegiatan mengajukan perubahan lingkup, jadwal, atau biaya beserta dampaknya.</li>
                    <li>Pimpinan atau perencana menyetujui atau menolak, dengan catatan.</li>
                    <li>Setelah disetujui, jadwal dan anggaran disesuaikan, lalu baseline ditetapkan ulang agar kinerja diukur terhadap rencana baru.</li>
                </ol>
                <p class="mt-3 text-xs text-slate-500">Semua keputusan tercatat di Riwayat sebagai jejak audit.</p>
            </div>
        </div>
    </div>
</template>
