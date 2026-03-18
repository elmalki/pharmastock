<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link} from '@inertiajs/vue3';
import {DocumentArrowDownIcon} from "@heroicons/vue/16/solid/index.js";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import {ref} from "vue";
import {saveAs} from 'file-saver';
import moment from "moment";

const props = defineProps({items: Object, sort_fields: Object});
const page = ref((props.items.current_page - 1) * props.items.per_page);

function downloadFiles() {
    moment.locale("fr");
    axios.post("/stock", {}, {responseType: "blob", headers: {Accept: "application/pdf"}})
        .then(response => {
            const blob = new Blob([response.data], {type: "application/pdf"});
            saveAs(blob, "stock_" + moment().format("D_M_Y") + ".pdf");
        })
        .catch(e => console.error(e));
}
</script>

<template>
    <AppLayout title="Destockages">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Destockages', href: route('destockages.index'), current: true}]"/>

            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Destockages</h1>
                <div class="flex items-center gap-x-3">
                    <Link :href="route('destockages.create')"
                          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                        </svg>
                        Nouveau
                    </Link>
                    <button @click="downloadFiles"
                            class="inline-flex items-center px-3 py-2 bg-rose-500 hover:bg-rose-600 rounded-md text-sm font-semibold text-white shadow-sm transition-colors">
                        PDF
                    </button>
                    <a target="_blank" href="/export"
                       class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 rounded-md text-sm font-semibold text-white shadow-sm transition-colors">
                        Excel
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                            <span class="text-base font-medium">Aucun destockage trouvé</span>
                        </div>
                    </template>

                    <Column field="n_destockage" header="N°" sortable style="width: 5rem">
                        <template #body="{ data }">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ data.n_destockage }}</span>
                        </template>
                    </Column>
                    <Column header="Motifs">
                        <template #body="{ data }">
                            <div class="text-sm text-gray-700 prose prose-sm max-w-none" v-html="data.motifs"></div>
                        </template>
                    </Column>
                    <Column header="Produits">
                        <template #body="{ data }">
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <div class="grid grid-cols-2 bg-gray-50 border-b border-gray-200">
                                    <div class="py-1.5 px-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Produit</div>
                                    <div class="py-1.5 px-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Qté</div>
                                </div>
                                <div v-for="produit in data.produits" :key="produit.id"
                                     class="grid grid-cols-2 border-b border-gray-100 last:border-b-0">
                                    <div class="py-1.5 px-3 text-sm font-medium text-gray-900 text-center">{{ produit.label }}</div>
                                    <div class="py-1.5 px-3 text-sm text-gray-700 text-center tabular-nums">{{ produit.pivot.qte }}</div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="fonctionnaire" header="Bénéficiaire" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-gray-700">{{ data.fonctionnaire }}</span>
                        </template>
                    </Column>
                    <Column field="user.name" header="Effectué par" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-gray-700">{{ data.user?.name }}</span>
                        </template>
                    </Column>
                    <Column header="" style="width: 4rem">
                        <template #body="{ data }">
                            <a :href="route('destockages.decharge', {destockage: data.id})" target="_blank"
                               class="p-1.5 rounded-md text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-flex"
                               title="Décharge">
                                <DocumentArrowDownIcon class="w-6 h-6"/>
                            </a>
                        </template>
                    </Column>
                </DataTable>

                <div class="border-t border-gray-200">
                    <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                               @page="event => router.get(route('destockages.index'), {page: 1 + event.page})"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
