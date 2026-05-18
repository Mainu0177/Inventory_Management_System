<template>
    <div class="sales-chart-card">
        <div class="sales-chart-card__header">
            <div>
                <h2 class="sales-chart-card__title">Sales Performance</h2>
                <p class="sales-chart-card__subtitle">Revenue over the last 6 months</p>
            </div>
            <div class="sales-chart-card__badge">
                <i class="fa fa-chart-area"></i> Trend
            </div>
        </div>

        <div v-if="hasData" class="sales-chart-card__canvas-wrap">
            <canvas ref="chartCanvas"></canvas>
        </div>

        <div v-else class="sales-chart-card__empty">
            <div class="sales-chart-card__empty-icon">
                <i class="fa fa-chart-area"></i>
            </div>
            <p class="sales-chart-card__empty-title">No Sales Data Yet</p>
            <p class="sales-chart-card__empty-sub">Create invoices to see your revenue trend here.</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Array,
        default: () => [],
        // expected: [{ month: 'Jan 2025', amount: 4500 }, ...]
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

const hasData = computed(() => props.data && props.data.length > 0);

function buildChart() {
    if (!chartCanvas.value || !hasData.value) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    const labels  = props.data.map(d => d.month);
    const amounts = props.data.map(d => d.amount);

    const ctx = chartCanvas.value.getContext('2d');

    // Green gradient fill
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0,   'rgba(33, 191, 115, 0.28)');
    gradient.addColorStop(1,   'rgba(33, 191, 115, 0.00)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue',
                data: amounts,
                borderColor: '#21bf73',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.45,
                pointRadius: 5,
                pointBackgroundColor: '#21bf73',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f9fafb',
                    bodyColor: '#d1fae5',
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => ` $${ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#9ca3af',
                        font: { family: 'Poppins', size: 11 },
                    },
                },
                y: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false,
                    },
                    border: { display: false, dash: [4, 4] },
                    ticks: {
                        color: '#9ca3af',
                        font: { family: 'Poppins', size: 11 },
                        callback: val => '$' + (val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val),
                    },
                },
            },
        },
    });
}

onMounted(() => { if (hasData.value) buildChart(); });

watch(() => props.data, () => {
    if (hasData.value) buildChart();
}, { deep: true });

onBeforeUnmount(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>

<style scoped>
.sales-chart-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    padding: 24px;
    height: 100%;
}

.sales-chart-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
}

.sales-chart-card__title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 3px;
}

.sales-chart-card__subtitle {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}

.sales-chart-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #21bf73;
    background: rgba(33, 191, 115, 0.10);
    border-radius: 20px;
    padding: 5px 12px;
    white-space: nowrap;
}

.sales-chart-card__canvas-wrap {
    position: relative;
    width: 100%;
}

/* Empty state */
.sales-chart-card__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
}
.sales-chart-card__empty-icon {
    width: 56px; height: 56px;
    background: rgba(33, 191, 115, 0.10);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
    font-size: 22px;
    color: #21bf73;
}
.sales-chart-card__empty-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 5px;
}
.sales-chart-card__empty-sub {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}
</style>
