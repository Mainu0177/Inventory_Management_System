<template>
    <div class="stat-card" :class="`stat-card--${color}`">
        <div class="stat-card__top">
            <div class="stat-card__icon-wrap">
                <i :class="icon" class="stat-card__icon"></i>
            </div>
            <div class="stat-card__trend" :class="trendPositive ? 'stat-card__trend--up' : 'stat-card__trend--down'">
                <i :class="trendPositive ? 'fa fa-arrow-up' : 'fa fa-arrow-down'" class="stat-card__trend-arrow"></i>
                <span>{{ trendLabel }}</span>
            </div>
        </div>
        <div class="stat-card__body">
            <p class="stat-card__label">{{ label }}</p>
            <h3 class="stat-card__value">{{ value }}</h3>
        </div>
        <div class="stat-card__glow"></div>
    </div>
</template>

<script setup>
defineProps({
    label:         { type: String,  required: true },
    value:         { type: String,  required: true },
    icon:          { type: String,  default: 'fa fa-chart-line' },
    color:         { type: String,  default: 'green' },   // green | blue | orange | purple
    trendPositive: { type: Boolean, default: true },
    trendLabel:    { type: String,  default: '' },
});
</script>

<style scoped>
.stat-card {
    position: relative;
    background: #ffffff;
    border-radius: 18px;
    padding: 22px 22px 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: default;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10);
}

/* Glow accent bar at bottom */
.stat-card__glow {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 18px 18px;
}
.stat-card--green  .stat-card__glow { background: linear-gradient(90deg, #21bf73, #56d598); }
.stat-card--blue   .stat-card__glow { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.stat-card--orange .stat-card__glow { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.stat-card--purple .stat-card__glow { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

/* Top row */
.stat-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 18px;
}

/* Icon */
.stat-card__icon-wrap {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.stat-card--green  .stat-card__icon-wrap { background: rgba(33, 191, 115, 0.12); }
.stat-card--blue   .stat-card__icon-wrap { background: rgba(59, 130, 246, 0.12); }
.stat-card--orange .stat-card__icon-wrap { background: rgba(245, 158, 11, 0.12); }
.stat-card--purple .stat-card__icon-wrap { background: rgba(139, 92, 246, 0.12); }

.stat-card__icon {
    font-size: 19px;
}
.stat-card--green  .stat-card__icon { color: #21bf73; }
.stat-card--blue   .stat-card__icon { color: #3b82f6; }
.stat-card--orange .stat-card__icon { color: #f59e0b; }
.stat-card--purple .stat-card__icon { color: #8b5cf6; }

/* Trend badge */
.stat-card__trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 20px;
    letter-spacing: 0.02em;
}
.stat-card__trend--up   { background: rgba(33, 191, 115, 0.12); color: #16a34a; }
.stat-card__trend--down { background: rgba(239, 68, 68, 0.10);  color: #dc2626; }

.stat-card__trend-arrow { font-size: 9px; }

/* Body */
.stat-card__label {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    margin: 0 0 5px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}
.stat-card__value {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.5px;
}
</style>
