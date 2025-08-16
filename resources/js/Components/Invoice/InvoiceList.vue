<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-body">
                <div>
                    <h3>Invoice List</h3>
                </div>
                <hr />
                <div>
                    <input
                    placeholder="Search..."
                    class="form-control mb-2 w-auto form-control-sm"
                    type="text"
                    />
                    <EasyDataTable
                    buttons-pagination
                    alternating
                    :headers="Header"
                    :items="Item"
                    :rows-per-page="10"
                    :search-field="searchField"
                    :search-value="searchValue"
                    >

                    <template #item-number="{ id }">
                        <button
                        class="viewBtn btn btn-outline-dark text-sm px-3 py-1 btn-sm m-0"
                        >
                        <i class="fa text-sm fa-eye"></i>
                        </button>
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </template>

                    </EasyDataTable>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Modal component for invoice details -->
        <InvoiceDetails/>

    </div>
</template>

<script setup>

</script>

<style scoped>

</style>
