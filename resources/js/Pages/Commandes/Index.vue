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

const props = defineProps({
    items: Object,
    sort_fields: Object,
    stats: Object,
    applied_filters: Object,
});

const deleteModal = ref(false);
const item_id = ref(null);
const item_ref = ref('');
const field = ref(null);
const page = ref((props.items.current_page - 1) * props.items.per_page);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Filter state
const showFilters = ref(!!(props.applied_filters?.situation || props.applied_filters?.paiement || props.applied_filters?.date_from || props.applied_filters?.date_to));
const selectedSituation = ref(props.applied_filters?.situation || null);
const selectedPaiement = ref(props.applied_filters?.paiement || null);
const dateFrom = ref(props.applied_filters?.date_from || '');
const dateTo = ref(props.applied_filters?.date_to || '');

const situationOptions = [
    {label: 'Toutes', value: null},
    {label: 'Payé', value: 'Payé'},
    {label: 'En compte', value: 'En compte'},
    {label: 'Crédit', value: 'Crédit'},
];

const paiementOptions = [
    {label: 'Tous', value: null},
    {label: 'Espèce', value: 'Éspèce'},
    {label: 'Chèque', value: 'Chèque'},
    {label: 'Effet', value: 'Effet'},
    {label: 'Virement', value: 'Virement'},
    {label: 'En compte', value: 'En compte'},
    {label: 'Multi Réglement', value: 'Multi Réglement'},
];

const hasActiveFilters = computed(() => selectedSituation.value || selectedPaiement.value || dateFrom.value || dateTo.value);

function buildParams(extra = {}) {
    const params = {...extra};
    if (selectedSituation.value) params.situation = selectedSituation.value;
    if (selectedPaiement.value) params.paiement = selectedPaiement.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    if (field.value) params.field = field.value;
    return params;
}

function applyFilters() {
    router.get(route('commandes.index'), buildParams(), {preserveState: true});
}

function clearFilters() {
    selectedSituation.value = null;
    selectedPaiement.value = null;
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('commandes.index'), {}, {preserveState: true});
}

function filterBySituation(situation) {
    selectedSituation.value = situation;
    showFilters.value = true;
    applyFilters();
}

function loadPage(order) {
    router.get(route('commandes.index'), buildParams({field: field.value, order}), {preserveState: true});
}

function confirmDelete(commande) {
    item_id.value = commande.id;
    item_ref.value = commande.n_bon || commande.n_facture;
    deleteModal.value = true;
}

function deleteCommande() {
    deleteModal.value = false;
    router.delete(`/commandes/${item_id.value}`);
}

function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function formatPrice(val) {
    if (val == null) return '—';
    return parseFloat(val).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Dhs';
}

function situationVal(situation) {
    if (!situation) return '';
    return typeof situation === 'object' ? situation?.value ?? situation : situation;
}

function situationSeverity(situation) {
    const val = situationVal(situation);
    if (val === 'Payé') return 'success';
    if (val === 'En compte') return 'warn';
    if (val === 'Crédit') return 'danger';
    return 'secondary';
}

function paiementVal(paiement) {
    if (!paiement) return '';
    return typeof paiement === 'object' ? paiement?.value ?? paiement : paiement;
}

function paiementSeverity(paiement) {
    const val = paiementVal(paiement);
    const map = {
        'Éspèce': 'success', 'Chèque': 'info', 'Effet': 'info',
        'Virement': 'info', 'En compte': 'warn', 'Multi Réglement': 'secondary',
    };
    return map[val] ?? 'secondary';
}

function isOverdue(commande) {
    if (!commande.dateEcheance || situationVal(commande.situation) === 'Payé') return false;
    return new Date(commande.dateEcheance) < new Date();
}
</script>

