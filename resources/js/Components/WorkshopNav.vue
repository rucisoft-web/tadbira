<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    sessions: { type: Array, default: () => [] },
    current: { type: String, default: '' },
});

const days = computed(() => [1, 2].map((day) => ({ day, items: props.sessions.filter((s) => s.day === day) })));
</script>

<template>
    <nav aria-label="Daftar sesi" class="space-y-5 text-sm">
        <Link :href="route('workshop.materials')" class="block font-semibold text-zamrud-800 hover:text-zamrud-950">← Semua materi</Link>
        <div v-for="d in days" :key="d.day">
            <p class="text-xs font-bold uppercase tracking-wider text-emas-600">Hari {{ d.day }}</p>
            <ul class="mt-2 space-y-1">
                <li v-for="s in d.items" :key="s.slug">
                    <Link
                        :href="route('workshop.material', s.slug)"
                        class="block rounded-lg px-3 py-2"
                        :class="s.slug === current ? 'bg-zamrud-900 text-white' : 'text-slate-600 hover:bg-zamrud-50 hover:text-zamrud-900'"
                        :aria-current="s.slug === current ? 'page' : undefined"
                    >
                        <span class="block text-xs tabular-nums" :class="s.slug === current ? 'text-emas-300' : 'text-slate-400'">{{ s.time }}</span>
                        <span class="font-medium">{{ s.title }}</span>
                    </Link>
                </li>
            </ul>
        </div>
    </nav>
</template>
