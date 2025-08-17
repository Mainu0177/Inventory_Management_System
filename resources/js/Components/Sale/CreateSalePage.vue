<template>
    <h1>This is sale page</h1>
    <p>{{ customers }}</p>
    <p>{{ products }}</p>
</template>

<script setup>
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import { createToaster } from '@meforma/vue-toaster';
import { ref } from 'vue';

const toaster = createToaster({
    position: 'top-right',
});

let page = usePage();

const customers = page.props.customers;
const products = page.props.products;



</script>

<style scoped>

</style>
