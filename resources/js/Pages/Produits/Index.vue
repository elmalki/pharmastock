<template>
    <AppLayout title="Produits">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <Breadcrumbs :pages="[{name:'Produits',href:route('produits.index'),current:true}]"></Breadcrumbs>
            </div>
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Link type="button" :href="route('produits.create')"
                          class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Ajouter
                    </Link>
                </div>
            </div>
            <div class="flex flex-row gap-1 my-2">
                <button class="uppercase ml-10 px-2  bg-rose-500 hover:bg-rose-600 rounded text-white" @click="downloadFiles">Liste pdf</button>
                <a target="_blank" class="uppercase ml-1 px-2  bg-green-500 hover:bg-green-600 rounded text-white" href="/export">Liste Excel</a>
                <!--button class="uppercase ml-1 px-2  bg-rose-500 hover:bg-rose-600 rounded text-white" @click="barcodes">Les Codes à barre générer</button-->

            </div>
            <div class=" bg-transparent pb-10 h-screen  mx-auto">
                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           @update:sort-order="value => loadPage(value)" @update:sort-field="value => field = value"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order">
                    <template #empty>
                        <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucun
                            produit trouvé
                        </div>
                    </template>
                    <Column field="label" header="Libellé" sortable></Column>
                    <Column field="categorie.label" header="Catégorie"></Column>
                    <Column header="Périssable" sortable>
                        <template #body="slotProps">
                            <div :class="[ slotProps.data.perissable?'bg-teal-500':'bg-pink-500','w-fit px-3 text-white  flex items-center justify-center']">
                                {{ slotProps.data.perissable ? 'OUI' : 'NON' }}
                            </div>

                        </template>
                    </Column>
                    <Column field="limit_command" header="Stock Critique" sortable></Column>
                    <Column field="qte" header="Quantité en stock" sortable></Column>

                    <Column header="Actions">
                        <template #body="slotProps">
                            <div class="flex gap-1">
                                <Link :href="route('produits.show',{produit:slotProps.data.id})"
                                      class="text-sky-600 px-3 hover:text-sky-700"
                                >Détail<span class="sr-only">, {{ slotProps.data.label }}</span>
                                </Link>
                                <Link :href="route('produits.edit',{produit:slotProps.data.id})" class="text-green-600 px-3 hover:text-green-700">
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
                               @page="event=>router.get(route('produits.index'),{page:1+event.page})">
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
                Supprimer ce produit
            </template>
            <template #content>
                Voulez-vous vraiment supprimer ce produit?
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
import {router, Link, usePage} from '@inertiajs/vue3';
import {computed, nextTick, reactive, ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import moment from "moment";
import {saveAs} from 'file-saver'
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
const props = defineProps({items: Array,sort_fields:Array});
const deleteModal = ref(false)
const item_id = ref(null)
const downloadFiles =()=> {
    moment.locale("fr");
    axios
        .post(
            "/stock",
            {},
            {
                responseType: "blob",
                headers: {
                    Accept: "application/pdf",
                },
            }
        )
        .then((response) => {
           // this.icon = "check_circle";
            //this.text = "Téléchargement effectué avec succés";
            //this.color = "success";
            setTimeout(() => {
                this.dialogDownload = false;
            }, 2500);
            var blob = new Blob([response.data], { type: "application/pdf" });
            saveAs(blob, "stock_" + moment().format("d_M_Y") + "_.pdf");
        })
        .catch((e) => {
            this.color = "pink";
            this.text = e;
            this.icon = "error";
            setTimeout(() => {
                this.dialogDownload = false;
            }, 1500);
        });
}
const barcodes =()=> {
    moment.locale("fr");
    axios
        .post(
            "/barcodes",
            {},
            {
                responseType: "blob",
                headers: {
                    Accept: "application/pdf",
                },
            }
        )
        .then((response) => {
            setTimeout(() => {
                this.dialogDownload = false;
            }, 2500);
            var blob = new Blob([response.data], { type: "application/pdf" });
            saveAs(blob, "barcodes_" + moment().format("d_M_Y") + "_.pdf");
        })
        .catch((e) => {
            this.color = "pink";
            this.text = e;
            this.icon = "error";
            setTimeout(() => {
                this.dialogDownload = false;
            }, 1500);
        });
}
function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/produits/${item_id.value}`);
}
const page = ref((props.items.current_page - 1)*10)

</script>
<style scoped>

</style>
