<script setup>
defineProps({
    value: { type: Number, default: 0 },
    planned: { type: Number, default: null },
    color: { type: String, default: '#10B981' },
});
</script>

<template>
    <div class="relative h-2 w-full rounded-full bg-gading-100" :title="planned !== null ? `Aktual ${value}% · Rencana ${planned}%` : `${value}%`">
        <div class="h-2 rounded-full transition-all" :style="{ width: `${Math.min(100, value)}%`, background: color }" />
        <div v-if="planned !== null" class="absolute -top-0.5 h-3 w-0.5 rounded bg-zamrud-950" :style="{ left: `calc(${Math.min(100, planned)}% - 1px)` }" />
    </div>
</template>
