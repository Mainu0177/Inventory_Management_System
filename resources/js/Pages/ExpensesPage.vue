<template>
    <SideNavLayout>
        <div class="px-4 md:px-8 py-6">
            <Expenses 
                :expenses="expenses" 
                :accounts="accounts" 
            />
        </div>
    </SideNavLayout>
</template>

<script setup>
import SideNavLayout from '../layout/SideNavLayout.vue';
import Expenses from '../Components/Delivery/Expenses.vue';

const props = defineProps({
    expenses: {
        type: Array,
        default: () => [],
    },
    accounts: {
        type: Array,
        default: () => [],
    },
});
</script>
