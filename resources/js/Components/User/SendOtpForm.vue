<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6 center-screen">
                <div class="card animated fadeIn w-90  p-4">
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <h4>EMAIL ADDRESS</h4>
                            <br/>
                            <label>Your email address</label>
                            <input v-model="form.email" id="email" placeholder="User Email" class="form-control" type="email"/>
                            <br/>

                            <button type="submit" class="btn mt-3 w-100  btn-secondary">Next</button>
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
    position: "top-right",
});

const form = useForm({
    email: '',
});
const page = usePage();

function submit() {
    if (form.email.length === 0) {
        toaster.warning('Your valid email must be provided');
    } else {
        form.post('/send-otp', {
            onSuccess: () => {
                if (page.props.flash.status === true) {
                    toaster.success(page.props.flash.message);
                    router.get('/verify-otp');
                } else {
                    toaster.error(page.props.flash.message);
                }
            }
        })
    }
}

</script>

<style scoped>

</style>
