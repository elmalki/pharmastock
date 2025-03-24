<template>
    <AppLayout title="Catégories">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <Breadcrumbs class="mb-4" :pages="[{name:'Catégories',href:route('categories.index'),current:true}]"></Breadcrumbs>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button" @click="createOrUpdateModal()"
                            class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Nouvelle Catégorie
                    </button>
                </div>
            </div>
            <div class=" bg-transparent pb-10 h-screen  mx-auto">
                <DataTable :value="items" tableStyle="min-width: 50rem" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20, 50]">
                    <template #empty>
                        <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucune
                            Catégorie trouvée
                        </div>
                    </template>
                    <Column field="label" header="Libellé" sortable></Column>
                    <Column field="total" header="Nombre de produits" sortable></Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex gap-1">
                                <button @click="item_id=slotProps.data.id,form.label=slotProps.data.label,createOrUpdateModal()" href="#"
                                        class="text-green-600 px-3 hover:text-green-700"
                                >Modifier<span class="sr-only">, {{ slotProps.data.label }}</span></button
                                >
                                <button @click="deleteModal=true,item_id=slotProps.data.id"
                                        class="text-red-600  hover:text-red-7r00"
                                >Supprimer<span class="sr-only">, {{ slotProps.data.label }}</span></button
                                >
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
        <DialogModal :show="addModal" @close="closeAddModal()">
            <template #title>
                {{ title }}
            </template>
            <template #content>
                <div class="mt-4" @keydown.enter="createOrEditItem()">
                    <TextInput
                        ref="label"
                        v-model="form.label"
                        type="text"
                        class="mt-1 block w-3/4 border"
                        placeholder="Libellé"
                        autocomplete="Libellé de la catégorie"
                    />
                    <span v-for="error in form.errors">
                        <InputError :message="error" class="mt-2"/>
                    </span>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="closeAddModal()">
                    Annuler
                </SecondaryButton>
                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="createOrEditItem()"
                >
                    {{button_name}}
                </PrimaryButton>
            </template>
        </DialogModal>
        <ConfirmationModal :show="deleteModal" @close="deleteModal=false">
            <template #title>
                Supprimer la catégorie
            </template>
            <template #content>
                Voulez-vous vraiment supprimer cette catégorie?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">
                    Annuler
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    @click="deleteCategory()"
                >
                    Supprimer
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router} from '@inertiajs/vue3';
import {nextTick, reactive, ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
const props = defineProps({items: Array});
const deleteModal = ref(false)
const addModal = ref(false)
const item_id = ref(null)
const search = ref(null)
const title = ref('')
const button_name  = ref('')
const form = reactive({
    label: '',
    processing: false,
    errors: []
});
const createOrUpdateModal = ()=>{
    title.value = item_id.value?'Modifier la catégorie':'Ajouter une nouvelle catégorie';
    button_name.value = item_id.value?'Modifier':'Ajouter';
    addModal.value = true
}
function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/categories/${item_id.value}`);
}

const createOrEditItem = () => {
    form.processing = true;
    if (!item_id.value) {
        axios.post(route('categories.store'), {
            label: form.label,
        }).then((response) => {
            form.processing = false;
            props.items.push(response.data)
            closeAddModal();
            nextTick().then(() => {
            });

        }).catch(error => {
            form.processing = false;
            form.errors = error.response?.data.errors.label;
        });
    } else {
        axios.put(route('categories.update', {category: item_id.value}), {
            label: form.label,
        }).then((response) => {
            form.processing = false;
            const item = props.items.find(el => response.data.id === el.id)
            item.label = response.data.label
            closeAddModal();
            nextTick().then(() => {
            });

        }).catch(error => {
            form.processing = false;
            form.errors = error.response?.data.errors.label;
        });

    }

};
const closeAddModal = () => {
    item_id.value = null;
    form.errors = [];
    form.label = ''
    addModal.value = false;
}
</script>
<style scoped>

</style>
