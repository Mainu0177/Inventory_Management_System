<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12"></div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <h3>Category</h3>
                        </div>
                        <hr />
                        <div class="float-end">
                            <a href="/CategorySavePage?id=0" class="btn btn-success mx-3 btn-sm">
                                Add Category
                            </a>
                        </div>

                        <!-- Modal -->

                        <div>
                            <input placeholder="Search..." class="form-control mb-2 w-auto form-control-sm" type="text" v-model="searchValue" />
                            <EasyDataTable buttons-pagination alternating :headers="Header" :items="Item" :rows-per-page="10" :search-field="searchField" :search-value="searchValue" show-index>

                                <template #item-number="{ id, name }">
                                    <a class="btn btn-success mx-3 btn-sm" :href="`/CategorySavePage?id=${id}`">Edit</a>
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
import { ref } from 'vue';
import { createToaster } from "@meforma/vue-toaster";

const toaster = createToaster({
    position: "top-right",
})

const Header = [
    // { text: 'Id', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Action', value: 'number' }
];

const page = usePage();
// console.log(page.props.categories);
const Item = ref(page.props.categories);

const searchValue = ref();
const searchField = ref('');

const DeleteClick = (id) => {
    let text = "Are you sure you want to delete this category?";
    if (confirm(text) === true) {
        router.get(`/delete-category/${id}`)
        toaster.success("Category deleted successfully");
    } else {
        text = "You canceled the deletion.";
    }
}

</script>

<style scoped></style>
