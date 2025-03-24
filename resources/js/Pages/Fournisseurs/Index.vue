<template>
    <AppLayout title="Catégories">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <Breadcrumbs :pages="[{name:'Fournisseurs',href:route('fournisseurs.index'),current:true}]"></Breadcrumbs>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Link type="button" href="/fournisseurs/create"
                            class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Ajouter
                    </Link>
                </div>
            </div>
            <DataTable :value="items" tableStyle="min-width: 50rem" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20, 50]">
                <template #empty>
                    <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucun
                        fournisseur trouvé
                    </div>
                </template>
                <Column field="societe" header="Société" sortable></Column>
                <Column field="contact" header="Contact" sortable></Column>
                <Column field="adresse" header="Adresse" sortable></Column>
                <Column field="email" header="Email" sortable></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-1">
                            <Link  :href="'/fournisseurs/'+slotProps.data.id+'/edit'"
                                   class="text-green-600 px-3 hover:text-green-700"
                            >Modifier<span class="sr-only">, {{ slotProps.data.label }}</span></Link
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
import {router,Link} from '@inertiajs/vue3';
import { ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({items: Array});
const deleteModal = ref(false)
const item_id = ref(null)
const search = ref(null)
function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/fournisseurs/${item_id.value}`);
}

</script>
<style scoped>

</style>
