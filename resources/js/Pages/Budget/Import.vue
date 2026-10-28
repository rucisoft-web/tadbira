<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    fiscalYears: Array,
    selectedFiscalYear: String,
    projects: Array,
    isGovernment: Boolean,
});

const terms = computed(() => usePage().props.terms);

const form = useForm({
    file: null,
    mode: 'projects',
    fiscal_year: props.selectedFiscalYear ?? props.fiscalYears[0]?.ulid,
    project: '',
    build_wbs: true,
});

const submit = () => form.post(route('budget.import.store'), { forceFormData: true });
</script>

<template>
    <Head :title="terms.budget_import" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-bold text-zamrud-950">{{ terms.budget_import }}</h1>
        </template>

        <div class="grid gap-6 xl:grid-cols-5">
            <form class="kartu space-y-5 p-6 xl:col-span-3" @submit.prevent="submit">
                <div>
                    <label class="label">Berkas XLS / XLSX / PDF</label>
                    <input
                        type="file"
                        accept=".xls,.xlsx,.csv,.ods,.pdf"
                        class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zamrud-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-zamrud-800 hover:file:bg-zamrud-100"
                        @input="form.file = $event.target.files[0]"
                    >
                    <InputError :message="form.errors.file" class="mt-1" />
                </div>

                <fieldset>
                    <legend class="label">Hasil impor</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4" :class="form.mode === 'projects' ? 'border-zamrud-500 bg-zamrud-50' : 'border-gading-200'">
                            <input v-model="form.mode" type="radio" value="projects" class="mt-0.5 text-zamrud-600">
                            <span>
                                <span class="block text-sm font-semibold text-zamrud-950">Banyak {{ terms.project?.toLowerCase() }} sekaligus</span>
                                <span class="text-xs text-slate-500">Setiap {{ isGovernment ? 'RO (Rincian Output)' : 'Kelompok' }} menjadi satu {{ terms.project?.toLowerCase() }} di tahun anggaran terpilih.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border p-4" :class="form.mode === 'existing' ? 'border-zamrud-500 bg-zamrud-50' : 'border-gading-200'">
                            <input v-model="form.mode" type="radio" value="existing" class="mt-0.5 text-zamrud-600">
                            <span>
                                <span class="block text-sm font-semibold text-zamrud-950">Ke {{ terms.project?.toLowerCase() }} yang ada</span>
                                <span class="text-xs text-slate-500">Seluruh isi berkas menjadi rincian anggaran satu {{ terms.project?.toLowerCase() }}.</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <div v-if="form.mode === 'projects'">
                    <label class="label">{{ terms.fiscal_year }}</label>
                    <select v-model="form.fiscal_year" class="masukan w-full">
                        <option v-for="fy in fiscalYears" :key="fy.ulid" :value="fy.ulid">{{ fy.code }}</option>
                    </select>
                    <InputError :message="form.errors.fiscal_year" class="mt-1" />
                </div>
                <div v-else>
                    <label class="label">{{ terms.project }}</label>
                    <select v-model="form.project" class="masukan w-full">
                        <option value="">Pilih…</option>
                        <option v-for="p in projects" :key="p.ulid" :value="p.ulid">{{ p.code ? `${p.code} · ` : '' }}{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.project" class="mt-1" />
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.build_wbs" type="checkbox" class="rounded border-gading-200 text-zamrud-600">
                    Bentuk {{ terms.wbs?.toLowerCase() }} dari struktur anggaran
                    <span class="text-slate-400">({{ isGovernment ? 'Komponen → tahap, Subkomponen → aktivitas' : 'Kelompok → tahap, Pos → aktivitas' }})</span>
                </label>

                <div class="flex justify-end">
                    <button class="tombol" :disabled="form.processing || !form.file">
                        {{ form.processing ? 'Mengimpor…' : 'Impor' }}
                    </button>
                </div>
            </form>

            <aside class="space-y-4 xl:col-span-2">
                <div v-if="isGovernment" class="kartu border-l-4 border-l-emas-400 p-5">
                    <h3 class="judul-kartu">Laporan realisasi SAKTI / OM-SPAN</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Unggah <span class="font-semibold">Laporan Ketersediaan Dana Detail</span> (PDF atau XLS) apa adanya.
                        Pagu Revisi menjadi anggaran, dan <span class="font-semibold">Realisasi s.d. Periode</span> langsung tercatat.
                    </p>
                    <p class="mt-2 text-sm text-slate-600">
                        Unggah laporan bulan berikutnya ke tahun anggaran yang sama untuk memperbarui pagu dan realisasi.
                        Proyek yang kodenya sama (mis. <span class="font-mono">2132.BEA.001</span>) diperbarui, bukan dibuat ulang.
                    </p>
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Format POK RKA-K/L</h3>
                    <p class="mt-1 text-sm text-slate-600">Untuk kementerian, lembaga, dan badan. Gunakan ekspor POK dari SAKTI apa adanya. Tingkat dikenali dari kodenya:</p>
                    <table class="mt-3 w-full text-xs">
                        <tbody class="divide-y divide-gading-100">
                            <tr><td class="py-1.5 font-semibold">Program</td><td class="font-mono">DK atau 025.04.DL</td></tr>
                            <tr><td class="py-1.5 font-semibold">Kegiatan</td><td class="font-mono">DK.2132 atau 4471</td></tr>
                            <tr><td class="py-1.5 font-semibold">KRO</td><td class="font-mono">BEA, QDB, atau 4471.QDB</td></tr>
                            <tr><td class="py-1.5 font-semibold">RO</td><td class="font-mono">BEA.001, 4471.QDB.001, atau 001</td></tr>
                            <tr><td class="py-1.5 font-semibold">Komponen</td><td class="font-mono">051</td></tr>
                            <tr><td class="py-1.5 font-semibold">Subkomponen</td><td class="font-mono">A</td></tr>
                            <tr><td class="py-1.5 font-semibold">Akun</td><td class="font-mono">521211</td></tr>
                            <tr><td class="py-1.5 font-semibold">Detail</td><td class="font-mono">tanpa kode, diawali "-"</td></tr>
                        </tbody>
                    </table>
                    <p class="mt-3 text-xs text-slate-500">Kolom yang dibaca: KODE, URAIAN, VOLUME, SATUAN, HARGA SATUAN, JUMLAH BIAYA, SD/CP.</p>
                    <a :href="route('budget.template', 'pok')" class="tombol-sekunder mt-4">Unduh contoh POK</a>
                </div>
                <div class="kartu p-5">
                    <h3 class="judul-kartu">Format RAB / RKAP</h3>
                    <p class="mt-1 text-sm text-slate-600">Untuk pemda, BUMN, BUMD, swasta, dan nirlaba. Tambahkan kolom LEVEL berisi <span class="font-mono">kelompok</span>, <span class="font-mono">pos</span>, atau <span class="font-mono">detail</span>.</p>
                    <a :href="route('budget.template', 'rab')" class="tombol-sekunder mt-4">Unduh templat RAB/RKAP</a>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
