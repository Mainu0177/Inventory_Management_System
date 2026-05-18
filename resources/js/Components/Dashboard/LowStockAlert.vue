<template>
    <div class="low-stock-card">
        <div class="low-stock-card__header">
            <div>
                <h2 class="low-stock-card__title">Low Stock Alerts</h2>
                <p class="low-stock-card__subtitle">Items needing restock</p>
            </div>
            <div
                class="low-stock-card__count-badge"
                :class="items.length > 0 ? 'low-stock-card__count-badge--danger' : 'low-stock-card__count-badge--ok'"
            >
                <i :class="items.length > 0 ? 'fa fa-exclamation-triangle' : 'fa fa-check-circle'"></i>
                {{ items.length > 0 ? items.length + ' items' : 'All OK' }}
            </div>
        </div>

        <!-- List -->
        <div v-if="items.length > 0" class="low-stock-card__list">
            <div
                v-for="item in items"
                :key="item.id"
                class="low-stock-item"
            >
                <div class="low-stock-item__info">
                    <div class="low-stock-item__avatar">
                        <i class="fa fa-box"></i>
                    </div>
                    <div class="low-stock-item__text">
                        <p class="low-stock-item__name">{{ item.name }}</p>
                        <p class="low-stock-item__meta">Threshold: {{ item.threshold }} units</p>
                    </div>
                </div>
                <div class="low-stock-item__stock">
                    <span
                        class="low-stock-item__qty"
                        :class="item.stock === 0 ? 'low-stock-item__qty--empty' : 'low-stock-item__qty--low'"
                    >
                        {{ item.stock }}
                    </span>
                    <span class="low-stock-item__unit">units left</span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="low-stock-card__empty">
            <div class="low-stock-card__empty-icon">
                <i class="fa fa-check-circle"></i>
            </div>
            <p class="low-stock-card__empty-title">All stock levels OK</p>
            <p class="low-stock-card__empty-sub">No products below the restock threshold.</p>
        </div>

        <!-- Footer Link -->
        <a v-if="items.length > 0" href="/ProductPage" class="low-stock-card__footer-btn">
            <i class="fa fa-boxes"></i> View Full Inventory
        </a>
    </div>
</template>

<script setup>
defineProps({
    items: {
        type: Array,
        default: () => [],
        // [{ id, name, stock, price, threshold }]
    },
});
</script>

<style scoped>
.low-stock-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.06);
    padding: 24px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Header */
.low-stock-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
}
.low-stock-card__title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 3px;
}
.low-stock-card__subtitle {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}

/* Badge */
.low-stock-card__count-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 5px 11px;
    border-radius: 20px;
    white-space: nowrap;
}
.low-stock-card__count-badge--danger {
    background: rgba(239, 68, 68, 0.10);
    color: #dc2626;
}
.low-stock-card__count-badge--ok {
    background: rgba(33, 191, 115, 0.10);
    color: #16a34a;
}

/* List */
.low-stock-card__list {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    overflow-y: auto;
    max-height: 320px;
    padding-right: 2px;
}
.low-stock-card__list::-webkit-scrollbar { width: 4px; }
.low-stock-card__list::-webkit-scrollbar-track { background: transparent; }
.low-stock-card__list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

/* Item */
.low-stock-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f9fafb;
    border-radius: 12px;
    padding: 12px 14px;
    transition: background 0.18s;
}
.low-stock-item:hover { background: #f3f4f6; }

.low-stock-item__info {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}
.low-stock-item__avatar {
    width: 36px; height: 36px;
    background: rgba(239, 68, 68, 0.08);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #ef4444;
    font-size: 14px;
    flex-shrink: 0;
}
.low-stock-item__text { min-width: 0; }
.low-stock-item__name {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}
.low-stock-item__meta {
    font-size: 11px;
    color: #9ca3af;
    margin: 0;
}

/* Stock qty */
.low-stock-item__stock {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}
.low-stock-item__qty {
    font-size: 16px;
    font-weight: 700;
    line-height: 1;
}
.low-stock-item__qty--low   { color: #f59e0b; }
.low-stock-item__qty--empty { color: #dc2626; }
.low-stock-item__unit {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    margin-top: 2px;
}

/* Empty state */
.low-stock-card__empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}
.low-stock-card__empty-icon {
    width: 54px; height: 54px;
    background: rgba(33, 191, 115, 0.10);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
    font-size: 22px;
    color: #21bf73;
}
.low-stock-card__empty-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 4px;
}
.low-stock-card__empty-sub {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
}

/* Footer button */
.low-stock-card__footer-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin-top: 18px;
    padding: 10px 0;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-decoration: none;
    transition: background 0.18s, color 0.18s;
    cursor: pointer;
}
.low-stock-card__footer-btn:hover {
    background: rgba(33, 191, 115, 0.08);
    color: #21bf73;
    border-color: rgba(33, 191, 115, 0.3);
}
</style>
