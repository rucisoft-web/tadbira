<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ title: { type: String, required: true }, intro: { type: String, default: '' } });

const sections = [
    { label: 'Organisasi', route: 'settings.organization' },
    { label: 'Pengguna', route: 'settings.members' },
    { label: 'Peran & hak akses', route: 'settings.roles' },
    { label: 'Unit kerja', route: 'settings.units' },
    { label: 'Tahun anggaran', route: 'settings.fiscal-years' },
    { label: 'Hari libur', route: 'settings.holidays' },
];
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-500">Pengaturan</span>
                <span class="text-slate-300">/</span>
                <span class="truncate font-semibold text-zamrud-950">{{ title }}</span>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-[13rem_1fr]">
            <nav class="flex gap-2 overflow-x-auto lg:flex-col lg:gap-1">
                <Link
                    v-for="s in sections"
                    :key="s.route"
                    :href="route(s.route)"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium"
                    :class="route().current(s.route) ? 'bg-zamrud-800 text-white' : 'text-slate-600 hover:bg-gading-100 hover:text-zamrud-900'"
                >{{ s.label }}</Link>
            </nav>
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-zamrud-950">{{ title }}</h1>
                <p v-if="intro" class="mt-1 max-w-3xl text-sm text-slate-600">{{ intro }}</p>
                <div class="mt-6"><slot /></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
