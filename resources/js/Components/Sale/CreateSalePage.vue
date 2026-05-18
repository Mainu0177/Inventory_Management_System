<template>
    <div class="sales-workspace animate-in fade-in duration-300">
        <!-- Dashboard Header -->
        <header class="sales-header mb-4">
            <div>
                <h1 class="sales-title">Sales & Proposals Workspace</h1>
                <p class="sales-subtitle">Manage professional customer quotes, convert proposals to invoices, and track delivery challan statuses.</p>
            </div>
            <button
                @click="openCreateModal"
                class="btn-new-proposal animate-btn"
                id="create-proposal-btn"
            >
                <i class="fa fa-plus-circle me-2"></i>
                Create New Proposal
            </button>
        </header>

        <!-- Navigation Tabs -->
        <div class="sales-tabs-nav mb-4">
            <button
                @click="activeTab = 'quotations'"
                class="tab-nav-btn"
                :class="{ 'tab-nav-btn--active': activeTab === 'quotations' }"
            >
                <i class="fa fa-file-invoice me-2"></i> Quotations ({{ quotations.length }})
            </button>
            <button
                @click="activeTab = 'challans'"
                class="tab-nav-btn"
                :class="{ 'tab-nav-btn--active': activeTab === 'challans' }"
            >
                <i class="fa fa-truck me-2"></i> Delivery Challans ({{ deliveryChallans.length }})
            </button>
        </div>

        <!-- ── TAB 1: QUOTATIONS ────────────────────────────────────────────────── -->
        <div v-if="activeTab === 'quotations'" class="tab-content">
            <!-- Filter & Search Controls -->
            <div class="filter-bar mb-4">
                <div class="search-input-wrap">
                    <i class="fa fa-search search-icon"></i>
                    <input
                        type="text"
                        placeholder="Search by quote number, client name, or PR reference..."
                        v-model="searchTerm"
                        class="search-input"
                    />
                    <span v-if="searchTerm" @click="searchTerm = ''" class="clear-search">
                        <i class="fa fa-times"></i>
                    </span>
                </div>
                
                <!-- Status Filter Carousel -->
                <div class="status-filters">
                    <button
                        v-for="status in ['All', 'Draft', 'Sent', 'Invoiced', 'Final', 'Rejected', 'Cancelled']"
                        :key="status"
                        @click="statusFilter = status"
                        class="status-filter-btn"
                        :class="{ 'status-filter-btn--active': statusFilter === status }"
                    >
                        {{ status }}
                    </button>
                </div>

                <button @click="downloadSummaryReport" class="btn-summary animate-btn">
                    <i class="fa fa-download me-1"></i> Summary
                </button>
            </div>

            <!-- Quotations Grid -->
            <div v-if="filteredQuotations.length > 0" class="row g-4">
                <div v-for="q in filteredQuotations" :key="q.id" class="col-md-6 col-xxl-6">
                    <div class="quote-card">
                        <div class="quote-card__top">
                            <div class="quote-card__icon-wrap" :class="getStatusClass(q.status)">
                                <i class="fa" :class="q.status === 'Invoiced' ? 'fa-check-circle' : 'fa-file-alt'"></i>
                            </div>
                            <div class="quote-card__meta">
                                <h3 class="quote-card__number">{{ q.quotation_no }}</h3>
                                <p class="quote-card__client">Client: <strong>{{ q.customer?.name || 'Unknown' }}</strong></p>
                                <div class="quote-card__submeta">
                                    <span v-if="q.pr_no">PR: {{ q.pr_no }}</span>
                                    <span class="created-by">Created By: {{ q.created_by }}</span>
                                </div>
                            </div>
                            <div class="quote-card__value-wrap">
                                <span class="quote-card__value">{{ formatCurrency(q.total_amount) }}</span>
                                <span class="quote-card__date">
                                    <i class="fa fa-calendar-alt me-1"></i> Expires {{ formatDate(q.valid_until) }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer actions -->
                        <div class="quote-card__bottom">
                            <div class="action-icons">
                                <button @click="triggerPreview(q)" class="action-icon-btn" title="Live Preview">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button @click="generatePDF(q)" class="action-icon-btn" title="Download PDF">
                                    <i class="fa fa-file-pdf"></i>
                                </button>
                                <button @click="shareEmail(q)" class="action-icon-btn" title="Email Share">
                                    <i class="fa fa-envelope"></i>
                                </button>
                                <button @click="shareWhatsApp(q)" class="action-icon-btn text-success" title="WhatsApp Share">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                                <button @click="openEditModal(q)" class="action-icon-btn text-primary" title="Edit Proposal">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button @click="deleteQuotation(q)" class="action-icon-btn text-danger" title="Delete Quote">
                                    <i class="fa fa-trash-alt"></i>
                                </button>
                            </div>

                            <div class="status-actions">
                                <!-- Convert to Invoice button -->
                                <button
                                    v-if="q.status === 'Draft' || q.status === 'Sent'"
                                    @click="convertToInvoice(q)"
                                    class="btn-convert-invoice animate-btn"
                                >
                                    Convert to Invoice <i class="fa fa-arrow-right ms-1"></i>
                                </button>

                                <!-- Create Delivery Challan -->
                                <button
                                    v-if="q.status === 'Sent' || q.status === 'Draft'"
                                    @click="createDeliveryChallan(q)"
                                    class="btn-challan animate-btn"
                                >
                                    Create Challan <i class="fa fa-truck ms-1"></i>
                                </button>

                                <!-- Dropdown Status Toggle -->
                                <select
                                    :value="q.status"
                                    @change="updateStatus(q.id, $event.target.value)"
                                    class="status-select"
                                    :class="getStatusBadgeClass(q.status)"
                                >
                                    <option v-for="s in ['Draft', 'Sent', 'Invoiced', 'Final', 'Rejected', 'Cancelled']" :key="s" :value="s">
                                        {{ s }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="empty-state">
                <div class="empty-icon-wrap">
                    <i class="fa fa-folder-open"></i>
                </div>
                <h3>No Proposals Registered</h3>
                <p>Start registering custom professional quotations or adjust search constraints to list matches.</p>
                <button @click="openCreateModal" class="btn btn-success px-4 py-2 mt-2 rounded-3">
                    <i class="fa fa-plus me-1"></i> Create New Proposal
                </button>
            </div>
        </div>

        <!-- ── TAB 2: DELIVERY CHALLANS ────────────────────────────────────────── -->
        <div v-if="activeTab === 'challans'" class="tab-content">
            <div v-if="deliveryChallans.length > 0" class="challan-table-wrap">
                <div class="table-responsive">
                    <table class="challan-table">
                        <thead>
                            <tr>
                                <th>Challan Number</th>
                                <th>Client Name</th>
                                <th>PR No</th>
                                <th>Related Quote</th>
                                <th>Status</th>
                                <th>Delivered At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dc in deliveryChallans" :key="dc.id" class="challan-row">
                                <td class="font-bold text-dark">{{ dc.challan_no }}</td>
                                <td>{{ dc.customer?.name || 'Unknown Client' }}</td>
                                <td><span class="mono-badge">{{ dc.pr_no || 'N/A' }}</span></td>
                                <td><span class="mono-badge bg-light">{{ dc.quotation_no }}</span></td>
                                <td>
                                    <span
                                        class="challan-status-badge"
                                        :class="dc.status === 'Delivered' ? 'challan-status-badge--delivered' : 'challan-status-badge--pending'"
                                    >
                                        {{ dc.status }}
                                    </span>
                                </td>
                                <td>{{ dc.delivered_at ? formatDate(dc.delivered_at) : 'Not Delivered' }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button
                                            v-if="dc.status === 'Pending'"
                                            @click="markAsDelivered(dc)"
                                            class="btn-mark-delivered animate-btn"
                                        >
                                            <i class="fa fa-check me-1"></i> Deliver & Subtract Stock
                                        </button>
                                        <button
                                            @click="printChallanPDF(dc)"
                                            class="btn btn-outline-secondary btn-sm rounded-3 px-3"
                                            title="Print Challan PDF"
                                        >
                                            <i class="fa fa-print"></i> PDF
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty Challans State -->
            <div v-else class="empty-state">
                <div class="empty-icon-wrap">
                    <i class="fa fa-truck-loading"></i>
                </div>
                <h3>No Delivery Challans Found</h3>
                <p>Create Delivery Challans from approved Quotations in the tab above to manage items delivery dispatch.</p>
            </div>
        </div>

        <!-- ── CREATE / EDIT PROPOSAL MODAL ──────────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="isAdding" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--giant">
                    <div class="modal-header-custom">
                        <div>
                            <h3 class="modal-title-custom">{{ isEditing ? 'Edit Proposal Detail' : 'Draft New Business Proposal' }}</h3>
                            <p class="modal-subtitle-custom text-muted">
                                {{ isEditing ? `Modifying properties for proposal #${editQuotationNo}` : 'Generate professional quotes, margins, discounts, and terms.' }}
                            </p>
                        </div>
                        <button @click="closeModal" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal Body Grid -->
                    <div class="modal-body-custom overflow-auto p-4" style="max-height: 80vh;">
                        <div class="row g-4">
                            <!-- Card 1: Customer Selection -->
                            <div class="col-md-4">
                                <div class="form-section-card h-100">
                                    <div class="form-section-header">
                                        <label class="section-label">Select Customer / Client <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="p-3">
                                        <select
                                            v-model="form.customer_id"
                                            class="form-select-custom w-100"
                                            required
                                        >
                                            <option value="">Choose Customer...</option>
                                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Dates -->
                            <div class="col-md-4">
                                <div class="form-section-card h-100">
                                    <div class="form-section-header">
                                        <label class="section-label">Proposal Dates</label>
                                    </div>
                                    <div class="p-3 space-y-3">
                                        <div>
                                            <span class="sublabel">Quote Date</span>
                                            <input type="date" v-model="quoteDate" class="form-input-custom mt-1" />
                                        </div>
                                        <div class="mt-2">
                                            <span class="sublabel">Valid Until</span>
                                            <input type="date" v-model="validUntil" class="form-input-custom mt-1" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Reference Info -->
                            <div class="col-md-4">
                                <div class="form-section-card h-100">
                                    <div class="form-section-header">
                                        <label class="section-label">References & Code</label>
                                    </div>
                                    <div class="p-3 space-y-3">
                                        <div>
                                            <span class="sublabel">Purchase Requisition (PR No)</span>
                                            <input type="text" v-model="form.pr_no" placeholder="e.g. PR-9087" class="form-input-custom mt-1" />
                                        </div>
                                        <div class="mt-2">
                                            <span class="sublabel">Quotation Number</span>
                                            <input
                                                type="text"
                                                readOnly
                                                :value="isEditing ? editQuotationNo : 'QTN-AUTO-GENERATED'"
                                                class="form-input-custom bg-light mt-1 text-muted"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Primary Product Picker Entry Row -->
                            <div class="col-md-12">
                                <div class="entry-bar-wrapper">
                                    <div class="entry-bar">
                                        <!-- Product Picker Dropdown -->
                                        <div class="entry-col flex-[2]">
                                            <span class="entry-label">Search / Select Product</span>
                                            <select @change="onProductSelect($event.target.value)" class="entry-select">
                                                <option value="">Search inventory products...</option>
                                                <option v-for="p in products" :key="p.id" :value="p.id">
                                                    {{ p.name }} - SKU: {{ p.code }} (${{ p.price }})
                                                </option>
                                                <option value="manual">+ Register Manual Product</option>
                                            </select>
                                        </div>

                                        <div class="entry-col flex-1">
                                            <span class="entry-label">Item SKU</span>
                                            <input type="text" v-model="tempItem.code" class="entry-input font-mono" placeholder="SKU-CODE" />
                                        </div>

                                        <div class="entry-col w-20">
                                            <span class="entry-label">UOM</span>
                                            <input type="text" v-model="tempItem.uom" class="entry-input text-center" placeholder="pcs" />
                                        </div>

                                        <div class="entry-col w-20">
                                            <span class="entry-label">Qty</span>
                                            <input type="number" v-model.number="tempItem.qty" class="entry-input text-center font-bold" min="1" />
                                        </div>

                                        <div class="entry-col w-32">
                                            <span class="entry-label">Unit Cost ($)</span>
                                            <input type="number" step="0.01" v-model.number="tempItem.unit_price" class="entry-input font-bold" placeholder="0.00" />
                                        </div>

                                        <div class="entry-col w-24">
                                            <span class="entry-label">Margin %</span>
                                            <input type="number" v-model.number="tempItem.margin" class="entry-input text-center font-bold" />
                                        </div>
                                    </div>

                                    <!-- Description Bar + Add action -->
                                    <div class="entry-desc-row mt-2">
                                        <div class="flex-grow-1 px-3">
                                            <span class="entry-label">Item Custom Name / Description</span>
                                            <input
                                                type="text"
                                                v-model="tempItem.name"
                                                placeholder="Enter product title or custom specification details..."
                                                class="entry-input-large"
                                            />
                                        </div>
                                        <button
                                            type="button"
                                            @click="addItem"
                                            class="btn-add-item animate-btn me-2"
                                        >
                                            <i class="fa fa-plus me-1"></i> Add Line
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Lines Table -->
                            <div class="col-md-12">
                                <div class="table-responsive border rounded-3 overflow-hidden shadow-sm bg-white">
                                    <table class="table-lines table mb-0 align-middle">
                                        <thead class="bg-dark text-white font-bold">
                                            <tr>
                                                <th class="px-3 py-2">Item Code</th>
                                                <th class="px-3 py-2">Product Description</th>
                                                <th class="px-3 py-2 text-center w-20">UOM</th>
                                                <th class="px-3 py-2 text-center w-24">Quantity</th>
                                                <th class="px-3 py-2 text-center w-32">Unit Price ($)</th>
                                                <th class="px-3 py-2 text-center w-24">Margin %</th>
                                                <th class="px-3 py-2 text-end w-36">Total Price</th>
                                                <th class="px-3 py-2 text-center w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in form.items" :key="index">
                                                <td class="px-3 font-mono font-bold">{{ item.code || 'MANUAL' }}</td>
                                                <td class="px-3">
                                                    <input
                                                        type="text"
                                                        v-model="item.name"
                                                        @input="updateItemCalculations(index)"
                                                        class="form-control form-control-sm font-bold border-0 p-0 shadow-none bg-transparent"
                                                    />
                                                    <input
                                                        type="text"
                                                        v-model="item.description"
                                                        class="form-control form-control-sm text-muted text-xs border-0 p-0 mt-1 shadow-none bg-transparent"
                                                        placeholder="Add specifications..."
                                                    />
                                                </td>
                                                <td class="text-center px-3">
                                                    <span class="badge bg-light text-dark px-2 py-1 text-uppercase">{{ item.uom || 'pcs' }}</span>
                                                </td>
                                                <td class="text-center px-3">
                                                    <input
                                                        type="number"
                                                        v-model.number="item.qty"
                                                        @input="updateItemCalculations(index)"
                                                        class="form-control form-control-sm text-center font-bold"
                                                        min="1"
                                                    />
                                                </td>
                                                <td class="text-center px-3">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        v-model.number="item.unit_price"
                                                        @input="updateItemCalculations(index)"
                                                        class="form-control form-control-sm text-center font-bold"
                                                    />
                                                </td>
                                                <td class="text-center px-3">
                                                    <input
                                                        type="number"
                                                        v-model.number="item.margin"
                                                        @input="updateItemCalculations(index)"
                                                        class="form-control form-control-sm text-center"
                                                    />
                                                </td>
                                                <td class="text-end px-3 font-bold text-dark">
                                                    {{ formatCurrency(item.total_price) }}
                                                </td>
                                                <td class="text-center px-3">
                                                    <button @click="removeItem(index)" class="btn text-danger p-0" type="button">
                                                        <i class="fa fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="form.items.length === 0">
                                                <td colspan="8" class="text-center py-5 text-muted italic">
                                                    No line items added yet. Choose a product SKU to begin.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Calculations Section -->
                            <div class="col-md-7">
                                <div class="form-section-card p-4">
                                    <label class="section-label mb-2">Terms & Conditions</label>
                                    <textarea
                                        v-model="form.terms"
                                        placeholder="Specify specific conditions (e.g. 50% advance payment, delivery timeline within 10 days...)"
                                        class="form-textarea-custom leading-relaxed"
                                        style="height: 120px;"
                                    ></textarea>
                                </div>
                            </div>

                            <!-- Totals Card -->
                            <div class="col-md-5">
                                <div class="totals-summary-card">
                                    <div class="totals-row">
                                        <span>Subtotal Value</span>
                                        <strong>{{ formatCurrency(calculateSubtotal()) }}</strong>
                                    </div>
                                    <div class="totals-row border-bottom pb-3">
                                        <span>VAT (5% Default)</span>
                                        <strong>{{ formatCurrency(calculateVAT()) }}</strong>
                                    </div>
                                    <div class="totals-row pt-3">
                                        <div class="d-flex flex-column">
                                            <span class="text-success uppercase font-bold tracking-wide">Final Investment</span>
                                            <span class="totals-value-grand mt-1">{{ formatCurrency(calculateFinalTotal()) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-footer-custom bg-light">
                        <button type="button" @click="closeModal" class="btn-discard">Discard Changes</button>
                        <div class="d-flex gap-2">
                            <button
                                v-for="s in ['Draft', 'Sent', 'Final']"
                                :key="s"
                                @click="submitQuotation(s)"
                                class="btn-save-proposal animate-btn"
                                :class="getSubmitBtnClass(s)"
                            >
                                Save as {{ s }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- ── LIVE PREVIEW DRAWER ──────────────────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="showPreview && previewDoc" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--large">
                    <div class="modal-header-custom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-cube bg-dark text-white rounded-3 p-2">
                                <i class="fa fa-file-invoice fs-4"></i>
                            </div>
                            <div>
                                <h3 class="modal-title-custom">Live Proposal Preview</h3>
                                <p class="modal-subtitle-custom text-muted mb-0">{{ previewDoc.quotation_no }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button @click="generatePDF(previewDoc)" class="btn btn-success rounded-3 px-3 animate-btn">
                                <i class="fa fa-download me-1"></i> Download PDF
                            </button>
                            <button @click="showPreview = false" class="btn-close-modal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Printed Invoice sheet -->
                    <div class="modal-body-custom p-4 bg-light overflow-auto" style="max-height: 75vh;">
                        <div class="print-sheet mx-auto bg-white shadow-lg p-5 rounded-3">
                            <div class="print-top-accent"></div>
                            
                            <!-- Head -->
                            <div class="d-flex justify-content-between align-items-start mt-4 mb-5">
                                <div>
                                    <div class="print-brand">FACTORY ELECTRIC</div>
                                    <p class="print-company-text text-muted">Factory Solutions & Supplies Directory</p>
                                </div>
                                <div class="text-end">
                                    <p class="print-company-title">Factory Electric Solution</p>
                                    <p class="print-company-details">Industrial Area, Sector 5<br>Contact: support@factoryelectric.com</p>
                                </div>
                            </div>

                            <!-- Divider line -->
                            <div class="print-divider mb-4"></div>

                            <!-- Document details -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <h4 class="print-section-title">BILL TO:</h4>
                                    <p class="print-customer-name">{{ previewDoc.customer?.name || 'Unknown' }}</p>
                                    <p class="print-customer-email">{{ previewDoc.customer?.email || 'N/A' }}</p>
                                </div>
                                <div class="col-6 text-end">
                                    <h4 class="print-section-title">PROPOSAL SPECS:</h4>
                                    <p class="print-spec-text">No: <strong>{{ previewDoc.quotation_no }}</strong></p>
                                    <p class="print-spec-text">PR Number: {{ previewDoc.pr_no || 'N/A' }}</p>
                                    <p class="print-spec-text">Date: {{ formatDate(previewDoc.created_at) }}</p>
                                    <p class="print-spec-text">Expires: {{ formatDate(previewDoc.valid_until) }}</p>
                                    <p class="print-spec-text">Status: <span class="badge bg-dark text-uppercase">{{ previewDoc.status }}</span></p>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive rounded-2 border overflow-hidden mb-4">
                                <table class="table print-table mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-center w-20">UOM</th>
                                            <th class="text-center w-20">Qty</th>
                                            <th class="text-end w-32">Unit Price</th>
                                            <th class="text-end w-36">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, i) in previewDoc.quotation_products" :key="i">
                                            <td>
                                                <p class="font-bold text-dark mb-0">{{ item.name }}</p>
                                                <span class="text-xs text-muted">{{ item.description || '' }}</span>
                                            </td>
                                            <td class="text-center">{{ item.uom || 'pcs' }}</td>
                                            <td class="text-center font-bold">{{ item.qty }}</td>
                                            <td class="text-end font-bold">{{ formatCurrency(item.unit_price) }}</td>
                                            <td class="text-end font-bold text-dark">{{ formatCurrency(item.total_price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals -->
                            <div class="d-flex justify-content-end mb-5">
                                <div class="w-50 bg-light p-3 rounded-3 text-end">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Subtotal:</span>
                                        <span class="font-bold">{{ formatCurrency(previewDoc.total_amount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-top pt-2">
                                        <span class="text-success font-bold">Total Investment:</span>
                                        <span class="text-dark font-bold fs-5">{{ formatCurrency(previewDoc.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div v-if="previewDoc.terms" class="print-terms-box p-3 rounded-2 bg-light mb-5">
                                <h5 class="text-xs font-bold text-dark uppercase mb-1">TERMS & SPECIAL CONDITIONS</h5>
                                <p class="text-xs text-muted leading-relaxed mb-0">{{ previewDoc.terms }}</p>
                            </div>

                            <!-- Signatures -->
                            <div class="row pt-5 text-center">
                                <div class="col-6">
                                    <div class="border-top pt-2 text-xs font-bold text-muted uppercase">PREPARED BY</div>
                                    <p class="text-xs mt-1 text-dark">{{ previewDoc.created_by }}</p>
                                </div>
                                <div class="col-6">
                                    <div class="border-top pt-2 text-xs font-bold text-muted uppercase">CLIENT STAMP & ACCEPTANCE</div>
                                </div>
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
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const toaster = createToaster({ position: 'top-right' });
const page = usePage();

// Tabs state
const activeTab = ref('quotations');

// Props
const quotations = computed(() => page.props.quotations || []);
const deliveryChallans = computed(() => page.props.deliveryChallans || []);
const customers = computed(() => page.props.customers || []);
const products = computed(() => page.props.products || []);

// Search and Filters
const searchTerm = ref('');
const statusFilter = ref('All');

const filteredQuotations = computed(() => {
    return quotations.value.filter(q => {
        const matchesStatus = statusFilter.value === 'All' || q.status === statusFilter.value;
        const matchesSearch =
            q.quotation_no.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (q.customer && q.customer.name.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
            (q.pr_no && q.pr_no.toLowerCase().includes(searchTerm.value.toLowerCase()));
        return matchesStatus && matchesSearch;
    });
});

// Create/Edit Form State
const isAdding = ref(false);
const isEditing = ref(false);
const editQuotationId = ref(null);
const editQuotationNo = ref('');

const quoteDate = ref(new Date().toISOString().split('T')[0]);
const validUntil = ref('');

const form = useForm({
    customer_id: '',
    pr_no: '',
    total_amount: 0,
    status: 'Draft',
    valid_until: '',
    terms: 'Standard industrial payment terms apply.',
    items: [],
});

// Temp Item State
const tempItem = ref({
    product_id: '',
    code: '',
    name: '',
    description: '',
    uom: 'pcs',
    qty: 1,
    unit_price: 0,
    margin: 0,
    discount: 0,
    total_price: 0,
});

// Live Preview State
const showPreview = ref(false);
const previewDoc = ref(null);

// Helpers
function formatCurrency(val) {
    const num = parseFloat(val) || 0;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(val) {
    if (!val) return 'N/A';
    const date = new Date(val);
    if (isNaN(date.getTime())) return val;
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function getStatusClass(status) {
    return {
        'bg-emerald-50 text-emerald-600': status === 'Invoiced',
        'bg-rose-50 text-rose-600': status === 'Cancelled',
        'bg-blue-50 text-blue-600': status !== 'Invoiced' && status !== 'Cancelled',
    };
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'Invoiced': return 'status-select--invoiced';
        case 'Cancelled': return 'status-select--cancelled';
        case 'Sent': return 'status-select--sent';
        case 'Final': return 'status-select--final';
        case 'Rejected': return 'status-select--rejected';
        default: return 'status-select--draft';
    }
}

function getSubmitBtnClass(status) {
    switch (status) {
        case 'Sent': return 'btn-save-proposal--sent';
        case 'Final': return 'btn-save-proposal--final';
        default: return 'btn-save-proposal--draft';
    }
}

// Product Selector Change
function onProductSelect(val) {
    if (!val) return;
    if (val === 'manual') {
        tempItem.value = {
            product_id: 'manual',
            code: 'MANUAL-' + Math.floor(Math.random() * 9000 + 1000),
            name: 'Custom Product Name',
            description: '',
            uom: 'pcs',
            qty: 1,
            unit_price: 0,
            margin: 0,
            discount: 0,
            total_price: 0,
        };
        return;
    }

    const prod = products.value.find(p => p.id == val);
    if (prod) {
        tempItem.value = {
            product_id: prod.id,
            code: prod.code || 'SKU',
            name: prod.name,
            description: prod.description || '',
            uom: prod.uom || 'pcs',
            qty: 1,
            unit_price: parseFloat(prod.price) || 0,
            margin: 0,
            discount: 0,
            total_price: parseFloat(prod.price) || 0,
        };
    }
}

// Operations inside Creation modal
function addItem() {
    if (!tempItem.value.name) {
        toaster.warning('Please enter an item name or description');
        return;
    }

    // calculate total price
    const qty = parseInt(tempItem.value.qty) || 1;
    const unitPrice = parseFloat(tempItem.value.unit_price) || 0;
    const margin = parseInt(tempItem.value.margin) || 0;
    const discount = parseFloat(tempItem.value.discount) || 0;

    const priceWithMargin = unitPrice * (1 + margin / 100);
    const priceWithDiscount = priceWithMargin * (1 - discount / 100);
    const totalPrice = priceWithDiscount * qty;

    form.items.push({
        product_id: tempItem.value.product_id || 'manual',
        code: tempItem.value.code,
        name: tempItem.value.name,
        description: tempItem.value.description,
        uom: tempItem.value.uom,
        qty: qty,
        unit_price: unitPrice,
        margin: margin,
        discount: discount,
        total_price: totalPrice,
    });

    // Reset Temp item
    tempItem.value = {
        product_id: '',
        code: '',
        name: '',
        description: '',
        uom: 'pcs',
        qty: 1,
        unit_price: 0,
        margin: 0,
        discount: 0,
        total_price: 0,
    };
}

function updateItemCalculations(index) {
    const item = form.items[index];
    if (!item) return;

    const qty = parseInt(item.qty) || 1;
    const unitPrice = parseFloat(item.unit_price) || 0;
    const margin = parseInt(item.margin) || 0;
    const discount = parseFloat(item.discount) || 0;

    const priceWithMargin = unitPrice * (1 + margin / 100);
    const priceWithDiscount = priceWithMargin * (1 - discount / 100);
    item.total_price = priceWithDiscount * qty;
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function calculateSubtotal() {
    return form.items.reduce((sum, item) => sum + (item.total_price || 0), 0);
}

function calculateVAT() {
    return calculateSubtotal() * 0.05; // 5% default VAT
}

function calculateFinalTotal() {
    return calculateSubtotal() + calculateVAT();
}

// Modal open/close actions
function openCreateModal() {
    form.reset();
    quoteDate.value = new Date().toISOString().split('T')[0];
    validUntil.value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    isEditing.value = false;
    editQuotationId.value = null;
    editQuotationNo.value = '';
    isAdding.value = true;
}

function openEditModal(q) {
    form.id = q.id;
    form.customer_id = q.customer_id;
    form.pr_no = q.pr_no || '';
    form.total_amount = q.total_amount;
    form.status = q.status;
    form.terms = q.terms || '';

    quoteDate.value = q.created_at ? new Date(q.created_at).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
    validUntil.value = q.valid_until ? new Date(q.valid_until).toISOString().split('T')[0] : '';

    form.items = q.quotation_products.map(item => ({
        product_id: item.product_id || 'manual',
        code: item.code || '',
        name: item.name,
        description: item.description || '',
        uom: item.uom || 'pcs',
        qty: item.qty,
        unit_price: parseFloat(item.unit_price),
        margin: item.margin,
        discount: parseFloat(item.discount),
        total_price: parseFloat(item.total_price),
    }));

    isEditing.value = true;
    editQuotationId.value = q.id;
    editQuotationNo.value = q.quotation_no;
    isAdding.value = true;
}

function closeModal() {
    isAdding.value = false;
    isEditing.value = false;
    editQuotationId.value = null;
    editQuotationNo.value = '';
    form.reset();
}

// Actions submission
function submitQuotation(statusVal) {
    if (!form.customer_id) {
        toaster.warning('Please select a customer for this proposal');
        return;
    }
    if (form.items.length === 0) {
        toaster.warning('Please add at least one item line to submit.');
        return;
    }

    form.status = statusVal;
    form.total_amount = calculateFinalTotal();
    form.valid_until = validUntil.value;

    const url = isEditing.value ? '/update-quotation' : '/create-quotation';

    form.post(url, {
        onSuccess: () => {
            if (page.props.flash?.status !== false) {
                toaster.success(page.props.flash?.message || 'Quotation transaction completed successfully');
                closeModal();
            } else {
                toaster.error(page.props.flash?.message || 'An error occurred during submission.');
            }
        },
        onError: () => {
            toaster.error('Please verify all details and try again.');
        }
    });
}

function deleteQuotation(q) {
    if (confirm(`Are you sure you want to permanently delete quotation "${q.quotation_no}"?`)) {
        router.get(`/delete-quotation/${q.id}`, {
            onSuccess: () => {
                toaster.success('Quotation deleted successfully');
            }
        });
    }
}

// Async Operations
async function updateStatus(id, newStatus) {
    try {
        const response = await axios.post('/update-quotation-status', { id, status: newStatus });
        if (response.data.status === 'Success') {
            toaster.success(response.data.message || 'Status updated.');
            router.reload({ only: ['quotations'] });
        }
    } catch (e) {
        toaster.error(e.response?.data?.message || 'Error updating status.');
    }
}

async function convertToInvoice(q) {
    if (confirm(`Do you want to convert Proposal "${q.quotation_no}" into a standard client invoice?`)) {
        try {
            const response = await axios.post('/convert-to-invoice', { quotation_id: q.id });
            if (response.data.status === 'Success') {
                toaster.success(response.data.message || 'Converted to invoice.');
                router.reload();
            }
        } catch (e) {
            toaster.error(e.response?.data?.message || 'Error occurred during invoice conversion.');
        }
    }
}

async function createDeliveryChallan(q) {
    if (confirm(`Do you want to generate a Delivery Challan for Proposal "${q.quotation_no}"?`)) {
        try {
            const response = await axios.post('/create-delivery-challan', { quotation_id: q.id });
            if (response.data.status === 'Success') {
                toaster.success(response.data.message || 'Delivery Challan generated successfully.');
                router.reload();
            }
        } catch (e) {
            toaster.error(e.response?.data?.message || 'Error occurred generating Challan.');
        }
    }
}

async function markAsDelivered(dc) {
    if (confirm(`Mark Delivery Challan "${dc.challan_no}" as DELIVERED? This will automatically subtract items quantity from your product stock levels.`)) {
        try {
            const response = await axios.post('/mark-challan-delivered', { challan_id: dc.id });
            if (response.data.status === 'Success') {
                toaster.success(response.data.message || 'Stock updated successfully!');
                router.reload();
            }
        } catch (e) {
            toaster.error(e.response?.data?.message || 'Failed to update stock levels.');
        }
    }
}

// Live Preview
function triggerPreview(q) {
    previewDoc.value = q;
    showPreview.value = true;
}

// Email & WhatsApp sharing
function shareEmail(q) {
    const subject = `Proposal Quotation ${q.quotation_no} - Factory Electric`;
    const itemsText = q.quotation_products.map(i => `- ${i.name}: ${i.qty} x ${formatCurrency(i.unit_price)} = ${formatCurrency(i.total_price)}`).join('\n');
    const body = `Dear Customer,\n\nPlease find attached our official proposal quotation ${q.quotation_no}.\n\nItems:\n${itemsText}\n\nTotal Investment: ${formatCurrency(q.total_amount)}\nValid Until: ${formatDate(q.valid_until)}\n\nThank you for choosing Factory Electric.`;
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

function shareWhatsApp(q) {
    const itemsText = q.quotation_products.map(i => `• ${i.name}: ${i.qty} x ${formatCurrency(i.unit_price)}`).join('\n');
    const text = `*Proposal Quotation ${q.quotation_no}*\n\nClient: ${q.customer?.name || 'Unknown'}\n\n*Items:*\n${itemsText}\n\n*Total:* ${formatCurrency(q.total_amount)}\n*Expires:* ${formatDate(q.valid_until)}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}

// Generate PDF
function generatePDF(q) {
    const doc = new jsPDF();
    const primaryColor = [33, 191, 115]; // Healthy green signature

    doc.setFontSize(22);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("FACTORY ELECTRIC", 14, 25);

    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text("Factory Solutions & Supplies Directory", 14, 30);
    doc.text("Industrial Area, Sector 5\nContact: support@factoryelectric.com", 200, 20, { align: 'right' });

    doc.setFontSize(28);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    doc.text("PROPOSAL QUOTATION", 105, 55, { align: 'center' });

    doc.setFontSize(10);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    
    // Specifications
    doc.text("Quotation No:", 14, 75);
    doc.text("Date:", 14, 82);
    doc.text("Valid Until:", 14, 89);
    doc.text("Status:", 14, 96);
    
    doc.setFont("helvetica", "normal");
    doc.text(q.quotation_no, 45, 75);
    doc.text(formatDate(q.created_at), 45, 82);
    doc.text(formatDate(q.valid_until), 45, 89);
    doc.setFont("helvetica", "bold");
    doc.text(q.status.toUpperCase(), 45, 96);

    // Bill to
    doc.text("BILL TO:", 200, 75, { align: 'right' });
    doc.setFont("helvetica", "normal");
    doc.text(q.customer?.name || 'Unknown Client', 200, 82, { align: 'right' });
    doc.text(q.customer?.email || 'N/A', 200, 88, { align: 'right' });

    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setLineWidth(1);
    doc.line(14, 105, 196, 105);

    // Table of items
    autoTable(doc, {
        startY: 112,
        head: [['Item Code', 'Description', 'UOM', 'Qty', 'Unit Price', 'Total']],
        body: q.quotation_products.map(item => [
            item.code || 'MANUAL',
            `${item.name}\n${item.description || ''}`,
            item.uom || 'pcs',
            item.qty,
            formatCurrency(item.unit_price),
            formatCurrency(item.total_price)
        ]),
        theme: 'grid',
        headStyles: { fillColor: primaryColor, textColor: [255, 255, 255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { halign: 'left', cellWidth: 30 },
            1: { halign: 'left' },
            2: { halign: 'center', cellWidth: 20 },
            3: { halign: 'center', cellWidth: 20 },
            4: { halign: 'right', cellWidth: 30 },
            5: { halign: 'right', cellWidth: 30 }
        },
        styles: { fontSize: 8, cellPadding: 4 }
    });

    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setFont("helvetica", "bold");
    doc.setFontSize(11);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("Total Investment", 130, finalY);
    doc.text(formatCurrency(q.total_amount), 196, finalY, { align: 'right' });

    if (q.terms) {
        doc.setFontSize(9);
        doc.setTextColor(0);
        doc.text("TERMS & SPECIAL CONDITIONS", 14, finalY + 25);
        doc.line(14, finalY + 28, 196, finalY + 28);
        doc.setFont("helvetica", "normal");
        doc.setFontSize(8);
        doc.setTextColor(100);
        const termsLines = doc.splitTextToSize(q.terms, 180);
        doc.text(termsLines, 14, finalY + 34);
    }

    doc.save(`${q.quotation_no}.pdf`);
}

function printChallanPDF(dc) {
    const doc = new jsPDF();
    const primaryColor = [37, 99, 235]; // Blue signature for dispatch

    doc.setFontSize(22);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("FACTORY ELECTRIC", 14, 25);

    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text("Factory Solutions & Supplies Directory", 14, 30);
    doc.text("Industrial Area, Sector 5\nContact: support@factoryelectric.com", 200, 20, { align: 'right' });

    doc.setFontSize(28);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    doc.text("DELIVERY CHALLAN", 105, 55, { align: 'center' });

    doc.setFontSize(10);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");

    // Specifications
    doc.text("Challan No:", 14, 75);
    doc.text("Date:", 14, 82);
    doc.text("Related Quote:", 14, 89);
    doc.text("Status:", 14, 96);

    doc.setFont("helvetica", "normal");
    doc.text(dc.challan_no, 45, 75);
    doc.text(formatDate(dc.created_at), 45, 82);
    doc.text(dc.quotation_no || 'N/A', 45, 89);
    doc.setFont("helvetica", "bold");
    doc.text(dc.status.toUpperCase(), 45, 96);

    // Bill to
    doc.text("BILL TO:", 200, 75, { align: 'right' });
    doc.setFont("helvetica", "normal");
    doc.text(dc.customer?.name || 'Unknown Client', 200, 82, { align: 'right' });

    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setLineWidth(1);
    doc.line(14, 105, 196, 105);

    // Table
    autoTable(doc, {
        startY: 112,
        head: [['Item SKU', 'Item Description', 'UOM', 'Quantity']],
        body: dc.delivery_challan_products.map(item => [
            item.code || 'MANUAL',
            `${item.name}\n${item.description || ''}`,
            item.uom || 'pcs',
            item.qty
        ]),
        theme: 'grid',
        headStyles: { fillColor: primaryColor, textColor: [255, 255, 255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { halign: 'left', cellWidth: 40 },
            1: { halign: 'left' },
            2: { halign: 'center', cellWidth: 30 },
            3: { halign: 'center', cellWidth: 30 }
        },
        styles: { fontSize: 8, cellPadding: 5 }
    });

    const finalY = doc.lastAutoTable.finalY + 30;
    doc.line(14, finalY, 70, finalY);
    doc.text("DISPATCHED BY (FACTORY)", 42, finalY + 5, { align: 'center' });

    doc.line(130, finalY, 196, finalY);
    doc.text("RECEIVED BY (CLIENT)", 163, finalY + 5, { align: 'center' });

    doc.save(`${dc.challan_no}.pdf`);
}

function downloadSummaryReport() {
    const doc = new jsPDF();
    doc.setFontSize(20);
    doc.text("SALES & PROPOSALS REPORT SUMMARY", 14, 20);
    doc.setFontSize(10);
    doc.text(`Generated on: ${formatDate(new Date())}`, 14, 28);

    autoTable(doc, {
        startY: 38,
        head: [['Quote No', 'Client Name', 'Subtotal', 'Status', 'Expires']],
        body: filteredQuotations.value.map(q => [
            q.quotation_no,
            q.customer?.name || 'Unknown',
            formatCurrency(q.total_amount),
            q.status,
            formatDate(q.valid_until)
        ]),
        theme: 'grid'
    });

    doc.save(`Proposals_Report_Summary.pdf`);
}
</script>

<style scoped>
.sales-workspace {
    padding: 24px 20px 40px;
}

/* Header */
.sales-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.sales-title {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin: 0;
    letter-spacing: -0.4px;
}
.sales-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0;
    line-height: 1.5;
}

.btn-new-proposal {
    background: #111827;
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 11px 22px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: background 0.2s, transform 0.12s;
}
.btn-new-proposal:hover {
    background: #21bf73;
    box-shadow: 0 4px 14px rgba(33, 191, 115, 0.2);
}

/* Tabs */
.sales-tabs-nav {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    gap: 20px;
}
.tab-nav-btn {
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 4px;
    font-size: 14px;
    font-weight: 700;
    color: #6b7280;
    cursor: pointer;
    transition: color 0.18s, border-color 0.18s;
}
.tab-nav-btn--active {
    color: #21bf73;
    border-color: #21bf73;
}

/* Filter Controls */
.filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 280px;
}
.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
}
.search-input {
    width: 100%;
    padding: 11px 40px;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.18s;
}
.search-input:focus { border-color: rgba(33, 191, 115, 0.4); }

.clear-search {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    cursor: pointer;
}

.status-filters {
    display: flex;
    gap: 6px;
    overflow-x: auto;
}
.status-filter-btn {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #4b5563;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s;
}
.status-filter-btn--active {
    background: #111827;
    color: #ffffff;
    border-color: #111827;
}

.btn-summary {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    padding: 10px 18px;
    font-size: 12px;
    font-weight: 600;
    color: #111827;
    cursor: pointer;
}

/* Quote Cards Grid */
.quote-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    transition: box-shadow 0.25s, transform 0.2s;
}
.quote-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.quote-card__top {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.quote-card__icon-wrap {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
}
.quote-card__meta {
    flex: 1;
}
.quote-card__number {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 3px;
}
.quote-card__client {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}
.quote-card__submeta {
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    margin-top: 2px;
    display: flex;
    gap: 8px;
}
.quote-card__value-wrap {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.quote-card__value {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}
.quote-card__date {
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
}

/* Card Bottom actions */
.quote-card__bottom {
    padding: 14px 20px;
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.action-icons {
    display: flex;
    gap: 6px;
}
.action-icon-btn {
    width: 28px; height: 28px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.action-icon-btn:hover {
    background: #e5e7eb;
    color: #111827;
}

.status-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-convert-invoice {
    background: rgba(33, 191, 115, 0.08);
    color: #16a34a;
    border: none;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.btn-convert-invoice:hover {
    background: #16a34a;
    color: #ffffff;
}

.btn-challan {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
    border: none;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.btn-challan:hover {
    background: #3b82f6;
    color: #ffffff;
}

.status-select {
    border-radius: 20px;
    border: none;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
    outline: none;
    cursor: pointer;
}
.status-select--draft { background: #f3f4f6; color: #4b5563; }
.status-select--sent { background: rgba(59,130,246,0.1); color: #3b82f6; }
.status-select--invoiced { background: rgba(33,191,115,0.1); color: #16a34a; }
.status-select--final { background: #111827; color: #ffffff; }
.status-select--rejected { background: rgba(245,158,11,0.1); color: #d97706; }
.status-select--cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }

/* Delivery Challan Table styling */
.challan-table-wrap {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}
.challan-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.challan-table th {
    background: #f9fafb;
    color: #4b5563;
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 14px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.challan-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.mono-badge {
    font-family: monospace;
    font-weight: 700;
    font-size: 10px;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
}
.challan-status-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}
.challan-status-badge--pending { background: rgba(245,158,11,0.08); color: #d97706; }
.challan-status-badge--delivered { background: rgba(33,191,115,0.08); color: #16a34a; }

.btn-mark-delivered {
    background: #10b981;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.btn-mark-delivered:hover { background: #059669; }

/* Giant Custom Modal */
.modal-backdrop-custom {
    position: fixed;
    inset: 0;
    background: rgba(26, 26, 46, 0.55);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-container-custom {
    background: #ffffff;
    border-radius: 28px;
    width: 100%;
    max-width: 600px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.modal-container-custom--giant {
    max-width: 1200px;
}
.modal-container-custom--large {
    max-width: 860px;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95) translateY(12px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header-custom {
    padding: 20px 28px;
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
    font-size: 11px;
    margin-top: 3px;
}

.btn-close-modal {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none; background: transparent;
    display: flex; align-items: center; justify-content: center;
    color: #9ca3af;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-close-modal:hover { background: #f3f4f6; color: #111827; }

/* Sections */
.form-section-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    overflow: hidden;
}
.form-section-header {
    background: #f9fafb;
    padding: 10px 16px;
    border-bottom: 1px solid #e5e7eb;
}
.section-label {
    font-size: 10px;
    font-weight: 700;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0;
}
.sublabel {
    display: block;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    color: #9ca3af;
}

.form-select-custom {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 13px;
    color: #1f2937;
    outline: none;
    background: #f9fafb;
}
.form-select-custom:focus { border-color: #21bf73; background: #ffffff; }

.form-input-custom {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 13px;
    color: #1f2937;
    outline: none;
    background: #f9fafb;
}
.form-input-custom:focus { border-color: #21bf73; background: #ffffff; }

.form-textarea-custom {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    font-size: 13px;
    color: #1f2937;
    outline: none;
    resize: none;
}

/* Primary Entry Bar */
.entry-bar-wrapper {
    background: #1f2937;
    border-radius: 16px;
    padding: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.entry-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.entry-col {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.entry-label {
    font-size: 9px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
}
.entry-select {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
}
.entry-input {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
}
.entry-input-large {
    width: 100%;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    color: #ffffff;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
}

.entry-desc-row {
    display: flex;
    align-items: flex-end;
}
.btn-add-item {
    background: #10b981;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.btn-add-item:hover { background: #059669; }

/* Lines Table */
.table-lines {
    font-size: 12px;
}

/* Totals Card */
.totals-summary-card {
    background: #111827;
    color: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.totals-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 12px;
}
.totals-row span { color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.totals-row strong { font-size: 14px; font-weight: 700; }
.totals-value-grand {
    font-size: 32px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -1px;
}

/* Modal Footer Custom button logic */
.modal-footer-custom {
    padding: 16px 28px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
.btn-discard {
    background: transparent;
    border: none;
    color: #9ca3af;
    font-size: 13px;
    font-weight: 700;
    padding: 8px 16px;
    cursor: pointer;
}
.btn-discard:hover { color: #111827; }

.btn-save-proposal {
    border: none;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.btn-save-proposal--draft { background: #f3f4f6; color: #4b5563; }
.btn-save-proposal--sent { background: #3b82f6; color: #ffffff; }
.btn-save-proposal--final { background: #111827; color: #ffffff; }

/* Empty state styling */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
}
.empty-icon-wrap {
    width: 60px; height: 60px;
    background: rgba(33, 191, 115, 0.1);
    color: #21bf73;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    margin-bottom: 16px;
}
.empty-state h3 { font-size: 16px; font-weight: 700; margin: 0 0 6px; }
.empty-state p { font-size: 12px; color: #6b7280; max-width: 380px; margin-bottom: 12px; }

/* Print Sheet Styling */
.print-sheet {
    max-width: 800px;
    min-height: 1000px;
    position: relative;
    border: 1px solid #e5e7eb;
}
.print-top-accent {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 6px;
    background: #10b981;
}
.print-brand {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: -1px;
    color: #10b981;
}
.print-company-text { font-size: 9px; font-weight: 700; text-transform: uppercase; }
.print-company-title { font-size: 12px; font-weight: 700; margin-bottom: 2px; }
.print-company-details { font-size: 10px; color: #6b7280; line-height: 1.4; }
.print-divider {
    height: 1px;
    background: #e5e7eb;
}
.print-section-title {
    font-size: 9px;
    font-weight: 700;
    color: #10b981;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}
.print-customer-name { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
.print-customer-email { font-size: 11px; color: #6b7280; }
.print-spec-text { font-size: 11px; color: #4b5563; margin-bottom: 2px; }

.print-table {
    font-size: 11px;
}
.print-table th { background: #111827 !important; color: #ffffff !important; }

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active { transition: opacity 0.22s; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }
</style>
