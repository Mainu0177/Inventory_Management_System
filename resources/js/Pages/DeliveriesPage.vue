<template>
    <SideNavLayout>
        <Deliveries :customers="customers" :quotations="quotations" :challans="challans" />
    </SideNavLayout>
</template>

<script setup>
import SideNavLayout from '../layout/SideNavLayout.vue';
import Deliveries from '../Components/Delivery/Deliveries.vue';

defineProps({
    customers: Array,
    quotations: Array,
    challans: Array
});
</script>

<style scoped>
</style>
