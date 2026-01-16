<script setup>
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js';
import { computed } from 'vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    title: {
        type: String,
        default: 'Bar Chart'
    },
    labels: {
        type: Array,
        required: true
    },
    data: {
        type: Array,
        required: true
    },
    backgroundColor: {
        type: String,
        default: 'rgba(59, 130, 246, 0.8)'
    },
    borderColor: {
        type: String,
        default: 'rgba(59, 130, 246, 1)'
    },
    label: {
        type: String,
        default: 'Data'
    }
});

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label,
            backgroundColor: props.backgroundColor,
            borderColor: props.borderColor,
            borderWidth: 1,
            data: props.data
        }
    ]
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top'
        },
        title: {
            display: true,
            text: props.title,
            font: {
                size: 16,
                weight: 'bold'
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                }
            }
        },
        x: {
            ticks: {
                maxRotation: 45,
                minRotation: 45
            }
        }
    }
}));
</script>

<template>
    <div class="chart-container">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

<style scoped>
.chart-container {
    position: relative;
    height: 400px;
    width: 100%;
}
</style>

