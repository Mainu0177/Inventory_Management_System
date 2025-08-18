<template>
    <div class="container-fluid">
        <div class="row">
            <!-- Billing Selection -->
            <div class="col-md-4 col-lg-4 p-2">
                <div class="card">
                    <div class="card-body">
                        <h4>Billed To</h4>
                        <div class="shadow-sm h-100 bg-white rounded-3 p-3 mt-4">
                            <div class="row">
                                <div class="col-8">
                                    <span class="text-bold text-dark">BILLED TO</span>
                                    <p class="text-xs mx-0 my-1">Name: <span>{{ selectedCustomer?.name || '' }}</span></p>
                                    <p class="text-xs mx-0 my-1">Email: <span>{{ selectedCustomer?.email || '' }}</span></p>
                                    <p class="text-xs mx-0 my-1">User ID: <span>{{ selectedCustomer?.id ||'' }}</span></p>
                                </div>
                                <div class="col-4">
                                    <p class="text-bold mx-0 my-1 text-dark">Invoice</p>
                                    <p class="text-xs mx-0 my-1">Date: {{ new Date().toISOString().slice(0, 10) }}</p>
                                </div>
                            </div>
                            <hr class="mx-0 my-2 p-0 bg-secondary"/>
                            <div class="row">
                                <div class="col-12">
                                    <table class="table w-100">
                                        <thead>
                                            <tr class="text-xs">
                                                <th>Name</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th>Remove</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="selectedProduct.length > 0" v-for="(product,index) in selectedProduct" :key="index" class="text-center">
                                                <td> {{ product.name }} </td>
                                                <td> {{ product.unit }} </td>
                                                <td> {{ product.price }} </td>
                                                <td>
                                                    <button @click="removeQty(product.id)" class="m-1">-</button>
                                                    <button @click="addQty(product.id)" class="m-1">+</button>
                                                    <button @click="removeProductFromSale(index)" class="btn btn-danger btn-sm">Remove</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <hr class="mx-0 my-2 p-0 bg-secondary"/>
                            <div class="row">
                                <div class="col-12">
                                    <p class="text-bold text-xs my-1 text-dark">Total: <i class="bi bi-currency-dollar"></i>{{ calculateTotal() }}</p>
                                    <p class="text-bold text-xs my-1 text-dark">VAT ({{ vatRate }}%): <i class="bi bi-currency-dollar"></i> {{vatAmount}}</p>
                                    <p><button class="btn btn-info btn-sm my-1 bg-gradient-primary w-40" @click="applyVat">Apply VAT</button></p>
                                    <p><button class="btn btn-secondary btn-sm my-1 bg-gradient-primary w-40" @click="removeVat">Remove VAT</button></p>

                                    <p><span class="text-xxs">Discount Mode:</span></p>
                                    <select v-model="usePercentageDiscount" >
                                        <option :value="false">Flat Discount</option>
                                        <option :value="true">Percentage Discount</option>
                                    </select>
                                    <p class="text-bold text-xs my-1 text-dark">Discount: <i class="bi bi-currency-dollar"></i> {{discountAmount}} </p>
                                    <div v-if="!usePercentageDiscount">
                                        <span class="text-xxs">Flat Discount:</span>
                                        <input type="number" v-model="discountPercent" class="form-control w-40" min="0" />
                                        <p><button @click="applyDiscount" class="btn btn-warning btn-sm my-1 bg-gradient-primary w-40">Apply Flat Discount</button></p>
                                    </div>
                                    <div v-else>
                                        <span class="text-xxs">Discount (%):</span>
                                        <input type="number" v-model="flatDiscount" class="form-control w-40" min="0" max="100" step="0.25" />
                                        <p><button @click="applyDiscount" class="btn btn-warning btn-sm my-1 bg-gradient-primary w-40">Apply Percentage Discount</button></p>
                                    </div>
                                    <p><button class="btn btn-secondary btn-sm my-1 bg-gradient-primary w-40" @click="removeDiscount">Remove Discount</button></p>

                                    <hr class="mx-0 my-2 p-0 bg-secondary"/>
                                    <p class="text-bold text-xs my-1 text-dark">Payable: <i class="bi bi-currency-dollar"></i> {{payable}}</p>
                                    <p><button class="btn btn-success btn-sm my-3 bg-gradient-primary w-40" @click="createInvoice">Confirm</button></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Selection -->
            <div class="col-md-4 col-lg-4 p-2">
                <div class="card">
                    <div class="card-body">
                        <h4>Select Product</h4>
                        <input
                            v-model="searchProductValue"
                            placeholder="Search..."
                            class="form-control mb-2 w-auto form-control-sm"
                            type="text"
                        />
                        <EasyDataTable
                            buttons-paginations
                            alternating
                            :items="ProductItem"
                            :headers="ProductHeader"
                            :rows-per-page="10"
                            :search-value="searchProductValue"
                            :search-field="searchProductField"
                        >
                            <template #item-image="{image}">
                                <img :src="image ? image: '/placeholder.png'" alt="Product Image" height="40px" width="40px" />
                            </template>

                            <template #item-action="{id, image, name, price, unit}">
                                <button @click="addProductToSale(id, image, name, price, unit)" :class="['btn btn-sm', unit > 0 ? 'btn-success' : 'btn-warning']">
                                    {{ unit > 0 ? 'Add' : 'Stock Out' }}
                                </button>
                            </template>
                        </EasyDataTable>
                    </div>
                </div>
            </div>

            <!-- Customer Selection -->
            <div class="col-md-4 col-lg-4 p-2">
                <div class="card">
                    <div class="card-body">
                        <h4>Select Customer</h4>
                        <input
                            placeholder="Search..."
                            class="form-control mb-2 w-auto form-control-sm"
                            type="text"
                            v-model="searchCustomerValue"
                        />
                        <EasyDataTable
                            buttons-pagination
                            alternating
                            :headers="CustomerHeader"
                            :items="CustomerItem"
                            :rows-per-page="10"
                            :search-field="searchCustomerField"
                            :search-value="searchCustomerValue"
                            show-index
                        >
                            <template #item-number="{id, name, email}">
                                <button @click="addCustomerToSale({id, name, email})" class="btn btn-success btn-sm">Add</button>
                            </template>
                        </EasyDataTable>
                    </div>
                </div>
            </div>
            <!-- {{ selectedCustomer }} -->
        </div>
    </div>
