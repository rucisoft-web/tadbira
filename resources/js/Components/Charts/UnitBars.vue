<script setup>
import './setup';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { rupiahRingkas } from '@/lib/format';

const props = defineProps({ units: { type: Array, required: true } });
const emit = defineEmits(['select']);

const data = computed(() => ({
    labels: props.units.map((u) => u.unit),
    datasets: [
        {
            label: 'Realisasi',
            data: props.units.map((u) => u.realized),
            backgroundColor: '#10B981',
            borderRadius: 999,
            barThickness: 10,
            grouped: false,
            order: 1,
        },
        {
            label: 'Anggaran',
            data: props.units.map((u) => u.budget),
            backgroundColor: 'rgba(6, 78, 59, 0.12)',
            borderRadius: 999,
            barThickness: 10,
            grouped: false,
            order: 2,
        },
    ],
}));

const options = computed(() => ({
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    onClick: (_, el) => el.length && emit('select', props.units[el[0].index].unit),
    onHover: (e, el) => { e.native.target.style.cursor = el.length ? 'pointer' : 'default'; },
    scales: {
        x: { ticks: { callback: (v) => rupiahRingkas(v) }, grid: { color: '#F5EFE0' } },
        y: { grid: { display: false } },
    },
    plugins: {
        legend: { position: 'bottom' },
        tooltip: {
            callbacks: {
                label: (c) => `${c.dataset.label}: ${rupiahRingkas(c.parsed.x)}`,
                afterBody: (items) => {
                    const u = props.units[items[0].dataIndex];
                    return [`Serapan ${u.absorption.toLocaleString('id-ID')}%`, `${u.count} kegiatan · ${u.kritis} terlambat`];
                },
            },
        },
    },
}));
</script>

<template>
    <div :style="{ height: `${Math.max(220, units.length * 30 + 70)}px` }">
        <Bar :data="data" :options="options" />
    </div>
</template>
