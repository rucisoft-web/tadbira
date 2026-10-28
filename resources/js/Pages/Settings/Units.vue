<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({ units: Array, unlisted: Array });

const form = useForm({ name: '', full_name: '' });
const add = () => form.post(route('settings.units.store'), { preserveScroll: true, onSuccess: () => form.reset() });

const editing = ref(null);
const edit = useForm({ name: '', full_name: '' });
const startEdit = (u) => { editing.value = u.ulid; edit.name = u.name; edit.full_name = u.full_name ?? ''; edit.clearErrors(); };
const save = (u) => edit.patch(route('settings.units.update', u.ulid), { preserveScroll: true, onSuccess: () => (editing.value = null) });
const remove = (u) => {
    if (confirm(`Hapus unit ${u.name}?`)) router.delete(route('settings.units.destroy', u.ulid), { preserveScroll: true });
};
const adopt = (name) => { form.name = name; form.full_name = ''; };
</script>

<template>
    <Head title="Unit kerja" />
    <SettingsLayout title="Unit kerja" intro="Fakultas, biro, direktorat, atau wilayah kerja. Nama singkat unit dipakai sebagai kategori kegiatan dan filter di dasbor; operator unit hanya mengubah kegiatan di unitnya.">
        <form class="kartu grid gap-3 p-5 sm:grid-cols-[12rem_1fr_auto] sm:items-end" @submit.prevent="add">
            <div>
                <label class="label" for="u-name">Nama singkat</label>
                <input id="u-name" v-model="form.name" type="text" class="masukan w-full" placeholder="mis. FADIB" required>
            </div>
            <div>
                <label class="label" for="u-full">Nama lengkap</label>
                <input id="u-full" v-model="form.full_name" type="text" class="masukan w-full" placeholder="mis. Fakultas Adab dan Ilmu Budaya">
            </div>
            <button class="tombol justify-center" :disabled="form.processing">Tambah unit</button>
            <p v-if="form.errors.name" class="text-sm text-orange-600 sm:col-span-3">{{ form.errors.name }}</p>
        </form>

        <div v-if="unlisted.length" class="mt-4 rounded-xl border border-emas-200 bg-emas-50 p-4 text-sm text-emas-900">
            Ada kategori kegiatan yang belum terdaftar sebagai unit:
            <button v-for="n in unlisted" :key="n" type="button" class="ml-2 rounded-full bg-white px-2 py-0.5 font-semibold text-zamrud-800 hover:bg-zamrud-50" @click="adopt(n)">+ {{ n }}</button>
        </div>

        <div class="kartu mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nama singkat</th>
                        <th class="px-3 py-3">Nama lengkap</th>
                        <th class="px-3 py-3 text-right">Kegiatan</th>
                        <th class="px-3 py-3 text-right">Pengguna</th>
                        <th class="px-3 py-3" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <tr v-for="u in units" :key="u.ulid">
                        <template v-if="editing === u.ulid">
                            <td class="px-4 py-2"><input v-model="edit.name" type="text" class="masukan w-full py-1"></td>
                            <td class="px-3 py-2">
                                <input v-model="edit.full_name" type="text" class="masukan w-full py-1">
                                <p v-if="edit.errors.name" class="mt-1 text-xs text-orange-600">{{ edit.errors.name }}</p>
                                <p v-else-if="edit.name !== u.name && u.projects" class="mt-1 text-xs text-emas-700">{{ u.projects }} kegiatan ikut berganti kategori.</p>
                            </td>
                            <td colspan="2" />
                            <td class="whitespace-nowrap px-3 py-2 text-right text-xs font-semibold">
                                <button type="button" class="text-zamrud-700 hover:underline" @click="save(u)">Simpan</button>
                                <button type="button" class="ml-3 text-slate-500 hover:underline" @click="editing = null">Batal</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="px-4 py-2.5 font-semibold text-zamrud-950">{{ u.name }}</td>
                            <td class="px-3 py-2.5 text-slate-600">{{ u.full_name || '–' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ u.projects }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ u.members }}</td>
                            <td class="whitespace-nowrap px-3 py-2.5 text-right text-xs font-semibold">
                                <button type="button" class="text-zamrud-700 hover:underline" @click="startEdit(u)">Ubah</button>
                                <button type="button" class="ml-3 text-orange-600 hover:underline" @click="remove(u)">Hapus</button>
                            </td>
                        </template>
                    </tr>
                    <tr v-if="!units.length"><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada unit kerja.</td></tr>
                </tbody>
            </table>
        </div>
    </SettingsLayout>
</template>
