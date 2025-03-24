<template>
    <div>
        <VAutocomplete closable-chips variant="outlined" :auto-select-first="true" density="compact" v-model="selectedFournisseur" @update:model-value="send" chips :items="fournisseurs" return-object item-title="societe">
            <template v-slot:chip="{ props, item }">
                <v-chip
                    v-bind="props" color="amber" variant="flat" size="x-large" ></v-chip>
            </template>
        </VAutocomplete>
    </div>
</template>

<script setup>
import {ref} from 'vue'

const props = defineProps({
    fournisseur:Object
})
const emit = defineEmits(['selected-fournisseur'])
const fournisseurs = ref([])
axios.get('/api/fournisseurs').then(response => {
    fournisseurs.value = response.data
})

const selectedFournisseur = ref(props.fournisseur)
const send = () => {
    emit('selected-fournisseur', selectedFournisseur.value)
}
</script>
