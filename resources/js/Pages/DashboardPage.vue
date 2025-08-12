<template>
    <h1>This is Dashboard Page</h1>
    <h1>Email:{{ page.props.user}}</h1>
</template>

<script setup>
import { Link, usePage, router, useForm } from '@inertiajs/vue3';
const page = usePage();
</script>
