<script setup>
defineProps({
    label: String,
    value: [String, Number],
    hint: String,
    accent: { type: String, default: '#10B981' },
});
</script>

<template>
    <div class="kartu relative overflow-hidden p-5">
        <span class="absolute inset-y-0 left-0 w-1.5" :style="{ background: accent }" />
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ label }}</p>
        <p class="mt-2 text-2xl font-bold text-zamrud-950">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <slot />
    </div>
</template>
