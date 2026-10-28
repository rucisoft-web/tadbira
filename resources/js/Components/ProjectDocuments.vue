<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    project: { type: Object, required: true },
    documents: { type: Array, default: () => [] },
    rules: { type: Object, required: true },
    tasks: { type: Array, default: () => [] },
    readOnly: { type: Boolean, default: false },
});

const accept = computed(() => props.rules.extensions.map((e) => `.${e}`).join(','));
const maxMb = computed(() => Math.round(props.rules.max_kb / 1024));
const taskOptions = computed(() => props.tasks.filter((t) => !t.is_summary || t.kind === 'milestone'));

const ukuran = (b) => {
    if (b < 1024) return `${b} B`;
    if (b < 1024 * 1024) return `${(b / 1024).toLocaleString('id-ID', { maximumFractionDigits: 0 })} KB`;
    return `${(b / 1024 / 1024).toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB`;
};
const waktu = (iso) => new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

// Warna lencana per jenis berkas.
const warna = (ext) => ({
    pdf: 'bg-red-100 text-red-700',
    doc: 'bg-sky-100 text-sky-700', docx: 'bg-sky-100 text-sky-700', odt: 'bg-sky-100 text-sky-700', rtf: 'bg-sky-100 text-sky-700',
    xls: 'bg-emerald-100 text-emerald-700', xlsx: 'bg-emerald-100 text-emerald-700', ods: 'bg-emerald-100 text-emerald-700', csv: 'bg-emerald-100 text-emerald-700',
    ppt: 'bg-orange-100 text-orange-700', pptx: 'bg-orange-100 text-orange-700', odp: 'bg-orange-100 text-orange-700',
    jpg: 'bg-violet-100 text-violet-700', jpeg: 'bg-violet-100 text-violet-700', png: 'bg-violet-100 text-violet-700', webp: 'bg-violet-100 text-violet-700', gif: 'bg-violet-100 text-violet-700',
}[ext] ?? 'bg-slate-100 text-slate-600');

// ---------- Unggah ----------
const form = useForm({ files: [], category: '', task: '', title: '' });
const input = ref(null);
const dragging = ref(false);
const localError = ref('');

const addFiles = (list) => {
    localError.value = '';
    const ok = [];
    for (const f of list) {
        const ext = f.name.split('.').pop().toLowerCase();
        if (!props.rules.extensions.includes(ext)) {
            localError.value = `"${f.name}" tidak didukung.`;
        } else if (f.size > props.rules.max_kb * 1024) {
            localError.value = `"${f.name}" melebihi ${maxMb.value} MB.`;
        } else {
            ok.push(f);
        }
    }
    form.files = [...form.files, ...ok].slice(0, 20);
};
const onPick = (e) => { addFiles(e.target.files); e.target.value = ''; };
const onDrop = (e) => { dragging.value = false; addFiles(e.dataTransfer.files); };
const removePicked = (i) => form.files.splice(i, 1);

