<template>
    <div>
        <input @keydown.enter.prevent="handleBarcodeScan($event)"/>
        <div v-for="item in selectedItems" :key="item.id">
            {{ item.label }}
        </div>
    </div>
</template>

<script setup>
import {ref, computed} from 'vue'

const produits = ref([])
axios.get('/api/produits').then(response => {
    produits.value = response.data
})
const selectedItems = ref([])
const query = ref('')
const filteredproduits = computed(() =>
    query.value === ''
        ? produits : produits.value.filter((produit) => {
            return produit.barcode == query.value
        })
)
const handleBarcodeScan = (e) => {
    const input = e.target.value
    const exactMatch = produits.value.find((item) => item.barcode === input);
    if (exactMatch) {
        if (!selectedItems.value.some(item => item.id === exactMatch.id)) {
            selectedItems.value.push(exactMatch); // Add exact match to selected items
        } else {
            selectedItems.value = selectedItems.value.filter(
                (item) => item.barcode !== input
            );
        }
        e.target.value = ""; // Clear input after adding
    } else {
        query.value = input; // Keep input if no match
    }
};
</script>