<template>
    <AppLayout title="Approvisionnements">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Approvisionnements', href: route('commandes.index'), current: true}]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Approvisionnements</h1>
                    <p class="mt-1 text-sm text-gray-500">Suivi de vos commandes fournisseurs</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('commandes.search')"
                          class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Recherche avancée
                    </Link>
                    <Link :href="route('commandes.create')"
                          class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                        </svg>
                        Nouvelle commande
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total -->
                <button @click="clearFilters"
                        class="group relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 hover:shadow-md hover:border-teal-200 transition-all duration-200 text-left overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-teal-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-teal-500 to-teal-600 shadow-lg shadow-teal-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total commandes</p>
                    </div>
                </button>

                <!-- Payées -->
                <button @click="filterBySituation('Payé')"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedSituation === 'Payé'
                                ? 'bg-emerald-50 border-emerald-300 shadow-md ring-2 ring-emerald-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-emerald-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-emerald-700">{{ stats.payees }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Payées</p>
                    </div>
                </button>

                <!-- En cours -->
                <button @click="filterBySituation('En compte')"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedSituation === 'En compte'
                                ? 'bg-amber-50 border-amber-300 shadow-md ring-2 ring-amber-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-amber-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-amber-700">{{ stats.en_cours }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">En cours</p>
                    </div>
                </button>

                <!-- En retard -->
                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-red-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-red-700">{{ stats.overdue }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">En retard</p>
                    </div>
                </div>
            </div>

            <!-- Montant banner -->
            <div class="mb-6 bg-gradient-to-r from-teal-800 to-emerald-900 rounded-2xl p-5 shadow-lg text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/5 rounded-full -mb-16"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/10 backdrop-blur-sm">
                            <svg class="w-7 h-7 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-teal-300">Montant total des achats</p>
                            <p class="text-2xl font-bold">{{ formatPrice(stats.montant) }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <p class="text-teal-300">Commandes</p>
                            <p class="text-lg font-semibold">{{ stats.total }}</p>
                        </div>
                        <div class="w-px h-10 bg-teal-700/50"></div>
                        <div class="text-center">
                            <p class="text-teal-300">Payées</p>
                            <p class="text-lg font-semibold text-emerald-400">{{ stats.payees }}</p>
                        </div>
                        <div class="w-px h-10 bg-teal-700/50"></div>
                        <div class="text-center">
                            <p class="text-teal-300">En retard</p>
                            <p class="text-lg font-semibold" :class="stats.overdue > 0 ? 'text-red-400' : 'text-teal-200'">{{ stats.overdue }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters bar -->
            <div class="mb-4">
                <div class="flex items-center gap-2">
                    <button @click="showFilters = !showFilters"
                            :class="[
                                'inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg border transition-all duration-200',
                                showFilters
                                    ? 'bg-teal-50 text-teal-700 border-teal-200 shadow-sm'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtres
                        <span v-if="hasActiveFilters"
                              class="flex items-center justify-center w-5 h-5 rounded-full bg-teal-600 text-white text-xs font-bold">
                            {{ (selectedSituation ? 1 : 0) + (selectedPaiement ? 1 : 0) + (dateFrom ? 1 : 0) + (dateTo ? 1 : 0) }}
                        </span>
                    </button>

                    <TransitionGroup
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-90"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-90"
                    >
                        <span v-if="selectedSituation" key="sit-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                              :class="{
                                  'bg-emerald-100 text-emerald-800': selectedSituation === 'Payé',
                                  'bg-amber-100 text-amber-800': selectedSituation === 'En compte',
                                  'bg-red-100 text-red-800': selectedSituation === 'Crédit',
                              }">
                            {{ selectedSituation }}
                            <button @click="selectedSituation = null; applyFilters()" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                            </button>
                        </span>
                        <span v-if="selectedPaiement" key="pay-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                            {{ selectedPaiement }}
                            <button @click="selectedPaiement = null; applyFilters()" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                            </button>
                        </span>
                        <span v-if="dateFrom" key="from-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-800 text-xs font-medium">
                            Depuis {{ dateFrom }}
                            <button @click="dateFrom = ''; applyFilters()" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                            </button>
                        </span>
                        <span v-if="dateTo" key="to-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-800 text-xs font-medium">
                            Jusqu'au {{ dateTo }}
                            <button @click="dateTo = ''; applyFilters()" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/></svg>
                            </button>
                        </span>
                        <button v-if="hasActiveFilters" key="clear-all"
                                @click="clearFilters"
                                class="text-xs text-gray-500 hover:text-red-600 font-medium transition-colors">
                            Tout effacer
                        </button>
                    </TransitionGroup>
                </div>

                <Transition
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="mt-3 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Situation</label>
                                <select v-model="selectedSituation" @change="applyFilters"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option v-for="opt in situationOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Mode de paiement</label>
                                <select v-model="selectedPaiement" @change="applyFilters"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option v-for="opt in paiementOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date début</label>
                                <input type="date" v-model="dateFrom" @change="applyFilters"
                                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"/>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date fin</label>
                                <input type="date" v-model="dateTo" @change="applyFilters"
                                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"/>
                            </div>
                            <div class="flex items-end">
                                <button v-if="hasActiveFilters" @click="clearFilters"
                                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-teal-100 text-teal-700 text-xs font-bold">
                            {{ items.total }}
                        </span>
                        <span class="text-sm text-gray-600 font-medium">
                            commande{{ items.total > 1 ? 's' : '' }}
                            <span v-if="hasActiveFilters" class="text-gray-400">filtrées</span>
                        </span>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-72 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['n_facture', 'n_bon', 'fournisseur.societe']"
                           @update:sort-order="value => loadPage(value)"
                           @update:sort-field="value => field = value"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucune commande trouvée</span>
                            <p class="text-sm text-gray-400 mt-1">
                                <span v-if="hasActiveFilters">Essayez de modifier vos filtres</span>
                                <span v-else>Commencez par créer une commande</span>
                            </p>
                            <button v-if="hasActiveFilters" @click="clearFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-teal-600 hover:text-teal-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Réinitialiser les filtres
                            </button>
                        </div>
                    </template>

                    <!-- N° Bon -->
                    <Column field="n_bon" header="N° Bon" sortable>
                        <template #body="{ data }">
                            <Link :href="route('commandes.show', {commande: data.id})"
                                  class="inline-flex items-center gap-1.5 font-mono text-xs text-teal-700 bg-teal-50 px-2.5 py-1 rounded-md font-semibold hover:bg-teal-100 transition-colors ring-1 ring-inset ring-teal-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ data.n_bon }}
                            </Link>
                        </template>
                    </Column>

                    <!-- N° Facture -->
                    <Column field="n_facture" header="N° Facture" sortable>
                        <template #body="{ data }">
                            <span v-if="data.n_facture" class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md">{{ data.n_facture }}</span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <!-- Fournisseur -->
                    <Column field="fournisseur.societe" header="Fournisseur" sortable>
                        <template #body="{ data }">
                            <div v-if="data.fournisseur" class="flex items-center gap-2.5">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ data.fournisseur.societe.substring(0, 2).toUpperCase() }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ data.fournisseur.societe }}</span>
                            </div>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <!-- Date -->
                    <Column field="date" header="Date" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                </svg>
                                <span class="text-sm text-gray-700 tabular-nums">{{ formatDate(data.date) }}</span>
                            </div>
                        </template>
                    </Column>

                    <!-- Montant -->
                    <Column field="montantPaye" header="Montant" sortable>
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900 tabular-nums">{{ formatPrice(data.montantPaye) }}</span>
                        </template>
                    </Column>

                    <!-- Paiement -->
                    <Column field="paiement" header="Paiement" sortable>
                        <template #body="{ data }">
                            <Tag :severity="paiementSeverity(data.paiement)" :value="paiementVal(data.paiement) || '—'" class="text-xs"/>
                        </template>
                    </Column>

                    <!-- Situation -->
                    <Column field="situation" header="Situation" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 rounded-full"
                                     :class="{
                                         'bg-emerald-500': situationVal(data.situation) === 'Payé',
                                         'bg-amber-500': situationVal(data.situation) === 'En compte',
                                         'bg-red-500': situationVal(data.situation) === 'Crédit',
                                     }"></div>
                                <Tag :severity="situationSeverity(data.situation)" :value="situationVal(data.situation) || '—'" class="text-xs"/>
                            </div>
                        </template>
                    </Column>

                    <!-- Échéance -->
                    <Column field="dateEcheance" header="Échéance" sortable>
                        <template #body="{ data }">
                            <div v-if="data.dateEcheance" class="flex items-center gap-1.5">
                                <span class="text-sm tabular-nums" :class="isOverdue(data) ? 'text-red-600 font-medium' : 'text-gray-700'">
                                    {{ formatDate(data.dateEcheance) }}
                                </span>
                                <span v-if="isOverdue(data)"
                                      class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">
                                    En retard
                                </span>
                            </div>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <!-- Actions -->
                    <Column header="" style="width: 8rem">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-0.5">
                                <Link :href="route('commandes.show', {commande: data.id})"
                                      class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all"
                                      title="Détail">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </Link>
                                <Link :href="route('commandes.edit', {commande: data.id})"
                                      class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all"
                                      title="Modifier">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    </svg>
                                </Link>
                                <button @click="confirmDelete(data)"
                                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all"
                                        title="Supprimer">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div class="border-t border-gray-200">
                    <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                               @page="event => router.get(route('commandes.index'), buildParams({page: 1 + event.page}))"/>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <ConfirmationModal :show="deleteModal" @close="deleteModal = false">
            <template #title>Supprimer cette commande</template>
            <template #content>
                Voulez-vous vraiment supprimer la commande <span class="font-semibold">{{ item_ref }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteCommande()">Supprimer</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
