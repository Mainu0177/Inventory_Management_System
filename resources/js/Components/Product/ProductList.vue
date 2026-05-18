<template>
    <div class="inventory-container">
        <!-- Header -->
        <header class="inventory-header">
            <div>
                <h1 class="inventory-title">Inventory Directory</h1>
                <p class="inventory-subtitle">Monitor stock levels, track transaction histories, and adjust product pricing details.</p>
            </div>
            <button
                @click="openAddModal"
                class="btn-add-product animate-btn"
                id="add-product-btn"
            >
                <i class="fa fa-plus-circle me-2"></i>
                Add Product
            </button>
        </header>

        <!-- Search input -->
        <div class="search-bar-wrap mb-4">
            <i class="fa fa-search search-icon"></i>
            <input
                type="text"
                placeholder="Search inventory by name, product code, or unit of measure..."
                v-model="searchTerm"
                class="search-input"
            />
            <span v-if="searchTerm" @click="searchTerm = ''" class="clear-search-btn">
                <i class="fa fa-times"></i>
            </span>
        </div>

        <!-- Inventory Table / List -->
        <div v-if="filteredProducts.length > 0" class="inventory-table-wrap">
            <div class="table-responsive">
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Product Info</th>
                            <th>UOM</th>
                            <th class="text-end">Base Price</th>
                            <th class="text-end">Stock Level</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in filteredProducts" :key="product.id" class="inventory-row">
                            <!-- Product Info -->
                            <td>
                                <div class="product-info-cell">
                                    <div class="product-image-box">
                                        <img
                                            :src="product.image ? '/' + product.image : '/placeholder.png'"
                                            alt="Product Image"
                                            class="product-thumb"
                                            @error="$event.target.src = '/placeholder.png'"
                                        />
                                    </div>
                                    <div>
                                        <p class="product-name" :title="product.name">{{ product.name }}</p>
                                        <span class="product-code">{{ product.code || 'NO-CODE' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- UOM -->
                            <td>
                                <span class="uom-badge">{{ product.uom || 'pcs' }}</span>
                            </td>

                            <!-- Price -->
                            <td class="text-end font-bold text-dark">
                                {{ formatCurrency(product.price) }}
                            </td>

                            <!-- Stock level & alert -->
                            <td class="text-end">
                                <div class="stock-status-cell">
                                    <div
                                        class="stock-badge"
                                        :class="isLowStock(product) ? 'stock-badge--low' : 'stock-badge--good'"
                                    >
                                        <i v-if="isLowStock(product)" class="fa fa-exclamation-triangle me-1"></i>
                                        {{ product.unit }} {{ product.uom || 'pcs' }}
                                    </div>
                                    <span class="stock-threshold">Threshold: {{ product.low_stock_threshold ?? 10 }}</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="action-buttons-wrap">
                                    <!-- Edit -->
                                    <button
                                        @click="openEditModal(product)"
                                        class="action-btn action-btn--edit"
                                        title="Edit Product"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <!-- Adjust Stock -->
                                    <button
                                        @click="openAdjustmentModal(product)"
                                        class="action-btn action-btn--adjust"
                                        title="Adjust Stock Level"
                                    >
                                        <i class="fa fa-exchange-alt"></i>
                                    </button>

                                    <!-- History Logs -->
                                    <button
                                        @click="openHistoryModal(product)"
                                        class="action-btn action-btn--history"
                                        title="Stock History Logs"
                                    >
                                        <i class="fa fa-history"></i>
                                    </button>

                                    <!-- Delete -->
                                    <button
                                        @click="confirmDelete(product)"
                                        class="action-btn action-btn--delete"
                                        title="Delete Product"
                                    >
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="inventory-empty-state">
            <div class="empty-icon-wrap">
                <i class="fa fa-box-open"></i>
            </div>
            <h3 class="empty-title">No Inventory Products Found</h3>
            <p class="empty-subtitle">Try refining your search keyword or add a new product item directly into your directory.</p>
            <button @click="openAddModal" class="btn btn-success px-4 py-2 rounded-3">
                <i class="fa fa-plus me-1"></i> Add Product
            </button>
        </div>

        <!-- Form Modal (Add / Edit Product) -->
        <transition name="modal-fade">
            <div v-if="isAdding" class="modal-backdrop-custom">
                <div class="modal-container-custom">
                    <!-- Modal Header -->
                    <div class="modal-header-custom">
                        <div>
                            <h3 class="modal-title-custom">
                                {{ isEditing ? 'Modify Product Profile' : 'Register New Product' }}
                            </h3>
                            <p class="modal-subtitle-custom text-muted">
                                {{ isEditing ? `Updating SKU details for ${form.name}` : 'Enter your inventory item SKU parameters below.' }}
                            </p>
                        </div>
                        <button @click="closeFormModal" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="submitForm" class="modal-form-custom" enctype="multipart/form-data">
                        <div class="row g-3 p-4 overflow-auto" style="max-height: 60vh;">
                            <!-- Name -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Product Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    required
                                    placeholder="e.g. Copper Wire 10mm"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Code / SKU -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Item SKU Code <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    v-model="form.code"
                                    required
                                    placeholder="e.g. CW-10MM-BLK"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- UOM -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Unit of Measure (UOM)</label>
                                <input
                                    type="text"
                                    v-model="form.uom"
                                    placeholder="e.g. pcs, meters, box"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Base Price -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Base Price ($) <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    v-model="form.price"
                                    required
                                    placeholder="e.g. 14.50"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Initial Stock level (only on Add Mode) -->
                            <div class="col-md-6" v-if="!isEditing">
                                <label class="form-label-custom">Initial Stock Level <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    v-model="form.unit"
                                    required
                                    placeholder="e.g. 100"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Low Stock Alert Threshold -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Low Stock Warning Threshold <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    v-model="form.low_stock_threshold"
                                    required
                                    placeholder="e.g. 10"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Description -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Product Description</label>
                                <textarea
                                    v-model="form.description"
                                    placeholder="Enter physical product properties or details..."
                                    class="form-textarea-custom"
                                ></textarea>
                            </div>

                            <!-- Product Image Upload -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Product Image File</label>
                                <div class="image-upload-wrap-custom">
                                    <div class="image-upload-preview">
                                        <img
                                            :src="imagePreview || (form.image ? '/' + form.image : '/placeholder.png')"
                                            alt="Preview"
                                            class="preview-img"
                                        />
                                    </div>
                                    <div class="image-upload-input-col">
                                        <input
                                            type="file"
                                            id="product-image-file"
                                            @change="handleImageChange"
                                            accept="image/*"
                                            class="form-control form-control-sm"
                                        />
                                        <span class="upload-tip">Supports PNG, JPG, WEBP (Max 2MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer-custom">
                            <button type="button" @click="closeFormModal" class="btn-discard">Discard</button>
                            <button
                                type="submit"
                                class="btn-save-product animate-btn"
                                :disabled="form.processing"
                            >
                                <i v-if="form.processing" class="fa fa-spinner fa-spin me-2"></i>
                                {{ isEditing ? 'Save Changes' : 'Register SKU' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Stock Adjustment Modal -->
        <transition name="modal-fade">
            <div v-if="adjustmentConfig.isOpen" class="modal-backdrop-custom">
                <div class="modal-container-custom">
                    <!-- Modal Header -->
                    <div class="modal-header-custom">
                        <div>
                            <h3 class="modal-title-custom">
                                Adjust Stock Level
                            </h3>
                            <p class="modal-subtitle-custom text-muted">
                                Register stock transactions for <strong>{{ adjustmentConfig.product?.name }}</strong>
                            </p>
                        </div>
                        <button @click="closeAdjustmentModal" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form @submit.prevent="submitAdjustment" class="modal-form-custom">
                        <div class="p-4 space-y-4">
                            <!-- Type selection (IN/OUT) -->
                            <div class="form-group-custom">
                                <label class="form-label-custom">Transaction Type</label>
                                <div class="d-flex gap-3">
                                    <label class="radio-label">
                                        <input
                                            type="radio"
                                            value="IN"
                                            v-model="adjustForm.type"
                                            class="radio-input"
                                        />
                                        <span class="radio-design radio-design--in"></span>
                                        <span class="radio-text">Stock IN (Receive Items)</span>
                                    </label>
                                    <label class="radio-label">
                                        <input
                                            type="radio"
                                            value="OUT"
                                            v-model="adjustForm.type"
                                            class="radio-input"
                                        />
                                        <span class="radio-design radio-design--out"></span>
                                        <span class="radio-text">Stock OUT (Disburse Items)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group-custom mt-3">
                                <label class="form-label-custom">Quantity <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    v-model="adjustForm.quantity"
                                    required
                                    min="1"
                                    class="form-input-custom"
                                    placeholder="Enter quantity amount..."
                                />
                            </div>

                            <!-- Reason -->
                            <div class="form-group-custom mt-3">
                                <label class="form-label-custom">Adjustment Reason <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    v-model="adjustForm.reason"
                                    required
                                    class="form-input-custom"
                                    placeholder="e.g. Sales dispatch, restock delivery, damaged item deduction..."
                                />
                            </div>

                            <!-- Reference ID -->
                            <div class="form-group-custom mt-3">
                                <label class="form-label-custom">Reference Voucher Code (Optional)</label>
                                <input
                                    type="text"
                                    v-model="adjustForm.reference_id"
                                    class="form-input-custom"
                                    placeholder="e.g. PO-9876, DO-4321..."
                                />
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer-custom">
                            <button type="button" @click="closeAdjustmentModal" class="btn-discard">Cancel</button>
                            <button
                                type="submit"
                                class="btn-save-product animate-btn"
                                :disabled="isSubmittingAdjustment"
                            >
                                <i v-if="isSubmittingAdjustment" class="fa fa-spinner fa-spin me-2"></i>
                                Apply Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- Stock Transaction History Logs Modal -->
        <transition name="modal-fade">
            <div v-if="historyConfig.isOpen" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--large">
                    <!-- Modal Header -->
                    <div class="modal-header-custom bg-light">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa fa-history text-secondary fs-5"></i>
                                <h3 class="modal-title-custom text-dark mb-0">
                                    Inventory Transaction Logs
                                </h3>
                            </div>
                            <p class="modal-subtitle-custom text-muted mb-0">
                                Complete stock movement records for <strong>{{ historyConfig.product?.name }}</strong>
                            </p>
                        </div>
                        <button @click="closeHistoryModal" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body-custom p-4">
                        <div v-if="isLoadingHistory" class="d-flex flex-column align-items-center justify-content-center py-5">
                            <div class="spinner-border text-success mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Loading transaction records...</p>
                        </div>
                        <div v-else>
                            <div class="table-responsive-custom">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Ref Number</th>
                                            <th class="text-end">Reason / Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="log in historyLogs" :key="log.id">
                                            <td class="text-dark font-medium">{{ log.date }}</td>
                                            <td>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                                    :class="log.type === 'IN' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                                >
                                                    <i :class="log.type === 'IN' ? 'fa fa-arrow-down' : 'fa fa-arrow-up'" class="me-1"></i>
                                                    STOCK {{ log.type }}
                                                </span>
                                            </td>
                                            <td class="font-bold text-dark">{{ log.quantity }}</td>
                                            <td class="text-muted font-mono">{{ log.reference_id }}</td>
                                            <td class="text-end text-muted">{{ log.reason }}</td>
                                        </tr>
                                        <tr v-if="historyLogs.length === 0">
                                            <td colspan="5" class="table-empty-row text-center py-4 text-muted italic">
                                                No stock movements registered for this SKU yet.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import axios from 'axios';

const toaster = createToaster({ position: 'top-right' });
const page = usePage();

// Reactive products computed list from page props
const products = computed(() => page.props.products || []);

// Search state
const searchTerm = ref('');

// Product Form Modal state
const isAdding = ref(false);
const isEditing = ref(false);
const editingProductId = ref(null);
const imagePreview = ref(null);
const selectedFile = ref(null);

const form = useForm({
    id: null,
    name: '',
    code: '',
    description: '',
    uom: 'pcs',
    price: 0,
    unit: 0,
    low_stock_threshold: 10,
    image: null,
});

// Adjustment Modal state
const adjustmentConfig = ref({
    isOpen: false,
    product: null,
});
const isSubmittingAdjustment = ref(false);
const adjustForm = ref({
    product_id: null,
    type: 'IN',
    quantity: 1,
    reason: '',
    reference_id: '',
});

// History Logs state
const historyConfig = ref({
    isOpen: false,
    product: null,
});
const isLoadingHistory = ref(false);
const historyLogs = ref([]);

// Search and filter
const filteredProducts = computed(() => {
    const q = searchTerm.value.trim().toLowerCase();
    if (!q) return products.value;
    return products.value.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.code && p.code.toLowerCase().includes(q))
    );
});

// Helpers
function formatCurrency(val) {
    const num = parseFloat(val) || 0;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function isLowStock(product) {
    const current = parseInt(product.unit) || 0;
    const threshold = parseInt(product.low_stock_threshold) ?? 10;
    return current <= threshold;
}

// Image handler
function handleImageChange(event) {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

// Product Form actions
function openAddModal() {
    form.reset();
    isEditing.value = false;
    editingProductId.value = null;
    imagePreview.value = null;
    selectedFile.value = null;
    isAdding.value = true;
}

function openEditModal(product) {
    form.id = product.id;
    form.name = product.name;
    form.code = product.code;
    form.description = product.description || '';
    form.uom = product.uom || 'pcs';
    form.price = product.price;
    form.unit = product.unit;
    form.low_stock_threshold = product.low_stock_threshold ?? 10;
    form.image = product.image;

    imagePreview.value = null;
    selectedFile.value = null;
    isEditing.value = true;
    editingProductId.value = product.id;
    isAdding.value = true;
}

function closeFormModal() {
    isAdding.value = false;
    isEditing.value = false;
    editingProductId.value = null;
    imagePreview.value = null;
    selectedFile.value = null;
    form.reset();
}

function submitForm() {
    const targetUrl = isEditing.value ? '/product-update' : '/create-product';

    // We use standard FormData since we may upload images
    const formData = new FormData();
    formData.append('id', form.id);
    formData.append('name', form.name);
    formData.append('code', form.code);
    formData.append('description', form.description || '');
    formData.append('uom', form.uom || 'pcs');
    formData.append('price', form.price);
    formData.append('unit', form.unit);
    formData.append('low_stock_threshold', form.low_stock_threshold);

    if (selectedFile.value) {
        formData.append('image', selectedFile.value);
    }

    router.post(targetUrl, formData, {
        onSuccess: () => {
            if (page.props.flash?.status !== false) {
                toaster.success(page.props.flash?.message || 'Inventory record updated successfully.');
                closeFormModal();
            } else {
                toaster.error(page.props.flash?.message || 'An error occurred.');
            }
        },
        onError: () => {
            toaster.error('Please verify all input values and try again.');
        }
    });
}

// Adjustments
function openAdjustmentModal(product) {
    adjustForm.value = {
        product_id: product.id,
        type: 'IN',
        quantity: 1,
        reason: '',
        reference_id: '',
    };
    adjustmentConfig.value = {
        isOpen: true,
        product,
    };
}

function closeAdjustmentModal() {
    adjustmentConfig.value = { isOpen: false, product: null };
}

async function submitAdjustment() {
    isSubmittingAdjustment.value = true;
    try {
        const response = await axios.post('/product-adjust-stock', adjustForm.value);
        if (response.data?.status === 'Success') {
            toaster.success(response.data.message || 'Stock level adjusted.');
            closeAdjustmentModal();
            // Reload page to reflect new stock values
            router.reload({ only: ['products'] });
        } else {
            toaster.error(response.data?.message || 'Failed to adjust stock level.');
        }
    } catch (e) {
        console.error(e);
        toaster.error(e.response?.data?.message || 'Error occurred while contacting the server.');
    } finally {
        isSubmittingAdjustment.value = false;
    }
}

// History Logs
async function openHistoryModal(product) {
    historyConfig.value = {
        isOpen: true,
        product,
    };
    isLoadingHistory.value = true;
    try {
        const response = await axios.get('/product-inventory-logs', {
            params: { product_id: product.id }
        });
        if (response.data?.status === 'Success') {
            historyLogs.value = response.data.logs || [];
        } else {
            toaster.error('Failed to load transaction logs.');
        }
    } catch (e) {
        console.error(e);
        toaster.error('Server network error loading log history.');
    } finally {
        isLoadingHistory.value = false;
    }
}

function closeHistoryModal() {
    historyConfig.value = { isOpen: false, product: null };
    historyLogs.value = [];
}

// Delete product
function confirmDelete(product) {
    const txt = `Are you sure you want to permanently delete product "${product.name}"? This action will remove the product SKU and its inventory logs permanently.`;
    if (confirm(txt)) {
        router.get(`/product-delete/${product.id}`, {
            onSuccess: () => {
                toaster.success('Product deleted successfully');
            },
            onError: () => {
                toaster.error('Failed to delete product.');
            }
        });
    }
}
</script>

<style scoped>
/* ── Container ───────────────────────────────────────────────────────────── */
.inventory-container {
    padding: 24px 20px 40px;
    animation: fadeEffect 0.35s ease both;
}

@keyframes fadeEffect {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Header ──────────────────────────────────────────────────────────────── */
.inventory-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}
.inventory-title {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
    letter-spacing: -0.4px;
}
.inventory-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    max-width: 600px;
    line-height: 1.5;
}

.btn-add-product {
    background: #21bf73;
    color: #ffffff;
    border: none;
    outline: none;
    border-radius: 12px;
    padding: 11px 22px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 14px rgba(33, 191, 115, 0.25);
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
}
.btn-add-product:hover {
    background: #199e5e;
    box-shadow: 0 6px 18px rgba(33, 191, 115, 0.35);
}
.btn-add-product:active {
    transform: scale(0.97);
}

/* ── Search Bar ──────────────────────────────────────────────────────────── */
.search-bar-wrap {
    position: relative;
    width: 100%;
}
.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
}
.search-input {
    width: 100%;
    padding: 13px 44px 13px 44px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    font-size: 13px;
    color: #1f2937;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
    border-color: rgba(33, 191, 115, 0.4);
    box-shadow: 0 4px 16px rgba(33, 191, 115, 0.08);
}
.clear-search-btn {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    transition: color 0.15s;
}
.clear-search-btn:hover { color: #dc2626; }

/* ── Table Layout ────────────────────────────────────────────────────────── */
.inventory-table-wrap {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}
.table-responsive {
    overflow-x: auto;
    width: 100%;
}
.inventory-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.inventory-table th {
    background: #f9fafb;
    color: #4b5563;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.05em;
    padding: 14px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.inventory-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.inventory-row {
    transition: background 0.18s;
}
.inventory-row:hover {
    background: #f9fafb;
}

/* Product Info cell */
.product-info-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.product-image-box {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #f3f4f6;
    border: 1px solid rgba(0,0,0,0.04);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.product-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-name {
    font-weight: 700;
    color: #111827;
    margin: 0 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 250px;
}
.product-code {
    font-size: 10px;
    font-family: monospace;
    font-weight: 700;
    color: #9ca3af;
    letter-spacing: 0.02em;
}

/* UOM badge */
.uom-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    background: #f3f4f6;
    color: #4b5563;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

/* Stock statuses */
.stock-status-cell {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
}
.stock-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}
.stock-badge--good {
    background: rgba(33, 191, 115, 0.08);
    color: #16a34a;
}
.stock-badge--low {
    background: rgba(239, 68, 68, 0.08);
    color: #dc2626;
}
.stock-threshold {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    font-style: italic;
}

/* Action button icons */
.action-buttons-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.action-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: none;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.action-btn--edit {
    background: rgba(33, 191, 115, 0.08);
    color: #21bf73;
}
.action-btn--edit:hover {
    background: #21bf73;
    color: #ffffff;
}

.action-btn--adjust {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}
.action-btn--adjust:hover {
    background: #3b82f6;
    color: #ffffff;
}

.action-btn--history {
    background: rgba(139, 92, 246, 0.08);
    color: #8b5cf6;
}
.action-btn--history:hover {
    background: #8b5cf6;
    color: #ffffff;
}

.action-btn--delete {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
}
.action-btn--delete:hover {
    background: #ef4444;
    color: #ffffff;
}

.font-bold { font-weight: 700; }
.text-dark { color: #111827; }

/* ── Empty State ─────────────────────────────────────────────────────────── */
.inventory-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
}
.empty-icon-wrap {
    width: 68px; height: 68px;
    background: rgba(33, 191, 115, 0.1);
    border-radius: 50%;
    color: #21bf73;
    font-size: 28px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 18px;
}
.empty-title {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 6px;
}
.empty-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 16px;
    max-width: 420px;
    line-height: 1.5;
}

/* ── Modals Backdrop and Boxes ───────────────────────────────────────────── */
.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 46, 0.55);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-container-custom {
    background: #ffffff;
    border-radius: 28px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.modal-container-custom--large {
    max-width: 780px;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95) translateY(12px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header-custom {
    padding: 22px 28px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-title-custom {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}
.modal-subtitle-custom {
    font-size: 12px;
    margin-top: 3px;
}

.btn-close-modal {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none; background: transparent;
    display: flex; align-items: center; justify-content: center;
    color: #9ca3af;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.btn-close-modal:hover {
    background: #f3f4f6;
    color: #111827;
}

/* Image Upload Layout */
.image-upload-wrap-custom {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 14px;
    padding: 12px;
}
.image-upload-preview {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-upload-input-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}
.upload-tip {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
}

/* Custom Table for History logs */
.table-responsive-custom {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}
.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.table-custom th {
    background: #111827;
    color: #ffffff;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 9px;
    letter-spacing: 0.05em;
    padding: 12px 18px;
}
.table-custom td {
    padding: 12px 18px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.table-custom tr:last-child td { border-bottom: none; }
.table-custom tr:hover td { background: #f9fafb; }

.font-medium { font-weight: 500; }
.font-mono { font-family: monospace; font-weight: 700; }

/* Radio Inputs for Adjustment */
.radio-label {
    display: flex;
    align-items: center;
    position: relative;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    color: #4b5563;
    user-select: none;
    gap: 8px;
}
.radio-input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}
.radio-design {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.18s;
}
.radio-design::after {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: transparent;
    transition: background 0.18s;
}
.radio-input:checked ~ .radio-design--in { border-color: #21bf73; }
.radio-input:checked ~ .radio-design--in::after { background: #21bf73; }
.radio-input:checked ~ .radio-design--out { border-color: #ef4444; }
.radio-input:checked ~ .radio-design--out::after { background: #ef4444; }
.radio-input:checked ~ .radio-text { color: #111827; }

/* Form inputs */
.modal-form-custom {
    display: flex;
    flex-direction: column;
}
.form-label-custom {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6b7280;
    margin-bottom: 6px;
}
.form-input-custom {
    width: 100%;
    padding: 10px 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
    color: #111827;
    outline: none;
    transition: border-color 0.18s, background 0.18s;
}
.form-input-custom:focus {
    border-color: #21bf73;
    background: #ffffff;
}

.form-textarea-custom {
    width: 100%;
    padding: 10px 14px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
    color: #111827;
    height: 80px;
    resize: none;
    outline: none;
    transition: border-color 0.18s, background 0.18s;
}
.form-textarea-custom:focus {
    border-color: #21bf73;
    background: #ffffff;
}

/* Modal Footer */
.modal-footer-custom {
    padding: 18px 28px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}
.btn-discard {
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    cursor: pointer;
    transition: color 0.15s;
}
.btn-discard:hover { color: #111827; }

.btn-save-product {
    background: #111827;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 10px 24px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: background 0.18s, transform 0.12s;
}
.btn-save-product:hover { background: #1f2937; }
.btn-save-product:active { transform: scale(0.97); }

.animate-btn { transition: transform 0.12s ease; }
.animate-btn:active { transform: scale(0.97); }

/* Fades */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.22s; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
</style>
