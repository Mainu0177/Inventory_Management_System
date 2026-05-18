<template>
    <div class="form-container">
        <div class="form-card">
            <!-- Form Header -->
            <div class="form-card__header">
                <div>
                    <h2 class="form-card__title">
                        {{ form.id !== 0 ? 'Edit Partner Details' : 'Register New Partner' }}
                    </h2>
                    <p class="form-card__subtitle">
                        {{ form.id !== 0 ? 'Modify partner profiles and directory records.' : 'Create a new business contact inside the system.' }}
                    </p>
                </div>
                <Link href="/CustomerPage" class="btn-back animate-btn">
                    <i class="fa fa-arrow-left me-1"></i> Back to Directory
                </Link>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="form-card__body">
                <div class="row g-4">
                    <!-- Business Name -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Business Name <span class="text-danger">*</span></label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="e.g. Acme Corporation"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Contact Person -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Primary Contact Person</label>
                        <input
                            id="contact_person"
                            type="text"
                            v-model="form.contact_person"
                            placeholder="e.g. John Doe"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Email Address -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="contact@company.com"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Phone Number -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
                        <input
                            id="mobile"
                            type="text"
                            v-model="form.mobile"
                            required
                            placeholder="e.g. +123456789"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Tax ID -->
                    <div class="col-md-12">
                        <label class="form-label-custom">Tax Registration ID (VAT/TAX)</label>
                        <input
                            id="tax_id"
                            type="text"
                            v-model="form.tax_id"
                            placeholder="e.g. VAT-987654"
                            class="form-input-custom"
                        />
                    </div>

                    <!-- Address sync control -->
                    <div class="col-md-12 d-flex align-items-center justify-content-between pt-2">
                        <label class="form-label-custom mb-0">Partner Addresses</label>
                        <button
                            type="button"
                            @click="toggleSameAsBilling"
                            class="btn-sync-addr"
                            :class="{ 'btn-sync-addr--active': sameAsBilling }"
                        >
                            <i :class="sameAsBilling ? 'fa fa-check-square' : 'fa fa-square'" class="me-2"></i>
                            Shipping Same as Billing
                        </button>
                    </div>

                    <!-- Billing Address -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Billing Address <span class="text-danger">*</span></label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            required
                            placeholder="Full billing address details..."
                            class="form-textarea-custom"
                        ></textarea>
                    </div>

                    <!-- Shipping Address -->
                    <div class="col-md-6">
                        <label class="form-label-custom">Shipping Address</label>
                        <textarea
                            id="shipping_address"
                            v-model="form.shipping_address"
                            :disabled="sameAsBilling"
                            :placeholder="sameAsBilling ? 'Same as billing address' : 'Full shipping address details...'"
                            class="form-textarea-custom"
                            :class="{ 'disabled-textarea': sameAsBilling }"
                        ></textarea>
                    </div>
                </div>

                <!-- Footer / Save Actions -->
                <div class="form-card__footer pt-4 mt-4 border-top">
                    <button
                        type="submit"
                        class="btn-save-form animate-btn"
                        :disabled="form.processing"
                    >
                        <i v-if="form.processing" class="fa fa-spinner fa-spin me-2"></i>
                        {{ form.id !== 0 ? 'Save Partner Changes' : 'Register Partner' }}
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

// Fetch routing parameters
const urlParams = new URLSearchParams(window.location.search);
let urlId = ref(parseInt(urlParams.get('id')) || 0);

// Initialize form
const form = useForm({
    id: urlId.value,
    name: '',
    email: '',
    mobile: '',
    address: '',
    contact_person: '',
    tax_id: '',
    shipping_address: '',
});

// Detect mode & Hydrate existing values
const customer = page.props.customer;
const sameAsBilling = ref(true);

let targetUrl = '/create-customer';

if (urlId.value !== 0 && customer !== null) {
    targetUrl = '/customer-update';
    form.name = customer.name;
    form.email = customer.email;
    form.mobile = customer.mobile;
    form.address = customer.address;
    form.contact_person = customer.contact_person || '';
    form.tax_id = customer.tax_id || '';
    form.shipping_address = customer.shipping_address || '';

    sameAsBilling.value = !customer.shipping_address || (customer.shipping_address === customer.address);
}

function toggleSameAsBilling() {
    sameAsBilling.value = !sameAsBilling.value;
    if (sameAsBilling.value) {
        form.shipping_address = '';
    }
}

// Action submission
function submit() {
    if (!form.name) {
        toaster.warning('Name is required');
        return;
    }
    if (!form.email) {
        toaster.warning('Email is required');
        return;
    }
    if (!form.mobile) {
        toaster.warning('Mobile number is required');
        return;
    }
    if (!form.address) {
        toaster.warning('Billing address is required');
        return;
    }

    // Sync address value if toggled
    const payload = {
        ...form.data(),
        shipping_address: sameAsBilling.value ? form.address : form.shipping_address,
    };

    router.post(targetUrl, payload, {
        onSuccess: () => {
            if (page.props.flash?.status !== false) {
                toaster.success(page.props.flash?.message || 'Partner record created successfully.');
                router.get('/CustomerPage');
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
.disabled-textarea {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-sync-addr {
    background: transparent;
    border: none;
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: color 0.15s;
}
.btn-sync-addr--active { color: #111827; }

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
