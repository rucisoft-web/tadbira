<script setup>
import { rupiahRingkas } from '@/lib/format';

defineProps({
    demos: { type: Array, default: () => [] },
    compact: { type: Boolean, default: false },
});

const colors = ['#10B981', '#D4A72C', '#38BDF8', '#F97316', '#A855F7'];
</script>

<template>
    <div :class="compact ? 'space-y-2' : 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3'">
        <a
            v-for="(demo, i) in demos"
            :key="demo.ulid"
            :href="route('login.guest', { organisasi: demo.ulid })"
            class="group block rounded-xl border border-gading-200 bg-white transition hover:border-zamrud-400 hover:shadow-md"
            :class="compact ? 'px-4 py-3' : 'p-5'"
        >
            <div class="flex items-start gap-3">
                <span class="mt-1 h-8 w-1.5 shrink-0 rounded-full" :style="{ background: colors[i % colors.length] }" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-zamrud-950" :title="demo.name">{{ demo.short_name }}</p>
                    <p class="text-xs text-slate-500">
                        {{ demo.sector }}<template v-if="demo.year"> · TA {{ demo.year }}</template>
                    </p>
                    <p v-if="!compact" class="mt-1 text-sm text-slate-600">{{ demo.name }}</p>
                    <p class="mt-1 text-xs text-slate-600">
                        {{ demo.projects }} {{ demo.project_term.toLowerCase() }} · pagu {{ rupiahRingkas(demo.budget) }}
                    </p>
                </div>
            </div>
            <p class="mt-2 text-sm font-semibold text-zamrud-700 group-hover:text-zamrud-900" :class="compact ? 'text-right' : ''">Masuk sebagai Tamu →</p>
        </a>
    </div>
</template>
