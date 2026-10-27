<script setup>
defineProps({ withText: { type: Boolean, default: false }, light: { type: Boolean, default: false } });
</script>

<template>
    <span class="inline-flex items-center gap-3">
        <svg viewBox="0 0 64 64" class="h-full w-auto shrink-0" role="img" aria-label="Tadbira">
            <rect width="64" height="64" rx="14" fill="#064E3B" />
            <g stroke-linecap="round" stroke-width="7">
                <line x1="13" y1="17" x2="33" y2="17" stroke="#D4A72C" />
                <line x1="23" y1="28" x2="47" y2="28" stroke="#34D399" />
                <line x1="17" y1="39" x2="39" y2="39" stroke="#38BDF8" />
                <line x1="31" y1="50" x2="51" y2="50" stroke="#F97316" />
            </g>
            <g fill="#FDE68A" transform="translate(50 15)">
                <rect x="-4" y="-4" width="8" height="8" />
                <rect x="-4" y="-4" width="8" height="8" transform="rotate(45)" />
            </g>
        </svg>
        <span v-if="withText" class="leading-none">
            <span class="block text-2xl font-bold tracking-tight" :class="light ? 'text-white' : 'text-zamrud-900'">tadbira</span>
            <span class="mt-1 block text-[0.6rem] font-semibold uppercase tracking-[0.18em]" :class="light ? 'text-emas-300' : 'text-zamrud-600'">Kelola proyek dengan amanah</span>
        </span>
    </span>
</template>
