<template>
    <div>
        <VAutocomplete variant="outlined" :auto-select-first="true" density="compact" v-model="selectedClient" @update:model-value="send" :items="clients" return-object item-title="nom"></VAutocomplete>
    </div>
</template>

<script setup>
import {ref} from 'vue'
const props = defineProps({
    client:Object
})
const emit = defineEmits(['selected-client'])
const clients = ref([])
axios.get('/api/clients').then(response => {
    clients.value = response.data
})

const query = ref('')
const selectedClient = ref(props.client)
const send = () => {
    emit('selected-client', selectedClient.value)
}
</script>
