<template>
    <div class="clients-container">
        <!-- Header Section -->
        <header class="clients-header">
            <div>
                <h1 class="clients-title">Business Partners</h1>
                <p class="clients-subtitle">Manage your client relationships, track sales performance, and view activity reports.</p>
            </div>
            <button
                @click="openAddModal"
                class="btn-add-partner animate-btn"
                id="add-client-btn"
            >
                <i class="fa fa-plus-circle me-2"></i>
                Add New Partner
            </button>
        </header>

        <!-- Search and Action Bar -->
        <div class="search-bar-wrap mb-4">
            <i class="fa fa-search search-icon"></i>
            <input
                type="text"
                placeholder="Search business partners by name, email, or contact person..."
                v-model="searchTerm"
                class="search-input"
            />
            <span v-if="searchTerm" @click="searchTerm = ''" class="clear-search-btn">
                <i class="fa fa-times"></i>
            </span>
        </div>

        <!-- Clients Grid -->
        <div v-if="filteredClients.length > 0" class="clients-grid" id="clients-grid">
            <div
                v-for="client in filteredClients"
                :key="client.id"
                class="client-card animate-card"
            >
                <!-- Card Header -->
                <div class="client-card__header">
                    <div class="client-card__avatar-wrap">
                        <div class="client-card__avatar">
                            {{ client.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="client-card__meta">
                            <h3 class="client-card__name" :title="client.name">{{ client.name }}</h3>
                            <span class="client-card__contact">
                                <i class="fa fa-user me-1 text-muted"></i>
                                {{ client.contact_person || 'No Contact Person' }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions Dropdown -->
                    <div class="dropdown position-relative">
                        <button
                            @click="toggleDropdown(client.id)"
                            class="btn-more"
                            aria-label="Actions menu"
                        >
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <transition name="dropdown-fade">
                            <div v-if="activeDropdownId === client.id" class="dropdown-menu-custom">
                                <div class="dropdown-menu-backdrop" @click="closeDropdown"></div>
                                <button @click="openEditModal(client)" class="dropdown-item-custom text-dark">
                                    <i class="fa fa-edit me-2 text-primary"></i> Edit Partner
                                </button>
                                <div class="dropdown-divider"></div>
                                <button @click="confirmDelete(client)" class="dropdown-item-custom text-danger">
                                    <i class="fa fa-trash-alt me-2"></i> Delete Partner
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Card Body - Stats -->
                <div class="client-card__stats-grid">
                    <div class="stat-box">
                        <span class="stat-box__label"><i class="fa fa-file-invoice me-1"></i> Invoices</span>
                        <p class="stat-box__val">{{ client.stats?.invoiceCount || 0 }}</p>
                    </div>
                    <div class="stat-box stat-box--rose">
                        <span class="stat-box__label"><i class="fa fa-exclamation-circle me-1"></i> Outstanding</span>
                        <p class="stat-box__val text-rose">{{ formatCurrency(client.stats?.outstanding || 0) }}</p>
                    </div>
                    <div class="stat-box stat-box--emerald col-span-2">
                        <span class="stat-box__label"><i class="fa fa-chart-line me-1"></i> Total Volume</span>
                        <p class="stat-box__val text-emerald">{{ formatCurrency(client.stats?.totalBusiness || 0) }}</p>
                    </div>
                </div>

                <!-- Quick Details -->
                <div class="client-card__details">
                    <div class="detail-row">
                        <i class="fa fa-envelope detail-icon"></i>
                        <span class="detail-text" :title="client.email">{{ client.email || 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <i class="fa fa-phone detail-icon"></i>
                        <span class="detail-text">{{ client.mobile || 'N/A' }}</span>
                    </div>
                </div>

                <!-- Report Trigger Buttons -->
                <div class="client-card__reports pt-3 border-top">
                    <button @click="openReport(client, 'monthly')" class="report-trigger-btn">
                        <span>Monthly Sales Report</span>
                        <span class="report-trigger-val">{{ formatCurrency(client.stats?.monthlyBusiness || 0) }}</span>
                    </button>
                    <button @click="openReport(client, 'yearly')" class="report-trigger-btn">
                        <span>Yearly Sales Report</span>
                        <span class="report-trigger-val">{{ formatCurrency(client.stats?.yearlyBusiness || 0) }}</span>
                    </button>
                </div>

                <!-- Addresses Row -->
                <div class="client-card__addresses pt-3 border-top">
                    <div class="address-col">
                        <h4 class="address-title"><i class="fa fa-map-marked-alt me-1"></i> Billing Address</h4>
                        <p class="address-text">{{ client.address }}</p>
                    </div>
                    <div class="address-col">
                        <h4 class="address-title"><i class="fa fa-truck me-1"></i> Shipping Address</h4>
                        <p class="address-text">{{ client.shipping_address || client.address }}</p>
                    </div>
                </div>

                <!-- Footer details -->
                <div class="client-card__footer">
                    <span class="tax-badge">TAX ID: {{ client.tax_id || 'NOT SET' }}</span>
                    <span class="since-text">PARTNER SINCE {{ formatDate(client.created_at) }}</span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="clients-empty-state">
            <div class="empty-icon-wrap">
                <i class="fa fa-users"></i>
            </div>
            <h3 class="empty-title">No Business Partners Found</h3>
            <p class="empty-subtitle">Try adjusting your search keywords or register a new business partner to get started.</p>
            <button @click="openAddModal" class="btn btn-success px-4 py-2 mt-2 rounded-3">
                <i class="fa fa-plus me-1"></i> Add Partner
            </button>
        </div>

        <!-- Report Modal -->
        <transition name="modal-fade">
            <div v-if="reportConfig.isOpen" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--large">
                    <!-- Modal Header -->
                    <div class="modal-header-custom bg-light">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa fa-calendar-alt text-primary fs-5"></i>
                                <h3 class="modal-title-custom text-dark mb-0">
                                    {{ reportConfig.type === 'monthly' ? 'Monthly' : 'Yearly' }} Partner Report
                                </h3>
                            </div>
                            <p class="modal-subtitle-custom text-muted mb-0">
                                Detailed activity and invoicing overview for <strong>{{ reportConfig.client?.name }}</strong>
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button
                                @click="downloadPDF"
                                class="btn-pdf-download animate-btn"
                                :disabled="isDownloadingPdf"
                            >
                                <i v-if="isDownloadingPdf" class="fa fa-spinner fa-spin me-2"></i>
                                <i v-else class="fa fa-download me-2"></i>
                                Download PDF Report
                            </button>
                            <button @click="closeReport" class="btn-close-modal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body-custom p-4">
                        <div v-if="isLoadingReport" class="d-flex flex-column align-items-center justify-content-center py-5">
                            <div class="spinner-border text-success mb-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted">Loading partner invoice details...</p>
                        </div>
                        <div v-else>
                            <!-- Mini Stat Cards -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="report-stat-card">
                                        <span class="report-stat-label">Total Invoices</span>
                                        <p class="report-stat-val">{{ reportInvoices.length }}</p>
                                        <span class="report-stat-sub">Issued during this period</span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="report-stat-card report-stat-card--emerald">
                                        <span class="report-stat-label">Total Revenue Generated</span>
                                        <p class="report-stat-val text-emerald">{{ formatCurrency(reportTotalAmount) }}</p>
                                        <span class="report-stat-sub">Sum of all transaction values</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Table list -->
                            <div class="table-responsive-custom">
                                <table class="table-custom">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>PR/PO Number</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th class="text-end">Payable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="inv in reportInvoices" :key="inv.id">
                                            <td class="font-bold text-dark">{{ inv.invoiceNo }}</td>
                                            <td class="text-muted">{{ inv.prNumber || 'N/A' }}</td>
                                            <td class="text-muted">{{ inv.date }}</td>
                                            <td class="text-muted">{{ formatCurrency(inv.total) }}</td>
                                            <td class="text-end font-bold text-success">{{ formatCurrency(inv.payable) }}</td>
                                        </tr>
                                        <tr v-if="reportInvoices.length === 0">
                                            <td colspan="5" class="table-empty-row text-center py-4 text-muted italic">
                                                No invoicing activity logged for this client during the selected timeframe.
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

        <!-- Partner Form Modal (Add / Edit) -->
        <transition name="modal-fade">
            <div v-if="isAdding" class="modal-backdrop-custom">
                <div class="modal-container-custom">
                    <!-- Modal Header -->
                    <div class="modal-header-custom">
                        <div>
                            <h3 class="modal-title-custom">
                                {{ isEditing ? 'Edit Partner Profile' : 'Register Business Partner' }}
                            </h3>
                            <p class="modal-subtitle-custom text-muted">
                                {{ isEditing ? `Updating information for ${form.name}` : 'Create a new client profile in your dashboard directory.' }}
                            </p>
                        </div>
                        <button @click="closeFormModal" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form @submit.prevent="submitForm" class="modal-form-custom">
                        <div class="row g-3 p-4 overflow-auto" style="max-height: 60vh;">
                            <!-- Business Name -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Business Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    required
                                    placeholder="e.g. Acme Corporation"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Contact Person -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Contact Person</label>
                                <input
                                    type="text"
                                    v-model="form.contact_person"
                                    placeholder="e.g. John Doe"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Email Address <span class="text-danger">*</span></label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    required
                                    placeholder="partner@company.com"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    v-model="form.mobile"
                                    required
                                    placeholder="e.g. +1234567890"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Tax ID -->
                            <div class="col-md-12">
                                <label class="form-label-custom">Tax Registration ID (VAT/TAX)</label>
                                <input
                                    type="text"
                                    v-model="form.tax_id"
                                    placeholder="e.g. TAX-987654"
                                    class="form-input-custom"
                                />
                            </div>

                            <!-- Address Sync Row -->
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
                                    v-model="form.address"
                                    required
                                    placeholder="Enter full physical billing address..."
                                    class="form-textarea-custom"
                                ></textarea>
                            </div>

                            <!-- Shipping Address -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Shipping Address</label>
                                <textarea
                                    v-model="form.shipping_address"
                                    :disabled="sameAsBilling"
                                    :placeholder="sameAsBilling ? 'Same as billing address' : 'Enter full physical shipping address...'"
                                    class="form-textarea-custom"
                                    :class="{ 'disabled-textarea': sameAsBilling }"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer-custom">
                            <button
                                type="button"
                                @click="closeFormModal"
                                class="btn-discard"
                            >
                                Discard
                            </button>
                            <button
                                type="submit"
                                class="btn-save-partner animate-btn"
                                :disabled="form.processing"
                            >
                                <i v-if="form.processing" class="fa fa-spinner fa-spin me-2"></i>
                                {{ isEditing ? 'Save Changes' : 'Register Partner' }}
                            </button>
                        </div>
                    </form>
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
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

// Toaster Notification
const toaster = createToaster({ position: 'top-right' });

// Page props
const page = usePage();
const clients = computed(() => page.props.customers || []);

// State
const searchTerm = ref('');
const activeDropdownId = ref(null);

// Form modal state
const isAdding = ref(false);
const isEditing = ref(false);
const editingClientId = ref(null);
const sameAsBilling = ref(true);

const form = useForm({
    id: null,
    name: '',
    email: '',
    mobile: '',
    address: '',
    contact_person: '',
    tax_id: '',
    shipping_address: '',
});

// Report modal state
const reportConfig = ref({
    isOpen: false,
    client: null,
    type: 'monthly',
});
const isLoadingReport = ref(false);
const reportInvoices = ref([]);
const reportTotalAmount = ref(0);
const isDownloadingPdf = ref(false);

// Filter Clients
const filteredClients = computed(() => {
    const q = searchTerm.value.trim().toLowerCase();
    if (!q) return clients.value;
    return clients.value.filter(c =>
        c.name.toLowerCase().includes(q) ||
        (c.email && c.email.toLowerCase().includes(q)) ||
        (c.contact_person && c.contact_person.toLowerCase().includes(q))
    );
});

// Helper formatters
function formatCurrency(value) {
    const num = parseFloat(value) || 0;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Action dropdown
function toggleDropdown(id) {
    activeDropdownId.value = activeDropdownId.value === id ? null : id;
}

function closeDropdown() {
    activeDropdownId.value = null;
}

// Form Modals trigger
function openAddModal() {
    form.reset();
    isEditing.value = false;
    editingClientId.value = null;
    sameAsBilling.value = true;
    isAdding.value = true;
    closeDropdown();
}

function openEditModal(client) {
    form.id = client.id;
    form.name = client.name;
    form.email = client.email;
    form.mobile = client.mobile;
    form.address = client.address;
    form.contact_person = client.contact_person;
    form.tax_id = client.tax_id;
    form.shipping_address = client.shipping_address;

    sameAsBilling.value = !client.shipping_address || (client.shipping_address === client.address);
    isEditing.value = true;
    editingClientId.value = client.id;
    isAdding.value = true;
    closeDropdown();
}

function closeFormModal() {
    isAdding.value = false;
    isEditing.value = false;
    editingClientId.value = null;
    form.reset();
}

function toggleSameAsBilling() {
    sameAsBilling.value = !sameAsBilling.value;
    if (sameAsBilling.value) {
        form.shipping_address = '';
    }
}

// Form submit
function submitForm() {
    const finalUrl = isEditing.value ? '/customer-update' : '/create-customer';

    // If sameAsBilling is toggled, sync address fields
    const payload = {
        ...form.data(),
        shipping_address: sameAsBilling.value ? form.address : form.shipping_address,
    };

    router.post(finalUrl, payload, {
        onSuccess: () => {
            if (page.props.flash?.status !== false) {
                toaster.success(page.props.flash?.message || 'Action executed successfully.');
                closeFormModal();
            } else {
                toaster.error(page.props.flash?.message || 'An error occurred.');
            }
        },
        onError: () => {
            toaster.error('Please verify the input fields and try again.');
        }
    });
}

// Delete client
function confirmDelete(client) {
    closeDropdown();
    const txt = `Are you sure you want to delete ${client.name}? All associated record references will remain but the client will be removed from the directory.`;
    if (confirm(txt)) {
        router.get(`/customer-delete/${client.id}`, {
            onSuccess: () => {
                toaster.success('Partner deleted successfully');
            },
            onError: () => {
                toaster.error('Failed to delete partner.');
            }
        });
    }
}

// Reports
async function openReport(client, type) {
    reportConfig.value = {
        isOpen: true,
        client,
        type,
    };
    isLoadingReport.value = true;

    try {
        const response = await axios.get('/customer-invoice-report', {
            params: {
                customer_id: client.id,
                type,
            }
        });
        if (response.data?.status === 'Success') {
            reportInvoices.value = response.data.invoices || [];
            reportTotalAmount.value = parseFloat(response.data.total) || 0;
        } else {
            toaster.error('Failed to load report data.');
        }
    } catch (e) {
        console.error(e);
        toaster.error('Error connecting to Server.');
    } finally {
        isLoadingReport.value = false;
    }
}

function closeReport() {
    reportConfig.value = { isOpen: false, client: null, type: 'monthly' };
    reportInvoices.value = [];
    reportTotalAmount.value = 0;
}

// Download PDF
function downloadPDF() {
    const client = reportConfig.value.client;
    if (!client) return;

    isDownloadingPdf.value = true;

    try {
        const doc = new jsPDF();
        const typeStr = reportConfig.value.type === 'monthly' ? 'Monthly' : 'Yearly';
        const title = `${client.name} - ${typeStr} Sales Report`;

        const dateRange = reportConfig.value.type === 'monthly'
            ? new Date().toLocaleDateString(undefined, { month: 'long', year: 'numeric' })
            : new Date().getFullYear().toString();

        // PDF Styling Header
        doc.setFontSize(22);
        doc.setTextColor(26, 26, 46);
        doc.text(title, 14, 22);

        doc.setFontSize(11);
        doc.setTextColor(107, 114, 128);
        doc.text(`Reporting Period: ${dateRange}`, 14, 30);
        doc.text(`Generated on: ${new Date().toLocaleString()}`, 14, 36);

        // Client info sidebar
        doc.setFontSize(10);
        doc.setTextColor(55, 65, 81);
        doc.text(`Contact: ${client.contact_person || 'N/A'}`, 14, 44);
        doc.text(`Tax ID: ${client.tax_id || 'N/A'}`, 14, 49);

        const tableData = reportInvoices.value.map(inv => [
            inv.invoiceNo,
            inv.prNumber || 'N/A',
            inv.date,
            formatCurrency(inv.total),
            formatCurrency(inv.payable)
        ]);

        autoTable(doc, {
            startY: 56,
            head: [['Invoice No', 'PR / PO Number', 'Date', 'Gross Total', 'Net Payable']],
            body: tableData,
            foot: [['', '', '', 'Total Revenue', formatCurrency(reportTotalAmount.value)]],
            theme: 'grid',
            headStyles: { fillColor: [26, 26, 46], textColor: [255, 255, 255], fontStyle: 'bold' },
            footStyles: { fillColor: [243, 244, 246], textColor: [17, 24, 39], fontStyle: 'bold' },
            styles: { font: 'helvetica', fontSize: 9 }
        });

        const filename = `${client.name.replace(/\s+/g, '_')}_${reportConfig.value.type}_report.pdf`;
        doc.save(filename);
        toaster.success('PDF downloaded successfully!');
    } catch (e) {
        console.error(e);
        toaster.error('Error generating PDF.');
    } finally {
        isDownloadingPdf.value = false;
    }
}
</script>

<style scoped>
/* ── Container ───────────────────────────────────────────────────────────── */
.clients-container {
    padding: 24px 20px 40px;
    animation: fadeEffect 0.35s ease both;
}

@keyframes fadeEffect {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Header ──────────────────────────────────────────────────────────────── */
.clients-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 28px;
}
.clients-title {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px;
    letter-spacing: -0.4px;
}
.clients-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    max-width: 600px;
    line-height: 1.5;
}

.btn-add-partner {
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
.btn-add-partner:hover {
    background: #199e5e;
    box-shadow: 0 6px 18px rgba(33, 191, 115, 0.35);
}
.btn-add-partner:active {
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

/* ── Clients Grid ────────────────────────────────────────────────────────── */
.clients-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 991px) {
    .clients-grid { grid-template-columns: 1fr; }
}

/* Client Card */
.client-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.client-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

/* Card Header */
.client-card__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}
.client-card__avatar-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}
.client-card__avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #21bf73, #56d598);
    color: #ffffff;
    font-size: 18px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border-radius: 14px;
    box-shadow: 0 4px 10px rgba(33, 191, 115, 0.2);
    flex-shrink: 0;
}
.client-card__meta { min-width: 0; }
.client-card__name {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.client-card__contact {
    font-size: 11px;
    font-weight: 500;
    color: #6b7280;
}

.btn-more {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: none; background: transparent;
    display: flex; align-items: center; justify-content: center;
    color: #9ca3af;
    cursor: pointer;
    transition: background 0.18s, color 0.18s;
}
.btn-more:hover {
    background: #f3f4f6;
    color: #111827;
}

/* Dropdown Menu Custom */
.dropdown-menu-custom {
    position: absolute;
    right: 0; top: 100%;
    margin-top: 6px;
    width: 170px;
    background: #ffffff;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    z-index: 100;
    overflow: hidden;
    padding: 4px 0;
}
.dropdown-menu-backdrop {
    position: fixed;
    inset: 0;
    z-index: -1;
}
.dropdown-item-custom {
    width: 100%;
    border: none;
    background: transparent;
    padding: 8px 14px;
    text-align: left;
    font-size: 12px;
    font-weight: 500;
    display: flex; align-items: center;
    cursor: pointer;
    transition: background 0.15s;
}
.dropdown-item-custom:hover {
    background: #f9fafb;
}
.dropdown-divider {
    height: 1px;
    background: #f3f4f6;
    margin: 4px 0;
}

/* Card Stats */
.client-card__stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.col-span-2 { grid-column: span 2; }

.stat-box {
    background: #f9fafb;
    border: 1px solid rgba(0,0,0,0.03);
    border-radius: 12px;
    padding: 10px 14px;
}
.stat-box__label {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 3px;
}
.stat-box__val {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-box--rose { background: rgba(239, 68, 68, 0.03); }
.stat-box--emerald { background: rgba(33, 191, 115, 0.03); }
.text-rose { color: #dc2626; }
.text-emerald { color: #16a34a; }

/* Details row */
.client-card__details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.detail-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
}
.detail-icon {
    width: 14px;
    color: #9ca3af;
    text-align: center;
}
.detail-text {
    color: #4b5563;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Reports */
.client-card__reports {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.report-trigger-btn {
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    padding: 4px 0;
    cursor: pointer;
    transition: color 0.15s;
}
.report-trigger-btn:hover {
    color: #111827;
    text-decoration: underline;
}
.report-trigger-val {
    color: #111827;
    font-weight: 700;
}

/* Addresses block */
.client-card__addresses {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.address-col { min-width: 0; }
.address-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 5px;
    letter-spacing: 0.02em;
}
.address-text {
    font-size: 11px;
    color: #4b5563;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 30px;
}

/* Footer details */
.client-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10px;
    font-weight: 700;
    padding-top: 6px;
}
.tax-badge {
    color: #9ca3af;
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.02em;
}
.since-text {
    color: #9ca3af;
    letter-spacing: 0.02em;
}

/* ── Empty State ─────────────────────────────────────────────────────────── */
.clients-empty-state {
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

/* ── Modal Layout ────────────────────────────────────────────────────────── */
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
    max-width: 640px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.modal-container-custom--large {
    max-width: 820px;
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

/* Report Stat Cards */
.report-stat-card {
    background: #f9fafb;
    border: 1px solid rgba(0,0,0,0.04);
    border-radius: 16px;
    padding: 16px 20px;
}
.report-stat-card--emerald { background: rgba(33, 191, 115, 0.03); }
.report-stat-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.report-stat-val {
    font-size: 24px;
    font-weight: 800;
    color: #111827;
    margin: 0 0 3px;
    letter-spacing: -0.5px;
}
.report-stat-sub {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
}

/* Custom Table */
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
}
.table-custom tr:last-child td { border-bottom: none; }
.table-custom tr:hover td { background: #f9fafb; }

.font-bold { font-weight: 700; }
.italic { font-style: italic; }

.btn-pdf-download {
    background: #111827;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    transition: background 0.18s;
}
.btn-pdf-download:hover { background: #1f2937; }
.btn-pdf-download:disabled { opacity: 0.6; cursor: not-allowed; }

/* Form Elements */
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
    height: 86px;
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

.btn-save-partner {
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
.btn-save-partner:hover { background: #1f2937; }
.btn-save-partner:active { transform: scale(0.97); }

/* Animation Utility */
.animate-btn { transition: transform 0.12s ease; }
.animate-btn:active { transform: scale(0.97); }

/* Fades */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active { transition: opacity 0.15s, transform 0.15s; }
.dropdown-fade-enter-from,
.dropdown-fade-leave-to { opacity: 0; transform: translateY(-4px); }

.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.22s; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
</style>
