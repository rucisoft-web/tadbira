<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({ profile: Object, sectors: Object, timezones: Object, stats: Object });
const form = useForm({ ...props.profile });
const save = () => form.patch(route('settings.organization.update'), { preserveScroll: true });
</script>

<template>
    <Head title="Pengaturan organisasi" />
    <SettingsLayout title="Organisasi" intro="Identitas organisasi tampil di bilah samping, laporan, dan halaman masuk. Sektor menentukan istilah yang dipakai (mis. Pagu DIPA dan Kegiatan, atau Anggaran RKAP dan Program Kerja).">
        <div class="grid gap-6 xl:grid-cols-[1fr_16rem]">
            <form class="kartu space-y-4 p-6" @submit.prevent="save">
                <div>
                    <label class="label" for="name">Nama organisasi</label>
                    <input id="name" v-model="form.name" type="text" class="masukan w-full" required>
                    <p v-if="form.errors.name" class="mt-1 text-sm text-orange-600">{{ form.errors.name }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="short">Nama singkat</label>
                        <input id="short" v-model="form.short_name" type="text" class="masukan w-full" placeholder="mis. UIN Suka">
                    </div>
                    <div>
                        <label class="label" for="code">Kode satker / entitas</label>
                        <input id="code" v-model="form.code" type="text" class="masukan w-full" placeholder="mis. 423755">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="sector">Sektor</label>
                        <select id="sector" v-model="form.sector" class="masukan w-full">
                            <option v-for="(label, key) in sectors" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="tz">Zona waktu</label>
                        <select id="tz" v-model="form.timezone" class="masukan w-full">
                            <option v-for="(label, key) in timezones" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                </div>
                <button class="tombol" :disabled="form.processing">Simpan</button>
            </form>
            <dl class="kartu grid grid-cols-2 gap-4 p-5 text-sm xl:grid-cols-1">
                <div><dt class="text-slate-500">Pengguna aktif</dt><dd class="text-xl font-bold text-zamrud-950">{{ stats.members }}</dd></div>
                <div><dt class="text-slate-500">Unit kerja</dt><dd class="text-xl font-bold text-zamrud-950">{{ stats.units }}</dd></div>
                <div><dt class="text-slate-500">Tahun anggaran</dt><dd class="text-xl font-bold text-zamrud-950">{{ stats.fiscal_years }}</dd></div>
                <div><dt class="text-slate-500">Kegiatan</dt><dd class="text-xl font-bold text-zamrud-950">{{ stats.projects }}</dd></div>
            </dl>
        </div>
    </SettingsLayout>
</template>