</template>

<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import { ref, watch } from 'vue';

const toaster = createToaster({
    position: 'top-right',
});

let page = usePage();

// const customers = page.props.customers;
// const products = page.props.products;

const selectedCustomer = ref(null);

const CustomerHeader = [
    { text: 'Name', value: 'name' },
    { text: 'Pick', value: 'number' },
];
const CustomerItem = page.props.customers;

const searchCustomerValue = ref();
const searchCustomerField = ref(['name']);

const addCustomerToSale = (customer) => {
    selectedCustomer.value = customer;
}


// product selection
const selectedProduct = ref([]);
const ProductHeader = [
    { text: 'Image', value: 'image' },
    { text: 'Name', value: 'name' },
    { text: 'Qty', value: 'unit' },
    { text: 'Action', value: 'action' },
];

const ProductItem = ref(page.props.products);

const searchProductValue = ref();
const searchProductField = ref(['name']);


const addProductToSale = (id, image, name, price, productUnit) => {
    const existingProduct = selectedProduct.value.find(product => product.id === id);
    if (existingProduct) {
        if (existingProduct.exitsQty > 0) {
            existingProduct.unit++;
            existingProduct.exitsQty--;
            calculateTotal();
        } else {
            toaster.warning(`${name} is out of stock!`);
        }
    } else {
        if (productUnit > 0) {
            const product = {
                id: id,
                image: image,
                name: name,
                price: price,
                unit: 1,
                exitsQty: productUnit - 1, // Decrease the available stock
                productPrice: price,
            };
            selectedProduct.value.push(product);
            calculateTotal();
        }else {
            toaster.warning(`${name} is out of stock!`);
        }
    }
}


const addQty = (id) => {
    const product = selectedProduct.value.find((product) => product.id === id);
    if (product.exitsQty > 0) {
        product.unit++;
        product.exitsQty--;
        calculateTotal();
    } else {
        toaster.warning(`${product.name} is out of stock`)
    }
}

const removeQty = (id) => {
    const product = selectedProduct.value.find((product) => product.id === id);
    if (product.unit > 1) {
        product.unit--;
        product.exitsQty++;
        calculateTotal();
    }
}

const removeProductFromSale = (index) => {
    selectedProduct.value.splice(index, 1);
    calculateTotal();
    calculatePayable();
    removeVat();
    removeDiscount();
    toaster.success("Product removed from sale");
}

// calculated section
const vatRate = ref(5);
const vatAmount = ref(0);
const flatDiscount = ref(0)
const discountPercent = ref(0);
const total = ref(0);
const discountAmount = ref(0);
const usePercentageDiscount = ref(0);

const calculateTotal = () => {
    return selectedProduct.value.reduce((sum, product) => sum + (product.productPrice * product.Unit), 0);
}

const applyVat = () => {
    vatAmount.value = (calculateTotal() * vatRate.value) / 100;
    calculateTotal();
}

const removeVat = () => {
    vatAmount.value = 0;
    calculateTotal();
}

const applyDiscount = () => {
    if (usePercentageDiscount.value) {
        discountAmount.value = (calculateTotal() * discountPercent.value) / 100;
    } else {
        discountAmount.value = flatDiscount.value;
    }
    calculateTotal();
}

const removeDiscount = () => {
    discountAmount.value = 0;
    calculateTotal();
}



const calculatePayable = () => {
    const totalAmount = calculateTotal();
    payable.value = totalAmount + vatAmount.value - discountAmount.value
}

watch(
    [selectedProduct, vatAmount, discountAmount],
    () => {
        calculatePayable();
    },
    {deep: true}
)

const payable = ref(0);

const form = useForm({
    customer_id: '',
    products: '',
    vat: '',
    discount: '',
    payable: calculateTotal(),
    total: '',
})

const createInvoice = () => {
    if(!selectedCustomer.value){
        toaster.warning("Please select a customer");
        return;
    }
    if (selectedProduct.value.length === 0) {
        toaster.warning("Please select a product");
        return;
    }

    form.products = selectedProduct.value;
    form.customer_id = selectedCustomer.value.id;
    form.total = total.value;
    form.vat = vatAmount.value;
    form.discount = discountAmount.value;
    form.payable = payable.value;
    const calculated = calculateTotal();

    form.total = calculated;
    form.payable = payable.value;

    form.post('/create-invoice', {
        onSuccess: () => {
            if (page.props.flash.status === true) {
                toaster.success(page.props.flash.message);
                setTimeout(() => {
                    router.get('/InvoiceListPage');
                }, 500)
                toaster.error(page.props.flash.message)
            }
        }
    })
}


</script>

<style scoped>

</style>
