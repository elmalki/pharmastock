<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link} from '@inertiajs/vue3';
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import {ref, computed} from "vue";
import {saveAs} from 'file-saver';
import {FilterMatchMode} from '@primevue/core/api';

const props = defineProps({items: Object, sort_fields: Object});
const page = ref((props.items.current_page - 1) * props.items.per_page);
const downloading = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Stats
const totalDestockages = computed(() => props.items.total ?? props.items.data?.length ?? 0);
const totalProduits = computed(() => {
    let s = 0;
    (props.items.data || []).forEach(d => s += (d.produits?.length ?? 0));
    return s;
});
const totalUnites = computed(() => {
    let s = 0;
    (props.items.data || []).forEach(d => (d.produits || []).forEach(p => s += (p.pivot?.qte ?? 0)));
    return s;
});

function downloadFiles() {
    window.open(route('destockages.pdf'), '_blank');
}
</script>

<template>
    <AppLayout title="Destockages">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Destockages', href: route('destockages.index'), current: true}]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-orange-500 via-red-500 to-rose-600 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Destockages</h1>
                            <p class="text-orange-200 text-sm mt-0.5">Suivi des sorties de stock et décharges</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="downloadFiles" :disabled="downloading"
                                class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors cursor-pointer disabled:opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            PDF
                        </button>
                        <a href="/export" target="_blank"
                           class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            Excel
                        </a>
                        <Link :href="route('destockages.create')"
                              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-orange-700 shadow-sm hover:bg-orange-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            Nouveau
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-3 gap-4 mb-8">
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-100 mb-3">
                            <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Destockages</p>
                        <p class="mt-1 text-3xl font-bold text-orange-700">{{ totalDestockages }}</p>
                    </div>
                </div>
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100 mb-3">
                            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Produits concernés</p>
                        <p class="mt-1 text-3xl font-bold text-violet-700">{{ totalProduits }}</p>
                    </div>
                </div>
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100 mb-3">
                            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Unités sorties</p>
                        <p class="mt-1 text-3xl font-bold text-rose-700">{{ totalUnites }}</p>
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100">
                            <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">Liste des destockages</span>
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-600/20">
                            {{ items.total }}
                        </span>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-72 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['n_destockage', 'motifs', 'user.name']"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucun destockage trouvé</span>
                            <p class="text-sm text-gray-400 mt-1">Commencez par créer un nouveau destockage</p>
                        </div>
                    </template>

                    <Column field="n_destockage" header="N°" sortable style="width: 6rem">
                        <template #body="{ data }">
                            <span class="font-mono text-sm font-semibold text-orange-700 bg-orange-50 px-2 py-0.5 rounded">{{ data.n_destockage }}</span>
                        </template>
                    </Column>
                    <Column header="Produits" style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="produit in data.produits" :key="produit.id"
                                      class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-200">
                                    {{ produit.label }}
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-violet-200 text-violet-800 text-[10px] font-bold tabular-nums">{{ produit.pivot.qte }}</span>
                                </span>
                                <span v-if="!data.produits?.length" class="text-gray-300">—</span>
                            </div>
                        </template>
                    </Column>
                    <Column header="Motifs" style="max-width: 16rem">
                        <template #body="{ data }">
                            <div v-if="data.motifs" class="text-xs text-gray-600 line-clamp-2 prose prose-xs max-w-none" v-html="data.motifs"></div>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>
                    <Column field="user.name" header="Par" sortable>
                        <template #body="{ data }">
                            <span v-if="data.user?.name" class="text-sm text-gray-700">{{ data.user.name }}</span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>
                    <Column header="" style="width: 4rem">
                        <template #body="{ data }">

                            <!--a :href="route('destockages.decharge', {destockage: data.id})" target="_blank"
                               class="p-2 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all inline-flex"
                               title="Télécharger la décharge">
                                <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </a-->
                        </template>
                    </Column>
                </DataTable>

                <div v-if="items.last_page > 1" class="border-t border-gray-100">
                    <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                               @page="event => router.get(route('destockages.index'), {page: 1 + event.page})"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
