<template>
    <div class="form-container">
        <div class="form-card">
            <!-- Form Header -->
            <div class="form-card__header">
                <div>
                    <h2 class="form-card__title">
                        {{ form.id !== 0 ? 'Edit Product Item' : 'Register New Inventory SKU' }}
                    </h2>
                    <p class="form-card__subtitle">
                        {{ form.id !== 0 ? 'Modify product attributes, pricing, and stock alerts.' : 'Register a new product with custom stock levels.' }}
                    </p>
                </div>
                <Link href="/ProductPage" class="btn-back animate-btn">
                    <i class="fa fa-arrow-left me-1"></i> Back to Inventory
                </Link>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="form-card__body" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Product Name -->
                    <div class="col-md-12">
                        <label class="form-label-custom">Product Name <span class="text-danger">*</span></label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="e.g. Galvanized Steel Conduit"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Item Code / SKU -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Item Code / SKU <span class="text-danger">*</span></label>
                        <input
                            id="code"
                            type="text"
                            v-model="form.code"
                            required
                            placeholder="e.g. SC-GALV-20MM"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- UOM -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Unit of Measure (UOM)</label>
                        <input
                            id="uom"
                            type="text"
                            v-model="form.uom"
                            placeholder="e.g. pcs, meters, box"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Base Price -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Base Unit Price ($) <span class="text-danger">*</span></label>
                        <input
                            id="price"
                            type="number"
                            step="0.01"
                            v-model="form.price"
                            required
                            placeholder="e.g. 24.99"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Initial Stock (Only on Add) -->
                    <div class="col-md-6" v-if="form.id === 0">
                        <label class="form-label-custom">Initial Stock level <span class="text-danger">*</span></label>
                        <input
                            id="unit"
                            type="number"
                            v-model="form.unit"
                            required
                            placeholder="e.g. 50"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Low Stock Alert Threshold -->
                    <div class="col-md-6" :class="{ 'col-md-12': form.id !== 0 }">
                        <label class="form-label-custom">Low Stock Warning Threshold <span class="text-danger">*</span></label>
                        <input
                            id="low_stock_threshold"
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
                            id="description"
                            v-model="form.description"
                            placeholder="Enter detailed description of physical product parameters..."
                            class="form-textarea-custom"
                        ></textarea>
                    </div>

                    <!-- Product Image -->
                    <div class="col-md-12">
                        <label class="form-label-custom">Product Image File</label>
                        <div class="image-upload-wrap-custom">
                            <div class="image-upload-preview">
                                <img
                                    :src="imagePreview || (form.image ? '/' + form.image : '/placeholder.png')"
                                    alt="Preview"
                                    class="preview-img"
                                    @error="$event.target.src = '/placeholder.png'"
                                />
                            </div>
                            <div class="image-upload-input-col">
                                <input
                                    type="file"
                                    id="image-file"
                                    @change="handleImage"
                                    accept="image/*"
                                    class="form-control form-control-sm"
                                />
                                <span class="upload-tip">Supports JPEG, PNG, JPG, SVG, WEBP (Max 2MB)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Save Action -->
                <div class="form-card__footer pt-4 mt-4 border-top">
                    <button
                        type="submit"
                        class="btn-save-form animate-btn"
                        :disabled="form.processing"
                    >
                        <i v-if="form.processing" class="fa fa-spinner fa-spin me-2"></i>
                        {{ form.id !== 0 ? 'Save Product Changes' : 'Register Product SKU' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({ position: 'top-right' });
const page = usePage();

// Parse route query params
const urlParams = new URLSearchParams(window.location.search);
let urlId = ref(parseInt(urlParams.get('id')) || 0);

// Initialize form
const form = useForm({
    id: urlId.value,
    name: '',
    code: '',
    description: '',
    uom: 'pcs',
    price: 0,
    unit: 0,
    low_stock_threshold: 10,
    image: null,
});

const product = page.props.product;
const imagePreview = ref(null);
const selectedFile = ref(null);

let targetUrl = '/create-product';

if (urlId.value !== 0 && product !== null) {
    targetUrl = '/product-update';
    form.name = product.name;
    form.code = product.code || '';
    form.description = product.description || '';
    form.uom = product.uom || 'pcs';
    form.price = product.price;
    form.unit = product.unit;
    form.low_stock_threshold = product.low_stock_threshold ?? 10;
    form.image = product.image;
}

function handleImage(event) {
    const file = event.target.files[0];
    if (file) {
        selectedFile.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (!form.name) {
        toaster.warning('Product name is required');
        return;
    }
    if (!form.code) {
        toaster.warning('Product SKU code is required');
        return;
    }
    if (form.price === undefined || form.price === null || form.price < 0) {
        toaster.warning('Product price is required');
        return;
    }
    if (urlId.value === 0 && (form.unit === undefined || form.unit === null || form.unit < 0)) {
        toaster.warning('Initial stock level is required');
        return;
    }

    // We build Form Data manually to support file uploads in Laravel
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
                toaster.success(page.props.flash?.message || 'Action executed successfully.');
                router.get('/ProductPage');
            } else {
                toaster.error(page.props.flash?.message || 'An error occurred during submission.');
            }
        },
        onError: () => {
            toaster.error('Please verify the input fields and try again.');
        }
    });
}
</script>

<style scoped>
.form-container {
    padding: 30px 20px;
    display: flex;
    justify-content: center;
    animation: slideUp 0.35s ease both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.form-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 800px;
    overflow: hidden;
}

.form-card__header {
    padding: 24px 28px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.form-card__title {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
    letter-spacing: -0.3px;
}

.form-card__subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.btn-back {
    background: #f3f4f6;
    color: #4b5563;
    text-decoration: none;
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: background 0.15s, color 0.15s;
}
.btn-back:hover {
    background: #e5e7eb;
    color: #111827;
}

/* Form Styles */
.form-card__body {
    padding: 28px;
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
    padding: 11px 16px;
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
    padding: 11px 16px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 13px;
    color: #111827;
    height: 100px;
    resize: none;
    outline: none;
    transition: border-color 0.18s, background 0.18s;
}
.form-textarea-custom:focus {
    border-color: #21bf73;
    background: #ffffff;
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

/* Actions */
.form-card__footer {
    display: flex;
    justify-content: flex-end;
}

.btn-save-form {
    background: #21bf73;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 11px 32px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(33, 191, 115, 0.2);
    cursor: pointer;
    transition: background 0.2s, transform 0.12s, box-shadow 0.2s;
}
.btn-save-form:hover {
    background: #199e5e;
    box-shadow: 0 6px 18px rgba(33, 191, 115, 0.3);
}
.btn-save-form:active {
    transform: scale(0.97);
}

.animate-btn { transition: transform 0.12s ease; }
.animate-btn:active { transform: scale(0.97); }
</style>
