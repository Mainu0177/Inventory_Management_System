<template>
<div class="container-fluid">
                        <div class="row">

                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100 bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        <span id="product">{{ summary.Product }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Product</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100 bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        <span id="product">{{ summary.Category }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Category</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100 bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        <span id="product">{{ summary.Customer }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Customer</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100  bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        <span id="product">{{ summary.Invoice }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Invoice</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100 bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        $ <span id="product">{{ summary.Total }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Total Sale</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100  bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        $ <span id="product">{{ summary.Vat }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Vat Collection</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 animated fadeIn p-2">
                                <div class="card card-plain h-100  bg-white">
                                    <div class="p-3">
                                        <div class="row">
                                            <div class="col-9 col-lg-8 col-md-8 col-sm-9">
                                                <div>
                                                    <h5 class="mb-0 text-capitalize font-weight-bold">
                                                        $ <span>{{ summary.Payable }}</span>
                                                    </h5>
                                                    <p class="mb-0 text-sm">Total Collection</p>
                                                </div>
                                            </div>
                                            <div class="col-3 col-lg-4 col-md-4 col-sm-3 text-end">
                                                <div class="icon icon-shape bg-success shadow-sm float-end rounded-3">
                                                    <img alt="" class="w-100 " src="../../Assets/img/icon.svg"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const summary = page.props.list;
</script>

<style scoped>

</style>
