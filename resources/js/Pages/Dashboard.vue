<template>
    <AppLayout>
        <div class="my-10 max-w-7xl mx-auto">
            <Product :total="total" :en_rupture="en_rupture" :perimes="perimes"></Product>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Product from '@/Pages/Stats/Product.vue'
const props = defineProps({total:Number,en_rupture:Number,perimes:Number})
</script>
