<template>
    <div class="banking-workspace px-4 py-3 animate-fade-in">
        <!-- ── HEADER SECTION ────────────────────────────────────────────────────────── -->
        <header class="workspace-header mb-5 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="workspace-title font-bold">Banking</h1>
                <p class="workspace-subtitle mb-0">Manage corporate bank accounts, balances, and financial transactions.</p>
            </div>
            <div class="d-flex gap-2">
                <button
                    @click="openAddAccountModal"
                    class="btn btn-light border rounded-3 px-3 py-2 text-xs font-bold shadow-sm hover-bg-light"
                >
                    <i class="fa fa-plus me-1 text-muted"></i> Add Account
                </button>
                <button
                    @click="openAddTransactionModal"
                    class="btn btn-dark rounded-3 px-3 py-2 text-xs font-bold shadow-sm"
                >
                    <i class="fa fa-wallet me-1"></i> New Transaction
                </button>
            </div>
        </header>

        <!-- ── BANK ACCOUNTS CARDS GRID ────────────────────────────────────────────── -->
        <div class="row g-4 mb-5">
            <div v-for="acc in bankAccounts" :key="acc.id" class="col-12 col-md-6 col-lg-4">
                <div class="bank-card rounded-4 p-4 text-white position-relative overflow-hidden shadow">
                    <!-- Glassmorphism accent circle -->
                    <div class="card-accent-circle"></div>

                    <div class="position-relative d-flex flex-column justify-content-between h-100">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-landmark-wrapper">
                                    <i class="fa fa-university fs-4 text-white-50"></i>
                                </div>
                                <span class="badge bg-white-10 text-uppercase tracking-wider text-xs font-bold">
                                    {{ acc.bankName }}
                                </span>
                            </div>
                            <h3 class="bank-account-name font-bold mb-1">{{ acc.accountName }}</h3>
                            <p class="bank-account-number font-mono tracking-widest text-xs text-white-50 mb-0">
                                {{ maskAccountNumber(acc.accountNumber) }}
                            </p>
                        </div>
                        
                        <div class="mt-4 pt-2 border-top border-white-10">
                            <span class="text-uppercase tracking-wider text-[10px] text-white-50 font-bold d-block mb-1">
                                Available Balance
                            </span>
                            <p class="available-balance font-mono font-bold mb-0">
                                {{ formatCurrency(acc.balance) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State for Accounts -->
            <div v-if="bankAccounts.length === 0" class="col-12 text-center py-5">
                <div class="empty-state bg-white border rounded-4 p-5 shadow-sm max-w-[500px] mx-auto">
                    <i class="fa fa-university text-muted fs-1 mb-3"></i>
                    <h4 class="font-bold text-dark mb-2">No Bank Accounts Found</h4>
                    <p class="text-muted text-xs mb-4">You haven't registered any bank accounts yet. Add your first corporate account to start logging transactions.</p>
                    <button @click="openAddAccountModal" class="btn btn-dark rounded-3 px-3 py-2 text-xs font-bold">
                        <i class="fa fa-plus me-1"></i> Add Bank Account
                    </button>
                </div>
            </div>
        </div>

        <!-- ── TRANSACTION HISTORY SECTION ────────────────────────────────────────── -->
        <div class="bg-white border rounded-4 shadow-sm overflow-hidden mb-5">
            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-history-wrapper bg-dark text-white rounded-3 p-2">
                        <i class="fa fa-history"></i>
                    </div>
                    <div>
                        <h2 class="section-title font-bold mb-0">Transaction History</h2>
                        <p class="section-subtitle mb-0 text-muted">A live log of all corporate bank deposits and withdrawals.</p>
                    </div>
                </div>
                <span class="badge bg-dark rounded-pill px-3 py-2 text-xs font-bold font-mono">
                    {{ bankTransactions.length }} total
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-xs">
                    <thead>
                        <tr class="bg-light-gray text-muted text-uppercase tracking-wider">
                            <th class="px-4 py-3 border-bottom font-bold">Transaction</th>
                            <th class="px-4 py-3 border-bottom font-bold">Category</th>
                            <th class="px-4 py-3 border-bottom font-bold">Reference / Note</th>
                            <th class="px-4 py-3 border-bottom font-bold text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in bankTransactions" :key="t.id" class="transition-colors">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="transaction-icon-wrapper" :class="t.type === 'Deposit' ? 'bg-emerald-50 text-success' : 'bg-rose-50 text-danger'">
                                        <i class="fa" :class="t.type === 'Deposit' ? 'fa-arrow-circle-up' : 'fa-arrow-circle-down'"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-dark mb-0">{{ t.type }}</p>
                                        <span class="text-muted text-[10px]">{{ formatDate(t.date) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge bg-light text-dark border font-bold text-uppercase px-2 py-1">
                                    {{ t.category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted max-w-[300px] truncate">
                                {{ t.reference || 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-end font-mono font-bold" :class="t.type === 'Deposit' ? 'text-success' : 'text-danger'">
                                {{ t.type === 'Deposit' ? '+' : '-' }}{{ formatCurrency(t.amount) }}
                            </td>
                        </tr>

                        <!-- Empty State for Transactions -->
                        <tr v-if="bankTransactions.length === 0">
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa fa-receipt fs-2 mb-2 d-block"></i>
                                <span class="font-bold text-dark d-block mb-1">No Transactions Registered</span>
                                <span class="text-xs">Select "New Transaction" above to log deposit or withdrawal events.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── MODAL 1: ADD BANK ACCOUNT ───────────────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="isAddingAcc" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--medium bg-white rounded-4 shadow-lg p-4">
                    <div class="modal-header-custom border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-cube bg-dark text-white rounded-3 p-2">
                                <i class="fa fa-university fs-5"></i>
                            </div>
                            <div>
                                <h3 class="modal-title-custom font-bold">Add Corporate Account</h3>
                                <p class="modal-subtitle-custom text-muted mb-0">Register a new corporate bank account.</p>
                            </div>
                        </div>
                        <button @click="closeAddAccountModal" class="btn-close-modal border-0 bg-transparent">
                            <i class="fa fa-times text-muted"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitAddAccount" class="space-y-4">
                        <div class="form-group">
                            <label class="form-label text-xs font-bold text-dark mb-1">Account Name</label>
                            <input
                                v-model="accForm.accountName"
                                required
                                placeholder="e.g. Operating Checking Account"
                                type="text"
                                class="form-control rounded-3 py-2 text-xs"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs font-bold text-dark mb-1">Bank Name</label>
                            <input
                                v-model="accForm.bankName"
                                required
                                placeholder="e.g. Chase, HSBC, Bank of America"
                                type="text"
                                class="form-control rounded-3 py-2 text-xs"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs font-bold text-dark mb-1">Account Number</label>
                            <input
                                v-model="accForm.accountNumber"
                                required
                                placeholder="e.g. 1234567890"
                                type="text"
                                class="form-control rounded-3 py-2 text-xs font-mono"
                            />
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label text-xs font-bold text-dark mb-1">Initial Balance</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-xs font-mono">$</span>
                                <input
                                    v-model.number="accForm.balance"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    type="number"
                                    class="form-control rounded-end-3 py-2 text-xs font-mono"
                                />
                            </div>
                        </div>

                        <div class="modal-footer-custom border-top pt-3 d-flex gap-2">
                            <button
                                type="button"
                                @click="closeAddAccountModal"
                                class="btn btn-light border rounded-3 py-2 text-xs font-bold flex-grow-1"
                            >
                                Discard
                            </button>
                            <button
                                type="submit"
                                class="btn btn-dark rounded-3 py-2 text-xs font-bold flex-grow-1 hover-success"
                            >
                                Add Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

        <!-- ── MODAL 2: LOG NEW TRANSACTION ────────────────────────────────────────── -->
        <transition name="modal-fade">
            <div v-if="isAddingTrans" class="modal-backdrop-custom">
                <div class="modal-container-custom modal-container-custom--medium bg-white rounded-4 shadow-lg p-4">
                    <div class="modal-header-custom border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-cube bg-dark text-white rounded-3 p-2">
                                <i class="fa fa-wallet fs-5"></i>
                            </div>
                            <div>
                                <h3 class="modal-title-custom font-bold">Log Transaction</h3>
                                <p class="modal-subtitle-custom text-muted mb-0">Record bank deposit or withdrawal events.</p>
                            </div>
                        </div>
                        <button @click="closeAddTransactionModal" class="btn-close-modal border-0 bg-transparent">
                            <i class="fa fa-times text-muted"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitAddTransaction" class="space-y-4">
                        <div class="form-group">
                            <label class="form-label text-xs font-bold text-dark mb-1">Select Bank Account</label>
                            <select
                                v-model="transForm.bankAccountId"
                                required
                                class="form-select rounded-3 py-2 text-xs"
                            >
                                <option value="">-- Choose Account --</option>
                                <option v-for="a in bankAccounts" :key="a.id" :value="a.id">
                                    {{ a.bankName }} - {{ a.accountName }} (${{ a.balance.toLocaleString() }})
                                </option>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label text-xs font-bold text-dark mb-1">Transaction Type</label>
                                    <select
                                        v-model="transForm.type"
                                        required
                                        class="form-select rounded-3 py-2 text-xs"
                                    >
                                        <option value="Deposit">Deposit (+)</option>
                                        <option value="Withdrawal">Withdrawal (-)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label text-xs font-bold text-dark mb-1">Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-xs font-mono">$</span>
                                        <input
                                            v-model.number="transForm.amount"
                                            required
                                            min="0.01"
                                            step="0.01"
                                            placeholder="0.00"
                                            type="number"
                                            class="form-control rounded-end-3 py-2 text-xs font-mono"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label text-xs font-bold text-dark mb-1">Category</label>
                            <input
                                v-model="transForm.category"
                                required
                                placeholder="e.g. Sales, Rent, Equipment, Software"
                                type="text"
                                class="form-control rounded-3 py-2 text-xs"
                            />
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label text-xs font-bold text-dark mb-1">Reference / Memo</label>
                            <input
                                v-model="transForm.reference"
                                placeholder="e.g. Inv #2026-0045, Office rent Q1"
                                type="text"
                                class="form-control rounded-3 py-2 text-xs"
                            />
                        </div>

                        <div class="modal-footer-custom border-top pt-3 d-flex gap-2">
                            <button
                                type="button"
                                @click="closeAddTransactionModal"
                                class="btn btn-light border rounded-3 py-2 text-xs font-bold flex-grow-1"
                            >
                                Discard
                            </button>
                            <button
                                type="submit"
                                class="btn btn-dark rounded-3 py-2 text-xs font-bold flex-grow-1 hover-success"
                            >
                                Process Transaction
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
import { usePage, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';

const props = defineProps({
    accounts: Array,
    transactions: Array
});

const page = usePage();
const toaster = createToaster({ position: 'top-right' });

// Computed fallbacks from Inertia props
const bankAccounts = computed(() => props.accounts || page.props.accounts || []);
const bankTransactions = computed(() => props.transactions || page.props.transactions || []);

// Modal States
const isAddingAcc = ref(false);
const isAddingTrans = ref(false);

// Forms
const accForm = ref({
    accountName: '',
    bankName: '',
    accountNumber: '',
    balance: 0
});

const transForm = ref({
    bankAccountId: '',
    amount: 0,
    type: 'Deposit',
    category: '',
    reference: ''
});

// Modals Trigger Handlers
function openAddAccountModal() {
    isAddingAcc.value = true;
    accForm.value = { accountName: '', bankName: '', accountNumber: '', balance: 0 };
}

function closeAddAccountModal() {
    isAddingAcc.value = false;
}

function openAddTransactionModal() {
    if (bankAccounts.value.length === 0) {
        toaster.warning('Please register a corporate bank account first.');
        return;
    }
    isAddingTrans.value = true;
    transForm.value = { bankAccountId: '', amount: 0, type: 'Deposit', category: '', reference: '' };
}

function closeAddTransactionModal() {
    isAddingTrans.value = false;
}

// Submits
function submitAddAccount() {
    router.post('/add-bank-account', accForm.value, {
        onSuccess: () => {
            closeAddAccountModal();
            toaster.success('Bank account registered successfully!');
        },
        onError: (err) => {
            const msg = Object.values(err)[0] || 'Failed to register account';
            toaster.error(msg);
        }
    });
}

function submitAddTransaction() {
    if (!transForm.value.bankAccountId) {
        toaster.warning('Please select a target bank account');
        return;
    }
    if (transForm.value.amount <= 0) {
        toaster.warning('Transaction amount must be greater than zero');
        return;
    }

    router.post('/add-bank-transaction', transForm.value, {
        onSuccess: () => {
            closeAddTransactionModal();
            toaster.success('Transaction processed successfully!');
        },
        onError: (err) => {
            const msg = Object.values(err)[0] || 'Transaction processing failed';
            toaster.error(msg);
        }
    });
}

// Helper utility functions
function formatCurrency(val) {
    const num = parseFloat(val) || 0;
    return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(val) {
    if (!val) return 'N/A';
    const date = new Date(val);
    if (isNaN(date.getTime())) return val;
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function maskAccountNumber(number) {
    if (!number) return '****';
    const clean = String(number).trim();
    if (clean.length <= 4) return clean;
    return '•••• •••• •••• ' + clean.substring(clean.length - 4);
}
</script>

<style scoped>
.banking-workspace {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #1f2937;
}

/* Animations */
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header style mapping */
.workspace-header {
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

/* Premium Bank Card with gradient overlay */
.bank-card {
    background: linear-gradient(135deg, #18181b 0%, #09090b 100%);
    min-height: 180px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.bank-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.4);
}

.card-accent-circle {
    position: absolute;
    top: -20px;
    right: -20px;
    width: 140px;
    height: 140px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
    filter: blur(15px);
    transition: background 0.25s ease;
}

.bank-card:hover .card-accent-circle {
    background: rgba(255, 255, 255, 0.06);
}

.icon-landmark-wrapper {
    background: rgba(255, 255, 255, 0.08);
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.bank-account-name {
    font-size: 16px;
    color: #ffffff;
    letter-spacing: -0.01em;
}

.available-balance {
    font-size: 24px;
    letter-spacing: -0.02em;
    color: #ffffff;
}

.border-white-10 {
    border-color: rgba(255, 255, 255, 0.08) !important;
}

.bg-white-10 {
    background-color: rgba(255, 255, 255, 0.08) !important;
}

/* History logs structure */
.icon-history-wrapper {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-title {
    font-size: 14px;
    color: #111827;
}

.section-subtitle {
    font-size: 10px;
}

.bg-light-gray {
    background-color: #f9fafb;
}

.transaction-icon-wrapper {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 16px;
}

/* Custom premium modal overlays matching invoice workspace */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}

.modal-container-custom {
    animation: zoomIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-container-custom--medium {
    width: 100%;
    max-width: 460px;
}

.icon-cube {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close-modal {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.btn-close-modal:hover {
    background: #e5e7eb;
}

.form-label {
    display: block;
}

.input-group-text {
    border-right: none;
}

.rounded-end-3 {
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

/* Modal fade animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.hover-success:hover {
    background-color: #059669 !important;
    border-color: #059669 !important;
    color: #fff !important;
}
</style>
