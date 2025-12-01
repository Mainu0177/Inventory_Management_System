<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12"></div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h3>Client</h3>
                        </div>
                        <hr/>
                        <div class="float-end">
                            <a href="/CustomerSavePage?id=0" class="btn btn-secondary mx-3 btn-sm">
                                Add Client
                            </a>
                        </div>

                        <!-- Modal -->

                        <div>
                            <input placeholder="Search..." class="form-control mb-2 w-auto form-control-sm" type="text" v-model="searchValue">
                            <EasyDataTable buttons-pagination alternating :headers="Header" :items="Item" :rows-per-page="10" :search-field="searchField"  :search-value="searchValue">
                                <template #item-number="{ id}">
                                    <Link class="btn btn-success mx-3 btn-sm" :href="`/CustomerSavePage?id=${id}`">Edit</Link>
                                    <button class="btn btn-danger btn-sm" @click="DeleteClick(id)">Delete</button>
                                </template>
                            </EasyDataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import { ref } from 'vue';

const toaster = createToaster({
    position: 'top-right',
});



const Header = [
    { text: 'Name', value: 'name' },
    { text: 'Email', value: 'email' },
    { text: 'Mobile', value: 'mobile' },
    { text: 'Action', value: 'number'}
];

const page = usePage();
const Item = ref(page.props.customers);

const searchValue = ref('');
const searchField = ref(['name', 'email', 'mobile']);

const DeleteClick = (id) => {
    let text = "Do you want to delete this customer?";
    if (confirm(text) === true) {
        router.get(`/customer-delete/${id}`)
        toaster.success('Customer deleted successfully');
    } else {
        text = "You pressed Cancel!";
            toaster.error('Customer deletion cancelled');
        }
    };

</script>

<style scoped>

</style>
