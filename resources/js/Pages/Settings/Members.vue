<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({ members: Array, roles: Array, units: Array, newPassword: Object });

const showAdd = ref(false);
const form = useForm({ name: '', email: '', role: 'operator', unit_id: null, password: '' });
const add = () => form.post(route('settings.members.store'), {
    preserveScroll: true,
    onSuccess: () => { form.reset(); showAdd.value = false; },
});

const search = ref('');
const roleFilter = ref('');
const shown = computed(() => {
    const q = search.value.trim().toLowerCase();
    return props.members.filter((m) => (!roleFilter.value || m.role === roleFilter.value)
        && (!q || `${m.name} ${m.email} ${m.unit ?? ''}`.toLowerCase().includes(q)));
});
const roleDescription = computed(() => props.roles.find((r) => r.key === form.role)?.description);

const patch = (m, data) => router.patch(route('settings.members.update', m.id), data, { preserveScroll: true });
const toggleActive = (m) => {
    if (m.is_active && !confirm(`Nonaktifkan ${m.name}? Ia tidak bisa masuk ke organisasi ini sampai diaktifkan kembali.`)) return;
    patch(m, { is_active: !m.is_active });
};
const resetPassword = (m) => {
    if (confirm(`Setel ulang kata sandi ${m.name}? Kata sandi lama langsung tidak berlaku.`)) {
        router.post(route('settings.members.password', m.id), {}, { preserveScroll: true });
    }
};
const remove = (m) => {
    if (confirm(`Keluarkan ${m.name} dari organisasi? Kegiatan dan riwayatnya tetap tersimpan.`)) {
        router.delete(route('settings.members.destroy', m.id), { preserveScroll: true });
    }
};
const copied = ref(false);
const copy = async () => {
    try {
        await navigator.clipboard.writeText(`Surel: ${props.newPassword.email}\nKata sandi: ${props.newPassword.password}`);
        copied.value = true;
    } catch {
        copied.value = false;
    }
};
const lastLogin = (v) => (v ? new Date(v).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'Belum pernah');
</script>

