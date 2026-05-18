<template>
    <SideNavLayout>
        <InvoiceList :list="list" :customers="customers" :products="products" :quotations="quotations" />
    </SideNavLayout>
</template>

<script setup>
import InvoiceList from '../Components/Invoice/InvoiceList.vue';
import SideNavLayout from '../layout/SideNavLayout.vue';

defineProps({
    list: Array,
    customers: Array,
    products: Array,
    quotations: Array
});
</script>

<style scoped>

</style>
