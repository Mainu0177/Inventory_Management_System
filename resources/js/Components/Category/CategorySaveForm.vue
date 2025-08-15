<template>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="float-end">
                            <Link href="/categoryPage" class="btn btn-success mx-3 btn-sm">
                                Back
                            </Link>
                        </div>
                        <form @summit.prevent="submit">
                            <div class="card-body">
                                <h4>Save Category</h4>
                                <input hidden id="id" name="id" v-model="form.id" placeholder="Category ID" class="form-control" type="text"/>
                                <br/>
                                <input id="name" name="name" v-model="form.name" placeholder="Category Name" class="form-control" type="text"/>
                                <br/>
                                <button type="submit"  class="btn w-100 btn-success">Save Change</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <h1>Category created page</h1> -->
</template>
<script setup>
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import { ref } from 'vue';

const toaster = createToaster({
    position: 'top-right',
})

const urlParams = new URLSearchParams(window.location.search);
let id = ref(parseInt(urlParams.get('id')));
// console.log(id)
// const id = urlParams.get('id');

const form = useForm({ name: '', id:id });
const page = usePage();

let URL = "/create-category";
let category = page.props.category;

if (id.value !== 0 && category !== null) {
    URL = "/update-category";
    form.name = category.name;
}

function submit() {
    if (form.name.length === 0) {
        toaster.warning("Category Name is required");
    } else {
        form.post(URL, {
            onSuccess: () => {
                if (page.props.flash.status === true) {
                    toaster.success(page.props.flash.message);
                    router.get('/categoryPage');
                } else {
                    toaster.error(page.props.flash.message);
                }
            }
        });
    }
}

</script>

<style scoped>

</style>
