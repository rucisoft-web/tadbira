<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({ roles: Array, abilities: Object });
</script>

<template>
    <Head title="Peran dan hak akses" />
    <SettingsLayout title="Peran & hak akses" intro="Setiap pengguna punya satu peran di organisasi ini. Operator unit hanya bisa mengubah kegiatan di unit kerjanya atau kegiatan yang ia pimpin.">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div v-for="r in roles" :key="r.key" class="kartu p-5">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="font-semibold text-zamrud-950">{{ r.label }}</h2>
                    <span class="text-xs text-slate-500">{{ r.count }} pengguna</span>
                </div>
                <p class="mt-1 text-sm text-slate-600">{{ r.description }}</p>
            </div>
        </div>

        <div class="kartu mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gading-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Hak akses</th>
                        <th v-for="r in roles" :key="r.key" class="px-3 py-3 text-center">{{ r.label }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gading-100">
                    <tr v-for="(label, key) in abilities" :key="key">
                        <td class="px-4 py-2.5 text-slate-700">{{ label }}</td>
                        <td v-for="r in roles" :key="r.key" class="px-3 py-2.5 text-center">
                            <span v-if="r.can[key] === true" class="font-bold text-zamrud-600" title="Semua kegiatan">✓</span>
                            <span v-else-if="r.can[key] === 'unit'" class="rounded-full bg-emas-100 px-2 py-0.5 text-xs font-semibold text-emas-800" title="Hanya kegiatan unitnya">unit</span>
                            <span v-else class="text-slate-300">–</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-sm text-slate-600">
            Ubah peran seseorang di halaman <Link :href="route('settings.members')" class="font-semibold text-zamrud-700 underline">Pengguna</Link>.
            Akun tamu (mode demo) selalu hanya bisa melihat.
        </p>
    </SettingsLayout>
</template>
