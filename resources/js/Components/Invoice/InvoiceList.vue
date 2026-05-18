<template>
    <div class="invoice-workspace animate-in fade-in duration-300 pb-5">
        <!-- Header Section -->
        <header class="workspace-header mb-4">
            <div>
                <h1 class="workspace-title">Invoices & Receivables</h1>
                <p class="workspace-subtitle">Track customer payments, record manual sales, and instantly generate digital invoices.</p>
            </div>
            <button
                @click="openAddModal"
                class="btn-primary-action animate-btn"
                id="create-invoice-btn"
            >
                <i class="fa fa-plus-circle me-2"></i>
                Create Invoice
            </button>
        </header>

        <!-- Search and Filter Controls -->
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <div class="search-input-wrap">
                    <i class="fa fa-search search-icon"></i>
                    <input
                        type="text"
                        placeholder="Search by Invoice No, Customer, PO, or Quote..."
                        v-model="searchQuery"
                        class="search-input"
                    />
                    <span v-if="searchQuery" @click="searchQuery = ''" class="clear-search">
                        <i class="fa fa-times"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="filter-tabs bg-light p-1 rounded-3 d-inline-flex border">
                    <button
                        v-for="f in ['All', 'Unpaid', 'Paid']"
                        :key="f"
                        @click="filterStatus = f"
                        class="filter-tab-btn"
                        :class="{ 'filter-tab-btn--active': filterStatus === f }"
                    >
                        {{ f }}
                    </button>
                </div>
            </div>
            <div class="col-md-3 text-end">
                <button
                    @click="downloadSummaryReport"
                    class="btn btn-outline-dark rounded-3 px-3 py-2 text-xs font-bold w-100"
                >
                    <i class="fa fa-download me-2"></i> Download Summary
                </button>
            </div>
        </div>

        <!-- Invoices List Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="table-dark-custom">
                        <tr>
                            <th class="px-4 py-3">INVOICE INFO</th>
                            <th class="px-4 py-3">CLIENT</th>
                            <th class="px-4 py-3 text-end">TOTAL AMOUNT</th>
                            <th class="px-4 py-3 text-end">BALANCE DUE</th>
                            <th class="px-4 py-3 text-center">STATUS</th>
                            <th class="px-4 py-3 text-end" style="width: 130px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inv in filteredInvoices" :key="inv.id" class="invoice-row" @click="triggerPreview(inv)">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-avatar">
                                        <i class="fa fa-receipt text-success"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold text-dark font-mono">{{ inv.invoiceNo }}</p>
                                        <span class="text-[10px] text-muted text-uppercase tracking-wider">
                                            PO: {{ inv.poNo || 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p class="mb-0 fw-semibold text-dark">{{ inv.clientName }}</p>
                                <span class="text-[10px] text-muted font-medium italic">
                                    Date: {{ formatDate(inv.createdAt) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end fw-bold text-dark">
                                {{ formatCurrency(inv.totalAmount) }}
                            </td>
                            <td class="px-4 py-3 text-end">
                                <span :class="inv.totalAmount - inv.paidAmount > 0 ? 'text-rose-600 fw-bold' : 'text-emerald-600 fw-bold'">
                                    {{ formatCurrency(inv.totalAmount - inv.paidAmount) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-status-pill" :class="getStatusClass(inv.status)">
                                    <i class="fa me-1" :class="inv.status === 'Paid' ? 'fa-check-circle' : 'fa-info-circle'"></i>
                                    {{ inv.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end" @click.stop>
                                <div class="d-flex justify-content-end gap-1">
                                    <button
                                        @click="triggerPreview(inv)"
                                        class="action-btn text-muted hover-dark"
                                        title="Preview Invoice"
                                    >
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button
                                        @click="downloadSinglePDF(inv)"
                                        class="action-btn text-muted hover-dark"
                                        title="Download PDF"
                                    >
                                        <i class="fa fa-download"></i>
                                    </button>
                                    
                                    <!-- Context Menu -->
                                    <div class="dropdown d-inline-block">
                                        <button
                                            class="action-btn text-muted hover-dark"
                                            type="button"
                                            :id="'dd-' + inv.id"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                        >
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" :aria-labelledby="'dd-' + inv.id">
                                            <li>
                                                <button @click="triggerPreview(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fa fa-eye text-muted me-2"></i> Preview Invoice
                                                </button>
                                            </li>
                                            <li>
                                                <button @click="triggerEdit(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fa fa-pencil-alt text-muted me-2"></i> Edit Invoice
                                                </button>
                                            </li>
                                            <li>
                                                <button @click="toggleInvoicePaid(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fa fa-check-circle text-emerald-500 me-2"></i> Mark as {{ inv.status === 'Paid' ? 'Unpaid' : 'Paid' }}
                                                </button>
                                            </li>
                                            <li>
                                                <button @click="createChallanFromInvoice(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fa fa-truck text-muted me-2"></i> Create Delivery Challan
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button @click="shareEmail(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fa fa-envelope text-muted me-2"></i> Share via Email
                                                </button>
                                            </li>
                                            <li>
                                                <button @click="shareWhatsApp(inv)" class="dropdown-item py-2 text-xs fw-semibold">
                                                    <i class="fab fa-whatsapp text-emerald-500 me-2"></i> Share on WhatsApp
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button @click="deleteInvoiceClick(inv)" class="dropdown-item py-2 text-xs fw-bold text-rose-600">
                                                    <i class="fa fa-trash me-2"></i> Delete Invoice
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredInvoices.length === 0">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="fa fa-receipt fs-1 text-light mb-3"></i>
                                    <p class="mb-0">No matching invoices found in your solutions system.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── WIZARD 1: SELECT QUOTATION OR MANUAL CHOICE ──────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="showAddModal" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--medium">
                    <div class="modal-header-custom">
                        <div>
                            <h3 class="modal-title-custom">Generate Invoice</h3>
                            <p class="modal-subtitle-custom mb-0">Select an approved quotation to convert, or create manually.</p>
                        </div>
                        <button @click="showAddModal = false" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body-custom p-4">
                        <div class="search-input-wrap mb-3">
                            <i class="fa fa-search search-icon"></i>
                            <input
                                type="text"
                                placeholder="Search by Client or Quote Number..."
                                v-model="wizardSearch"
                                class="search-input"
                            />
                        </div>

                        <div class="quotations-list-container custom-scrollbar mb-4 border rounded-3 overflow-auto" style="max-height: 250px;">
                            <div
                                v-for="q in filteredQuotations"
                                :key="q.id"
                                @click="selectedQuotationId = q.id"
                                class="quotation-selection-row p-3 border-b d-flex justify-content-between align-items-center cursor-pointer"
                                :class="{ 'quotation-selection-row--active': selectedQuotationId === q.id }"
                            >
                                <div>
                                    <p class="mb-0 fw-bold text-dark">{{ q.clientName }}</p>
                                    <span class="text-[10px] text-muted text-uppercase tracking-wider">
                                        {{ q.quotationNo }} • Reference: {{ q.prNo || 'N/A' }}
                                    </span>
                                </div>
                                <div class="text-end d-flex align-items-center gap-3">
                                    <span class="fw-bold text-dark">{{ formatCurrency(q.totalAmount) }}</span>
                                    <i class="fa fs-5" :class="selectedQuotationId === q.id ? 'fa-check-circle text-success' : 'fa-circle-notch text-light'"></i>
                                </div>
                            </div>
                            <div v-if="filteredQuotations.length === 0" class="text-center py-4 text-muted text-xs">
                                <i class="fa fa-file-invoice mb-2 fs-4 text-light block"></i>
                                No active approved/sent quotations available to convert.
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button
                                @click="triggerManualCreation"
                                class="flex-grow-1 btn btn-light rounded-3 py-3 font-bold text-dark border hover-bg-gray"
                            >
                                Manual Invoice
                            </button>
                            <button
                                @click="generateInvoiceFromQuotation"
                                :disabled="!selectedQuotationId"
                                class="flex-grow-2 btn btn-dark rounded-3 py-3 font-bold text-white hover-success active-scale disabled-opacity"
                            >
                                Generate Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- ── WIZARD 2: MANUAL / EDIT INVOICE DRAWER ────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="isCreatingManual" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--xlarge flex flex-col h-[90vh]">
                    <div class="modal-header-custom bg-white border-b shrink-0">
                        <div>
                            <h3 class="modal-title-custom">{{ editingInvoiceId ? 'Edit Invoice' : 'New Manual Invoice' }}</h3>
                            <p class="modal-subtitle-custom mb-0">Craft a professional client invoice from scratch with custom parameters.</p>
                        </div>
                        <button @click="closeManualInvoice" class="btn-close-modal">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>

                    <div class="modal-body-custom p-4 bg-light overflow-auto flex-grow-1">
                        <!-- Invoice Specs Card -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card p-3 border-0 shadow-sm rounded-4 h-100 bg-white">
                                    <label class="sublabel mb-2">Customer Selection</label>
                                    <select v-model="manualForm.clientId" class="form-select-custom w-100">
                                        <option value="">Choose a Customer...</option>
                                        <option v-for="c in customers" :key="c.id" :value="c.id">
                                            {{ c.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card p-3 border-0 shadow-sm rounded-4 h-100 bg-white">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="sublabel mb-1">PO Number</label>
                                            <input type="text" v-model="manualForm.poNo" class="form-input-custom" placeholder="Optional PO#" />
                                        </div>
                                        <div class="col-6">
                                            <label class="sublabel mb-1">PR/Ref Number</label>
                                            <input type="text" v-model="manualForm.prNo" class="form-input-custom" placeholder="Optional PR#" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card p-3 border-0 shadow-sm rounded-4 h-100 bg-white d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="sublabel">CALCULATED TOTAL</span>
                                        <i class="fa fa-calculator text-success"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] fw-bold text-success text-uppercase mb-0">TOTAL DUE AMOUNT</p>
                                        <p class="fs-2 fw-bold text-dark font-mono mb-0">{{ formatCurrency(calculatedTotal) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Selector Input Bar -->
                        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-dark text-white">
                            <label class="sublabel text-gray-300 mb-2">PICK ITEM FROM STOCK INVENTORY OR MANUAL DESCRIPTION</label>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <select @change="onProductSelect($event.target.value)" class="entry-select w-100" id="manual-product-dropdown">
                                        <option value="">Search inventory products...</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">
                                            {{ p.name }} (SKU: {{ p.code }}) - ${{ p.price }} [Stock: {{ p.unit }}]
                                        </option>
                                        <option value="manual">+ Register Custom Manual Product</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" v-model="tempItem.code" class="entry-input font-mono w-100" placeholder="SKU-CODE" />
                                </div>
                                <div class="col-md-2">
                                    <input type="text" v-model="tempItem.uom" class="entry-input text-center w-100" placeholder="UoM (pcs)" />
                                </div>
                                <div class="col-md-1">
                                    <input type="number" v-model.number="tempItem.qty" class="entry-input text-center font-bold w-100" min="1" />
                                </div>
                                <div class="col-md-2">
                                    <input type="number" v-model.number="tempItem.unitPrice" class="entry-input fw-bold w-100" placeholder="0.00" />
                                </div>
                                <div class="col-md-1">
                                    <button @click="addManualItem" type="button" class="btn btn-success w-100 py-2 rounded-3 animate-btn">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-2 g-2">
                                <div class="col-md-12">
                                    <input
                                        type="text"
                                        placeholder="Detailed item specifications & description..."
                                        v-model="tempItem.description"
                                        class="entry-input-large"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Manual Items Table -->
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                            <div class="table-responsive">
                                <table class="table align-middle text-sm mb-0">
                                    <thead class="table-dark-custom">
                                        <tr>
                                            <th class="px-4 py-3">ITEM CODE</th>
                                            <th class="px-4 py-3">DESCRIPTION</th>
                                            <th class="px-4 py-3 text-center" style="width: 80px;">UoM</th>
                                            <th class="px-4 py-3 text-center" style="width: 100px;">QTY</th>
                                            <th class="px-4 py-3 text-center" style="width: 130px;">UNIT PRICE</th>
                                            <th class="px-4 py-3 text-end" style="width: 140px;">TOTAL</th>
                                            <th class="px-3 py-3 text-center" style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in manualForm.items" :key="index">
                                            <td class="px-4 py-3 fw-bold text-dark font-mono text-uppercase">{{ item.code }}</td>
                                            <td class="px-4 py-3">
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm fw-bold border-0 p-0 bg-transparent text-dark"
                                                    v-model="item.name"
                                                />
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm border-0 p-0 bg-transparent text-muted mt-1 text-xs"
                                                    v-model="item.description"
                                                    placeholder="Description details..."
                                                />
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="badge bg-light text-dark text-uppercase border">{{ item.uom }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input
                                                    type="number"
                                                    class="form-control form-control-sm text-center fw-bold rounded-3 border-gray"
                                                    v-model.number="item.qty"
                                                    @input="recalculateItemPrice(index)"
                                                    min="1"
                                                />
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input
                                                    type="number"
                                                    class="form-control form-control-sm text-center fw-bold rounded-3 border-gray"
                                                    v-model.number="item.unitPrice"
                                                    @input="recalculateItemPrice(index)"
                                                    step="0.01"
                                                />
                                            </td>
                                            <td class="px-4 py-3 text-end fw-bold text-dark font-mono">
                                                {{ formatCurrency(item.totalPrice) }}
                                            </td>
                                            <td class="px-3 py-3 text-center">
                                                <button @click="removeManualItem(index)" class="btn btn-link text-rose-500 p-0">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="manualForm.items.length === 0">
                                            <td colspan="7" class="text-center py-5 text-muted text-xs">
                                                No items have been added to this invoice sheet. Choose products from inventory above!
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-custom bg-white border-t shrink-0 p-3 d-flex gap-3">
                        <button
                            @click="closeManualInvoice"
                            class="flex-grow-1 btn btn-light py-3 rounded-3 font-bold border"
                        >
                            Discard Changes
                        </button>
                        <button
                            @click="submitManualInvoice"
                            :disabled="!manualForm.clientId || manualForm.items.length === 0"
                            class="flex-grow-2 btn btn-dark py-3 rounded-3 font-bold hover-success active-scale disabled-opacity"
                        >
                            Confirm & Save Invoice
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- ── WIZARD 3: PREVIEW DRAWER ──────────────────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="showPreview && previewDoc" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--xlarge flex flex-col h-[90vh]">
                    <div class="modal-header-custom border-b bg-white shrink-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-cube bg-dark text-white rounded-3 p-2">
                                <i class="fa fa-receipt fs-4"></i>
                            </div>
                            <div>
                                <h3 class="modal-title-custom">Invoice Specifications sheet</h3>
                                <p class="modal-subtitle-custom mb-0">{{ previewDoc.invoiceNo }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button @click="downloadSinglePDF(previewDoc)" class="btn btn-success rounded-3 px-3 py-2 text-xs font-bold text-white animate-btn">
                                <i class="fa fa-download me-1"></i> Download PDF
                            </button>
                            <button @click="showPreview = false" class="btn-close-modal">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body-custom p-4 bg-light overflow-auto flex-grow-1">
                        <div class="print-sheet mx-auto bg-white shadow-lg p-5 rounded-3" style="max-width: 800px; min-height: 1000px;">
                            <div class="print-top-accent"></div>
                            
                            <!-- Head -->
                            <div class="d-flex justify-content-between align-items-start mt-4 mb-5">
                                <div>
                                    <div class="print-brand text-success">FACTORY ELECTRIC</div>
                                    <p class="print-company-text text-muted">Factory Solutions & Supplies Directory</p>
                                </div>
                                <div class="text-end">
                                    <p class="print-company-title fw-bold text-dark mb-1">Factory Electric Solution</p>
                                    <p class="print-company-details text-muted text-xs mb-0">Industrial Area, Sector 5<br>Contact: support@factoryelectric.com</p>
                                </div>
                            </div>

                            <!-- Divider line -->
                            <div class="print-divider mb-4"></div>

                            <!-- Document Details Grid -->
                            <div class="row mb-4 text-xs">
                                <div class="col-6">
                                    <h4 class="print-section-title uppercase text-muted mb-2 font-bold">BILL TO:</h4>
                                    <p class="print-customer-name fs-6 fw-bold text-dark mb-1">{{ previewDoc.clientName }}</p>
                                    <p class="print-customer-email text-muted mb-0">Customer ID: {{ previewDoc.clientId }}</p>
                                </div>
                                <div class="col-6 text-end">
                                    <h4 class="print-section-title uppercase text-muted mb-2 font-bold">INVOICE SPECIFICATIONS:</h4>
                                    <p class="print-spec-text text-dark">Invoice No: <strong>{{ previewDoc.invoiceNo }}</strong></p>
                                    <p class="print-spec-text text-dark">Reference Quote: {{ previewDoc.quotationNo || 'Manual' }}</p>
                                    <p class="print-spec-text text-dark">PO Number: {{ previewDoc.poNo || 'N/A' }}</p>
                                    <p class="print-spec-text text-dark">Date: {{ formatDate(previewDoc.createdAt) }}</p>
                                    <p class="print-spec-text text-dark">Status: <span class="badge bg-dark text-uppercase">{{ previewDoc.status }}</span></p>
                                </div>
                            </div>

                            <!-- Items List Table -->
                            <div class="table-responsive rounded-3 border overflow-hidden mb-4 text-xs">
                                <table class="table print-table mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="px-3 py-2 text-uppercase font-bold">Description</th>
                                            <th class="px-3 py-2 text-center text-uppercase font-bold" style="width: 80px;">UoM</th>
                                            <th class="px-3 py-2 text-center text-uppercase font-bold" style="width: 80px;">Qty</th>
                                            <th class="px-3 py-2 text-end text-uppercase font-bold" style="width: 120px;">Unit Price</th>
                                            <th class="px-3 py-2 text-end text-uppercase font-bold" style="width: 140px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, i) in previewDoc.items" :key="i">
                                            <td class="px-3 py-3 border-bottom">
                                                <p class="fw-bold text-dark mb-0 font-mono">{{ item.code }} - {{ item.name }}</p>
                                                <span class="text-muted text-[10px]">{{ item.description }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-center border-bottom border-l uppercase text-muted font-bold">{{ item.uom }}</td>
                                            <td class="px-3 py-3 text-center border-bottom border-l font-bold text-dark">{{ item.qty }}</td>
                                            <td class="px-3 py-3 text-end border-bottom border-l font-mono">{{ formatCurrency(item.unitPrice) }}</td>
                                            <td class="px-3 py-3 text-end border-bottom border-l font-mono fw-bold text-dark">{{ formatCurrency(item.totalPrice) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals Section -->
                            <div class="d-flex justify-content-end mb-5 text-xs">
                                <div class="w-50 bg-light p-4 rounded-3 border">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Payable:</span>
                                        <span class="fw-bold text-dark font-mono">{{ formatCurrency(previewDoc.totalAmount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-top pt-2">
                                        <span class="text-muted">Amount Paid:</span>
                                        <span class="fw-bold text-success font-mono">{{ formatCurrency(previewDoc.paidAmount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-top pt-2">
                                        <span class="fw-bold text-rose-600">Balance Due:</span>
                                        <span class="fw-bold text-rose-600 font-mono fs-6">{{ formatCurrency(previewDoc.totalAmount - previewDoc.paidAmount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Signatures section -->
                            <div class="row pt-5 text-center text-xs">
                                <div class="col-6">
                                    <div class="border-top pt-2 text-muted fw-bold uppercase">PREPARED BY</div>
                                    <p class="mt-1 fw-bold text-dark uppercase font-mono">{{ previewDoc.createdBy || 'ADMIN' }}</p>
                                </div>
                                <div class="col-6">
                                    <div class="border-top pt-2 text-muted fw-bold uppercase">AUTHORIZED STAMP & SIGNATURE</div>
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

const props = defineProps({
    list: Array,
    customers: Array,
    products: Array,
    quotations: Array
});

const toaster = createToaster({ position: 'top-right' });
const page = usePage();

// Computed fallbacks from page.props (bulletproof integration)
const list = computed(() => props.list || page.props.list || []);
const customers = computed(() => props.customers || page.props.customers || []);
const products = computed(() => props.products || page.props.products || []);
const quotations = computed(() => props.quotations || page.props.quotations || []);

// Filters & Search
const searchQuery = ref('');
const filterStatus = ref('All');

// Modal States
const showAddModal = ref(false);
const wizardSearch = ref('');
const selectedQuotationId = ref('');
const isCreatingManual = ref(false);
const editingInvoiceId = ref(null);

// Manual form structure
const manualForm = ref({
    clientId: '',
    poNo: '',
    prNo: '',
    invoiceNo: '',
    items: []
});

// Temp Item Add block
const tempItem = ref({
    product_id: '',
    code: '',
    name: '',
    description: '',
    uom: 'pcs',
    qty: 1,
    unitPrice: 0,
    margin: 0,
    discount: 0,
    totalPrice: 0
});

// Preview state
const showPreview = ref(false);
const previewDoc = ref(null);

// Mapped calculations
const calculatedTotal = computed(() => {
    return manualForm.value.items.reduce((sum, item) => sum + (parseFloat(item.totalPrice) || 0), 0);
});

// Filters Invoices computed property
const filteredInvoices = computed(() => {
    return list.value.filter(inv => {
        // Status filter
        if (filterStatus.value === 'Unpaid' && inv.status === 'Paid') return false;
        if (filterStatus.value === 'Paid' && inv.status !== 'Paid') return false;

        // Search query
        if (!searchQuery.value) return true;
        const q = searchQuery.value.toLowerCase();
        return (
            inv.invoiceNo.toLowerCase().includes(q) ||
            inv.clientName.toLowerCase().includes(q) ||
            (inv.poNo && inv.poNo.toLowerCase().includes(q)) ||
            (inv.quotationNo && inv.quotationNo.toLowerCase().includes(q))
        );
    });
});

// Filter quotations for selection
const filteredQuotations = computed(() => {
    if (!wizardSearch.value) return quotations.value;
    const q = wizardSearch.value.toLowerCase();
    return quotations.value.filter(qt =>
        qt.clientName.toLowerCase().includes(q) ||
        qt.quotationNo.toLowerCase().includes(q)
    );
});

// Handlers
function openAddModal() {
    showAddModal.value = true;
    selectedQuotationId.value = '';
    wizardSearch.value = '';
}

function triggerManualCreation() {
    showAddModal.value = false;
    isCreatingManual.value = true;
    editingInvoiceId.value = null;
    resetManualForm();
}

function resetManualForm() {
    manualForm.value = {
        clientId: '',
        poNo: '',
        prNo: '',
        invoiceNo: 'INV-' + new Date().getFullYear() + '-' + Math.floor(100000 + Math.random() * 900000),
        items: []
    };
    resetTempItem();
}

function resetTempItem() {
    tempItem.value = {
        product_id: '',
        code: '',
        name: '',
        description: '',
        uom: 'pcs',
        qty: 1,
        unitPrice: 0,
        margin: 0,
        discount: 0,
        totalPrice: 0
    };
}

// Product Selector dropdown logic
function onProductSelect(val) {
    if (!val) return;
    if (val === 'manual') {
        tempItem.value = {
            product_id: 'manual',
            code: 'MANUAL-' + Math.floor(1000 + Math.random() * 9000),
            name: 'Custom Product Name',
            description: '',
            uom: 'pcs',
            qty: 1,
            unitPrice: 0,
            margin: 0,
            discount: 0,
            totalPrice: 0
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
            unitPrice: parseFloat(prod.price) || 0,
            margin: 0,
            discount: 0,
            totalPrice: parseFloat(prod.price) || 0
        };
    }
}

// Add manual item to invoice items list
function addManualItem() {
    if (!tempItem.value.name) {
        toaster.warning('Please select a product or input a name');
        return;
    }

    const price = parseFloat(tempItem.value.unitPrice) || 0;
    const qty = parseInt(tempItem.value.qty) || 1;
    const total = price * qty;

    manualForm.value.items.push({
        productId: tempItem.value.product_id,
        code: tempItem.value.code || 'MANUAL',
        name: tempItem.value.name,
        description: tempItem.value.description,
        uom: tempItem.value.uom,
        qty: qty,
        unitPrice: price,
        margin: 0,
        discount: 0,
        totalPrice: total
    });

    resetTempItem();
    document.getElementById('manual-product-dropdown').value = '';
    toaster.success('Item added successfully');
}

function recalculateItemPrice(index) {
    const item = manualForm.value.items[index];
    item.totalPrice = (parseFloat(item.qty) || 0) * (parseFloat(item.unitPrice) || 0);
}

function removeManualItem(index) {
    manualForm.value.items.splice(index, 1);
}

function closeManualInvoice() {
    isCreatingManual.value = false;
    editingInvoiceId.value = null;
    resetManualForm();
}

// Submit manual form (create or edit)
function submitManualInvoice() {
    if (!manualForm.value.clientId) {
        toaster.warning('Please choose a Customer');
        return;
    }
    if (manualForm.value.items.length === 0) {
        toaster.warning('Please add at least one item');
        return;
    }

    const endpoint = editingInvoiceId.value ? '/update-invoice' : '/create-invoice';
    const payload = {
        id: editingInvoiceId.value,
        clientId: manualForm.value.clientId,
        poNo: manualForm.value.poNo,
        prNo: manualForm.value.prNo,
        invoiceNo: manualForm.value.invoiceNo,
        items: manualForm.value.items,
        status: editingInvoiceId.value ? undefined : 'Unpaid',
        paidAmount: editingInvoiceId.value ? undefined : 0
    };

    router.post(endpoint, payload, {
        onSuccess: () => {
            closeManualInvoice();
            toaster.success('Invoice saved successfully!');
        },
        onError: (err) => {
            const msg = Object.values(err)[0] || 'Something went wrong';
            toaster.error(msg);
        }
    });
}

// Generate standard invoice from selected quotation
function generateInvoiceFromQuotation() {
    if (!selectedQuotationId.value) return;

    router.post('/convert-to-invoice', { quotation_id: selectedQuotationId.value }, {
        onSuccess: () => {
            showAddModal.value = false;
            toaster.success('Quotation converted to Invoice successfully!');
        },
        onError: (err) => {
            toaster.error(err.message || 'Failed to convert quotation');
        }
    });
}

// Edit invoice handler
function triggerEdit(inv) {
    editingInvoiceId.value = inv.id;
    manualForm.value = {
        id: inv.id,
        clientId: inv.clientId,
        poNo: inv.poNo || '',
        prNo: inv.prNo || '',
        invoiceNo: inv.invoiceNo,
        items: JSON.parse(JSON.stringify(inv.items))
    };
    isCreatingManual.value = true;
}

// Delete click handler
function deleteInvoiceClick(inv) {
    if (confirm(`Are you sure you want to permanently delete Invoice ${inv.invoiceNo}? Inventory items will be restored.`)) {
        router.get(`/invoice-delete/${inv.id}`, {
            onSuccess: () => {
                toaster.success('Invoice deleted and inventory restored!');
            }
        });
    }
}

// Preview Modal Trigger
function triggerPreview(inv) {
    previewDoc.value = inv;
    showPreview.value = true;
}

// Toggle Invoice Paid / Unpaid Status
function toggleInvoicePaid(inv) {
    const newStatus = inv.status === 'Paid' ? 'Unpaid' : 'Paid';
    axios.post('/update-invoice-status', { id: inv.id, status: newStatus })
        .then(res => {
            toaster.success(`Invoice marked as ${newStatus}`);
            router.reload();
        })
        .catch(err => {
            toaster.error('Failed to update invoice status');
        });
}

// Create Delivery Challan from Invoice
function createChallanFromInvoice(inv) {
    axios.post('/invoice-create-delivery-challan', { invoice_id: inv.id })
        .then(res => {
            toaster.success('Delivery Challan created successfully!');
            router.reload();
        })
        .catch(err => {
            toaster.error(err.response?.data?.message || 'Failed to generate Delivery Challan');
        });
}

// Share Functions
function shareEmail(inv) {
    const subject = `Invoice ${inv.invoiceNo} from Factory Electric Solution`;
    const itemsText = inv.items.map(i => `- ${i.name}: ${i.qty} x ${formatCurrency(i.unitPrice)} = ${formatCurrency(i.totalPrice)}`).join('\n');
    const body = `Dear Customer,\n\nPlease find details for invoice ${inv.invoiceNo}.\n\nItems List:\n${itemsText}\n\nTotal Due: ${formatCurrency(inv.totalAmount)}\nStatus: ${inv.status}\n\nThank you for your business.`;
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

function shareWhatsApp(inv) {
    const itemsText = inv.items.map(i => `• ${i.name}: ${i.qty} x ${formatCurrency(i.unitPrice)}`).join('\n');
    const text = `*Invoice ${inv.invoiceNo}*\nClient: ${inv.clientName}\n\n*Items List:*\n${itemsText}\n\n*Total Due:* ${formatCurrency(inv.totalAmount)}\n*Status:* ${inv.status}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}

// Generate Gorgeous single Invoice PDF
function downloadSinglePDF(inv) {
    const doc = new jsPDF();
    const primaryColor = [33, 191, 115]; // Clean healthy green signature

    // Branding
    doc.setFontSize(22);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("FACTORY ELECTRIC", 14, 25);

    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text("Factory Solutions & Supplies Directory", 14, 30);
    doc.text("Industrial Area, Sector 5\nContact: support@factoryelectric.com", 200, 20, { align: 'right' });

    // Document header
    doc.setFontSize(28);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    doc.text("OFFICIAL INVOICE", 105, 55, { align: 'center' });

    // Specs grid left
    doc.setFontSize(10);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    doc.text("Invoice No:", 14, 75);
    doc.text("Reference Quote:", 14, 82);
    doc.text("PO Number:", 14, 89);
    doc.text("Date:", 14, 96);
    doc.text("Status:", 14, 103);

    doc.setFont("helvetica", "normal");
    doc.text(inv.invoiceNo, 50, 75);
    doc.text(inv.quotationNo || 'Manual', 50, 82);
    doc.text(inv.poNo || 'N/A', 50, 89);
    doc.text(formatDate(inv.createdAt), 50, 96);
    doc.setFont("helvetica", "bold");
    doc.text(inv.status.toUpperCase(), 50, 103);

    // Bill to right
    doc.text("BILL TO:", 200, 75, { align: 'right' });
    doc.setFont("helvetica", "normal");
    doc.text(inv.clientName, 200, 82, { align: 'right' });
    doc.text(`Customer ID: ${inv.clientId}`, 200, 88, { align: 'right' });

    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setLineWidth(1);
    doc.line(14, 110, 196, 110);

    // Items table
    autoTable(doc, {
        startY: 118,
        head: [['Item Code', 'Description', 'UOM', 'Qty', 'Unit Price', 'Total']],
        body: inv.items.map(item => [
            item.code || 'MANUAL',
            `${item.name}\n${item.description || ''}`,
            item.uom || 'pcs',
            item.qty,
            formatCurrency(item.unitPrice),
            formatCurrency(item.totalPrice)
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

    // Totals Box right align
    doc.setFont("helvetica", "bold");
    doc.setFontSize(10);
    doc.text("Total Payable:", 130, finalY);
    doc.text(formatCurrency(inv.totalAmount), 196, finalY, { align: 'right' });

    doc.text("Amount Paid:", 130, finalY + 7);
    doc.setTextColor(33, 191, 115);
    doc.text(formatCurrency(inv.paidAmount), 196, finalY + 7, { align: 'right' });

    doc.setTextColor(220, 38, 38);
    doc.text("Balance Due:", 130, finalY + 14);
    doc.text(formatCurrency(inv.totalAmount - inv.paidAmount), 196, finalY + 14, { align: 'right' });

    // Footer signature lines
    const pageHeight = doc.internal.pageSize.height;
    doc.setDrawColor(200);
    doc.setLineWidth(0.5);
    doc.line(14, pageHeight - 30, 70, pageHeight - 30);
    doc.line(140, pageHeight - 30, 196, pageHeight - 30);

    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.setFont("helvetica", "bold");
    doc.text("PREPARED BY: " + (inv.createdBy || 'ADMIN').toUpperCase(), 42, pageHeight - 25, { align: 'center' });
    doc.text("CLIENT SIGNATURE & STAMP", 168, pageHeight - 25, { align: 'center' });

    doc.save(`${inv.invoiceNo}.pdf`);
}

// Download Invoice Summary Report PDF
function downloadSummaryReport() {
    const doc = new jsPDF();
    const primaryColor = [31, 41, 55]; // Dark primary

    doc.setFontSize(18);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setFont("helvetica", "bold");
    doc.text("INVOICES GENERAL SUMMARY REPORT", 14, 20);

    doc.setFontSize(9);
    doc.setTextColor(120);
    doc.setFont("helvetica", "normal");
    doc.text(`Generated on: ${formatDate(new Date())}`, 14, 27);
    doc.text(`Total Records Found: ${filteredInvoices.value.length}`, 14, 33);

    autoTable(doc, {
        startY: 40,
        head: [['Invoice No', 'Client Name', 'Created Date', 'Total Amount', 'Paid', 'Status']],
        body: filteredInvoices.value.map(inv => [
            inv.invoiceNo,
            inv.clientName,
            formatDate(inv.createdAt),
            formatCurrency(inv.totalAmount),
            formatCurrency(inv.paidAmount),
            inv.status.toUpperCase()
        ]),
        headStyles: { fillColor: primaryColor },
        styles: { fontSize: 8 }
    });

    doc.save(`invoice_summary_${Date.now()}.pdf`);
}

// Helpers for Class names & Currency format
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
    if (status === 'Paid') return 'bg-emerald-50 text-emerald-600 border border-emerald-200';
    return 'bg-amber-50 text-amber-600 border border-amber-200';
}
</script>

<style scoped>
/* Sleek Corporate Dark Theme Elements */
.invoice-workspace {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #1f2937;
}

.workspace-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 20px;
}

.workspace-title {
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.025em;
    margin: 0;
}

.workspace-subtitle {
    font-size: 13px;
    color: #6b7280;
    margin: 4px 0 0 0;
}

/* Actions Bar */
.btn-primary-action {
    background: #111827;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(17, 24, 39, 0.15);
    transition: all 0.2s ease;
}
.btn-primary-action:hover {
    background: #21bf73;
    box-shadow: 0 4px 12px rgba(33, 191, 115, 0.2);
}

/* Modern Form Search Wrap */
.search-input-wrap {
    position: relative;
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
    padding: 10px 14px 10px 42px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s ease;
}
.search-input:focus {
    background: #ffffff;
    border-color: #111827;
    box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.05);
}
.clear-search {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #9ca3af;
}
.clear-search:hover {
    color: #111827;
}

/* Filter Tab Options */
.filter-tab-btn {
    border: none;
    background: transparent;
    padding: 6px 18px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.filter-tab-btn--active {
    background: #ffffff;
    color: #111827;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

/* Gorgeous custom table */
.table-dark-custom {
    background: #1f2937;
    color: #ffffff;
}
.table-dark-custom th {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #d1d5db;
    border: none;
}

.invoice-row {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.invoice-row:hover {
    background-color: #f9fafb !important;
}

.icon-avatar {
    width: 36px;
    height: 36px;
    background: #f3f4f6;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.badge-status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 30px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

/* Context actions buttons */
.action-btn {
    border: none;
    background: transparent;
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.action-btn:hover {
    background: #e5e7eb;
    color: #111827 !important;
}

/* Custom premium modal design */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(17, 24, 39, 0.4);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1060;
}

.modal-container-custom {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    width: 90%;
    animation: modal-enter 0.25s ease-out;
}
.modal-container-custom--medium {
    max-width: 550px;
}
.modal-container-custom--large {
    max-width: 850px;
}
.modal-container-custom--xlarge {
    max-width: 1100px;
}

.modal-header-custom {
    padding: 20px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-title-custom {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.02em;
}
.modal-subtitle-custom {
    font-size: 12px;
    color: #6b7280;
}
.btn-close-modal {
    border: none;
    background: #f3f4f6;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: all 0.15s ease;
}
.btn-close-modal:hover {
    background: #e5e7eb;
    color: #111827;
}

/* Selection rows inside wizard */
.quotation-selection-row {
    transition: all 0.15s ease;
}
.quotation-selection-row:hover {
    background-color: #f9fafb;
}
.quotation-selection-row--active {
    background-color: #f0fdf4 !important;
    border-color: #21bf73 !important;
}

/* Premium form customization elements */
.sublabel {
    display: block;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    color: #9ca3af;
    letter-spacing: 0.05em;
}

.form-select-custom, .form-input-custom {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    color: #1f2937;
    outline: none;
    background: #f9fafb;
    transition: all 0.2s ease;
}
.form-select-custom:focus, .form-input-custom:focus {
    border-color: #21bf73;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(33, 191, 115, 0.08);
}

/* Premium Black Entry Bar */
.entry-select, .entry-input {
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 12px;
    outline: none;
    transition: all 0.2s ease;
}
.entry-select:focus, .entry-input:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #21bf73;
}
.entry-input-large {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #ffffff;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 12px;
    outline: none;
}
.entry-input-large:focus {
    background: rgba(255, 255, 255, 0.12);
    border-color: #21bf73;
}

/* Print Sheet Styling inside modal preview */
.print-sheet {
    position: relative;
    border: 1px solid #e5e7eb;
}
.print-top-accent {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: #21bf73;
}
.print-brand {
    font-size: 20px;
    font-weight: 900;
    letter-spacing: -0.03em;
}
.print-divider {
    height: 1px;
    background: #e5e7eb;
    width: 100%;
}
.print-section-title {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.05em;
    color: #9ca3af;
}

.table.print-table {
    border-collapse: collapse;
    width: 100%;
}
.table.print-table th {
    background: #111827;
    color: #ffffff;
    font-weight: 700;
    font-size: 9px;
}

/* Animations */
@keyframes modal-enter {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(15px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity 0.2s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
}

.animate-btn {
    transition: all 0.15s ease-out;
}
.animate-btn:hover {
    transform: translateY(-1px);
}
.animate-btn:active {
    transform: translateY(1px);
}
</style>
