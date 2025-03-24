<template>
    <AppLayout title="Produits">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                    <Breadcrumbs class="mb-4" :pages="[{name:'Produits',href:route('produits.index'),current:false},{name:'Produits Périmés',href:route('produits.perimes'),current:true}]"></Breadcrumbs>
            </div>
            <div class=" bg-transparent pb-10 h-screen  mx-auto">
                <DataTable :value="items" tableStyle="min-width: 50rem" paginator :rows="5" :rowsPerPageOptions="[5, 10, 20, 50]">
                    <template #empty>
                        <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucun
                            produit pirimé trouvé
                        </div>
                    </template>
                    <Column field="n_lot" header="Lot" sortable></Column>
                    <Column field="produit.label" header="Label" ></Column>
                    <Column field="produit.categorie.label" header="Label" ></Column>
                    <Column field="qte" header="Quantité" sortable></Column>
                </DataTable>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="flex flex-row">
                            <button class="uppercase ml-10 px-2  bg-rose-500 hover:bg-rose-600 rounded text-white" @click="downloadFiles">Liste pdf</button>
                            <a target="_blank" class="uppercase ml-1 px-2  bg-green-500 hover:bg-green-600 rounded text-white" href="/exportPerimes">Liste Excel</a>
                        </div>
                        <table class="table-fixed min-w-full mt-2 divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Lot
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Libellé
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Catégorie
                                </th>
                                <th scope="col"
                                    class=" py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Quantité périmée
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="item in items" :key="item.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.n_lot }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.produit.label }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.produit.categorie?.label }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.qte }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link} from '@inertiajs/vue3';
import {computed, nextTick, reactive, ref} from "vue";
import moment from "moment";
import {saveAs} from 'file-saver'
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
const props = defineProps({items: Array});
const deleteModal = ref(false)
const item_id = ref(null)

const downloadFiles =()=> {
    moment.locale("fr");
    axios
        .post(
            "/perimesPDF",
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
            saveAs(blob, "stock_perime_" + moment().format("d_M_Y") + "_.pdf");
        })
        .catch((e) => {
           console.error(e)
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

</script>
<style scoped>

</style>
