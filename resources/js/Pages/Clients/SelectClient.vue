<script setup>
import {ref} from 'vue'

const props = defineProps({
    client: Object
})
const emit = defineEmits(['selected-client'])
const clients = ref([])

axios.get('/api/clients').then(response => {
    clients.value = response.data
    if (!selectedClient.value && clients.value.length) {
        const defaultClient = clients.value.find(c => c.id === 1) || clients.value[0]
        selectedClient.value = defaultClient
        emit('selected-client', defaultClient)
    }
})

const selectedClient = ref(props.client)
const send = () => {
    emit('selected-client', selectedClient.value)
}
</script>

<template>
    <VAutocomplete
        variant="outlined"
        :auto-select-first="true"
        density="compact"
        v-model="selectedClient"
        @update:model-value="send"
        :items="clients"
        return-object
        item-title="nom"
        placeholder="Rechercher un client..."
    />
</template>
