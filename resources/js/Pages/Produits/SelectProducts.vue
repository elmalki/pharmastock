<template>
    <div class="mt-6">
        <v-layout row>
            <v-autocomplete
                multiple
                clearable
                chips
                closable-chips
                return-object
                v-model="selected"
                :items="items"
                :item-title="searchLabel"
                :auto-select-first="true"
                clear-on-select
                class="bg-white"
                label="Produits"
                variant='outlined'
            >
                <template v-slot:chip="{ props, item }">
                    <v-chip
                        v-bind="props" color="amber" variant="flat" ></v-chip>
                </template>
            </v-autocomplete>
        </v-layout>
        <div class="mt-10 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-2">
            <ProductInput v-for="product in selected" :entree="product.entree" :id="product.id"
                          :label="product.label" :perissable="product.perissable"
                          @remove-item="(id)=>removeProduct(id)"></ProductInput>
        </div>

    </div>
</template>

<script setup>
import 'vuetify/styles'
import {ref, watch} from 'vue'
import ProductInput from "@/Pages/Produits/product-input.vue";
const emit = defineEmits(['selected'])
const props = defineProps({produits:Array})
let produits = []
const items = ref([])
const expirationDate = {}
axios.get('/api/produits').then(response => {
    produits = response.data;
    items.value = produits
    produits.forEach((el) => {
        el.entree = {expirationDate: null, qte: null, n_lot: null, prix_achat: null, prix_vente: null,tva:20};
        let id = el.label;
        expirationDate[id] = false;
    });
})
props.produits?.forEach(el=>el.entree=el.pivot)
const selected = ref(props.produits)
watch(selected,(val)=> emit('selected', selected.value))
const removeProduct = (id) => {
    selected.value = selected.value.filter(_ => _.id !== id)
}

function searchLabel(item) {
    return item.label
}

</script>
<style>
[type='text']:focus, input:where(:not([type])):focus, [type='email']:focus, [type='url']:focus, [type='password']:focus, [type='number']:focus, [type='date']:focus, [type='datetime-local']:focus, [type='month']:focus, [type='search']:focus, [type='tel']:focus, [type='time']:focus, [type='week']:focus, [multiple]:focus, textarea:focus, select:focus {
    --tw-ring-color: gray;
    --tw-shadow-colored: gray !important;
    border-color:gray !important;
}
</style>
