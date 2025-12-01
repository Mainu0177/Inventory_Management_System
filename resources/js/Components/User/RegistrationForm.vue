<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-10 center-screen">
                <div class="card animated fadeIn w-100 p-3">
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <h4>Sign Up</h4>
                            <hr/>
                            <div class="container-fluid m-0 p-0">
                                <div class="row m-0 p-0">

                                    <div class="col-md-4 p-2">
                                        <label>Name</label>
                                        <input v-model="form.name" id="name" placeholder="First Name" class="form-control" type="text"/>
                                    </div>

                                    <div class="col-md-4 p-2">
                                        <label>Email Address</label>
                                        <input v-model="form.email" id="email" placeholder="User Email" class="form-control" type="email"/>
                                    </div>

                                    <div class="col-md-4 p-2">
                                        <label>Mobile Number</label>
                                        <input v-model="form.phone" id="mobile" placeholder="Mobile" class="form-control" type="mobile"/>
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Password</label>
                                        <input v-model="form.password" id="password" placeholder="User Password" class="form-control" type="password"/>
                                    </div>
                                    <div class="col-md-4 p-2">
                                        <label>Confirm Password</label>
                                        <input v-model="form.cpassword" id="cpassword" placeholder="User Confirm Password" class="form-control" type="password"/>
                                    </div>
                                </div>
                                <div class="row m-0 p-0">
                                    <div class="col-md-4 p-2">
                                        <button type="submit" href="/registration"  class="btn mt-3 w-100  btn-secondary">Complete</button>
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
    import { Link, useForm, usePage, router } from '@inertiajs/vue3';
    import { createToaster } from '@meforma/vue-toaster';

const toaster = createToaster({
    position: 'top-right',
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
});
const page = usePage();

function submit(){
    if (form.name.length === 0) {
        toaster.warning("Name is required");
    } else if (form.email.length === 0) {
        toaster.warning("Email is required");
    } else if (form.phone.length === 0) {
        toaster.warning("Phone is required");
    } else if (form.password.length === 0) {
        toaster.warning("Password is required");
    } else if (form.password !== form.cpassword) {
        toaster.warning("Password and Confirm Password do not match");
    } else {
        form.post('/user-registration', {
            onSuccess: () => {
                if (page.props.flash.status === true) {
                    toaster.success(page.props.flash.message);
                    return router.get('/login');
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
