<template>
    <div class="dashboard">

        <!-- Page Header -->
        <div class="dashboard__header">
            <div>
                <h1 class="dashboard__title">Dashboard</h1>
                <p class="dashboard__subtitle">Welcome back! Here's your business overview.</p>
            </div>
            <div class="dashboard__date">
                <i class="fa fa-calendar-alt"></i>
                {{ currentDate }}
            </div>
        </div>

        <!-- Stat Cards Grid -->
        <div class="dashboard__stats-grid">
            <StatCard
                label="Total Revenue"
                :value="formatCurrency(summary.totalRevenue)"
                icon="fa fa-dollar-sign"
                color="green"
                :trend-positive="true"
                trend-label="Collected"
            />
            <StatCard
                label="Outstanding Amount"
                :value="formatCurrency(summary.outstanding)"
                icon="fa fa-file-invoice-dollar"
                color="orange"
                :trend-positive="summary.outstanding === 0"
                trend-label="Pending"
            />
            <StatCard
                label="Active Clients"
                :value="String(summary.activeClients)"
                icon="fa fa-users"
                color="blue"
                :trend-positive="true"
                :trend-label="summary.activeClients + ' total'"
            />
            <StatCard
                label="Inventory Value"
                :value="formatCurrency(summary.inventoryValue)"
                icon="fa fa-boxes"
                color="purple"
                :trend-positive="true"
                :trend-label="summary.productCount + ' products'"
            />
        </div>

        <!-- Quick Metrics Row -->
        <div class="dashboard__metrics-row">
            <div class="metric-pill">
                <i class="fa fa-file-alt metric-pill__icon"></i>
                <span class="metric-pill__label">Total Invoices</span>
                <span class="metric-pill__value">{{ summary.invoiceCount }}</span>
            </div>
            <div class="metric-pill">
                <i class="fa fa-box metric-pill__icon metric-pill__icon--purple"></i>
                <span class="metric-pill__label">Total Products</span>
                <span class="metric-pill__value">{{ summary.productCount }}</span>
            </div>
            <div class="metric-pill">
                <i class="fa fa-exclamation-triangle metric-pill__icon metric-pill__icon--orange"></i>
                <span class="metric-pill__label">Low Stock Items</span>
                <span class="metric-pill__value" :class="summary.lowStockProducts.length > 0 ? 'metric-pill__value--danger' : ''">
                    {{ summary.lowStockProducts.length }}
                </span>
            </div>
            <div class="metric-pill">
                <i class="fa fa-user-friends metric-pill__icon metric-pill__icon--blue"></i>
                <span class="metric-pill__label">Active Clients</span>
                <span class="metric-pill__value">{{ summary.activeClients }}</span>
            </div>
        </div>

        <!-- Chart + Low Stock -->
        <div class="dashboard__bottom-grid">
            <!-- Sales Chart (2/3 width) -->
            <div class="dashboard__chart-col">
                <SalesChart :data="summary.monthlyRevenue" />
            </div>

            <!-- Low Stock Alert (1/3 width) -->
            <div class="dashboard__stock-col">
                <LowStockAlert :items="summary.lowStockProducts" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import StatCard      from './StatCard.vue';
import SalesChart    from './SalesChart.vue';
import LowStockAlert from './LowStockAlert.vue';

// ── Inertia page props ──────────────────────────────────────────────────────
const page = usePage();

/**
 * Reactive summary object. Falls back to safe defaults so the
 * page never crashes even if the API returns partial data.
 */
const summary = computed(() => {
    const raw = page.props.summary ?? {};
    return {
        totalRevenue:     raw.totalRevenue    ?? 0,
        outstanding:      raw.outstanding     ?? 0,
        activeClients:    raw.activeClients   ?? 0,
        inventoryValue:   raw.inventoryValue  ?? 0,
        productCount:     raw.productCount    ?? 0,
        invoiceCount:     raw.invoiceCount    ?? 0,
        monthlyRevenue:   raw.monthlyRevenue  ?? [],
        lowStockProducts: raw.lowStockProducts ?? [],
    };
});

// ── Helpers ─────────────────────────────────────────────────────────────────
function formatCurrency(value) {
    const num = parseFloat(value) || 0;
    if (num >= 1_000_000) return '$' + (num / 1_000_000).toFixed(2) + 'M';
    if (num >= 1_000)     return '$' + (num / 1_000).toFixed(2) + 'K';
    return '$' + num.toFixed(2);
}

const currentDate = computed(() =>
    new Date().toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    })
);
</script>

<style scoped>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.dashboard {
    padding: 24px 20px 40px;
    animation: dashFadeIn 0.4s ease both;
}

@keyframes dashFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0);    }
}

/* ── Header ─────────────────────────────────────────────────────────────── */
.dashboard__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 28px;
}
.dashboard__title {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
    letter-spacing: -0.4px;
}
.dashboard__subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}
.dashboard__date {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 500;
    color: #9ca3af;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 8px 14px;
    white-space: nowrap;
}

/* ── Stats Grid ──────────────────────────────────────────────────────────── */
.dashboard__stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 18px;
}

/* ── Quick Metrics Row ───────────────────────────────────────────────────── */
.dashboard__metrics-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.metric-pill {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 1px 8px rgba(0, 0, 0, 0.05);
    border-radius: 14px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: transform 0.2s ease;
}
.metric-pill:hover { transform: translateY(-2px); }

.metric-pill__icon {
    font-size: 15px;
    color: #21bf73;
    width: 16px;
    text-align: center;
}
.metric-pill__icon--purple { color: #8b5cf6; }
.metric-pill__icon--orange { color: #f59e0b; }
.metric-pill__icon--blue   { color: #3b82f6; }

.metric-pill__label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    flex: 1;
}
.metric-pill__value {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}
.metric-pill__value--danger { color: #dc2626; }

/* ── Bottom Grid ─────────────────────────────────────────────────────────── */
.dashboard__bottom-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 18px;
}
.dashboard__chart-col,
.dashboard__stock-col {
    min-width: 0;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1199px) {
    .dashboard__stats-grid  { grid-template-columns: repeat(2, 1fr); }
    .dashboard__metrics-row { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 900px) {
    .dashboard__bottom-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .dashboard {
        padding: 16px 12px 32px;
    }
    .dashboard__stats-grid  { grid-template-columns: 1fr; }
    .dashboard__metrics-row { grid-template-columns: 1fr 1fr; }
    .dashboard__title       { font-size: 22px; }
    .dashboard__date        { display: none; }
}
</style>
