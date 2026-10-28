<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { persen, rupiah, rupiahRingkas, tanggal } from '@/lib/format';

const props = defineProps({
    project: Object,
    summary: Object,
    tasks: Array,
    permissions: Object,
});

const evm = computed(() => props.summary.evm ?? {});
const baseline = computed(() => props.project.baseline);
const canSet = computed(() => (baseline.value ? props.permissions.approve_change : props.permissions.edit));

const reason = ref('');
const busy = ref(false);
const setBaseline = () => {
    if (baseline.value && !reason.value.trim()) return;
    if (!confirm(baseline.value
        ? 'Tetapkan ulang baseline dengan jadwal dan anggaran saat ini? Baseline lama diganti.'
        : 'Bekukan jadwal dan anggaran saat ini sebagai baseline?')) return;
    busy.value = true;
    router.post(route('baseline.store', props.project.ulid), { reason: reason.value }, {
        preserveScroll: true,
        onFinish: () => { busy.value = false; reason.value = ''; },
    });
};

const indexTone = (v) => (v === null || v === undefined ? 'text-slate-400' : v >= 0.95 ? 'text-zamrud-700' : v >= 0.8 ? 'text-emas-700' : 'text-orange-600');
const moneyTone = (v) => (v >= 0 ? 'text-zamrud-700' : 'text-orange-600');
const idx = (v) => (v === null || v === undefined ? '–' : v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

const rows = computed(() => [
    { k: 'BAC', name: 'Anggaran saat selesai', v: rupiah(evm.value.bac), note: baseline.value ? 'Anggaran pada baseline.' : 'Anggaran saat ini (belum ada baseline).' },
    { k: 'PV', name: 'Nilai rencana', v: rupiah(evm.value.pv), note: `BAC × rencana per hari ini (${persen(props.summary.planned, 1)}).` },
    { k: 'EV', name: 'Nilai hasil', v: rupiah(evm.value.ev), note: `BAC × kemajuan fisik (${persen(props.summary.progress, 1)}).` },
    { k: 'AC', name: 'Biaya aktual', v: rupiah(evm.value.ac), note: 'Realisasi yang sudah dibayar.' },
    { k: 'SV', name: 'Varians jadwal', v: rupiah(evm.value.sv), tone: moneyTone(evm.value.sv), note: 'EV − PV. Negatif berarti tertinggal dari rencana.' },
    { k: 'CV', name: 'Varians biaya', v: rupiah(evm.value.cv), tone: moneyTone(evm.value.cv), note: 'EV − AC. Negatif berarti biaya melebihi nilai pekerjaan.' },
    { k: 'SPI', name: 'Indeks kinerja jadwal', v: idx(evm.value.spi), tone: indexTone(evm.value.spi), note: 'EV ÷ PV. 1,00 = tepat rencana.' },
    { k: 'CPI', name: 'Indeks kinerja biaya', v: idx(evm.value.cpi), tone: indexTone(evm.value.cpi), note: 'EV ÷ AC. Di bawah 1,00 = boros.' },
    { k: 'EAC', name: 'Perkiraan biaya akhir', v: rupiah(evm.value.eac), note: 'BAC ÷ CPI, bila efisiensi saat ini berlanjut.' },
    { k: 'ETC', name: 'Perkiraan biaya sisa', v: rupiah(evm.value.etc), note: 'EAC − AC.' },
    { k: 'VAC', name: 'Varians saat selesai', v: rupiah(evm.value.vac), tone: moneyTone(evm.value.vac), note: 'BAC − EAC. Negatif berarti diperkirakan melampaui anggaran.' },
    { k: 'TCPI', name: 'Efisiensi yang dibutuhkan', v: idx(evm.value.tcpi), tone: evm.value.tcpi > 1.1 ? 'text-orange-600' : 'text-zamrud-700', note: '(BAC − EV) ÷ (BAC − AC). Di atas 1,10 sulit dicapai.' },
]);

const slipped = computed(() => props.tasks
    .filter((t) => !t.is_summary && t.finish_variance > 0 && t.percent_complete < 100)
    .sort((a, b) => b.finish_variance - a.finish_variance)
    .slice(0, 8));
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="kartu overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gading-100 px-5 py-4">
                    <div>
                        <h3 class="judul-kartu">Nilai hasil (earned value)</h3>
                        <p class="text-xs text-slate-500">Membandingkan rencana, hasil fisik, dan biaya dalam satu satuan rupiah.</p>
                    </div>
                    <div class="flex gap-4 text-center">
                        <div><p class="text-[11px] font-semibold uppercase text-slate-500">SPI</p><p class="text-xl font-bold tabular-nums" :class="indexTone(evm.spi)">{{ idx(evm.spi) }}</p></div>
                        <div><p class="text-[11px] font-semibold uppercase text-slate-500">CPI</p><p class="text-xl font-bold tabular-nums" :class="indexTone(evm.cpi)">{{ idx(evm.cpi) }}</p></div>
                        <div><p class="text-[11px] font-semibold uppercase text-slate-500">EAC</p><p class="text-xl font-bold tabular-nums text-zamrud-950">{{ rupiahRingkas(evm.eac) }}</p></div>
                    </div>
                </div>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gading-100">
                        <tr v-for="r in rows" :key="r.k">
                            <td class="w-16 px-5 py-2.5 font-mono text-xs font-semibold text-emas-700">{{ r.k }}</td>
                            <td class="px-3 py-2.5 text-zamrud-950">{{ r.name }}<span class="block text-xs text-slate-500">{{ r.note }}</span></td>
                            <td class="whitespace-nowrap px-5 py-2.5 text-right font-semibold tabular-nums" :class="r.tone ?? 'text-zamrud-950'">{{ r.v }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="border-t border-gading-100 bg-gading-50 px-5 py-3 text-xs text-slate-600">
                    Biaya aktual mengikuti realisasi yang <b>dibayar</b>. Pada kontrak bertermin, pembayaran sering tertinggal dari pekerjaan fisik sehingga CPI tampak lebih baik dari sebenarnya. Catat realisasi berstatus "ditagih" bila ingin melihat beban biaya lebih awal.
                </p>
            </div>

            <div v-if="baseline" class="kartu p-5">
                <h3 class="judul-kartu mb-3">Aktivitas yang paling bergeser dari baseline</h3>
                <ul class="divide-y divide-gading-100 text-sm">
                    <li v-for="t in slipped" :key="t.id" class="flex items-center gap-3 py-2">
                        <span class="w-10 text-xs tabular-nums text-slate-400">{{ t.wbs }}</span>
                        <span class="flex-1 truncate" :class="t.critical ? 'text-orange-700' : 'text-zamrud-950'">{{ t.title }}</span>
                        <span class="text-xs text-slate-500">{{ tanggal(t.baseline_finish) }} → {{ tanggal(t.finish) }}</span>
                        <span class="w-16 text-right font-semibold tabular-nums text-orange-600">+{{ t.finish_variance }} hr</span>
                    </li>
                    <li v-if="!slipped.length" class="py-4 text-center text-slate-500">Tidak ada aktivitas berjalan yang bergeser dari baseline.</li>
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="kartu p-5">
                <h3 class="judul-kartu mb-1">Baseline</h3>
                <template v-if="baseline">
                    <p class="text-xs text-slate-500">Baseline ke-{{ baseline.number }} · {{ new Date(baseline.at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}{{ baseline.by ? ` oleh ${baseline.by}` : '' }}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Selesai baseline</dt><dd class="font-semibold tabular-nums">{{ tanggal(baseline.finish) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Perkiraan selesai</dt><dd class="font-semibold tabular-nums">{{ tanggal(project.computed_finish_date) }}</dd></div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Varians selesai</dt>
                            <dd class="font-semibold tabular-nums" :class="summary.finish_variance > 0 ? 'text-orange-600' : 'text-zamrud-700'">
                                {{ summary.finish_variance > 0 ? `terlambat ${summary.finish_variance} hari` : summary.finish_variance < 0 ? `lebih cepat ${-summary.finish_variance} hari` : 'tepat' }}
                            </dd>
                        </div>
                        <div class="flex justify-between"><dt class="text-slate-500">Anggaran baseline</dt><dd class="font-semibold tabular-nums">{{ rupiahRingkas(baseline.budget) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Anggaran saat ini</dt><dd class="font-semibold tabular-nums">{{ rupiahRingkas(summary.budget) }}</dd></div>
                    </dl>
                </template>
                <p v-else class="mt-1 text-sm text-slate-600">
                    Belum ada baseline. Tetapkan setelah jadwal dan anggaran disepakati, agar keterlambatan diukur terhadap rencana awal, bukan jadwal yang terus bergeser.
                </p>

                <form v-if="canSet" class="mt-4 space-y-2 border-t border-gading-100 pt-4" @submit.prevent="setBaseline">
                    <template v-if="baseline">
                        <label class="label">Alasan menetapkan ulang</label>
                        <input v-model="reason" type="text" class="masukan w-full" placeholder="mis. revisi DIPA, addendum kontrak" required>
                    </template>
                    <button class="tombol w-full justify-center" :disabled="busy">{{ baseline ? 'Tetapkan ulang baseline' : 'Tetapkan baseline' }}</button>
                    <p v-if="baseline" class="text-xs text-slate-500">Sebaiknya melalui permintaan perubahan yang disetujui (tab Perubahan).</p>
                </form>
                <p v-else-if="baseline" class="mt-4 border-t border-gading-100 pt-4 text-xs text-slate-500">Menetapkan ulang baseline hanya oleh pimpinan, perencana, atau admin.</p>
            </div>

            <div class="kartu p-5 text-sm text-slate-600">
                <h3 class="judul-kartu mb-2">Cara membaca</h3>
                <ul class="list-disc space-y-1.5 pl-4">
                    <li><b>SPI</b> dan <b>CPI</b> di atas 0,95 berarti sehat; 0,80–0,95 perlu perhatian; di bawah 0,80 kritis.</li>
                    <li><b>EAC</b> di atas BAC adalah tanda dini kekurangan anggaran; pertimbangkan revisi atau penghematan.</li>
                    <li><b>TCPI</b> di atas 1,10 berarti sisa pekerjaan harus jauh lebih efisien agar anggaran cukup.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
