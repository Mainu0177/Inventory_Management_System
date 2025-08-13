<template>
    <div>
        <nav id="topNav" class="navbar fixed-top bg-success top-navbar">
            <div class="container-fluid">
                <a id="MenuBar" @click="NavOpenClose" class="icon-nav mx-0 my-1 h5">
                    <i class="fa text-white fa-bars"></i>
                </a>
            </div>
        </nav>
        <div id="sideNav" class="side-nav-open">
            <div class="side-nav-top text-center">
                <img alt="" class="side-nav-logo" src="./Assets/img/logo.svg" />
            </div>

            <a href="/dashboardPage" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-tachometer-alt text-green"></i></span>
                <span class="side-bar-item-caption">Dashboard</span>
            </a>

            <a href="category-list.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-th-list text-green"></i></span>
                <span class="side-bar-item-caption">Category</span>
            </a>

            <a href="customer-list.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-users text-green"></i></span>
                <span class="side-bar-item-caption">Customer</span>
            </a>

            <a href="product-list.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-box text-green"></i></span>
                <span class="side-bar-item-caption">Product</span>
            </a>

            <a href="salePage.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-shopping-cart text-green"></i></span>
                <span class="side-bar-item-caption">Create Sale</span>
            </a>

            <a href="invoiceList.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-file-invoice-dollar text-green"></i></span>
                <span class="side-bar-item-caption">Invoice</span>
            </a>

            <a href="profileForm.html" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-user-circle text-green"></i></span>
                <span class="side-bar-item-caption">Profile</span>
            </a>

            <a href="" class="side-bar-item">
                <span class="side-bar-item-icon"><i class="fa fa-sign-out-alt text-green"></i></span>
                <span class="side-bar-item-caption">Logout</span>
            </a>

        </div>
        <div id="content" class="content">
            <div class="container-fluid">
                <main>
                    <!-- start content -->
                    <slot>

                    </slot>
                    <!-- end content -->
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>

</style>
