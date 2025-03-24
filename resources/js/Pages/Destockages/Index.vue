<template>
    <AppLayout title="Produits Déstockés">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="uppercase text-base font-semibold text-gray-900">Produits Déstockés</h1>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="flex flex-row">

                    <button class="uppercase  px-2  bg-rose-500 hover:bg-rose-600 rounded text-white" @click="downloadFiles">Liste pdf</button>
                    <a target="_blank" class="uppercase ml-1 px-2  bg-green-500 hover:bg-green-600 rounded text-white" href="/export">Liste Excel</a>

                </div>
                <div class=" bg-transparent pb-10 h-screen  mx-auto">
                    <DataTable :value="items.data" tableStyle="min-width: 50rem"
                               @update:sort-order="value => loadPage(value)" @update:sort-field="value => field = value"
                               :sort-field="sort_fields.field" :sortOrder="sort_fields.order">
                        <template #empty>
                            <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucun
                                déstockage trouvé
                            </div>
                        </template>
                        <Column field="n_destockage" header="N°" sortable></Column>
                        <Column  header="Motifs">
                            <template #body="slotProps">
                                <div v-html="slotProps.data.motifs"></div>
                            </template>
                        </Column>
                        <Column header="Produits">
                            <template #body="slotProps">
                                <table class=" bg-amber-200 min-w-full divide-y divide-gray-300">
                                    <thead>
                                    <tr class="">
                                    <th class="py-3.5 px-4 text-center text-sm font-semibold text-gray-900">Produit</th>
                                    <th class="px-4 py-3.5 text-center text-sm font-semibold text-gray-900">Qté</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white text-center">
                                    <tr v-for="produit in slotProps.data.produits" :key="produit.id"  class="divide-x  divide-gray-500 border-gray-500 ">
                                        <td class="py-4 pr-4 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0"> {{ produit.label }}</td>
                                        <td class="p-4 text-sm whitespace-nowrap text-gray-800">{{produit.pivot.qte}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </template>
                        </Column>
                        <Column field="fonctionnaire" header="Bénéficiaire" sortable></Column>
                        <Column field="user.name" header="Effectué par" sortable></Column>
                        <Column header="Décharges">
                            <template #body="slotProps">
                                <a :href="route('destockages.decharge',{destockage:slotProps.data.id})" target="_blank">
                                <DocumentArrowDownIcon
                                    class="size-10 text-orange-500 hover:text-orange-600"
                                    aria-hidden="true"></DocumentArrowDownIcon>
                                </a>
                                <!--div class="flex gap-1">
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
                                </div-->
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
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link} from '@inertiajs/vue3';
import {DocumentArrowDownIcon} from "@heroicons/vue/16/solid/index.js";
import moment from "moment";
import {saveAs} from 'file-saver'
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import {ref} from "vue";
import {MagnifyingGlassIcon} from "@heroicons/vue/24/solid/index.js";
const props = defineProps({items: Array,sort_fields:Array});
const page = ref((props.items.current_page - 1)*10)

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

function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/produits/${item_id.value}`);
}

</script>
<style scoped>

</style>
