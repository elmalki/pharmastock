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
    </div>
</template>

<script setup>
import 'vuetify/styles'
import {ref, watch} from 'vue'
const emit = defineEmits(['selected'])
const props = defineProps({produits:Array})
let produits = []
const items = ref([])
axios.get('/api/produits').then(response => {
    produits = response.data;
    items.value = produits
})
props.produits?.forEach(el=>el.entree=el.pivot)
const selected = ref(props.produits)
watch(selected,(val)=> emit('selected', selected.value))

function searchLabel(item) {
    return `${item.label}(${item.barcode})`
}

</script>
<style>
#input-0:focus {
    --tw-ring-color: white !important;
    --tw-shadow-colored: white !important;
}
</style>