<template>
    <Head title="Pengguna" />
    <SettingsLayout title="Pengguna" intro="Tambahkan rekan kerja, atur peran dan unit kerjanya, nonaktifkan akun yang tidak lagi bertugas, atau setel ulang kata sandi yang terlupa.">
        <div v-if="newPassword" class="mb-6 rounded-2xl border border-emas-300 bg-emas-50 p-5">
            <p class="font-semibold text-zamrud-950">Kata sandi untuk {{ newPassword.email }}</p>
            <p class="mt-2 font-mono text-lg tracking-wide text-zamrud-900">{{ newPassword.password }}</p>
            <p class="mt-2 text-sm text-slate-600">Hanya ditampilkan sekali. Sampaikan langsung kepada yang bersangkutan dan minta ia menggantinya lewat menu Profil setelah masuk.</p>
            <button type="button" class="tombol-sekunder mt-3" @click="copy">{{ copied ? 'Tersalin' : 'Salin surel dan kata sandi' }}</button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <input v-model="search" type="search" class="masukan w-full sm:w-64" placeholder="Cari nama, surel, unit…">
            <select v-model="roleFilter" class="masukan">
                <option value="">Semua peran</option>
                <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.label }}</option>
            </select>
            <span class="text-sm text-slate-500">{{ shown.length }} dari {{ members.length }} pengguna</span>
            <button type="button" class="tombol ml-auto" @click="showAdd = !showAdd">+ Tambah pengguna</button>
        </div>

        <form v-if="showAdd" class="kartu mt-4 grid gap-4 p-5 md:grid-cols-2" @submit.prevent="add">
            <div>
                <label class="label" for="m-name">Nama lengkap</label>
                <input id="m-name" v-model="form.name" type="text" class="masukan w-full" required>
                <p v-if="form.errors.name" class="mt-1 text-sm text-orange-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="label" for="m-email">Surel</label>
                <input id="m-email" v-model="form.email" type="email" class="masukan w-full" required>
                <p v-if="form.errors.email" class="mt-1 text-sm text-orange-600">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="label" for="m-role">Peran</label>
                <select id="m-role" v-model="form.role" class="masukan w-full">
                    <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.label }}</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ roleDescription }}</p>
            </div>
            <div>
                <label class="label" for="m-unit">Unit kerja</label>
                <select id="m-unit" v-model="form.unit_id" class="masukan w-full">
                    <option :value="null">Tanpa unit</option>
                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}{{ u.full_name ? ` · ${u.full_name}` : '' }}</option>
                </select>
                <p v-if="form.role === 'operator' && !form.unit_id" class="mt-1 text-xs text-emas-700">Operator tanpa unit hanya bisa mengubah kegiatan yang ia pimpin.</p>
            </div>
            <div>
                <label class="label" for="m-pass">Kata sandi awal (opsional)</label>
                <input id="m-pass" v-model="form.password" type="text" class="masukan w-full" autocomplete="new-password" placeholder="Kosongkan agar dibuat otomatis">
                <p v-if="form.errors.password" class="mt-1 text-sm text-orange-600">{{ form.errors.password }}</p>
            </div>
            <div class="flex items-end gap-3">
                <button class="tombol" :disabled="form.processing">Tambahkan</button>
                <button type="button" class="tombol-sekunder" @click="showAdd = false">Batal</button>
            </div>
            <p class="text-xs text-slate-500 md:col-span-2">Bila surel sudah terdaftar di Tadbira (mis. di organisasi lain), akun itu langsung ditambahkan dengan kata sandinya sendiri.</p>
        </form>

        <div class="kartu mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-3 py-3">Peran</th>
                        <th class="px-3 py-3">Unit kerja</th>
                        <th class="px-3 py-3">Terakhir masuk</th>
                        <th class="px-3 py-3" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <tr v-for="m in shown" :key="m.id" :class="m.is_active ? '' : 'bg-slate-50 text-slate-400'">
                        <td class="px-4 py-3">
                            <p class="font-semibold" :class="m.is_active ? 'text-zamrud-950' : ''">
                                {{ m.name }}
                                <span v-if="m.is_self" class="ml-1 rounded bg-zamrud-50 px-1.5 text-[11px] font-semibold text-zamrud-700">Anda</span>
                                <span v-if="!m.is_active" class="ml-1 rounded bg-slate-200 px-1.5 text-[11px] font-semibold text-slate-600">Nonaktif</span>
                            </p>
                            <p class="text-xs text-slate-500">{{ m.email }}<span v-if="m.projects"> · memimpin {{ m.projects }} kegiatan</span></p>
                        </td>
                        <td class="px-3 py-3">
                            <select class="masukan w-36 py-1 text-sm" :value="m.role" :disabled="m.is_self" @change="patch(m, { role: $event.target.value })">
                                <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.label }}</option>
                                <option v-if="!roles.some((r) => r.key === m.role)" :value="m.role">{{ m.role_label }}</option>
                            </select>
                        </td>
                        <td class="px-3 py-3">
                            <select class="masukan w-36 py-1 text-sm" :value="m.unit_id ?? ''" @change="patch(m, { unit_id: $event.target.value ? Number($event.target.value) : null })">
                                <option value="">–</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-xs text-slate-500">{{ lastLogin(m.last_login_at) }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-right text-xs font-semibold">
                            <div v-if="!m.is_self" class="flex flex-col items-end gap-1">
                                <button type="button" class="text-zamrud-700 hover:underline" @click="resetPassword(m)">Setel ulang sandi</button>
                                <button type="button" class="text-slate-600 hover:underline" @click="toggleActive(m)">{{ m.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button type="button" class="text-orange-600 hover:underline" @click="remove(m)">Keluarkan</button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!shown.length"><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada pengguna yang cocok.</td></tr>
                </tbody>
            </table>
        </div>
    </SettingsLayout>
</template>
