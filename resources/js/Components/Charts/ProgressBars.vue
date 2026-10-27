<script setup>
import './setup';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { paletGaris } from '@/lib/format';

const props = defineProps({ projects: { type: Array, required: true } });

const data = computed(() => ({
    labels: props.projects.map((p) => (p.name.length > 34 ? `${p.name.slice(0, 32)}…` : p.name)),
    datasets: [
        {
            label: 'Kemajuan aktual',
            data: props.projects.map((p) => p.progress),
            backgroundColor: props.projects.map((p, i) => p.color || paletGaris[i % paletGaris.length]),
            borderRadius: 999,
            barThickness: 12,
        },
        {
            label: 'Rencana per hari ini',
            data: props.projects.map((p) => p.planned),
            backgroundColor: 'rgba(6, 78, 59, 0.12)',
            borderRadius: 999,
            barThickness: 12,
        },
    ],
}));

const options = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: { min: 0, max: 100, ticks: { callback: (v) => `${v}%` }, grid: { color: '#F5EFE0' } },
        y: { grid: { display: false } },
    },
    plugins: {
        legend: { position: 'bottom' },
        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${c.parsed.x}%` } },
    },
};
</script>

<template>
    <div :style="{ height: `${Math.max(220, projects.length * 46 + 70)}px` }">
        <Bar :data="data" :options="options" />
    </div>
</template>
