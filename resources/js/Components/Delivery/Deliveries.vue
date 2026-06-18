<template>
  <div class="deliveries-root">

    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <header class="deliveries-header">
      <div class="header-top">
        <div>
          <h1 class="page-title">Deliveries</h1>
          <p class="page-sub">Manage delivery challans and stock dispatch.</p>
        </div>
        <button @click="isAdding = true" class="btn-primary">
          <Plus class="icon-sm" />
          New Delivery
        </button>
      </div>

      <!-- Controls bar -->
      <div class="controls-bar">
        <div class="search-wrap">
          <Search class="search-icon" />
          <input
            type="text"
            placeholder="Search by Challan No, Quote Ref, or PR..."
            v-model="searchQuery"
            class="search-input"
          />
        </div>

        <div class="filter-tabs">
          <button
            v-for="f in ['All', 'Pending', 'Delivered']"
            :key="f"
            @click="filter = f"
            :class="['filter-tab', filter === f ? 'filter-tab--active' : '']"
          >{{ f }}</button>
        </div>

        <button @click="downloadDeliveryReport" class="btn-outline">
          <Download class="icon-sm" />
          Download Summary
        </button>
      </div>
    </header>

    <!-- ── Stats Bar ──────────────────────────────────────────────────── -->
    <div class="stats-bar">
      <div class="stat-card">
        <Truck class="stat-icon stat-icon--blue" />
        <div>
          <p class="stat-value">{{ props.challans?.length ?? 0 }}</p>
          <p class="stat-label">Total Challans</p>
        </div>
      </div>
      <div class="stat-card">
        <Package class="stat-icon stat-icon--amber" />
        <div>
          <p class="stat-value">{{ pendingCount }}</p>
          <p class="stat-label">Pending</p>
        </div>
      </div>
      <div class="stat-card">
        <CheckCircle2 class="stat-icon stat-icon--green" />
        <div>
          <p class="stat-value">{{ deliveredCount }}</p>
          <p class="stat-label">Delivered</p>
        </div>
      </div>
    </div>

    <!-- ── Challan Grid ───────────────────────────────────────────────── -->
    <div v-if="filteredChallans.length > 0" class="challan-grid">
      <div
        v-for="challan in filteredChallans"
        :key="challan.id"
        class="challan-card"
      >
        <!-- Card Top -->
        <div class="card-top">
          <div class="challan-no-badge">{{ challan.challan_no }}</div>
          <div class="card-top-right">
            <span :class="['status-badge', challan.status === 'Delivered' ? 'status-badge--green' : 'status-badge--amber']">
              {{ challan.status }}
            </span>

            <!-- 3-dot Menu -->
            <div class="menu-wrap">
              <button
                @click.stop="activeMenuId = activeMenuId === challan.id ? null : challan.id"
                class="menu-trigger"
              >
                <MoreVertical class="icon-xs" />
              </button>

              <template v-if="activeMenuId === challan.id">
                <div class="fixed inset-0 z-10" style="position:fixed;inset:0;z-index:10;" @click="activeMenuId = null" />
                <div class="dropdown-menu">
                  <button @click="setPreviewDoc(challan)" class="dropdown-item">
                    <Eye class="icon-xs text-zinc-400" /> Preview Challan
                  </button>
                  <button @click="handleEdit(challan)" class="dropdown-item">
                    <Edit2 class="icon-xs text-zinc-400" /> Edit Challan
                  </button>
                  <button
                    v-if="challan.status === 'Pending'"
                    @click="handleDeliver(challan)"
                    class="dropdown-item"
                  >
                    <CheckCircle2 class="icon-xs" style="color:#10b981;" /> Mark as Delivered
                  </button>
                  <div class="dropdown-divider" />
                  <button @click="shareViaEmail(challan)" class="dropdown-item">
                    <Mail class="icon-xs text-zinc-400" /> Share via Email
                  </button>
                  <button @click="shareViaWhatsApp(challan)" class="dropdown-item">
                    <MessageCircle class="icon-xs" style="color:#10b981;" /> Share on WhatsApp
                  </button>
                  <div class="dropdown-divider" />
                  <button @click="handleDelete(challan.id)" class="dropdown-item dropdown-item--danger">
                    <Trash2 class="icon-xs" /> Delete Challan
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Client Info -->
        <div class="client-row">
          <div class="client-icon-wrap">
            <Truck class="client-icon" />
          </div>
          <div class="client-info">
            <h3 class="client-name">{{ challan.customer?.name || 'Manual Delivery' }}</h3>
            <p class="client-ref">
              Ref: {{ challan.quotation_no }}
              <span v-if="challan.pr_no"> | PR: {{ challan.pr_no }}</span>
            </p>
          </div>
        </div>

        <!-- Items List -->
        <div class="items-list">
          <div
            v-for="(item, i) in challan.delivery_challan_products"
            :key="i"
            class="item-row"
          >
            <span class="item-name">{{ item.name }}</span>
            <span class="item-qty">x{{ item.qty }} {{ item.uom }}</span>
          </div>
          <p v-if="!challan.delivery_challan_products?.length" class="item-empty">No items.</p>
        </div>

        <!-- Card Footer -->
        <div class="card-footer">
          <div class="footer-left">
            <span class="delivery-date">
              {{ challan.status === 'Delivered'
                ? `Delivered: ${formatDate(challan.delivered_at)}`
                : 'Awaiting dispatch' }}
            </span>
            <div class="footer-actions">
              <button @click="setPreviewDoc(challan)" class="btn-mini">
                <Eye class="icon-xs" /> PREVIEW
              </button>
              <button @click="downloadChallanPDF(challan)" class="btn-mini btn-mini--fill">
                <Download class="icon-xs" /> DOWNLOAD DC
              </button>
            </div>
          </div>

          <button
            v-if="challan.status === 'Pending'"
            @click="handleDeliver(challan)"
            class="btn-deliver"
          >
            <Package style="width:14px;height:14px;" /> Deliver
          </button>
          <div v-else class="delivered-badge">
            <CheckCircle2 style="width:14px;height:14px;color:#10b981;" />
            <span>Completed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon-wrap">
        <Truck class="empty-icon" />
      </div>
      <h3 class="empty-title">No delivery challans found</h3>
      <p class="empty-sub">
        {{ searchQuery ? 'Try a different search term.' : 'Create your first delivery challan to get started.' }}
      </p>
      <button v-if="!searchQuery" @click="isAdding = true" class="btn-primary" style="margin-top:1.5rem;">
        <Plus class="icon-sm" /> New Delivery
      </button>
    </div>


    <!-- ══ MODAL: Dispatch from Quotation ══════════════════════════════ -->
    <transition name="fade">
      <div v-if="isAdding" class="modal-overlay" @click.self="isAdding = false">
        <div class="modal-box modal-box--sm">
          <div class="modal-header-bar">
            <h2 class="modal-title">Dispatch Inventory</h2>
            <button @click="isAdding = false" class="modal-close-btn"><X class="icon-sm" /></button>
          </div>

          <div class="modal-body">
            <div class="field-group">
              <label class="field-label">Related Quotation / Order</label>
              <select v-model="selectedQuotationId" class="field-select">
                <option value="">Select a source document</option>
                <option v-for="q in props.quotations" :key="q.id" :value="q.id">
                  {{ q.quotation_no }} ({{ q.customer?.name }})
                </option>
              </select>
            </div>

            <div class="info-box">
              <p class="info-text">
                Selecting a quotation will automatically populate the delivery items.
                Marking as "Delivered" will subtract stock from the master inventory.
              </p>
            </div>

            <div class="modal-footer-actions">
              <button
                @click="isAdding = false; isCreatingManual = true"
                class="btn-ghost"
              >Manual Dispatch</button>
              <button
                @click="handleCreateChallan"
                :disabled="!selectedQuotationId"
                class="btn-dark"
              >Create from Quote</button>
            </div>
          </div>
        </div>
      </div>
    </transition>


    <!-- ══ MODAL: Manual Challan Form ══════════════════════════════════ -->
    <transition name="fade">
      <div v-if="isCreatingManual" class="modal-overlay" @click.self="closeManual">
        <div class="modal-box modal-box--lg">
          <!-- Header -->
          <div class="modal-header-bar">
            <div>
              <h2 class="modal-title">{{ editingChallanId ? 'Edit' : 'Manual' }} Delivery Challan</h2>
              <p class="modal-sub">Dispatch items without a source quotation.</p>
            </div>
            <button @click="closeManual" class="modal-close-btn"><X class="icon-sm" /></button>
          </div>

          <!-- Scrollable body -->
          <div class="modal-scroll-body">
            <div class="manual-grid">
              <!-- Left: Fields -->
              <div class="manual-fields">
                <div class="field-group">
                  <label class="field-label">Customer</label>
                  <select v-model="manualChallan.customer_id" class="field-select">
                    <option value="">Select Customer</option>
                    <option v-for="c in props.customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                </div>
                <div class="two-col">
                  <div class="field-group">
                    <label class="field-label">PO Number</label>
                    <input type="text" v-model="manualChallan.po_no" class="field-input" placeholder="Optional" />
                  </div>
                  <div class="field-group">
                    <label class="field-label">Ref / PR Number</label>
                    <input type="text" v-model="manualChallan.pr_no" class="field-input" placeholder="Optional" />
                  </div>
                </div>
              </div>

              <!-- Right: Hint box -->
              <div class="manual-hint-box">
                <Truck style="width:40px;height:40px;color:#d4d4d8;margin:0 auto 8px;" />
                <p class="hint-text">Manual dispatch will generate a DC series.<br/>Ensure stock is checked before finalising.</p>
              </div>
            </div>

            <!-- Items section -->
            <div class="items-section">
              <h3 class="section-title">Line Items</h3>

              <!-- Add item row -->
              <div class="add-item-box">
                <div class="add-item-grid">
                  <input
                    type="text"
                    placeholder="Item Name *"
                    v-model="tempItem.name"
                    class="field-input col-span-5"
                    @keyup.enter="addManualItem"
                  />
                  <input type="text" placeholder="Code" v-model="tempItem.code" class="field-input col-span-2" />
                  <input type="number" placeholder="Qty" v-model.number="tempItem.qty" class="field-input col-span-2" min="1" />
                  <input type="text" placeholder="UOM" v-model="tempItem.uom" class="field-input col-span-2" />
                  <button @click="addManualItem" class="add-item-btn col-span-1">
                    <Plus style="width:18px;height:18px;" />
                  </button>
                </div>
                <input
                  type="text"
                  placeholder="Detailed Description / Serial Numbers (optional)"
                  v-model="tempItem.description"
                  class="field-input"
                  style="margin-top:10px;"
                />
              </div>

              <!-- Items table -->
              <div class="items-table-wrap">
                <table class="items-table">
                  <thead>
                    <tr>
                      <th>Code</th>
                      <th>Item</th>
                      <th class="text-center">Qty</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in manualChallan.items" :key="idx">
                      <td class="item-code">{{ item.code }}</td>
                      <td class="item-name-td">
                        <span class="font-semibold">{{ item.name }}</span>
                        <span v-if="item.description" class="item-desc"> – {{ item.description }}</span>
                      </td>
                      <td class="text-center">{{ item.qty }} {{ item.uom }}</td>
                      <td class="text-right">
                        <button @click="removeManualItem(idx)" class="remove-btn">
                          <Trash2 style="width:15px;height:15px;" />
                        </button>
                      </td>
                    </tr>
                    <tr v-if="manualChallan.items.length === 0">
                      <td colspan="4" class="empty-row">No items added yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer-bar">
            <button @click="closeManual" class="btn-ghost">Discard</button>
            <button
              @click="handleCreateManualChallan"
              :disabled="!manualChallan.customer_id || manualChallan.items.length === 0"
              class="btn-dark"
            >
              {{ editingChallanId ? 'Update Challan' : 'Save & Preview Challan' }}
            </button>
          </div>
        </div>
      </div>
    </transition>


    <!-- ══ MODAL: Challan Preview ═══════════════════════════════════════ -->
    <transition name="fade">
      <div v-if="showPreview && previewDoc" class="modal-overlay preview-overlay" @click.self="showPreview = false">
        <div class="preview-box">
          <!-- Preview Header -->
          <div class="preview-header">
            <div class="preview-header-left">
              <div class="preview-icon-wrap">
                <Truck style="width:22px;height:22px;color:#fff;" />
              </div>
              <div>
                <h2 class="preview-title">Delivery Challan Preview</h2>
                <p class="preview-challan-no">{{ previewDoc.challan_no }}</p>
              </div>
            </div>
            <div class="preview-header-actions">
              <button @click="downloadChallanPDF(previewDoc)" class="btn-download-pdf">
                <Download style="width:14px;height:14px;" /> Download PDF
              </button>
              <button @click="showPreview = false" class="modal-close-btn">
                <X class="icon-sm" />
              </button>
            </div>
          </div>

          <!-- A4 Preview Body -->
          <div class="preview-scroll">
            <div class="a4-page">
              <!-- Green accent bar -->
              <div class="a4-accent-bar"></div>

              <div class="a4-body">
                <!-- Company Header -->
                <div class="a4-company-row">
                  <div class="a4-logo">
                    <img
                      v-if="settings?.logoUrl"
                      :src="settings.logoUrl"
                      alt="Company Logo"
                      class="logo-img"
                    />
                    <span v-else class="company-name-big">
                      {{ (settings?.name || 'COMPANY').toUpperCase() }}
                    </span>
                  </div>
                  <div class="a4-company-info">
                    <p class="font-bold text-sm">{{ settings?.name }}</p>
                    <p>{{ settings?.address }}</p>
                    <p>{{ settings?.contact }}</p>
                    <p class="company-email">{{ settings?.email }}</p>
                    <p v-if="settings?.taxId">TRN: {{ settings.taxId }}</p>
                  </div>
                </div>

                <!-- Title banner -->
                <div class="a4-title-banner">
                  <h1 class="a4-doc-title">Delivery Challan</h1>
                  <div class="a4-meta-row">
                    <span>No: {{ previewDoc.challan_no }}</span>
                    <span>Date: {{ formatDate(previewDoc.status === 'Delivered' ? previewDoc.delivered_at : new Date()) }}</span>
                    <span>Ref Quote: {{ previewDoc.quotation_no }}</span>
                  </div>
                </div>

                <!-- Ship To / Status -->
                <div class="a4-info-grid">
                  <div class="a4-ship-to">
                    <h3 class="a4-section-label">Ship To</h3>
                    <p class="font-bold text-sm" style="margin-bottom:4px;">{{ previewDoc.customer?.name || 'Manual Client' }}</p>
                    <p class="a4-small-text">PO Number: {{ previewDoc.po_no || 'N/A' }}</p>
                  </div>
                  <div class="a4-status-info">
                    <div class="a4-status-row">
                      <span class="a4-label">Status</span>
                      <span :class="['a4-value', previewDoc.status === 'Delivered' ? 'a4-value--green' : 'a4-value--amber']">
                        {{ previewDoc.status }}
                      </span>
                    </div>
                    <div class="a4-status-row">
                      <span class="a4-label">Ref PR</span>
                      <span class="a4-value">{{ previewDoc.pr_no || 'N/A' }}</span>
                    </div>
                    <div v-if="previewDoc.status === 'Delivered'" class="a4-status-row">
                      <span class="a4-label">Delivered On</span>
                      <span class="a4-value">{{ formatDate(previewDoc.delivered_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Items Table -->
                <div class="a4-table-wrap">
                  <table class="a4-table">
                    <thead>
                      <tr>
                        <th style="width:40px;text-align:center;">SL</th>
                        <th>Description</th>
                        <th style="width:70px;text-align:center;">UOM</th>
                        <th style="width:70px;text-align:center;">Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, i) in previewDoc.delivery_challan_products" :key="i">
                        <td style="text-align:center;color:#a1a1aa;">{{ i + 1 }}</td>
                        <td>
                          <p class="font-bold" style="margin:0;">{{ item.name }}</p>
                          <p v-if="item.description" class="a4-item-desc">{{ item.description }}</p>
                        </td>
                        <td style="text-align:center;color:#71717a;">{{ item.uom }}</td>
                        <td style="text-align:center;font-weight:900;">{{ item.qty }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Declaration -->
                <div class="a4-declaration">
                  <h3 class="a4-section-label" style="color:#18181b;border-color:#e4e4e7;">Declaration</h3>
                  <p class="a4-small-text" style="margin-top:6px;">
                    Received the above items in good condition. All items are strictly for the designated purpose.
                    Any discrepancy must be reported within 24 hours of delivery.
                  </p>
                </div>

                <!-- Signatures -->
                <div class="a4-signatures">
                  <div class="sig-block">
                    <div class="sig-line"></div>
                    <p class="sig-title">Authorized By</p>
                    <p class="sig-name">{{ previewDoc.created_by || 'Admin' }}</p>
                  </div>
                  <div class="sig-block">
                    <div class="sig-line" style="background:#18181b;"></div>
                    <p class="sig-title">Receiver's Signature &amp; Stamp</p>
                    <p class="sig-name" style="font-style:italic;">Received In Good State</p>
                  </div>
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
import { router, usePage } from '@inertiajs/vue3';
import {
  Truck, CheckCircle2, Package, Search, Plus, Download,
  MoreVertical, Trash2, Mail, MessageCircle, Edit2, X, Eye
} from 'lucide-vue-next';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

// ── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  challans:   { type: Array, default: () => [] },
  quotations: { type: Array, default: () => [] },
  customers:  { type: Array, default: () => [] },
  settings:   { type: Object, default: () => ({}) },
});

// ── Reactive State ───────────────────────────────────────────────────────────
const filter            = ref('All');
const isAdding          = ref(false);
const isCreatingManual  = ref(false);
const searchQuery       = ref('');
const editingChallanId  = ref(null);
const activeMenuId      = ref(null);
const selectedQuotationId = ref('');
const showPreview       = ref(false);
const previewDoc        = ref(null);

const manualChallan = ref({
  customer_id: '',
  po_no:  '',
  pr_no:  '',
  items:  [],
});

const tempItem = ref({
  code:        '',
  name:        '',
  description: '',
  qty:         1,
  uom:         'pcs',
});

// ── Computed ─────────────────────────────────────────────────────────────────
const filteredChallans = computed(() => {
  if (!props.challans) return [];
  const q = searchQuery.value.toLowerCase();
  return props.challans
    .filter(c => filter.value === 'All' || c.status === filter.value)
    .filter(c =>
      c.challan_no?.toLowerCase().includes(q) ||
      c.quotation_no?.toLowerCase().includes(q) ||
      c.pr_no?.toLowerCase().includes(q) ||
      c.customer?.name?.toLowerCase().includes(q)
    );
});

const pendingCount   = computed(() => props.challans?.filter(c => c.status === 'Pending').length   ?? 0);
const deliveredCount = computed(() => props.challans?.filter(c => c.status === 'Delivered').length ?? 0);

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatDate = (dateValue) => {
  if (!dateValue) return 'N/A';
  const d = new Date(dateValue);
  if (isNaN(d.getTime())) return 'N/A';
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

// ── Actions ───────────────────────────────────────────────────────────────────
const handleDeliver = (challan) => {
  router.post('/mark-challan-delivered', { id: challan.id }, {
    onSuccess: () => { activeMenuId.value = null; },
  });
};

const handleDelete = (id) => {
  if (!confirm('Delete this delivery challan?')) return;
  router.get('/delete-delivery-challan/' + id, {}, {
    onSuccess: () => { activeMenuId.value = null; },
  });
};

const handleCreateChallan = () => {
  if (!selectedQuotationId.value) return;
  router.post('/create-delivery-challan', { quotation_id: selectedQuotationId.value }, {
    onSuccess: () => {
      isAdding.value = false;
      selectedQuotationId.value = '';
    },
  });
};

const handleCreateManualChallan = () => {
  if (!manualChallan.value.customer_id || manualChallan.value.items.length === 0) return;

  if (editingChallanId.value) {
    router.post('/update-delivery-challan', {
      id:          editingChallanId.value,
      customer_id: manualChallan.value.customer_id,
      po_no:       manualChallan.value.po_no,
      pr_no:       manualChallan.value.pr_no,
      items:       manualChallan.value.items,
    }, {
      onSuccess: () => closeManual(),
    });
  } else {
    router.post('/create-delivery-challan', manualChallan.value, {
      onSuccess: () => closeManual(),
    });
  }
};

// ── Utilities ─────────────────────────────────────────────────────────────────
const setPreviewDoc = (challan) => {
  previewDoc.value  = challan;
  showPreview.value = true;
  activeMenuId.value = null;
};

const handleEdit = (challan) => {
  editingChallanId.value = challan.id;
  manualChallan.value = {
    customer_id: challan.customer_id,
    po_no: challan.po_no  || '',
    pr_no: challan.pr_no  || '',
    items: challan.delivery_challan_products.map(i => ({ ...i })),
  };
  isCreatingManual.value = true;
  activeMenuId.value = null;
};

const closeManual = () => {
  isCreatingManual.value = false;
  editingChallanId.value = null;
  resetManualForm();
};

const resetManualForm = () => {
  manualChallan.value = { customer_id: '', po_no: '', pr_no: '', items: [] };
};

const addManualItem = () => {
  if (!tempItem.value.name?.trim()) return;
  manualChallan.value.items.push({ ...tempItem.value });
  tempItem.value = { code: '', name: '', description: '', qty: 1, uom: 'pcs' };
};

const removeManualItem = (idx) => {
  manualChallan.value.items.splice(idx, 1);
};

// ── Share ─────────────────────────────────────────────────────────────────────
const shareViaEmail = (challan) => {
  const subject   = `Delivery Challan ${challan.challan_no} from ${props.settings?.name || 'Our Company'}`;
  const itemsText = challan.delivery_challan_products.map(i => `- ${i.name}: ${i.qty} ${i.uom}`).join('\n');
  const companyInfo = `\n---\n${props.settings?.name || ''}\n${props.settings?.address || ''}\n${props.settings?.contact || ''}\n${props.settings?.email || ''}`;
  const body = `Hi ${challan.customer?.name || ''},\n\nPlease find the details for Delivery Challan ${challan.challan_no}.\n\nItems:\n${itemsText}\n\nReference Quote: ${challan.quotation_no}\nStatus: ${challan.status}${companyInfo}`;
  window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  activeMenuId.value = null;
};

const shareViaWhatsApp = (challan) => {
  const itemsText   = challan.delivery_challan_products.map(i => `• ${i.name}: ${i.qty} ${i.uom}`).join('\n');
  const companyInfo = `\n\n*${props.settings?.name || ''}*\n${props.settings?.contact || ''}\n${props.settings?.email || ''}`;
  const text = `*Delivery Challan ${challan.challan_no}*\n\nClient: ${challan.customer?.name || ''}\n\n*Items:*\n${itemsText}\n\n*Ref Quote:* ${challan.quotation_no}\n*Status:* ${challan.status}${companyInfo}`;
  window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
  activeMenuId.value = null;
};

// ── PDF Export ────────────────────────────────────────────────────────────────
const downloadDeliveryReport = () => {
  const doc          = new jsPDF();
  const primaryColor = [39, 39, 42];
  const companyName  = props.settings?.name || 'Company';

  doc.setFontSize(20);
  doc.setTextColor(...primaryColor);
  doc.text('DELIVERY SUMMARY REPORT', 14, 20);

  doc.setFontSize(9);
  doc.setTextColor(100);
  doc.text(`Company: ${companyName}`, 14, 26);
  doc.text(`Generated on: ${formatDate(new Date())}`, 14, 31);
  doc.text(`Total Deliveries: ${filteredChallans.value.length}`, 14, 36);

  autoTable(doc, {
    startY: 44,
    head:   [['DC No.', 'Customer', 'Quote Ref', 'Date', 'Status']],
    body:   filteredChallans.value.map(c => [
      c.challan_no,
      c.customer?.name || 'Manual',
      c.quotation_no,
      formatDate(c.status === 'Delivered' ? c.delivered_at : new Date()),
      c.status,
    ]),
    headStyles:  { fillColor: primaryColor },
    styles:      { fontSize: 8 },
    alternateRowStyles: { fillColor: [249, 249, 249] },
  });

  doc.save(`delivery_report_${Date.now()}.pdf`);
};

const downloadChallanPDF = (challan) => {
  const doc          = new jsPDF();
  const primaryColor = [74, 139, 94];
  const s            = props.settings || {};
  const companyName  = (s.name || 'COMPANY').toUpperCase();

  // ── Company Header ───────────────────────────────────────────────────
  if (s.logoUrl) {
    try {
      doc.addImage(s.logoUrl, 'PNG', 14, 10, 28, 28);
    } catch {
      doc.setFontSize(22);
      doc.setTextColor(...primaryColor);
      doc.text(companyName, 14, 25);
    }
  } else {
    doc.setFontSize(22);
    doc.setTextColor(...primaryColor);
    doc.text(companyName, 14, 25);
  }

  // Right-aligned company info
  doc.setFontSize(8);
  doc.setTextColor(100);
  let infoY = 15;
  if (s.address) {
    const lines = doc.splitTextToSize(s.address, 65);
    doc.text(lines, 200, infoY, { align: 'right' });
    infoY += lines.length * 4;
  }
  if (s.contact || s.email) {
    doc.text(`${s.contact || ''}${s.email ? ' | ' + s.email : ''}`, 200, infoY, { align: 'right' });
    infoY += 4;
  }
  if (s.taxId) {
    doc.text(`Tax ID: ${s.taxId}`, 200, infoY, { align: 'right' });
  }

  // ── Document Title ───────────────────────────────────────────────────
  doc.setFontSize(30);
  doc.setTextColor(0);
  doc.setFont('helvetica', 'bold');
  doc.text('DELIVERY CHALLAN', 105, 53, { align: 'center' });

  // ── Meta fields (left) ───────────────────────────────────────────────
  doc.setFontSize(10);
  doc.setTextColor(0);
  doc.setFont('helvetica', 'bold');
  const metaLabels = ['Challan No:', 'Date:', 'Ref Quote:', 'Status:'];
  metaLabels.forEach((label, i) => doc.text(label, 14, 73 + i * 7));

  doc.setFont('helvetica', 'normal');
  doc.text(challan.challan_no, 46, 73);
  doc.text(formatDate(challan.status === 'Delivered' ? challan.delivered_at : new Date()), 46, 80);
  doc.text(challan.quotation_no, 46, 87);
  doc.setFont('helvetica', 'bold');
  doc.text(challan.status.toUpperCase(), 46, 94);

  // ── Ship-to (right) ──────────────────────────────────────────────────
  doc.setFont('helvetica', 'bold');
  doc.text('SHIP TO:', 200, 73, { align: 'right' });
  doc.setFont('helvetica', 'normal');
  const clientName  = challan.customer?.name || 'Customer Details';
  const clientLines = doc.splitTextToSize(clientName, 60);
  doc.text(clientLines, 200, 80, { align: 'right' });

  // Divider
  doc.setDrawColor(...primaryColor);
  doc.setLineWidth(1.5);
  doc.line(14, 108, 196, 108);

  // ── Items Table ──────────────────────────────────────────────────────
  autoTable(doc, {
    startY: 116,
    head:   [['SL.', 'Item Code', 'Description', 'UOM', 'Ordered Qty', 'Delivered Qty']],
    body:   challan.delivery_challan_products.map((item, idx) => [
      idx + 1,
      item.code  || '',
      `${item.name}\n${item.description || ''}`,
      item.uom,
      item.qty,
      item.qty,
    ]),
    theme:      'grid',
    headStyles: {
      fillColor:  primaryColor,
      textColor:  [255, 255, 255],
      fontSize:   9,
      fontStyle:  'bold',
      halign:     'center',
    },
    columnStyles: {
      0: { halign: 'center', cellWidth: 15 },
      1: { halign: 'left',   cellWidth: 30 },
      2: { halign: 'left' },
      3: { halign: 'center', cellWidth: 20 },
      4: { halign: 'center', cellWidth: 25 },
      5: { halign: 'center', cellWidth: 25 },
    },
    styles:              { fontSize: 8, cellPadding: 4 },
    alternateRowStyles:  { fillColor: [245, 245, 245] },
  });

  // ── Declaration ──────────────────────────────────────────────────────
  const finalY = doc.lastAutoTable.finalY + 10;

  doc.setTextColor(0);
  doc.setFontSize(10);
  doc.setFont('helvetica', 'bold');
  doc.text('DECLARATION', 14, finalY + 10);
  doc.setLineWidth(0.5);
  doc.setDrawColor(...primaryColor);
  doc.line(14, finalY + 13, 196, finalY + 13);

  doc.setFont('helvetica', 'normal');
  doc.setFontSize(8);
  doc.setTextColor(100);
  doc.text('Received the above items in good condition. All items are strictly for the designated purpose.', 14, finalY + 20);

  // ── Signatures ───────────────────────────────────────────────────────
  const pageHeight = doc.internal.pageSize.height;
  const sigY       = pageHeight - 45;

  doc.setTextColor(0);
  doc.setFontSize(8);
  doc.setDrawColor(200);
  doc.setLineWidth(0.1);

  doc.line(14, sigY, 64, sigY);
  doc.setFont('helvetica', 'bold');
  doc.text('CREATED BY', 39, sigY + 5, { align: 'center' });
  doc.setFont('helvetica', 'normal');
  doc.text(challan.created_by || 'Admin', 39, sigY + 11, { align: 'center' });

  doc.line(14, pageHeight - 20, 80, pageHeight - 20);
  doc.setFont('helvetica', 'bold');
  doc.text("RECEIVER'S SIGNATURE & STAMP", 14, pageHeight - 14);

  doc.save(`${challan.challan_no}.pdf`);
};
</script>

<style scoped>
/* ── Root ──────────────────────────────────────────────────────────────── */
.deliveries-root {
  padding: 1.5rem 1.5rem 5rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  max-width: 1400px;
}

/* ── Header ─────────────────────────────────────────────────────────────── */
.deliveries-header { display: flex; flex-direction: column; gap: 1rem; }

.header-top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.page-title  { font-size: 1.75rem; font-weight: 700; color: #18181b; margin: 0; }
.page-sub    { color: #71717a; font-size: 0.85rem; margin: 0.2rem 0 0; }

/* Controls bar */
.controls-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #fff;
  border: 1px solid #e4e4e7;
  border-radius: 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}

.search-wrap { position: relative; flex: 1; min-width: 200px; }
.search-icon {
  position: absolute;
  left: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  width: 16px; height: 16px;
  color: #a1a1aa;
  pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 0.6rem 1rem 0.6rem 2.5rem;
  background: #f4f4f5;
  border: none;
  border-radius: 0.85rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: #18181b;
  outline: none;
  transition: box-shadow 0.2s;
}
.search-input:focus { box-shadow: 0 0 0 2px rgba(16,185,129,.25); }

.filter-tabs {
  display: flex;
  gap: 0.25rem;
  background: #f4f4f5;
  padding: 0.35rem;
  border-radius: 0.85rem;
  border: 1px solid #e4e4e7;
}
.filter-tab {
  padding: 0.4rem 1rem;
  border-radius: 0.6rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #71717a;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.18s;
}
.filter-tab--active {
  background: #fff;
  color: #18181b;
  box-shadow: 0 1px 4px rgba(0,0,0,.08);
  border: 1px solid #e4e4e7;
}

/* ── Stats Bar ──────────────────────────────────────────────────────────── */
.stats-bar {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.stat-card {
  flex: 1;
  min-width: 140px;
  display: flex;
  align-items: center;
  gap: 0.85rem;
  background: #fff;
  border: 1px solid #e4e4e7;
  border-radius: 1rem;
  padding: 1rem 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.stat-icon { width: 26px; height: 26px; flex-shrink: 0; }
.stat-icon--blue  { color: #3b82f6; }
.stat-icon--amber { color: #f59e0b; }
.stat-icon--green { color: #10b981; }
.stat-value { font-size: 1.4rem; font-weight: 800; color: #18181b; margin: 0; line-height: 1; }
.stat-label { font-size: 0.72rem; color: #a1a1aa; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin: 0.2rem 0 0; }

/* ── Challan Grid ───────────────────────────────────────────────────────── */
.challan-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.25rem;
}

.challan-card {
  background: #fff;
  border: 1px solid #e4e4e7;
  border-radius: 1.5rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
  transition: box-shadow 0.22s, transform 0.18s;
}
.challan-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,.10);
  transform: translateY(-2px);
}

/* Card top */
.card-top { display: flex; align-items: center; justify-content: space-between; }
.challan-no-badge {
  background: #f4f4f5;
  color: #71717a;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  transition: background 0.18s, color 0.18s;
}
.challan-card:hover .challan-no-badge { background: #18181b; color: #fff; }

.card-top-right { display: flex; align-items: center; gap: 0.5rem; }

.status-badge {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 0.5rem;
}
.status-badge--green { background: #d1fae5; color: #065f46; }
.status-badge--amber { background: #fef3c7; color: #92400e; }

/* 3-dot menu */
.menu-wrap    { position: relative; }
.menu-trigger {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px; height: 28px;
  border: none;
  background: transparent;
  border-radius: 0.5rem;
  color: #a1a1aa;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.menu-trigger:hover { background: #f4f4f5; color: #18181b; }

.dropdown-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 6px);
  width: 210px;
  background: #fff;
  border: 1px solid #f0f0f0;
  border-radius: 1rem;
  box-shadow: 0 12px 32px rgba(0,0,0,.14);
  padding: 0.4rem 0;
  z-index: 20;
  overflow: hidden;
  animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity:0; transform: scale(.95) translateY(-6px); } to { opacity:1; transform: scale(1) translateY(0); } }

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  width: 100%;
  padding: 0.55rem 1rem;
  font-size: 0.78rem;
  font-weight: 700;
  color: #3f3f46;
  background: transparent;
  border: none;
  cursor: pointer;
  text-align: left;
  transition: background 0.15s;
}
.dropdown-item:hover { background: #f9f9f9; }
.dropdown-item--danger { color: #dc2626; }
.dropdown-item--danger:hover { background: #fef2f2; }
.dropdown-divider { height: 1px; background: #f0f0f0; margin: 0.25rem 0; }

/* Client row */
.client-row   { display: flex; align-items: center; gap: 0.85rem; }
.client-icon-wrap {
  width: 46px; height: 46px;
  background: #f4f4f5;
  border: 1px solid #e4e4e7;
  border-radius: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.client-icon  { width: 22px; height: 22px; color: #18181b; }
.client-info  { overflow: hidden; }
.client-name  { font-size: 0.9rem; font-weight: 700; color: #18181b; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.client-ref   { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #a1a1aa; margin: 0.2rem 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Items list */
.items-list { display: flex; flex-direction: column; gap: 0; max-height: 130px; overflow-y: auto; }
.item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.45rem 0;
  border-bottom: 1px solid #fafafa;
  font-size: 0.78rem;
}
.item-row:last-child { border-bottom: none; }
.item-name  { color: #52525b; font-weight: 500; }
.item-qty   { font-size: 0.68rem; font-weight: 700; color: #18181b; background: #f4f4f5; padding: 0.15rem 0.5rem; border-radius: 0.35rem; white-space: nowrap; }
.item-empty { font-size: 0.78rem; color: #d4d4d8; font-style: italic; margin: 0; }

/* Card footer */
.card-footer {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  padding-top: 0.85rem;
  border-top: 1px solid #f4f4f5;
}
.footer-left    { display: flex; flex-direction: column; gap: 0.4rem; }
.delivery-date  { font-size: 0.67rem; color: #a1a1aa; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-style: italic; }
.footer-actions { display: flex; gap: 0.35rem; }

.btn-mini {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.65rem;
  font-size: 0.66rem;
  font-weight: 700;
  background: #fff;
  color: #18181b;
  border: 1px solid #e4e4e7;
  border-radius: 0.55rem;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-mini:hover { background: #f9f9f9; }
.btn-mini--fill { background: #f4f4f5; }
.btn-mini--fill:hover { background: #e4e4e7; }

.btn-deliver {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 1.1rem;
  background: #059669;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .08em;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(5,150,105,.25);
  transition: background 0.15s, transform 0.15s;
}
.btn-deliver:hover  { background: #047857; }
.btn-deliver:active { transform: scale(.96); }

.delivered-badge {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: #f4f4f5;
  border: 1px solid #e4e4e7;
  border-radius: 0.75rem;
  padding: 0.45rem 0.75rem;
}
.delivered-badge span { font-size: 0.68rem; font-weight: 800; color: #a1a1aa; text-transform: uppercase; letter-spacing: .06em; }

/* ── Empty State ────────────────────────────────────────────────────────── */
.empty-state {
  text-align: center;
  padding: 5rem 2rem;
  background: #fff;
  border: 1px dashed #e4e4e7;
  border-radius: 1.5rem;
}
.empty-icon-wrap {
  width: 70px; height: 70px;
  background: #f4f4f5;
  border-radius: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
}
.empty-icon  { width: 32px; height: 32px; color: #d4d4d8; }
.empty-title { font-size: 1.1rem; font-weight: 700; color: #18181b; margin: 0 0 0.5rem; }
.empty-sub   { font-size: 0.85rem; color: #a1a1aa; margin: 0; }

/* ── Buttons ────────────────────────────────────────────────────────────── */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.4rem;
  background: #18181b;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  border-radius: 0.85rem;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0,0,0,.15);
  transition: background 0.2s, transform 0.15s;
  text-decoration: none;
}
.btn-primary:hover  { background: #059669; }
.btn-primary:active { transform: scale(.96); }

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.1rem;
  background: #fff;
  color: #18181b;
  font-size: 0.78rem;
  font-weight: 700;
  border: 1px solid #e4e4e7;
  border-radius: 0.85rem;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-outline:hover { background: #f4f4f5; }

.btn-dark {
  padding: 0.55rem 1.4rem;
  background: #18181b;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-dark:hover           { background: #059669; }
.btn-dark:disabled        { opacity: 0.45; cursor: not-allowed; }

.btn-ghost {
  padding: 0.55rem 1.1rem;
  background: transparent;
  color: #71717a;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-ghost:hover { background: #f4f4f5; }

/* ── Icons ──────────────────────────────────────────────────────────────── */
.icon-sm { width: 18px; height: 18px; }
.icon-xs { width: 15px; height: 15px; }

/* ── Modal shared ───────────────────────────────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(0,0,0,.55);
  backdrop-filter: blur(4px);
}

.modal-box {
  position: relative;
  background: #fff;
  border-radius: 1.5rem;
  width: 100%;
  box-shadow: 0 24px 64px rgba(0,0,0,.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.modal-box--sm { max-width: 480px; }
.modal-box--lg { max-width: 860px; max-height: 88vh; }

.modal-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.4rem 1.75rem;
  border-bottom: 1px solid #f0f0f0;
}
.modal-title { font-size: 1.25rem; font-weight: 800; color: #18181b; margin: 0; }
.modal-sub   { font-size: 0.82rem; color: #71717a; margin: 0.2rem 0 0; }
.modal-close-btn {
  width: 34px; height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  border-radius: 0.6rem;
  cursor: pointer;
  color: #a1a1aa;
  transition: background 0.15s, color 0.15s;
}
.modal-close-btn:hover { background: #f4f4f5; color: #18181b; }

.modal-body          { padding: 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1.1rem; }
.modal-scroll-body   { flex: 1; overflow-y: auto; padding: 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1.5rem; }
.modal-footer-bar    { padding: 1rem 1.75rem; border-top: 1px solid #f0f0f0; background: #fcfcfc; display: flex; justify-content: flex-end; gap: 0.75rem; }
.modal-footer-actions{ display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem; }

/* ── Form fields ────────────────────────────────────────────────────────── */
.field-group { display: flex; flex-direction: column; gap: 0.35rem; }
.field-label {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: #71717a;
}
.field-input {
  padding: 0.6rem 0.85rem;
  background: #f9f9f9;
  border: 1px solid #e4e4e7;
  border-radius: 0.75rem;
  font-size: 0.85rem;
  color: #18181b;
  outline: none;
  transition: border-color 0.18s, box-shadow 0.18s;
  width: 100%;
  box-sizing: border-box;
}
.field-input:focus { border-color: #a1a1aa; box-shadow: 0 0 0 2px rgba(0,0,0,.06); }

.field-select {
  padding: 0.65rem 0.85rem;
  background: #f9f9f9;
  border: 1px solid #e4e4e7;
  border-radius: 0.75rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: #18181b;
  outline: none;
  width: 100%;
  cursor: pointer;
  transition: border-color 0.18s;
}
.field-select:focus { border-color: #a1a1aa; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }

.info-box { background: #f9f9f9; border: 1px dashed #e4e4e7; border-radius: 0.85rem; padding: 0.85rem 1rem; }
.info-text { font-size: 0.8rem; color: #71717a; margin: 0; line-height: 1.55; }

/* Manual modal layout */
.manual-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.manual-fields { display: flex; flex-direction: column; gap: 1rem; }
.manual-hint-box {
  background: #f9f9f9;
  border: 1px solid #f0f0f0;
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 1.5rem;
}
.hint-text { font-size: 0.78rem; color: #a1a1aa; font-weight: 500; line-height: 1.5; margin: 0; }

/* Items section */
.items-section { display: flex; flex-direction: column; gap: 0.85rem; }
.section-title { font-size: 0.9rem; font-weight: 800; color: #18181b; margin: 0; }

.add-item-box { background: #f9f9f9; border: 1px solid #e4e4e7; border-radius: 1rem; padding: 1rem; }
.add-item-grid {
  display: grid;
  grid-template-columns: 5fr 2fr 2fr 2fr 1fr;
  gap: 0.6rem;
}
.col-span-1 { grid-column: span 1; }
.col-span-2 { grid-column: span 2; }
.col-span-5 { grid-column: span 5; }

.add-item-btn {
  width: 100%;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #18181b;
  color: #fff;
  border: none;
  border-radius: 0.65rem;
  cursor: pointer;
  transition: background 0.15s;
}
.add-item-btn:hover { background: #3f3f46; }

.items-table-wrap { border: 1px solid #f0f0f0; border-radius: 1rem; overflow: hidden; }
.items-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.items-table thead { background: #f9f9f9; border-bottom: 1px solid #f0f0f0; }
.items-table th { padding: 0.65rem 1rem; text-align: left; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #a1a1aa; }
.items-table tbody tr { border-bottom: 1px solid #f9f9f9; }
.items-table tbody tr:last-child { border-bottom: none; }
.items-table td { padding: 0.65rem 1rem; }

.item-code    { font-family: monospace; font-size: 0.72rem; color: #a1a1aa; }
.item-name-td { color: #18181b; font-weight: 600; }
.item-desc    { color: #a1a1aa; font-size: 0.72rem; }
.text-center  { text-align: center; font-weight: 700; color: #18181b; }
.text-right   { text-align: right; }
.font-semibold{ font-weight: 600; }
.empty-row    { text-align: center; padding: 2.5rem; color: #d4d4d8; font-style: italic; }

.remove-btn { background: transparent; border: none; cursor: pointer; color: #d4d4d8; transition: color 0.15s; }
.remove-btn:hover { color: #dc2626; }

/* ── Preview Modal ──────────────────────────────────────────────────────── */
.preview-overlay { z-index: 100; backdrop-filter: blur(8px); }
.preview-box {
  position: relative;
  background: #fff;
  border-radius: 2rem;
  width: 100%;
  max-width: 900px;
  max-height: 92vh;
  box-shadow: 0 32px 80px rgba(0,0,0,.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.2);
}

.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.5rem;
  border-bottom: 1px solid #f0f0f0;
  background: #fff;
  flex-shrink: 0;
}
.preview-header-left  { display: flex; align-items: center; gap: 1rem; }
.preview-icon-wrap {
  width: 44px; height: 44px;
  background: #18181b;
  border-radius: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.preview-title     { font-size: 1rem; font-weight: 900; color: #18181b; margin: 0; letter-spacing: -.01em; }
.preview-challan-no{ font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #a1a1aa; margin: 0.15rem 0 0; }
.preview-header-actions { display: flex; align-items: center; gap: 0.5rem; }

.btn-download-pdf {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  background: #059669;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  border: none;
  border-radius: 0.6rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(5,150,105,.25);
  transition: background 0.15s, transform 0.15s;
}
.btn-download-pdf:hover  { background: #047857; }
.btn-download-pdf:active { transform: scale(.96); }

/* A4 page */
.preview-scroll { flex: 1; overflow-y: auto; padding: 2rem; background: #f4f4f5; }

.a4-page {
  background: #fff;
  border-radius: 1rem;
  max-width: 780px;
  min-height: 1000px;
  margin: 0 auto;
  box-shadow: 0 8px 32px rgba(0,0,0,.10);
  border: 1px solid #e4e4e7;
  overflow: hidden;
}
.a4-accent-bar { height: 8px; background: linear-gradient(90deg, #059669, #10b981); }
.a4-body { padding: 2.5rem 3rem; display: flex; flex-direction: column; gap: 2rem; }

/* Company row */
.a4-company-row { display: flex; justify-content: space-between; align-items: flex-start; }
.a4-logo { display: flex; align-items: center; }
.logo-img { height: 60px; width: auto; object-fit: contain; }
.company-name-big { font-size: 1.6rem; font-weight: 900; color: #059669; letter-spacing: -.03em; }
.a4-company-info {
  text-align: right;
  font-size: 0.72rem;
  color: #71717a;
  line-height: 1.6;
  max-width: 220px;
}
.a4-company-info .font-bold { color: #18181b; font-weight: 700; }
.company-email { color: #059669; font-weight: 700; }

/* Title banner */
.a4-title-banner {
  text-align: center;
  padding: 2rem 0;
  border-top: 1px solid #f0f0f0;
  border-bottom: 1px solid #f0f0f0;
  background: #fafafa;
  margin: 0 -3rem;
  padding-left: 3rem;
  padding-right: 3rem;
}
.a4-doc-title { font-size: 2.2rem; font-weight: 900; color: #18181b; letter-spacing: -.04em; text-transform: uppercase; margin: 0 0 0.5rem; }
.a4-meta-row {
  display: flex;
  justify-content: center;
  gap: 2rem;
  font-size: 0.68rem;
  font-weight: 700;
  color: #a1a1aa;
  text-transform: uppercase;
  letter-spacing: .06em;
}

/* Info grid */
.a4-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; }
.a4-ship-to   { display: flex; flex-direction: column; gap: 0.4rem; }
.a4-section-label {
  font-size: 0.68rem;
  font-weight: 900;
  color: #059669;
  text-transform: uppercase;
  letter-spacing: .08em;
  border-bottom: 1px solid #d1fae5;
  padding-bottom: 0.3rem;
  margin: 0 0 0.4rem;
}
.a4-small-text { font-size: 0.72rem; color: #71717a; margin: 0; font-style: italic; }
.font-bold { font-weight: 700; }
.text-sm   { font-size: 0.85rem; }

.a4-status-info { display: flex; flex-direction: column; gap: 0.5rem; }
.a4-status-row  { display: flex; justify-content: space-between; border-bottom: 1px solid #fafafa; padding: 0.3rem 0; }
.a4-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #a1a1aa; }
.a4-value { font-size: 0.78rem; font-weight: 700; color: #18181b; }
.a4-value--green { color: #059669; }
.a4-value--amber { color: #d97706; }

/* Items table */
.a4-table-wrap { border-radius: 0.85rem; overflow: hidden; border: 1px solid #f0f0f0; }
.a4-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.a4-table thead tr { background: #18181b; color: #fff; }
.a4-table thead th { padding: 0.85rem 1.25rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; font-size: 0.68rem; }
.a4-table tbody tr { border-bottom: 1px solid #f4f4f5; transition: background 0.12s; }
.a4-table tbody tr:last-child { border-bottom: none; }
.a4-table tbody tr:hover { background: #fafafa; }
.a4-table td { padding: 0.85rem 1.25rem; }
.a4-item-desc { font-size: 0.68rem; color: #a1a1aa; margin: 0.2rem 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 350px; }

/* Declaration */
.a4-declaration { border-top: 1px solid #f0f0f0; padding-top: 1.5rem; }

/* Signatures */
.a4-signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; padding-top: 3rem; padding-bottom: 1rem; }
.sig-block     { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.sig-line      { width: 100%; height: 1px; background: #e4e4e7; }
.sig-title     { font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; color: #18181b; margin: 0; }
.sig-name      { font-size: 0.65rem; font-weight: 600; color: #a1a1aa; text-transform: uppercase; letter-spacing: .04em; margin: 0; }

/* ── Transition ─────────────────────────────────────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.22s ease; }
.fade-enter-from,   .fade-leave-to     { opacity: 0; }

/* ── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 640px) {
  .deliveries-root { padding: 1rem 1rem 5rem; }
  .challan-grid    { grid-template-columns: 1fr; }
  .manual-grid     { grid-template-columns: 1fr; }
  .add-item-grid   { grid-template-columns: 1fr 1fr; }
  .a4-signatures   { grid-template-columns: 1fr; gap: 2rem; }
  .a4-info-grid    { grid-template-columns: 1fr; }
  .a4-body         { padding: 1.5rem; }
  .a4-title-banner { margin: 0 -1.5rem; padding-left: 1.5rem; padding-right: 1.5rem; }
  .stats-bar       { flex-wrap: nowrap; overflow-x: auto; }
  .modal-box--lg   { max-height: 95vh; border-radius: 1rem; }
}
</style>
