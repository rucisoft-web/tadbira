<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const open = ref(false);
const terms = computed(() => page.props.terms ?? {});
const org = computed(() => page.props.organization);
const flash = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error);
const isGuest = computed(() => page.props.auth?.is_guest);
const access = computed(() => page.props.auth?.access);
const can = computed(() => access.value?.can ?? {});

const nav = computed(() => [
    { label: 'Dasbor', route: 'dashboard', active: 'dashboard', icon: 'M3 13h8V3H3zm0 8h8v-6H3zm10 0h8V11h-8zm0-18v6h8V3z' },
    { label: terms.value.project ?? 'Proyek', route: 'projects.index', active: 'projects.*', icon: 'M4 6h10M8 12h12M6 18h8' },
    can.value.import_budget && { label: terms.value.budget_import ?? 'Impor anggaran', route: 'budget.import', active: 'budget.*', icon: 'M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2' },
    can.value.settings && { label: 'Pengaturan', route: 'settings.organization', active: 'settings.*', icon: 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z' },
].filter(Boolean));

const switchOrg = (ulid) => router.post(route('organizations.switch', ulid));
</script>

<template>
    <div class="min-h-screen bg-gading-50 bg-pola-terang">
        <!-- Bilah samping -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-zamrud-900 bg-pola text-zamrud-50 transition-transform lg:translate-x-0"
            :class="open ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-20 items-center px-6">
                <Link :href="route('dashboard')" class="h-10">
                    <ApplicationLogo with-text light class="h-10" />
                </Link>
            </div>

            <div v-if="org" class="mx-4 mb-4 rounded-xl border border-white/10 bg-white/5 p-3">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-emas-300">{{ org.sector_label }}</p>
                <p class="mt-0.5 text-sm font-semibold leading-snug text-white">{{ org.name }}</p>
                <p v-if="access && !isGuest" class="mt-1 text-xs text-zamrud-100/80">
                    {{ access.role_label }}<span v-if="access.unit"> · {{ access.unit }}</span>
                </p>
                <div v-if="($page.props.organizations ?? []).length > 1" class="mt-2">
                    <select
                        class="w-full rounded-lg border-white/10 bg-zamrud-950/40 py-1 text-xs text-zamrud-50 focus:border-emas-400 focus:ring-emas-400"
                        :value="org.ulid"
                        @change="switchOrg($event.target.value)"
                    >
                        <option v-for="o in $page.props.organizations" :key="o.ulid" :value="o.ulid">{{ o.name }}</option>
                    </select>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                    :class="route().current(item.active) ? 'bg-white/10 text-white shadow-inner' : 'text-zamrud-100/80 hover:bg-white/5 hover:text-white'"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path :d="item.icon" />
                    </svg>
                    {{ item.label }}
                    <span v-if="route().current(item.active)" class="ml-auto h-1.5 w-1.5 rounded-full bg-emas-400" />
                </Link>
            </nav>

            <!-- Garis warna seperti logo -->
            <div class="mx-6 mb-4 space-y-1.5 opacity-70">
                <span class="block h-1 w-1/2 rounded-full bg-emas-500" />
                <span class="ml-6 block h-1 w-2/3 rounded-full bg-zamrud-400" />
                <span class="ml-2 block h-1 w-1/2 rounded-full bg-sky-400" />
                <span class="ml-12 block h-1 w-1/3 rounded-full bg-orange-500" />
            </div>
            <p class="px-6 pb-5 font-arab text-lg text-emas-200/80" dir="rtl" lang="ar">يُدَبِّرُ الْأَمْرَ</p>
        </aside>

        <div v-if="open" class="fixed inset-0 z-30 bg-zamrud-950/40 lg:hidden" @click="open = false" />

        <div class="lg:pl-64">
            <!-- Bilah atas -->
            <header class="sticky top-0 z-20 border-b border-gading-200 bg-gading-50/90 backdrop-blur">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" class="rounded-lg p-2 text-zamrud-900 hover:bg-gading-100 lg:hidden" @click="open = !open">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <slot name="header" />
                    </div>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center gap-2 rounded-full border border-gading-200 bg-white py-1 pl-1 pr-3 text-sm font-medium text-zamrud-900 hover:bg-gading-100">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zamrud-800 text-xs font-bold text-white">
                                    {{ $page.props.auth.user.name.split(' ').map((s) => s[0]).slice(0, 2).join('') }}
                                </span>
                                <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                            <DropdownLink v-if="can.settings" :href="route('settings.organization')">Pengaturan</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <div v-if="isGuest" class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900 sm:px-6 lg:px-8">
                <span><strong>Mode tamu.</strong> Anda melihat data demo dan tidak bisa mengubah apa pun.</span>
                <Link :href="route('logout')" method="post" as="button" class="font-medium underline hover:text-amber-700">Keluar</Link>
            </div>

            <div v-if="flashError" class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6 lg:mx-8">
                {{ flashError }}
            </div>

            <div v-if="flash" class="mx-4 mt-4 rounded-xl border border-zamrud-200 bg-zamrud-50 px-4 py-3 text-sm text-zamrud-800 sm:mx-6 lg:mx-8">
                {{ flash }}
            </div>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