const upload = () => form.post(route('documents.store', props.project.ulid), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
const uploadErrors = computed(() => Object.entries(form.errors).map(([, v]) => v));

// ---------- Daftar ----------
const q = ref('');
const cat = ref('');
const shown = computed(() => props.documents.filter((d) =>
    (!cat.value || d.category === cat.value)
    && (!q.value || `${d.title} ${d.original_name} ${d.task?.label ?? ''}`.toLowerCase().includes(q.value.toLowerCase()))));
const usedCategories = computed(() => [...new Set(props.documents.map((d) => d.category).filter(Boolean))]);
const totalSize = computed(() => props.documents.reduce((a, d) => a + d.size, 0));

const editing = ref(null);
const edit = useForm({ title: '', category: '', task: '' });
const startEdit = (d) => {
    editing.value = d.ulid;
    edit.title = d.title;
    edit.category = d.category ?? '';
    edit.task = d.task?.ulid ?? '';
};
const saveEdit = (d) => edit.patch(route('documents.update', d.ulid), { preserveScroll: true, onSuccess: () => (editing.value = null) });
const remove = (d) => {
    if (confirm(`Hapus dokumen "${d.title}"? Berkasnya ikut terhapus dari server.`)) {
        router.delete(route('documents.destroy', d.ulid), { preserveScroll: true });
    }
};
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-3">
        <!-- Unggah -->
        <form v-if="!readOnly" class="kartu space-y-4 p-5 xl:order-2" @submit.prevent="upload">
            <h3 class="judul-kartu">Unggah dokumen</h3>
            <div
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-8 text-center transition"
                :class="dragging ? 'border-zamrud-500 bg-zamrud-50' : 'border-gading-200 hover:border-zamrud-300'"
                @click="input.click()"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="onDrop"
            >
                <svg class="h-8 w-8 text-zamrud-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" /></svg>
                <p class="mt-2 text-sm font-semibold text-zamrud-900">Seret berkas ke sini atau klik untuk memilih</p>
                <p class="mt-1 text-xs text-slate-500">Bisa beberapa berkas sekaligus (maks. 20), masing-masing ≤ {{ maxMb }} MB.</p>
                <input ref="input" type="file" multiple :accept="accept" class="hidden" @change="onPick">
            </div>

            <ul v-if="form.files.length" class="divide-y divide-gading-100 rounded-lg border border-gading-200 text-sm">
                <li v-for="(f, i) in form.files" :key="i" class="flex items-center justify-between gap-2 px-3 py-2">
                    <span class="min-w-0 truncate">{{ f.name }}</span>
                    <span class="flex shrink-0 items-center gap-2 text-xs text-slate-500">
                        {{ ukuran(f.size) }}
                        <button type="button" class="text-red-600 hover:underline" @click="removePicked(i)">batal</button>
                    </span>
                </li>
            </ul>

            <div v-if="form.files.length === 1">
                <label class="label">Judul (opsional)</label>
                <input v-model="form.title" type="text" class="masukan w-full" :placeholder="form.files[0].name.replace(/\.[^.]+$/, '')">
            </div>
            <div>
                <label class="label">Kategori</label>
                <select v-model="form.category" class="masukan w-full">
                    <option value="">Tanpa kategori</option>
                    <option v-for="c in rules.categories" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
            <div>
                <label class="label">Terkait aktivitas / tonggak (opsional)</label>
                <select v-model="form.task" class="masukan w-full">
                    <option value="">Kegiatan secara umum</option>
                    <option v-for="t in taskOptions" :key="t.ulid" :value="t.ulid">{{ t.wbs }} {{ t.title }}</option>
                </select>
            </div>

            <div v-if="form.progress" class="h-2 overflow-hidden rounded-full bg-gading-100">
                <div class="h-full bg-zamrud-500 transition-all" :style="{ width: `${form.progress.percentage}%` }" />
            </div>
            <p v-if="localError" class="text-sm text-red-600">{{ localError }}</p>
            <p v-for="(e, i) in uploadErrors" :key="i" class="text-sm text-red-600">{{ e }}</p>

            <button class="tombol w-full justify-center" :disabled="!form.files.length || form.processing">
                {{ form.processing ? 'Mengunggah…' : `Unggah ${form.files.length || ''} berkas` }}
            </button>
            <p class="text-xs text-slate-500">Jenis: {{ rules.extensions.join(', ') }}.</p>
        </form>

        <!-- Daftar -->
        <div class="kartu overflow-hidden xl:order-1" :class="readOnly ? 'xl:col-span-3' : 'xl:col-span-2'">
            <div class="flex flex-wrap items-center gap-3 border-b border-gading-200 p-4">
                <input v-model="q" type="search" class="masukan min-w-0 flex-1" placeholder="Cari judul, nama berkas, aktivitas…">
                <select v-if="usedCategories.length" v-model="cat" class="masukan">
                    <option value="">Semua kategori</option>
                    <option v-for="c in usedCategories" :key="c" :value="c">{{ c }}</option>
                </select>
                <span class="text-xs text-slate-500">{{ documents.length }} berkas · {{ ukuran(totalSize) }}</span>
            </div>

            <ul class="divide-y divide-gading-100">
                <li v-for="d in shown" :key="d.ulid" class="p-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 w-12 shrink-0 rounded-md py-1 text-center text-[11px] font-bold uppercase" :class="warna(d.extension)">{{ d.extension }}</span>
                        <div class="min-w-0 flex-1">
                            <template v-if="editing !== d.ulid">
                                <p class="font-semibold text-zamrud-950">{{ d.title }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ d.original_name }} · {{ ukuran(d.size) }} · {{ d.uploader ?? '–' }}, {{ waktu(d.uploaded_at) }}</p>
                                <p class="mt-1 flex flex-wrap gap-1.5 text-xs">
                                    <span v-if="d.category" class="rounded bg-emas-50 px-1.5 py-0.5 font-medium text-emas-800">{{ d.category }}</span>
                                    <span v-if="d.task" class="rounded bg-zamrud-50 px-1.5 py-0.5 text-zamrud-800">{{ d.task.label }}</span>
                                </p>
                            </template>
                            <form v-else class="grid gap-2 sm:grid-cols-3" @submit.prevent="saveEdit(d)">
                                <input v-model="edit.title" type="text" class="masukan sm:col-span-3" required>
                                <select v-model="edit.category" class="masukan">
                                    <option value="">Tanpa kategori</option>
                                    <option v-for="c in rules.categories" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <select v-model="edit.task" class="masukan">
                                    <option value="">Kegiatan secara umum</option>
                                    <option v-for="t in taskOptions" :key="t.ulid" :value="t.ulid">{{ t.wbs }} {{ t.title }}</option>
                                </select>
                                <div class="flex gap-2">
                                    <button class="tombol" :disabled="edit.processing">Simpan</button>
                                    <button type="button" class="tombol-sekunder" @click="editing = null">Batal</button>
                                </div>
                            </form>
                        </div>
                        <div v-if="editing !== d.ulid" class="flex shrink-0 flex-wrap justify-end gap-x-3 gap-y-1 text-xs font-semibold">
                            <a v-if="d.previewable" :href="route('documents.show', { document: d.ulid, lihat: 1 })" target="_blank" rel="noopener" class="text-zamrud-700 hover:underline">Lihat</a>
                            <a :href="route('documents.show', d.ulid)" class="text-zamrud-700 hover:underline">Unduh</a>
                            <button v-if="d.can_manage" type="button" class="text-slate-600 hover:underline" @click="startEdit(d)">Ubah</button>
                            <button v-if="d.can_manage" type="button" class="text-red-600 hover:underline" @click="remove(d)">Hapus</button>
                        </div>
                    </div>
                </li>
                <li v-if="!shown.length" class="px-4 py-12 text-center text-sm text-slate-500">
                    {{ documents.length ? 'Tidak ada dokumen yang cocok.' : 'Belum ada dokumen. Unggah TOR, kontrak, BAST, laporan, atau foto kegiatan.' }}
                </li>
            </ul>
        </div>
    </div>
</template>
