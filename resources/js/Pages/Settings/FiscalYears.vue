<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { rupiah, tanggal } from '@/lib/format';

defineProps({ fiscalYears: Array, statuses: Object });

const create = useForm({ year: new Date().getFullYear() + 1, ceiling_amount: '', kembali: true });
const add = () => create.post(route('fiscal-years.store'), { preserveScroll: true });

const editing = ref(null);
const edit = useForm({ code: '', status: '', start_date: '', end_date: '', ceiling_amount: '', notes: '' });
const startEdit = (fy) => {
    editing.value = fy.ulid;
    Object.assign(edit, { code: fy.code, status: fy.status, start_date: fy.start_date, end_date: fy.end_date, ceiling_amount: fy.ceiling_amount ?? '', notes: fy.notes ?? '' });
    edit.clearErrors();
};
const save = (fy) => edit.patch(route('settings.fiscal-years.update', fy.ulid), { preserveScroll: true, onSuccess: () => (editing.value = null) });
const remove = (fy) => {
    if (confirm(`Hapus ${fy.code}?`)) router.delete(route('settings.fiscal-years.destroy', fy.ulid), { preserveScroll: true });
};
const badge = { planning: 'bg-sky-100 text-sky-800', active: 'bg-zamrud-100 text-zamrud-800', closed: 'bg-slate-200 text-slate-600' };
</script>

<template>
    <Head title="Tahun anggaran" />
    <SettingsLayout title="Tahun anggaran" intro="Atur pagu, rentang tanggal, dan status setiap tahun anggaran. Tahun yang ditutup tidak bisa lagi menerima pencatatan realisasi.">
        <form class="kartu flex flex-wrap items-end gap-3 p-5" @submit.prevent="add">
            <div>
                <label class="label" for="fy-year">Tahun baru</label>
                <input id="fy-year" v-model="create.year" type="number" min="2000" max="2100" class="masukan w-28" required>
            </div>
            <div>
                <label class="label" for="fy-ceiling">Pagu (opsional)</label>
                <input id="fy-ceiling" v-model="create.ceiling_amount" type="number" min="0" class="masukan w-48">
            </div>
            <button class="tombol" :disabled="create.processing">Tambah tahun anggaran</button>
            <p v-if="create.errors.year" class="w-full text-sm text-orange-600">{{ create.errors.year }}</p>
        </form>

        <div class="mt-4 space-y-3">
            <div v-for="fy in fiscalYears" :key="fy.ulid" class="kartu p-5">
                <form v-if="editing === fy.ulid" class="grid gap-3 sm:grid-cols-3" @submit.prevent="save(fy)">
                    <div><label class="label">Kode</label><input v-model="edit.code" type="text" class="masukan w-full" required></div>
                    <div>
                        <label class="label">Status</label>
                        <select v-model="edit.status" class="masukan w-full">
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div><label class="label">Pagu (Rp)</label><input v-model="edit.ceiling_amount" type="number" min="0" class="masukan w-full"></div>
                    <div><label class="label">Mulai</label><input v-model="edit.start_date" type="date" class="masukan w-full" required></div>
                    <div>
                        <label class="label">Selesai</label><input v-model="edit.end_date" type="date" class="masukan w-full" required>
                        <p v-if="edit.errors.end_date" class="mt-1 text-xs text-orange-600">{{ edit.errors.end_date }}</p>
                    </div>
                    <div><label class="label">Catatan</label><input v-model="edit.notes" type="text" class="masukan w-full"></div>
                    <div class="flex gap-3 sm:col-span-3">
                        <button class="tombol" :disabled="edit.processing">Simpan</button>
                        <button type="button" class="tombol-sekunder" @click="editing = null">Batal</button>
                    </div>
                </form>
                <div v-else class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <div class="min-w-[8rem]">
                        <p class="text-lg font-bold text-zamrud-950">{{ fy.code }}</p>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="badge[fy.status]">{{ statuses[fy.status] ?? fy.status }}</span>
                    </div>
                    <div class="text-sm"><p class="text-slate-500">Rentang</p><p class="font-medium">{{ tanggal(fy.start_date) }} – {{ tanggal(fy.end_date) }}</p></div>
                    <div class="text-sm"><p class="text-slate-500">Pagu</p><p class="font-medium">{{ fy.ceiling_amount !== null ? rupiah(fy.ceiling_amount) : '–' }}</p></div>
                    <div class="text-sm"><p class="text-slate-500">Kegiatan</p><p class="font-medium">{{ fy.projects }}</p></div>
                    <div class="ml-auto text-xs font-semibold">
                        <button type="button" class="text-zamrud-700 hover:underline" @click="startEdit(fy)">Ubah</button>
                        <button v-if="!fy.projects" type="button" class="ml-3 text-orange-600 hover:underline" @click="remove(fy)">Hapus</button>
                    </div>
                    <p v-if="fy.notes" class="w-full text-sm text-slate-600">{{ fy.notes }}</p>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
