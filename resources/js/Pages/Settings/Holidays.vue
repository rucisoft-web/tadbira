<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { tanggalPanjang } from '@/lib/format';

const props = defineProps({ year: Number, years: Array, holidays: Array });

const form = useForm({ date: '', name: '', is_working_day: false });
const add = () => form.post(route('settings.holidays.store'), { preserveScroll: true, onSuccess: () => form.reset() });
const remove = (h) => {
    if (confirm(`Hapus "${h.name}"?`)) router.delete(route('settings.holidays.destroy', h.id), { preserveScroll: true });
};
const pickYear = (y) => router.get(route('settings.holidays'), { tahun: y }, { preserveScroll: true });
</script>

<template>
    <Head title="Hari libur" />
    <SettingsLayout title="Hari libur" intro="Mesin penjadwalan melewati Sabtu, Minggu, dan hari libur saat menghitung lama pengerjaan. Tambahkan libur khusus organisasi (mis. dies natalis) atau hari kerja pengganti. Kegiatan berjalan yang melewati tanggal itu langsung dijadwalkan ulang.">
        <form class="kartu grid gap-3 p-5 sm:grid-cols-[11rem_1fr_auto_auto] sm:items-end" @submit.prevent="add">
            <div><label class="label" for="h-date">Tanggal</label><input id="h-date" v-model="form.date" type="date" class="masukan w-full" required></div>
            <div><label class="label" for="h-name">Keterangan</label><input id="h-name" v-model="form.name" type="text" class="masukan w-full" placeholder="mis. Dies Natalis" required></div>
            <label class="flex items-center gap-2 pb-2 text-sm text-slate-600">
                <input v-model="form.is_working_day" type="checkbox" class="rounded border-gading-200 text-zamrud-600">
                Hari kerja pengganti
            </label>
            <button class="tombol justify-center" :disabled="form.processing">Tambah</button>
            <p v-if="form.errors.date" class="text-sm text-orange-600 sm:col-span-4">{{ form.errors.date }}</p>
        </form>

        <div class="mt-6 flex flex-wrap gap-2">
            <button v-for="y in years" :key="y" type="button" class="rounded-full px-3 py-1 text-sm font-semibold" :class="y === year ? 'bg-zamrud-800 text-white' : 'bg-white text-slate-600 ring-1 ring-gading-200 hover:bg-gading-100'" @click="pickYear(y)">{{ y }}</button>
        </div>

        <div class="kartu mt-4 divide-y divide-gading-100">
            <div v-for="h in holidays" :key="h.id" class="flex items-center gap-4 px-5 py-3 text-sm">
                <span class="w-56 shrink-0 text-slate-600">{{ tanggalPanjang(h.date) }}</span>
                <span class="flex-1 font-medium text-zamrud-950">{{ h.name }}</span>
                <span v-if="h.is_working_day" class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-800">Hari kerja</span>
                <span v-if="h.national" class="text-xs text-slate-400">Nasional</span>
                <button v-else type="button" class="text-xs font-semibold text-orange-600 hover:underline" @click="remove(h)">Hapus</button>
            </div>
            <p v-if="!holidays.length" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada hari libur untuk tahun {{ year }}.</p>
        </div>
    </SettingsLayout>
</template>
