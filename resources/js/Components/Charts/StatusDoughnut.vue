<script setup>
import './setup';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

const props = defineProps({
    items: { type: Array, required: true }, // [{label, count, color}]
    centerLabel: String,
    centerValue: [String, Number],
});

const data = computed(() => ({
    labels: props.items.map((i) => i.label),
    datasets: [{
        data: props.items.map((i) => i.count),
        backgroundColor: props.items.map((i) => i.color),
        borderColor: '#FFFFFF',
        borderWidth: 3,
        hoverOffset: 6,
    }],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { position: 'bottom' } },
};
</script>

<template>
    <div class="relative h-60">
        <Doughnut :data="data" :options="options" />
        <div class="pointer-events-none absolute inset-x-0 top-[38%] text-center">
            <p class="text-2xl font-bold text-zamrud-950">{{ centerValue }}</p>
            <p class="text-xs text-slate-500">{{ centerLabel }}</p>
        </div>
    </div>
</template>
