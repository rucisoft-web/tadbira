<script setup>
import './setup';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { rupiah, rupiahRingkas } from '@/lib/format';

const props = defineProps({
    monthly: { type: Array, required: true },
    ceiling: { type: Number, default: 0 },
    ceilingLabel: { type: String, default: 'Pagu' },
});

const data = computed(() => ({
    labels: props.monthly.map((m) => m.month),
    datasets: [
        {
            label: 'Rencana penyerapan',
            data: props.monthly.map((m) => m.plan),
            borderColor: '#D4A72C',
            backgroundColor: 'rgba(212, 167, 44, 0.08)',
            borderDash: [6, 4],
            tension: 0.35,
            fill: true,
            pointRadius: 0,
        },
        {
            label: 'Realisasi',
            data: props.monthly.map((m) => m.actual),
            borderColor: '#0E7C5A',
            backgroundColor: 'rgba(16, 185, 129, 0.18)',
            tension: 0.35,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: '#0E7C5A',
        },
        ...(props.ceiling > 0 ? [{
            label: props.ceilingLabel,
            data: props.monthly.map(() => props.ceiling),
            borderColor: '#F97316',
            borderWidth: 1.5,
            pointRadius: 0,
            fill: false,
        }] : []),
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    scales: {
        y: { beginAtZero: true, ticks: { callback: (v) => rupiahRingkas(v) }, grid: { color: '#F5EFE0' } },
        x: { grid: { display: false } },
    },
    plugins: {
        legend: { position: 'bottom' },
        tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${rupiah(c.parsed.y)}` } },
    },
};
</script>

<template>
    <div class="h-72">
        <Line :data="data" :options="options" />
    </div>
</template>
