<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card animated fadeIn w-100 p-3">
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <h4>Profile Update</h4>
                            <hr/>
                            <div class="container-fluid m-0 p-0">
                                <div class="row m-0 p-0">
                                    <div class="col-md-4 p-2">
                                        <label>Name</label>
                                        <input id="name" v-model="form.name" placeholder="First Name" class="form-control" type="text"/>
                                    </div>

                                    <div class="col-md-4 p-2">
                                        <label>Email Address</label>
                                        <input id="email" v-model="form.email" placeholder="User Email" class="form-control" type="email"/>
                                    </div>

                                    <div class="col-md-4 p-2">
                                        <label>Mobile Number</label>
                                        <input id="mobile" v-model="form.phone" placeholder="Mobile" class="form-control" type="mobile"/>
                                    </div>
                                </div>
                                <div class="row m-0 p-0">
                                    <div class="col-md-4 p-2">
                                        <button type="submit"  class="btn mt-3 w-100  btn-success">Update</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({
    position: 'top-right'
})

const page = usePage();
const user = page.props.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone
})

function submit() {
    if (form.name.length === 0) {
        toaster.warning("Name is required");
    } else if (form.email.length === 0) {
        toaster.warning("Email is required");
    } else if (form.phone.length === 0) {
        toaster.warning("Mobile is required");
    } else {
        form.post('/user-profile-update', {
            onSuccess: () =>{
                if (page.props.flash?.status) {
                    toaster.success(page.props.flash.message);
                    router.get('/userProfilePage')
                }
            }
        });
    }
}

</script>

<style scoped>

</style>
