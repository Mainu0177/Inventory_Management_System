<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Expenses</h1>
        <p class="text-zinc-500 text-sm mt-1">Record and categorize business expenditures.</p>
      </div>
      <button
        @click="isAdding = true"
        class="flex items-center justify-center gap-2 px-6 py-2 bg-zinc-950 text-white rounded-xl text-sm font-bold hover:bg-zinc-800 transition-all shadow-lg active:scale-95"
      >
        <Plus class="w-4 h-4" />
        Log Expense
      </button>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="md:col-span-2 lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm">
           <h2 class="text-sm font-bold uppercase tracking-widest text-zinc-400 mb-6">Expense Summary</h2>
           <div class="space-y-4">
              <div v-for="stat in summaryStats" :key="stat.cat" class="space-y-1.5">
                <div class="flex justify-between text-sm font-medium">
                  <span class="text-zinc-500">{{ stat.cat }}</span>
                  <span class="text-zinc-900">{{ formatCurrency(stat.val) }}</span>
                </div>
                <div class="h-1.5 w-full bg-zinc-100 rounded-full overflow-hidden">
                  <div 
                    :style="{ width: Math.min(100, (stat.val / maxExpenseAmount) * 100) + '%' }"
                    :class="['h-full rounded-full transition-all duration-1000 ease-out', stat.color]" 
                  ></div>
                </div>
              </div>
           </div>
        </div>
      </div>

      <div class="md:col-span-2 space-y-4">
        <div v-for="exp in props.expenses" :key="exp.id" class="bg-white p-5 rounded-2xl border border-zinc-200 shadow-sm flex items-center justify-between hover:border-zinc-300 transition-all cursor-pointer group">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-900 group-hover:bg-zinc-950 group-hover:text-white transition-colors">
              <Tag class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-zinc-900">{{ exp.category }}</h3>
              <p class="text-xs text-zinc-500">{{ exp.description || 'No description' }}</p>
            </div>
          </div>
          <div class="text-right flex items-center gap-6">
            <div>
              <p class="text-sm font-bold text-rose-600">-{{ formatCurrency(exp.amount) }}</p>
              <p class="text-[10px] text-zinc-400 font-medium uppercase">{{ exp.payment_method }} • {{ formatDate(exp.date) }}</p>
            </div>
            <ChevronRight class="w-4 h-4 text-zinc-300 group-hover:text-zinc-900 transition-colors" />
          </div>
        </div>
        
        <div v-if="props.expenses.length === 0" class="py-20 text-center bg-zinc-50 rounded-3xl border border-dashed border-zinc-200">
          <p class="text-zinc-400 font-medium italic">No expenses recorded yet.</p>
        </div>
      </div>
    </div>

    <!-- Modal Overlay -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isAdding" class="fixed inset-0 z-[60] flex items-center justify-center p-6">
        <div @click="isAdding = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        
        <div class="relative w-full max-w-md bg-white rounded-3xl p-8 shadow-2xl transform transition-all">
          <h2 class="text-2xl font-bold mb-6">New Expense</h2>
          
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Category</label>
              <input required placeholder="Utilities, Rent, Meals etc." type="text" class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm" v-model="newExp.category" />
            </div>
            
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Amount</label>
              <input required type="number" step="0.01" min="0.01" class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm" v-model.number="newExp.amount" />
            </div>
            
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Payment Method</label>
              <div class="flex gap-2">
                <button
                  v-for="m in ['Bank', 'Cash']"
                  :key="m"
                  type="button"
                  @click="newExp.paymentMethod = m"
                  :class="[
                    'flex-1 py-2 text-xs font-bold rounded-lg border transition-all',
                    newExp.paymentMethod === m ? 'bg-zinc-950 text-white border-zinc-950' : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50'
                  ]"
                >
                  {{ m }}
                </button>
              </div>
            </div>
            
            <div v-if="newExp.paymentMethod === 'Bank'" class="space-y-1">
              <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Bank Account</label>
              <select required class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm" v-model="newExp.bankAccountId">
                <option value="">Select Account</option>
                <option v-for="a in props.accounts" :key="a.id" :value="a.id">{{ a.accountName }}</option>
              </select>
            </div>
            
            <div class="space-y-1">
              <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Description</label>
              <textarea class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm h-20 resize-none" v-model="newExp.description"></textarea>
            </div>
            
            <button type="submit" :disabled="form.processing" class="w-full py-3 bg-zinc-950 text-white rounded-xl font-bold shadow-lg shadow-zinc-950/20 mt-4 active:scale-95 transition-all disabled:opacity-50">
              {{ form.processing ? 'Submitting...' : 'Submit Expense' }}
            </button>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Plus, Tag, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
  expenses: {
    type: Array,
    default: () => [],
  },
  accounts: {
    type: Array,
    default: () => [],
  },
});

const isAdding = ref(false);

const newExp = ref({ 
  amount: 0, 
  category: '', 
  description: '', 
  paymentMethod: 'Bank', 
  bankAccountId: '' 
});

const form = useForm({
  amount: 0,
  category: '',
  description: '',
  paymentMethod: 'Bank',
  bankAccountId: '',
});

const handleSubmit = () => {
  form.amount = newExp.value.amount;
  form.category = newExp.value.category;
  form.description = newExp.value.description;
  form.paymentMethod = newExp.value.paymentMethod;
  form.bankAccountId = newExp.value.bankAccountId;
  
  form.post('/add-expense', {
    onSuccess: () => {
      isAdding.value = false;
      newExp.value = { amount: 0, category: '', description: '', paymentMethod: 'Bank', bankAccountId: '' };
      form.reset();
    },
  });
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Summary Stats Calculation
const summaryStats = computed(() => {
  const getCatTotal = (cat) => props.expenses.filter(e => e.category === cat).reduce((sum, e) => sum + parseFloat(e.amount), 0);
  
  const utilities = getCatTotal('Utilities');
  const rent = getCatTotal('Rent');
  const salary = getCatTotal('Salary');
  const others = props.expenses.filter(e => !['Utilities', 'Rent', 'Salary'].includes(e.category)).reduce((sum, e) => sum + parseFloat(e.amount), 0);
  
  return [
    { cat: 'Utilities', val: utilities, color: 'bg-blue-500' },
    { cat: 'Rent', val: rent, color: 'bg-zinc-950' },
    { cat: 'Salary', val: salary, color: 'bg-emerald-500' },
    { cat: 'Others', val: others, color: 'bg-amber-500' },
  ];
});

const maxExpenseAmount = computed(() => {
  if (!props.expenses || props.expenses.length === 0) return 1;
  const max = Math.max(...props.expenses.map(e => parseFloat(e.amount)));
  return max > 0 ? max : 1;
});
</script>
