<script setup>
import { computed } from 'vue';
import { warnaKesehatan } from '@/lib/format';

const props = defineProps({ health: String });
const w = computed(() => warnaKesehatan[props.health] ?? warnaKesehatan.baik);
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="[w.bg, w.text]">
        <span class="h-1.5 w-1.5 rounded-full" :class="w.dot" />
        {{ w.label }}
    </span>
</template>
