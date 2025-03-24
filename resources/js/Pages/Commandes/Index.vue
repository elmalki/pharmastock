<template>
    <AppLayout title="Stockagess">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="uppercase text-base font-semibold text-gray-900">Stockages</h1>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Link type="button" href="/commandes/create"
                          class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Ajouter
                    </Link>
                </div>
            </div>
            <div class=" bg-transparent pb-10 h-screen  mx-auto">
                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           @update:sort-order="value => loadPage(value)" @update:sort-field="value => field = value"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order">
                    <template #empty>
                        <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucune
                            commande trouvée
                        </div>
                    </template>
                    <Column field="n_facture" header="N° Facture" sortable></Column>
                    <Column field="n_bon" header="N° Bon/Marché" sortable></Column>
                    <Column field="fournisseur.societe" header="Fournisseur"></Column>
                    <Column header="Date" sortable sort-field="date">
                        <template #body="slotProps">
                            {{ slotProps.data.date.split("-").reverse().join("-") }}
                        </template>
                    </Column>
                    <Column field="paiement" header="Mode" sortable></Column>
                    <Column field="dateEcheance" header="Date d'écheance" sortable></Column>
                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex gap-1">
                            <Link :href="route('commandes.show',{commande:slotProps.data.id})"
                                  class="text-sky-600 px-3 hover:text-sky-700"
                            >Détail<span class="sr-only">, {{ slotProps.data.label }}</span>
                            </Link>
                            <Link :href="route('commandes.edit',{commande:slotProps.data.id})" class="text-green-600 px-3 hover:text-green-700">
                                Modifier<span class="sr-only">, {{ slotProps.data.label }}</span>
                            </Link>
                            <button @click="deleteModal=true,item_id=slotProps.data.id"
                                    class="text-red-600  hover:text-red-7r00">Supprimer<span
                                class="sr-only">, {{ slotProps.data.label }}</span></button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
                <div class="card">
                    <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                               @page="event=>router.get(route('commandes.index'),{page:1+event.page})">
                        <template #start="slotProps">
                        </template>
                        <template #end>
                            <Button type="button" icon="pi pi-search"/>
                        </template>
                    </Paginator>

                </div>
            </div>
        </div>
        <ConfirmationModal :show="deleteModal" @close="deleteModal=false">
            <template #title>
                Supprimer ce entree
            </template>
            <template #content>
                Voulez-vous vraiment supprimer ce entree?
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
import {PencilIcon} from '@heroicons/vue/20/solid'
import DataTable from 'primevue/datatable';
import Paginator from 'primevue/paginator';
import Column from 'primevue/column';
import {Link, router} from '@inertiajs/vue3';
import {ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({items: Array, sort_fields: Array});
const deleteModal = ref(false)
const item_id = ref(null)
const field = ref(null)
const page = ref((props.items.current_page - 1)*10)

function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/commandes/${item_id.value}`);
}

const loadPage = (v) => {
    router.get(route('commandes.index'), {field: field._value, order: v})
}

</script>
<style scoped>

</style>
