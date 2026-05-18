<template>
    <SideNavLayout>
        <Banking :accounts="accounts" :transactions="transactions" />
    </SideNavLayout>
</template>

<script setup>
import Banking from '../Components/Banking/Banking.vue';
import SideNavLayout from '../layout/SideNavLayout.vue';

defineProps({
    accounts: Array,
    transactions: Array
});
</script>

<style scoped>

</style>
