<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from 'primevue/datatable';
import Paginator from 'primevue/paginator';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import {Link, router} from '@inertiajs/vue3';
import {computed, ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {FilterMatchMode} from '@primevue/core/api';

const props = defineProps({items: Object, sort_fields: Object});

const deleteModal = ref(false);
const item_id = ref(null);
const item_facture = ref('');
const field = ref(null);
const page = ref((props.items.current_page - 1) * props.items.per_page);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Stats
const stats = computed(() => {
    const data = props.items.data;
    return {
        count: props.items.total,
        ca: data.reduce((s, v) => s + parseFloat(v.subtotal ?? 0), 0),
        benefice: data.reduce((s, v) => s + parseFloat(v.benefice ?? 0), 0),
        impayes: data.filter(v => parseFloat(v.reste ?? 0) > 0).length,
    };
});

function loadPage(order) {
    router.get(route('ventes.index'), {field: field.value, order}, {preserveState: true});
}

function confirmDelete(vente) {
    item_id.value = vente.id;
    item_facture.value = vente.n_facture;
    deleteModal.value = true;
}

function deleteVente() {
    deleteModal.value = false;
    router.delete(`/ventes/${item_id.value}`);
}

function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function formatPrice(val) {
    if (val == null) return '—';
    return parseFloat(val).toFixed(2) + ' Dhs';
}

function paiementSeverity(paiement) {
    const val = typeof paiement === 'object' ? paiement?.value ?? paiement : paiement;
    const map = {
        'Éspèce': 'success',
        'Chèque': 'info',
        'Effet': 'info',
        'Virement': 'info',
        'TPE': 'success',
        'En compte': 'warn',
        'Crédit': 'warn',
        'Multi Réglement': 'secondary',
    };
    return map[val] ?? 'secondary';
}

function paiementLabel(paiement) {
    if (!paiement) return '—';
    return typeof paiement === 'object' ? paiement?.value ?? paiement : paiement;
}

function statutSeverity(statut) {
    const val = typeof statut === 'object' ? statut?.value ?? statut : statut;
    if (val === 'payé') return 'success';
    if (val === 'partiel') return 'warn';
    if (val === 'annulé') return 'danger';
    return 'secondary';
}

function statutLabel(statut) {
    if (!statut) return '—';
    const val = typeof statut === 'object' ? statut?.value ?? statut : statut;
    const map = {'payé': 'Payé', 'partiel': 'Partiel', 'annulé': 'Annulé'};
    return map[val] ?? val;
}
</script>

<template>
    <AppLayout title="Ventes">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Ventes', href: route('ventes.index'), current: true}]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Ventes</h1>
                <Link :href="route('ventes.create')"
                      class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                    </svg>
                    Nouvelle vente
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Ventes (page)</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ items.data.length }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">CA (page)</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatPrice(stats.ca) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Bénéfice (page)</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600">{{ formatPrice(stats.benefice) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Impayées (page)</div>
                    <div class="mt-1 text-2xl font-bold" :class="stats.impayes > 0 ? 'text-amber-600' : 'text-gray-900'">{{ stats.impayes }}</div>
                </div>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Search bar -->
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <span class="text-sm text-gray-500">{{ items.total }} vente{{ items.total > 1 ? 's' : '' }} au total</span>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-64 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['n_facture', 'client.nom']"
                           @update:sort-order="value => loadPage(value)"
                           @update:sort-field="value => field = value"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                            </svg>
                            <span class="text-base font-medium">Aucune vente trouvée</span>
                        </div>
                    </template>

                    <!-- N° Facture -->
                    <Column field="n_facture" header="N° Facture" sortable>
                        <template #body="{ data }">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ data.n_facture }}</span>
                        </template>
                    </Column>

                    <!-- Client -->
                    <Column field="client.nom" header="Client" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-900">{{ data.client?.nom ?? '—' }}</span>
                        </template>
                    </Column>

                    <!-- Date -->
                    <Column field="date" header="Date" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-700">{{ formatDate(data.date) }}</span>
                        </template>
                    </Column>

                    <!-- Montant -->
                    <Column field="subtotal" header="Montant" sortable>
                        <template #body="{ data }">
                            <div>
                                <span class="font-semibold text-gray-900">{{ formatPrice(data.total) }}</span>
                                <div v-if="parseFloat(data.remise) > 0" class="text-xs text-gray-400">
                                    <span class="line-through">{{ formatPrice(data.subtotal) }}</span>
                                    <span class="ml-1 text-emerald-500">-{{ data.remise }}%</span>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Paiement -->
                    <Column field="paiement" header="Paiement" sortable>
                        <template #body="{ data }">
                            <Tag :severity="paiementSeverity(data.paiement)" :value="paiementLabel(data.paiement)" class="text-xs"/>
                        </template>
                    </Column>

                    <!-- Statut -->
                    <Column field="statut" header="Statut" sortable>
                        <template #body="{ data }">
                            <Tag :severity="statutSeverity(data.statut)" :value="statutLabel(data.statut)" class="text-xs"/>
                        </template>
                    </Column>

                    <!-- Reste -->
                    <Column field="reste" header="Reste" sortable>
                        <template #body="{ data }">
                            <span v-if="parseFloat(data.reste) > 0" class="font-medium text-amber-600">{{ formatPrice(data.reste) }}</span>
                            <span v-else class="text-gray-400">—</span>
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column header="" style="width: 7rem">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('ventes.show', {vente: data.id})"
                                      class="p-1.5 rounded-md text-gray-400 hover:text-indigo-600 hover:bg-indigo-50"
                                      title="Détail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="sr-only">Détail</span>
                                </Link>
                                <Link :href="route('ventes.edit', {vente: data.id})"
                                      class="p-1.5 rounded-md text-gray-400 hover:text-green-600 hover:bg-green-50"
                                      title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    </svg>
                                    <span class="sr-only">Modifier</span>
                                </Link>
                                <button @click="confirmDelete(data)"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50"
                                        title="Supprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                    <span class="sr-only">Supprimer</span>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <!-- Pagination -->
                <div class="border-t border-gray-200">
                    <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                               @page="event => router.get(route('ventes.index'), {page: 1 + event.page})"/>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <ConfirmationModal :show="deleteModal" @close="deleteModal = false">
            <template #title>Supprimer cette vente</template>
            <template #content>
                Voulez-vous vraiment supprimer la vente <span class="font-semibold">{{ item_facture }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteVente()">Supprimer</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
