<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">
                <div>
                    <h3>Invoice List</h3>
                </div>
                <hr />
                <div>
                    <input
                    placeholder="Search..."
                    class="form-control mb-2 w-auto form-control-sm"
                    type="text"
                    v-model="searchValue"
                    />
                    <EasyDataTable
                    buttons-pagination
                    alternating
                    :headers="Header"
                    :items="Item"
                    :rows-per-page="10"
                    :search-field="searchField"
                    :search-value="searchValue"
                    >

                    <template #item-number="{ id }">
                        <button
                        @click="showDetails(id)"
                        class="viewBtn btn btn-outline-dark text-sm px-3 py-1 btn-sm m-0 me-2"
                        >
                        <i class="fa text-sm fa-eye"></i>
                        </button>
                        <button @click="DeleteClick(id)" class="btn btn-danger btn-sm">Delete</button>
                    </template>

                    </EasyDataTable>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Modal component for invoice details -->
        <InvoiceDetails v-if="show" :customer="customer" @close="CloseModel" />

    </div>
</template>

<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import { ref } from 'vue';
import InvoiceDetails from './InvoiceDetails.vue';

const toaster = createToaster({
    position: 'top-right',
});

const page = usePage();
const Item = ref(page.props.list);

const Header = ref([
    { text: 'Name', value: 'customer.name' },
    { text: 'Customer ID', value: 'customer.id' },
    { text: 'Phone', value: 'customer.mobile' },
    { text: 'Total', value: 'total' },
    { text: 'Discount', value: 'discount' },
    { text: 'Vat', value: 'vat' },
    { text: 'Payable', value: 'payable' },
    { text: 'Action', value: 'number' },

]);

const searchField = ref(['customer.name']);
const searchValue = ref();

const show = ref(false);
const customer = ref();

const showDetails = (id)=>{
    show.value = !show.value;
    customer.value = Item.value.find(item => item.id === id);
    // console.log(customer.value);
}

const CloseModel = ()=>{
    show.value = false;
}

const DeleteClick = (id) => {
    let text = 'Are you sure you want to delete this invoice?';
    if (confirm(text) === true) {
        router.get(`/invoice-delete/${id}`)
        toaster.success('Invoice deleted successfully');
    } else {
        text = 'You canceled the deletion.';
        toaster.error(text);
    }
}

</script>

<style scoped>

</style>
