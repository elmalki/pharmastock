<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref, computed} from "vue";
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import {Link, useForm, usePage} from "@inertiajs/vue3";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Paginator from "primevue/paginator";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const page = usePage();
const items = ref(null);
const searching = ref(false);
const hasSearched = ref(false);
const props = defineProps({errors: Object, fournisseurs: Array});

const form = useForm({
    fournisseur_id: null,
    endDate: null,
    startDate: null,
    n_bon: null,
    n_facture: null,
});

const hasFilters = computed(() =>
    form.fournisseur_id || form.n_bon || form.n_facture || form.startDate || form.endDate
);

const resultCount = computed(() => items.value?.total ?? 0);

const search = (pageNum = 1) => {
    searching.value = true;
    axios.post("/commandes/search?page=" + pageNum, form)
        .then(response => {
            items.value = response.data;
            hasSearched.value = true;
        })
        .catch(error => console.error(error))
        .finally(() => searching.value = false);
};

function clearAndReset() {
    form.reset();
    items.value = null;
    hasSearched.value = false;
}

function formatDate(d) {
    if (!d) return '—';
    return d.split('-').reverse().join('/');
}

function fmt(v) {
    if (!v) return '—';
    return parseFloat(v).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Dhs';
}
</script>

<template>
    <AppLayout title="Recherche Commandes">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Approvisionnements', href: route('commandes.index'), current: false},
                {name: 'Recherche', href: route('commandes.search'), current: true}
            ]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Recherche avancée</h1>
                            <p class="text-sky-200 text-sm mt-0.5">Recherchez dans vos commandes d'approvisionnement</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div v-if="hasSearched" class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-xl px-4 py-2">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                            </svg>
                            <span class="text-sm font-semibold text-white">{{ resultCount }} résultat{{ resultCount > 1 ? 's' : '' }}</span>
                        </div>
                        <Link :href="route('commandes.index')"
                              class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                            </svg>
                            Retour
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Search Form -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8" @keyup.enter="search()">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100">
                            <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900">Critères de recherche</h2>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Fournisseur -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fournisseur</label>
                        <Select v-model="form.fournisseur_id" :options="fournisseurs" option-label="societe"
                                option-value="id" placeholder="Tous les fournisseurs" class="w-full" filter showClear/>
                    </div>

                    <!-- Reference numbers -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Bon / Marché</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/>
                                    </svg>
                                </div>
                                <input v-model="form.n_bon" type="text"
                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                       placeholder="Numéro de bon">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Facture / Engagement</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <input v-model="form.n_facture" type="text"
                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                       placeholder="Numéro de facture">
                            </div>
                        </div>
                    </div>

                    <!-- Date range -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date début</label>
                            <div class="relative">
                                <DatePicker v-model="form.startDate" date-format="yy-mm-dd" class="w-full" showIcon iconDisplay="input"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date fin</label>
                            <div class="relative">
                                <DatePicker v-model="form.endDate" date-format="yy-mm-dd" class="w-full" showIcon iconDisplay="input"/>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-5 border-t border-gray-100">
                        <button v-if="hasFilters" @click="clearAndReset" type="button"
                                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-red-600 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>
                            </svg>
                            Réinitialiser
                        </button>
                        <div v-else></div>
                        <button @click="search()" type="button" :disabled="searching"
                                class="inline-flex items-center gap-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-sky-200 hover:from-sky-500 hover:to-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer">
                            <svg v-if="!searching" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                            <svg v-else class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ searching ? 'Recherche...' : 'Rechercher' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4"
            >
                <div v-if="hasSearched">
                    <!-- Results header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-900">Résultats</h2>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset"
                                  :class="resultCount > 0 ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-50 text-gray-500 ring-gray-200'">
                                {{ resultCount }} trouvé{{ resultCount > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!items?.data?.length" class="bg-white rounded-2xl shadow-sm border-2 border-dashed border-gray-200 p-16 text-center">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Aucun résultat</h3>
                        <p class="text-sm text-gray-500 max-w-sm mx-auto">Aucune commande ne correspond à vos critères. Essayez de modifier vos filtres.</p>
                        <button @click="clearAndReset" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-sky-600 hover:text-sky-700 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>
                            </svg>
                            Réinitialiser la recherche
                        </button>
                    </div>

                    <!-- Results table -->
                    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <DataTable :value="items.data" tableStyle="min-width: 50rem" stripedRows rowHover>
                            <Column field="n_facture" header="N° Facture" sortable>
                                <template #body="{ data }">
                                    <span class="font-mono text-sm font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded">{{ data.n_facture }}</span>
                                </template>
                            </Column>
                            <Column field="n_bon" header="N° Bon/Marché" sortable>
                                <template #body="{ data }">
                                    <span v-if="data.n_bon" class="font-mono text-sm text-gray-700">{{ data.n_bon }}</span>
                                    <span v-else class="text-gray-300">—</span>
                                </template>
                            </Column>
                            <Column field="fournisseur.societe" header="Fournisseur">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-violet-700">{{ (data.fournisseur?.societe || '?').substring(0, 2).toUpperCase() }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ data.fournisseur?.societe || '—' }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Date" sortable sort-field="date">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 tabular-nums">{{ formatDate(data.date) }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="paiement" header="Mode" sortable>
                                <template #body="{ data }">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20': data.paiement === 'Éspèce',
                                              'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20': data.paiement === 'Chèque',
                                              'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20': data.paiement === 'Virement',
                                              'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20': data.paiement === 'Effet',
                                              'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20': data.paiement === 'Crédit',
                                              'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/10': !['Éspèce','Chèque','Virement','Effet','Crédit'].includes(data.paiement),
                                          }">
                                        {{ data.paiement }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="montant" header="Montant" sortable>
                                <template #body="{ data }">
                                    <span class="text-sm font-semibold text-gray-900 tabular-nums">{{ fmt(data.montant) }}</span>
                                </template>
                            </Column>
                            <Column field="dateEcheance" header="Échéance" sortable>
                                <template #body="{ data }">
                                    <span v-if="data.dateEcheance" class="text-sm text-gray-500 tabular-nums">{{ formatDate(data.dateEcheance) }}</span>
                                    <span v-else class="text-gray-300">—</span>
                                </template>
                            </Column>
                            <Column header="" style="width: 6rem">
                                <template #body="{ data }">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <Link :href="route('commandes.show', {commande: data.id})"
                                              class="p-2 rounded-lg text-gray-400 hover:text-sky-600 hover:bg-sky-50 transition-all" title="Détail">
                                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            </svg>
                                        </Link>
                                        <Link :href="route('commandes.edit', {commande: data.id})"
                                              class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all" title="Modifier">
                                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                            </svg>
                                        </Link>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>

                        <div v-if="items.last_page > 1" class="border-t border-gray-100">
                            <Paginator :rows="items.per_page" :totalRecords="items.total"
                                       @page="event => search(1 + event.page)"/>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </AppLayout>
</template>
