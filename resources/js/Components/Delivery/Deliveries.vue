<template>
  <div class="space-y-8 animate-in fade-in duration-500 pb-20 pt-6 px-4 md:px-8">
    <!-- Header -->
    <header class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Deliveries</h1>
          <p class="text-zinc-500 text-sm mt-1">Manage delivery challans and stock dispatch.</p>
        </div>
        <button
          @click="isAdding = true"
          class="flex items-center justify-center gap-2 px-6 py-3 bg-zinc-900 text-white rounded-2xl text-sm font-bold hover:bg-emerald-600 transition-all shadow-xl shadow-zinc-900/10 active:scale-95"
        >
          <Plus class="w-5 h-5" />
          New Delivery
        </button>
      </div>

      <!-- Controls -->
      <div class="flex flex-col md:flex-row gap-4 p-4 bg-white border border-zinc-200 rounded-3xl shadow-sm">
        <div class="relative flex-1">
          <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400" />
          <input
            type="text"
            placeholder="Search by Challan No, Quote Ref, or PR..."
            v-model="searchQuery"
            class="w-full pl-12 pr-4 py-3 bg-zinc-50 border-none rounded-2xl text-sm font-medium focus:ring-2 focus:ring-zinc-950/5 transition-all text-zinc-900 outline-none"
          />
        </div>
        <div class="flex items-center gap-2 bg-zinc-50 p-1.5 rounded-2xl border border-zinc-100">
          <button
            v-for="f in ['All', 'Pending', 'Delivered']"
            :key="f"
            @click="filter = f"
            :class="[
              'px-5 py-2 rounded-xl text-xs font-bold transition-all',
              filter === f 
                ? 'bg-white text-zinc-950 shadow-sm border border-zinc-100' 
                : 'text-zinc-500 hover:text-zinc-700'
            ]"
          >
            {{ f }}
          </button>
        </div>
        <button
          @click="downloadDeliveryReport"
          class="flex items-center gap-2 px-6 py-3 bg-white text-zinc-900 border border-zinc-200 rounded-2xl text-xs font-bold hover:bg-zinc-50 transition-all shadow-sm"
        >
          <Download class="w-4 h-4" />
          Download Summary
        </button>
      </div>
    </header>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-2 gap-6">
      <div v-for="challan in filteredChallans" :key="challan.id" class="bg-white p-6 rounded-3xl border border-zinc-200 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between group">
        <div>
          <div class="flex items-center justify-between mb-4">
            <div class="px-3 py-1 bg-zinc-100 rounded-full text-[10px] font-bold uppercase tracking-wider text-zinc-500 group-hover:bg-zinc-950 group-hover:text-white transition-colors">
              {{ challan.challan_no }}
            </div>
            <div class="flex items-center gap-2">
              <div :class="[
                'flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold',
                challan.status === 'Delivered' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
              ]">
                {{ challan.status }}
              </div>
              
              <div class="relative">
                <button 
                  @click.stop="activeMenuId = activeMenuId === challan.id ? null : challan.id"
                  class="p-1 hover:bg-zinc-100 rounded-lg transition-colors text-zinc-400 hover:text-zinc-950"
                >
                  <MoreVertical class="w-4 h-4" />
                </button>
                
                <div v-if="activeMenuId === challan.id">
                    <div 
                      class="fixed inset-0 z-10" 
                      @click="activeMenuId = null"
                    ></div>
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-zinc-100 py-2 z-20 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        <button
                           @click="setPreviewDoc(challan)"
                           class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition-colors"
                         >
                           <Eye class="w-4 h-4 text-zinc-400" /> Preview Challan
                         </button>
                        <button
                           @click="handleEdit(challan)"
                         class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition-colors"
                       >
                         <Edit2 class="w-4 h-4 text-zinc-400" /> Edit Challan
                       </button>
                       <button
                           v-if="challan.status === 'Pending'"
                           @click="handleDeliver(challan)"
                           class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition-colors"
                         >
                           <CheckCircle2 class="w-4 h-4 text-emerald-500" /> Mark as Delivered
                         </button>
                       
                       <div class="h-px bg-zinc-100 my-1" />
                       <button
                         @click="shareViaEmail(challan)"
                         class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition-colors"
                       >
                         <Mail class="w-4 h-4 text-zinc-400" /> Share via Email
                       </button>
                       <button
                         @click="shareViaWhatsApp(challan)"
                         class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-zinc-700 hover:bg-zinc-50 transition-colors"
                       >
                         <MessageCircle class="w-4 h-4 text-emerald-500" /> Share on WhatsApp
                       </button>
                       
                       <div class="h-px bg-zinc-100 my-1" />
                       <button
                         @click="handleDelete(challan.id)"
                         class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors"
                       >
                         <Trash2 class="w-4 h-4" /> Delete Challan
                       </button>
                    </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-zinc-50 rounded-2xl flex items-center justify-center border border-zinc-100 shadow-sm grow-0 shrink-0">
              <Truck class="w-6 h-6 text-zinc-900" />
            </div>
            <div class="overflow-hidden">
              <h3 class="font-bold text-zinc-900 leading-none mb-1 truncate">{{ challan.customer ? challan.customer.name : 'Manual Delivery' }}</h3>
              <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest truncate">Ref: {{ challan.quotation_no }} <span v-if="challan.pr_no">| PR: {{ challan.pr_no }}</span></p>
            </div>
          </div>

          <div class="space-y-1 mb-6 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
             <div v-for="(item, i) in challan.delivery_challan_products" :key="i" class="flex items-center justify-between text-xs py-2 border-b border-zinc-50 last:border-0 grow">
                 <span class="text-zinc-600 font-medium">{{ item.name }}</span>
                 <span class="font-bold text-zinc-900 px-2 py-0.5 bg-zinc-100 rounded text-[10px]">x{{ item.qty }} {{ item.uom }}</span>
             </div>
          </div>
        </div>

        <div class="pt-4 mt-auto border-t border-zinc-100 flex items-center justify-between">
          <div class="flex flex-col">
            <span class="text-[10px] text-zinc-400 font-bold uppercase tracking-tight italic mb-1">
              {{ challan.status === 'Delivered' ? `Delivered: ${formatDate(challan.delivered_at)}` : 'Awaiting dispatch' }}
            </span>
            <div class="flex items-center gap-1">
              <button 
                @click="setPreviewDoc(challan)"
                class="flex items-center gap-2 px-3 py-1.5 bg-white text-zinc-900 rounded-lg text-[10px] font-bold hover:bg-zinc-50 transition-all border border-zinc-200 shadow-sm"
              >
                <Eye class="w-3 h-3" />
                PREVIEW
              </button>
              <button 
                @click="downloadChallanPDF(challan)"
                class="flex items-center gap-2 px-3 py-1.5 bg-zinc-100 text-zinc-900 rounded-lg text-[10px] font-bold hover:bg-zinc-200 transition-all border border-zinc-200 shadow-sm"
              >
                <Download class="w-3 h-3" />
                DOWNLOAD DC
              </button>
            </div>
          </div>
          <button
            v-if="challan.status === 'Pending'"
            @click="handleDeliver(challan)"
            class="px-5 py-2.5 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center gap-2"
          >
            <Package class="w-3.5 h-3.5" />
            Deliver
          </button>
          <div v-else class="bg-zinc-50 px-3 py-1.5 rounded-xl border border-zinc-100 flex items-center gap-2">
               <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
               <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">Completed</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <transition name="fade">
        <div v-if="isAdding" class="fixed inset-0 z-[60] flex items-center justify-center p-6">
            <div @click="isAdding = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-lg bg-white rounded-3xl p-8 shadow-2xl animate-in zoom-in-95 duration-200">
                <h2 class="text-2xl font-bold mb-6 text-zinc-900">Dispatch Inventory</h2>
                <div class="space-y-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Related Quotation/Order</label>
                        <select
                            v-model="selectedQuotationId"
                            class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none"
                        >
                            <option value="">Select a source document</option>
                            <option v-for="q in quotations" :key="q.id" :value="q.id">
                                {{ q.quotation_no }} ({{ q.customer?.name }})
                            </option>
                        </select>
                    </div>
                    
                    <div class="p-4 bg-zinc-50 rounded-2xl border border-zinc-200 border-dashed">
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Selecting a quotation will automatically populate the delivery items. 
                            Marking as "Delivered" will subtract stock from the master inventory.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            @click="isAdding = false; isCreatingManual = true"
                            class="flex-1 px-6 py-2 text-sm font-bold text-zinc-600 hover:bg-zinc-100 rounded-xl transition-colors"
                        >
                            Manual Dispatch
                        </button>
                        <button
                            @click="handleCreateChallan"
                            :disabled="!selectedQuotationId"
                            class="px-8 py-2 bg-zinc-900 text-white rounded-xl text-sm font-bold hover:bg-zinc-800 disabled:opacity-50 transition-all"
                        >
                            Create from Quote
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <transition name="fade">
        <div v-if="isCreatingManual" class="fixed inset-0 z-[60] flex items-center justify-center p-6">
            <div @click="closeManual" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
            <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl flex flex-col h-[85vh] animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900">Manual Delivery Challan</h2>
                        <p class="text-sm text-zinc-500">Dispatch items without a source quotation.</p>
                    </div>
                    <button @click="closeManual" class="p-2 hover:bg-zinc-100 rounded-lg">
                        <X class="w-6 h-6 text-zinc-400" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 block mb-1">Customer</label>
                                <select
                                    v-model="manualChallan.customer_id"
                                    class="w-full px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-zinc-950/5 transition-all text-zinc-900 appearance-none cursor-pointer"
                                >
                                    <option value="">Select Customer</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 block mb-1">PO Number</label>
                                    <input
                                        type="text"
                                        v-model="manualChallan.po_no"
                                        class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none"
                                        placeholder="Optional"
                                    />
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 block mb-1">Ref/PR Number</label>
                                    <input
                                        type="text"
                                        v-model="manualChallan.pr_no"
                                        class="w-full px-4 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none"
                                        placeholder="Optional"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="bg-zinc-50 p-6 rounded-2xl border border-zinc-100 flex items-center justify-center text-center">
                            <div>
                                <Truck class="w-10 h-10 text-zinc-300 mx-auto mb-2" />
                                <p class="text-xs text-zinc-500 font-medium tracking-tight">Manual dispatch will generate DC series.<br/>Ensure stock is checked before finalizing.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-zinc-900">Line Items</h3>
                        <div class="bg-zinc-50 p-4 rounded-2xl border border-zinc-200 space-y-4">
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-12 md:col-span-5">
                                    <input
                                        type="text"
                                        placeholder="Item Name"
                                        v-model="tempItem.name"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none"
                                    />
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <input
                                        type="text"
                                        placeholder="Code"
                                        v-model="tempItem.code"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none"
                                    />
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <input
                                        type="number"
                                        placeholder="Qty"
                                        v-model.number="tempItem.qty"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none"
                                    />
                                </div>
                                <div class="col-span-4 md:col-span-2">
                                    <input
                                        type="text"
                                        placeholder="UOM"
                                        v-model="tempItem.uom"
                                        class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-sm focus:outline-none"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-1">
                                    <button
                                        @click="addManualItem"
                                        class="w-full h-full min-h-[38px] flex items-center justify-center bg-zinc-900 text-white rounded-lg hover:bg-zinc-800 transition-colors"
                                    >
                                        <Plus class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <input
                                type="text"
                                placeholder="Detailed Description / Serial Numbers"
                                v-model="tempItem.description"
                                class="w-full px-3 py-2 border border-zinc-200 rounded-lg text-xs focus:outline-none"
                            />
                        </div>

                        <div class="border border-zinc-100 rounded-2xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-zinc-50 border-b border-zinc-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-zinc-500 text-[10px] uppercase">Code</th>
                                        <th class="px-4 py-3 text-left font-bold text-zinc-500 text-[10px] uppercase">Item</th>
                                        <th class="px-4 py-3 text-center font-bold text-zinc-500 text-[10px] uppercase w-20">Qty</th>
                                        <th class="px-4 py-3 text-center font-bold text-zinc-500 text-[10px] uppercase w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-50">
                                    <tr v-for="(item, idx) in manualChallan.items" :key="idx">
                                        <td class="px-4 py-3 font-mono text-[10px] text-zinc-400">{{ item.code }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-900">{{ item.name }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-zinc-900">{{ item.qty }} {{ item.uom }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button
                                                @click="removeManualItem(idx)"
                                                class="text-zinc-300 hover:text-rose-600 transition-colors"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="manualChallan.items.length === 0">
                                        <td colspan="4" class="px-4 py-12 text-center text-zinc-400 italic">No items added to dispatch yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-zinc-100 bg-zinc-50/50 flex justify-end gap-3">
                    <button
                        @click="closeManual"
                        class="px-6 py-2 text-sm font-bold text-zinc-500 hover:bg-zinc-100 rounded-xl"
                    >
                        Discard
                    </button>
                    <button
                        @click="handleCreateManualChallan"
                        :disabled="!manualChallan.customer_id || manualChallan.items.length === 0"
                        class="px-8 py-2 bg-zinc-900 text-white rounded-xl text-sm font-bold hover:bg-emerald-600 disabled:opacity-50 transition-all shadow-xl shadow-zinc-950/10"
                    >
                        Save & Preview Challan
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <transition name="fade">
        <div v-if="showPreview && previewDoc" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div @click="showPreview = false" class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>
            <div class="relative w-full max-w-4xl bg-white rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-white/20 animate-in zoom-in-95 duration-200">
                <div class="px-8 py-6 border-b border-zinc-100 flex items-center justify-between bg-white relative z-10 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-zinc-950 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-zinc-950/20">
                            <Truck class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-zinc-900 tracking-tight">Delivery Challan Preview</h2>
                            <p class="text-zinc-500 text-[10px] font-bold uppercase tracking-widest">{{ previewDoc.challan_no }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="downloadChallanPDF(previewDoc)"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 active:scale-95"
                        >
                            <Download class="w-3.5 h-3.5" />
                            Download PDF
                        </button>
                        <button 
                            @click="showPreview = false"
                            class="p-3 hover:bg-zinc-100 rounded-2xl transition-all"
                        >
                            <X class="w-5 h-5 text-zinc-400" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-12 bg-zinc-50 scrollbar-hide">
                    <div class="bg-white shadow-2xl rounded-[1.5rem] mx-auto overflow-hidden border border-zinc-100" style="max-width: 800px; min-height: 1123px">
                        <div class="h-2 bg-emerald-600 w-full"></div>
                        
                        <div class="p-12 space-y-12">
                            <div class="flex justify-between items-start">
                                <div class="space-y-4">
                                    <div class="text-3xl font-black text-emerald-600 tracking-tighter">
                                        FACTORY ELECTRIC
                                    </div>
                                </div>
                                <div class="text-right space-y-1">
                                    <p class="text-xs font-bold text-zinc-900">Factory Electric Solution</p>
                                    <p class="text-[10px] text-zinc-500 max-w-[200px] leading-relaxed ml-auto">Dhaka, Bangladesh</p>
                                    <p class="text-[10px] text-zinc-500">+880 123 456 789</p>
                                    <p class="text-[10px] text-emerald-600 font-bold">info@factoryelectric.com</p>
                                </div>
                            </div>

                            <div class="text-center space-y-1 py-10 border-y border-zinc-100 bg-zinc-50/50 -mx-12 px-12">
                                <h1 class="text-4xl font-black text-zinc-950 tracking-tighter uppercase">Delivery Challan</h1>
                                <div class="flex justify-center gap-8 text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                                    <span>No: {{ previewDoc.challan_no }}</span>
                                    <span>Date: {{ formatDate(previewDoc.status === 'Delivered' ? previewDoc.delivered_at : new Date()) }}</span>
                                    <span>Ref Quote: {{ previewDoc.quotation_no }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-12 text-[11px]">
                                <div class="space-y-3">
                                    <h3 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest border-b border-emerald-100 pb-1">Ship To</h3>
                                    <div class="space-y-1">
                                        <p class="text-sm font-black text-zinc-900">{{ previewDoc.customer?.name || 'Manual Client' }}</p>
                                        <p class="text-[10px] text-zinc-500 font-medium italic">PO Number: {{ previewDoc.po_no || 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between border-b border-zinc-50 py-1">
                                        <span class="text-zinc-400 font-bold uppercase tracking-widest text-[9px]">Status</span>
                                        <span :class="[
                                            'font-black uppercase',
                                            previewDoc.status === 'Delivered' ? 'text-emerald-600' : 'text-amber-600'
                                        ]">{{ previewDoc.status }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-zinc-50 py-1">
                                        <span class="text-zinc-400 font-bold uppercase tracking-widest text-[9px]">Ref PR</span>
                                        <span class="text-zinc-900">{{ previewDoc.pr_no || 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl overflow-hidden border border-zinc-100 shadow-sm mt-8">
                                <table class="w-full text-left text-[11px]">
                                    <thead>
                                        <tr class="bg-zinc-950 text-white">
                                            <th class="px-6 py-4 font-bold uppercase tracking-widest w-12 text-center">SL</th>
                                            <th class="px-6 py-4 font-bold uppercase tracking-widest">Description</th>
                                            <th class="px-6 py-4 font-bold uppercase tracking-widest text-center w-24">UOM</th>
                                            <th class="px-6 py-4 font-bold uppercase tracking-widest text-center w-24">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 font-medium">
                                        <tr v-for="(item, i) in previewDoc.delivery_challan_products" :key="i" class="hover:bg-zinc-50 transition-colors">
                                            <td class="px-6 py-4 text-center text-zinc-400">{{ i + 1 }}</td>
                                            <td class="px-6 py-4">
                                                <p class="font-bold text-zinc-900">{{ item.name }}</p>
                                                <p class="text-[9px] text-zinc-500 mt-0.5 leading-relaxed truncate max-w-md">{{ item.description }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-center text-zinc-500">{{ item.uom }}</td>
                                            <td class="px-6 py-4 text-center font-black text-zinc-900">{{ item.qty }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="space-y-4 pt-12">
                                <h3 class="text-[10px] font-black text-zinc-900 uppercase tracking-widest border-b border-zinc-100 pb-1">Declaration</h3>
                                <p class="text-[9px] text-zinc-500 leading-relaxed max-w-xl italic">
                                    Received the above items in good condition. All items are strictly for the designated purpose. Any discrepancy must be reported within 24 hours of delivery.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-24 pt-24 pb-12">
                                <div class="space-y-4 text-center">
                                    <div class="h-px bg-zinc-200"></div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-zinc-900 uppercase tracking-widest">Authorized By</p>
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase">{{ previewDoc.created_by || 'Admin' }}</p>
                                    </div>
                                </div>
                                <div class="space-y-4 text-center">
                                    <div class="h-px bg-zinc-900"></div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] font-black text-zinc-900 uppercase tracking-widest">Receiver Signature & Stamp</p>
                                        <p class="text-[9px] text-zinc-400 font-medium uppercase tracking-widest italic">Received In Good State</p>
                                    </div>
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
import { router } from '@inertiajs/vue3';
import { Truck, CheckCircle2, Package, Search, Plus, ExternalLink, MapPin, Download, MoreVertical, Trash2, Mail, MessageCircle, Edit2, X, Eye } from 'lucide-vue-next';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

const props = defineProps({
  challans: Array,
  quotations: Array,
  customers: Array
});

// State
const filter = ref('All');
const isAdding = ref(false);
const isCreatingManual = ref(false);
const searchQuery = ref('');
const editingChallanId = ref(null);
const activeMenuId = ref(null);
const selectedQuotationId = ref('');
const showPreview = ref(false);
const previewDoc = ref(null);

const manualChallan = ref({
  customer_id: '',
  po_no: '',
  pr_no: '',
  items: []
});

const tempItem = ref({
  code: '',
  name: '',
  description: '',
  qty: 1,
  uom: 'pcs'
});

// Computed
const filteredChallans = computed(() => {
  if(!props.challans) return [];
  return props.challans
    .filter(c => filter.value === 'All' || c.status === filter.value)
    .filter(c => 
      c.challan_no?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.quotation_no?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.pr_no?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Formatting
const formatDate = (dateString) => {
  if (!dateString) return '';
  const d = new Date(dateString);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

// API Interactions
const handleDeliver = (challan) => {
    router.post(route('challan.deliver'), { id: challan.id }, {
        onSuccess: () => {
            activeMenuId.value = null;
        }
    });
};

const handleDelete = (id) => {
    if (confirm('Delete this delivery challan?')) {
        router.get(route('challan.delete', id), {}, {
            onSuccess: () => {
                activeMenuId.value = null;
            }
        });
    }
};

const handleCreateChallan = () => {
    if (!selectedQuotationId.value) return;
    router.post(route('challan.create'), { quotation_id: selectedQuotationId.value }, {
        onSuccess: () => {
            isAdding.value = false;
            selectedQuotationId.value = '';
        }
    });
};

const handleCreateManualChallan = () => {
    if (!manualChallan.value.customer_id || manualChallan.value.items.length === 0) return;

    if (editingChallanId.value) {
        router.post(route('challan.update'), {
            id: editingChallanId.value,
            customer_id: manualChallan.value.customer_id,
            po_no: manualChallan.value.po_no,
            pr_no: manualChallan.value.pr_no,
            items: manualChallan.value.items
        }, {
            onSuccess: () => {
                closeManual();
            }
        });
    } else {
        router.post(route('challan.create'), manualChallan.value, {
            onSuccess: () => {
                closeManual();
            }
        });
    }
};

// Utilities
const setPreviewDoc = (challan) => {
    previewDoc.value = challan;
    showPreview.value = true;
    activeMenuId.value = null;
};

const handleEdit = (challan) => {
    editingChallanId.value = challan.id;
    manualChallan.value = {
        customer_id: challan.customer_id,
        po_no: challan.po_no || '',
        pr_no: challan.pr_no || '',
        items: challan.delivery_challan_products.map(i => ({...i}))
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
    manualChallan.value = {
        customer_id: '',
        po_no: '',
        pr_no: '',
        items: []
    };
};

const addManualItem = () => {
    if (!tempItem.value.name) return;
    manualChallan.value.items.push({...tempItem.value});
    tempItem.value = { code: '', name: '', description: '', qty: 1, uom: 'pcs' };
};

const removeManualItem = (idx) => {
    manualChallan.value.items.splice(idx, 1);
};

const shareViaEmail = (challan) => {
    const subject = `Delivery Challan ${challan.challan_no}`;
    const itemsText = challan.delivery_challan_products.map(i => `- ${i.name}: ${i.qty} ${i.uom}`).join('\n');
    const body = `Hi ${challan.customer?.name || ''},\n\nPlease find the details for Delivery Challan ${challan.challan_no}.\n\nItems:\n${itemsText}\n\nReference Quote: ${challan.quotation_no}\nStatus: ${challan.status}`;
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    activeMenuId.value = null;
};

const shareViaWhatsApp = (challan) => {
    const itemsText = challan.delivery_challan_products.map(i => `• ${i.name}: ${i.qty} ${i.uom}`).join('\n');
    const text = `*Delivery Challan ${challan.challan_no}*\n\nClient: ${challan.customer?.name || ''}\n\n*Items:*\n${itemsText}\n\n*Ref Quote:* ${challan.quotation_no}\n*Status:* ${challan.status}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
    activeMenuId.value = null;
};

const downloadDeliveryReport = () => {
    const doc = new jsPDF();
    const primaryColor = [39, 39, 42];

    doc.setFontSize(20);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("DELIVERY SUMMARY REPORT", 14, 20);

    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text(`Generated on: ${formatDate(new Date())}`, 14, 28);
    doc.text(`Total Deliveries: ${filteredChallans.value.length}`, 14, 34);

    autoTable(doc, {
      startY: 45,
      head: [['DC No.', 'Customer', 'Quote Ref', 'Date', 'Status']],
      body: filteredChallans.value.map(c => [
        c.challan_no,
        c.customer?.name || 'Manual',
        c.quotation_no,
        formatDate(c.status === 'Delivered' ? c.delivered_at : new Date()),
        c.status
      ]),
      headStyles: { fillColor: primaryColor },
      styles: { fontSize: 8 },
    });

    doc.save(`delivery_report_${Date.now()}.pdf`);
};

const downloadChallanPDF = (challan) => {
    const doc = new jsPDF();
    const primaryColor = [74, 139, 94];
    
    doc.setFontSize(22);
    doc.setTextColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.text("FACTORY ELECTRIC", 14, 25);

    doc.setFontSize(8);
    doc.setTextColor(100);
    
    let infoY = 15;
    doc.text("Factory Electric Solution", 200, infoY, { align: 'right' }); infoY += 4;
    doc.text("Dhaka, Bangladesh", 200, infoY, { align: 'right' }); infoY += 4;
    doc.text("+880 123 456 789 | info@factoryelectric.com", 200, infoY, { align: 'right' });

    doc.setFontSize(32);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    doc.text("DELIVERY CHALLAN", 105, 55, { align: 'center' });
    
    doc.setFontSize(10);
    doc.setTextColor(0);
    doc.setFont("helvetica", "bold");
    
    doc.text("Challan No:", 14, 75);
    doc.text("Date:", 14, 82);
    doc.text("Ref Quote:", 14, 89);
    doc.text("Status:", 14, 96);
    
    doc.setFont("helvetica", "normal");
    doc.text(challan.challan_no, 45, 75);
    doc.text(formatDate(challan.status === 'Delivered' ? challan.delivered_at : new Date()), 45, 82);
    doc.text(challan.quotation_no, 45, 89);
    doc.setFont("helvetica", "bold");
    doc.text(challan.status.toUpperCase(), 45, 96);

    doc.setFont("helvetica", "bold");
    doc.text("SHIP TO:", 200, 75, { align: 'right' });
    doc.setFont("helvetica", "normal");
    
    const clientName = challan.customer?.name || "Customer Details";
    const clientLines = doc.splitTextToSize(clientName, 60);
    doc.text(clientLines, 200, 82, { align: 'right' });

    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.setLineWidth(1.5);
    doc.line(14, 110, 196, 110);
    
    autoTable(doc, {
      startY: 120,
      head: [['SL.', 'Item Code', 'Description', 'UOM', 'Ordered Qty', 'Delivered Qty']],
      body: challan.delivery_challan_products.map((item, idx) => [
        idx + 1,
        item.code || '',
        `${item.name}\n${item.description || ''}`,
        item.uom,
        item.qty,
        item.qty
      ]),
      theme: 'grid',
      headStyles: { 
        fillColor: primaryColor, 
        textColor: [255, 255, 255],
        fontSize: 9,
        fontStyle: 'bold',
        halign: 'center'
      },
      columnStyles: {
        0: { halign: 'center', cellWidth: 15 },
        1: { halign: 'left', cellWidth: 30 },
        2: { halign: 'left' },
        3: { halign: 'center', cellWidth: 20 },
        4: { halign: 'center', cellWidth: 25 },
        5: { halign: 'center', cellWidth: 25 }
      },
      styles: { fontSize: 8, cellPadding: 4 },
      alternateRowStyles: { fillColor: [245, 245, 245] }
    });
    
    const finalY = doc.lastAutoTable.finalY + 10;
    
    doc.setTextColor(0);
    doc.setFontSize(10);
    doc.setFont("helvetica", "bold");
    doc.text("DECLARATION", 14, finalY + 10);
    doc.setLineWidth(0.5);
    doc.setDrawColor(primaryColor[0], primaryColor[1], primaryColor[2]);
    doc.line(14, finalY + 13, 196, finalY + 13);
    
    doc.setFont("helvetica", "normal");
    doc.setFontSize(8);
    doc.setTextColor(100);
    doc.text("Received the above items in good condition. All items are strictly for the designated purpose.", 14, finalY + 20);
    
    const pageHeight = doc.internal.pageSize.height;
    doc.setTextColor(0);
    doc.setFontSize(8);
    doc.setDrawColor(200);
    doc.setLineWidth(0.1);
    
    const sigY = pageHeight - 45;
    
    doc.line(14, sigY, 64, sigY);
    doc.setFont("helvetica", "bold");
    doc.text("CREATE BY", 39, sigY + 5, { align: 'center' });
    doc.setFont("helvetica", "normal");
    doc.text(challan.created_by || "Admin", 39, sigY + 10, { align: 'center' });
    
    doc.line(14, pageHeight - 20, 80, pageHeight - 20);
    doc.setFont("helvetica", "bold");
    doc.text("RECEIVER'S SIGNATURE & STAMP", 14, pageHeight - 15);
    
    doc.save(`${challan.challan_no}.pdf`);
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
