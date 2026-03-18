<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import {ref, computed} from "vue";
import {FilterMatchMode} from '@primevue/core/api';
import {saveAs} from 'file-saver';

const props = defineProps({items: Array});

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Filter state
const showFilters = ref(false);
const selectedCategorie = ref(null);
const selectedExpiry = ref(null);

const expiryOptions = [
    {label: 'Tous', value: null},
    {label: 'Moins de 30 jours', value: 30},
    {label: '30 - 90 jours', value: 90},
    {label: '90 - 180 jours', value: 180},
    {label: 'Plus de 180 jours', value: 999},
];

// Computed filtered items
const allItems = computed(() => props.items ?? []);

const uniqueCategories = computed(() => {
    const cats = new Map();
    allItems.value.forEach(i => {
        if (i.produit?.categorie) cats.set(i.produit.categorie.id, i.produit.categorie.label);
    });
    return Array.from(cats, ([id, label]) => ({id, label}));
});

const filteredItems = computed(() => {
    let result = allItems.value;
    if (selectedCategorie.value) {
        result = result.filter(i => i.produit?.categorie?.id === selectedCategorie.value);
    }
    if (selectedExpiry.value) {
        result = result.filter(i => {
            const days = daysSinceExpiry(i.expirationDate);
            if (selectedExpiry.value === 30) return days < 30;
            if (selectedExpiry.value === 90) return days >= 30 && days < 90;
            if (selectedExpiry.value === 180) return days >= 90 && days < 180;
            if (selectedExpiry.value === 999) return days >= 180;
            return true;
        });
    }
    return result;
});

const hasActiveFilters = computed(() => selectedCategorie.value || selectedExpiry.value);

function clearFilters() {
    selectedCategorie.value = null;
    selectedExpiry.value = null;
}

// Stats — computed from ALL items (not filtered)
const totalLots = computed(() => allItems.value.length);
const totalUnites = computed(() => allItems.value.reduce((s, i) => s + (i.qte ?? 0), 0));
const totalValeur = computed(() => allItems.value.reduce((s, i) => s + (i.qte ?? 0) * parseFloat(i.prix_achat ?? 0), 0));

const criticalCount = computed(() => allItems.value.filter(i => daysSinceExpiry(i.expirationDate) < 30).length);
const recentCount = computed(() => allItems.value.filter(i => {
    const d = daysSinceExpiry(i.expirationDate);
    return d >= 30 && d < 90;
}).length);
const oldCount = computed(() => allItems.value.filter(i => daysSinceExpiry(i.expirationDate) >= 90).length);

// Unique products
const uniqueProducts = computed(() => {
    const prods = new Set();
    allItems.value.forEach(i => { if (i.produit) prods.add(i.produit.id); });
    return prods.size;
});

// Helpers
function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function formatPrice(val) {
    if (val == null) return '—';
    return parseFloat(val).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Dhs';
}

function daysSinceExpiry(date) {
    if (!date) return 0;
    return Math.floor((new Date() - new Date(date)) / (1000 * 60 * 60 * 24));
}

function expirySeverity(date) {
    const days = daysSinceExpiry(date);
    if (days >= 180) return 'danger';
    if (days >= 90) return 'danger';
    if (days >= 30) return 'warn';
    return 'warn';
}

function expiryLabel(date) {
    const days = daysSinceExpiry(date);
    if (days >= 365) return Math.floor(days / 365) + ' an' + (Math.floor(days / 365) > 1 ? 's' : '');
    if (days >= 30) return Math.floor(days / 30) + ' mois';
    return days + 'j';
}

function filterByExpiry(value) {
    selectedExpiry.value = value;
    showFilters.value = true;
}

function downloadPDF() {
    axios.post('/perimesPDF', {}, {
        responseType: 'blob',
        headers: {Accept: 'application/pdf'},
    }).then((response) => {
        const now = new Date();
        const dateStr = now.getDate() + '_' + (now.getMonth() + 1) + '_' + now.getFullYear();
        saveAs(new Blob([response.data], {type: 'application/pdf'}), 'stock_perime_' + dateStr + '.pdf');
    });
}
</script>

<template>
    <AppLayout title="Produits Périmés">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Produits', href: route('produits.index'), current: false},
                {name: 'Produits Périmés', href: route('produits.perimes'), current: true}
            ]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Produits Périmés</h1>
                    <p class="mt-1 text-sm text-gray-500">Suivi et gestion des lots ayant dépassé leur date de péremption</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="downloadPDF()"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zM10 8a.75.75 0 01.75.75v3.69l1.22-1.22a.75.75 0 111.06 1.06l-2.5 2.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.22 1.22V8.75A.75.75 0 0110 8z" clip-rule="evenodd"/>
                        </svg>
                        PDF
                    </button>
                    <a href="/exportPerimes" target="_blank"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zM10 8a.75.75 0 01.75.75v3.69l1.22-1.22a.75.75 0 111.06 1.06l-2.5 2.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.22 1.22V8.75A.75.75 0 0110 8z" clip-rule="evenodd"/>
                        </svg>
                        Excel
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total lots périmés -->
                <button @click="clearFilters"
                        class="group relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 hover:shadow-md hover:border-red-200 transition-all duration-200 text-left overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-red-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ totalLots }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Lots périmés</p>
                    </div>
                </button>

                <!-- Périmés < 30j -->
                <button @click="filterByExpiry(30)"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedExpiry === 30
                                ? 'bg-amber-50 border-amber-300 shadow-md ring-2 ring-amber-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-amber-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-700">{{ criticalCount }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">&lt; 30 jours</p>
                    </div>
                </button>

                <!-- Périmés 30-90j -->
                <button @click="filterByExpiry(90)"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedExpiry === 90
                                ? 'bg-orange-50 border-orange-300 shadow-md ring-2 ring-orange-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-orange-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-orange-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 shadow-lg shadow-orange-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-orange-700">{{ recentCount }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">30 - 90 jours</p>
                    </div>
                </button>

                <!-- Périmés > 90j -->
                <button @click="filterByExpiry(999)"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedExpiry === 999
                                ? 'bg-rose-50 border-rose-300 shadow-md ring-2 ring-rose-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-rose-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-rose-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-rose-600 to-red-700 shadow-lg shadow-rose-600/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-rose-700">{{ oldCount }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">&gt; 90 jours</p>
                    </div>
                </button>
            </div>

            <!-- Value banner -->
            <div class="mb-6 bg-gradient-to-r from-red-900 to-rose-800 rounded-2xl p-5 shadow-lg text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/5 rounded-full -mb-16"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/10 backdrop-blur-sm">
                            <svg class="w-7 h-7 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-red-300">Perte totale estimée</p>
                            <p class="text-2xl font-bold">{{ formatPrice(totalValeur) }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <p class="text-red-300">Produits</p>
                            <p class="text-lg font-semibold">{{ uniqueProducts }}</p>
                        </div>
                        <div class="w-px h-10 bg-red-700/50"></div>
                        <div class="text-center">
                            <p class="text-red-300">Unités</p>
                            <p class="text-lg font-semibold">{{ totalUnites }}</p>
                        </div>
                        <div class="w-px h-10 bg-red-700/50"></div>
                        <div class="text-center">
                            <p class="text-red-300">Lots</p>
                            <p class="text-lg font-semibold">{{ totalLots }}</p>
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
                                    ? 'bg-red-50 text-red-700 border-red-200 shadow-sm'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtres
                        <span v-if="hasActiveFilters"
                              class="flex items-center justify-center w-5 h-5 rounded-full bg-red-600 text-white text-xs font-bold">
                            {{ (selectedCategorie ? 1 : 0) + (selectedExpiry ? 1 : 0) }}
                        </span>
                    </button>

                    <!-- Active filter chips -->
                    <TransitionGroup
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-90"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-90"
                    >
                        <span v-if="selectedExpiry" key="expiry-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                              :class="{
                                  'bg-amber-100 text-amber-800': selectedExpiry === 30,
                                  'bg-orange-100 text-orange-800': selectedExpiry === 90,
                                  'bg-red-100 text-red-800': selectedExpiry === 180,
                                  'bg-rose-100 text-rose-800': selectedExpiry === 999,
                              }">
                            {{ expiryOptions.find(o => o.value === selectedExpiry)?.label }}
                            <button @click="selectedExpiry = null" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                </svg>
                            </button>
                        </span>
                        <span v-if="selectedCategorie" key="cat-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-800 text-xs font-medium">
                            {{ uniqueCategories.find(c => c.id === selectedCategorie)?.label }}
                            <button @click="selectedCategorie = null" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                </svg>
                            </button>
                        </span>
                        <button v-if="hasActiveFilters" key="clear-all"
                                @click="clearFilters"
                                class="text-xs text-gray-500 hover:text-red-600 font-medium transition-colors">
                            Tout effacer
                        </button>
                    </TransitionGroup>
                </div>

                <!-- Expanded filter panel -->
                <Transition
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFilters" class="mt-3 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Catégorie</label>
                                <select v-model="selectedCategorie"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option :value="null">Toutes les catégories</option>
                                    <option v-for="cat in uniqueCategories" :key="cat.id" :value="cat.id">
                                        {{ cat.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Ancienneté de péremption</label>
                                <select v-model="selectedExpiry"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                                    <option v-for="opt in expiryOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
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

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-red-100 text-red-700 text-xs font-bold">
                            {{ filteredItems.length }}
                        </span>
                        <span class="text-sm text-gray-600 font-medium">
                            lot{{ filteredItems.length > 1 ? 's' : '' }} périmé{{ filteredItems.length > 1 ? 's' : '' }}
                            <span v-if="hasActiveFilters" class="text-gray-400">filtrés</span>
                        </span>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-72 text-sm"/>
                    </div>
                </div>

                <DataTable :value="filteredItems" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['n_lot', 'produit.label', 'produit.categorie.label']"
                           paginator :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]"
                           stripedRows rowHover
                           sortField="expirationDate" :sortOrder="1">
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucun produit périmé</span>
                            <p class="text-sm text-gray-400 mt-1">
                                <span v-if="hasActiveFilters">Essayez de modifier vos filtres</span>
                                <span v-else>Tous vos produits sont en règle</span>
                            </p>
                            <button v-if="hasActiveFilters" @click="clearFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-red-600 hover:text-red-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Réinitialiser les filtres
                            </button>
                        </div>
                    </template>

                    <Column field="n_lot" header="N° Lot" sortable>
                        <template #body="{ data }">
                            <span class="inline-flex items-center gap-1.5 font-mono text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded-md font-medium">
                                {{ data.n_lot || '—' }}
                            </span>
                        </template>
                    </Column>

                    <Column field="produit.label" header="Produit" sortable style="min-width: 12rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold bg-red-100 text-red-700">
                                    {{ (data.produit?.label ?? '?').substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ data.produit?.label ?? '—' }}</div>
                                    <div v-if="data.produit?.dci" class="text-xs text-gray-400">{{ data.produit.dci }}</div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="produit.categorie.label" header="Catégorie" sortable>
                        <template #body="{ data }">
                            <span v-if="data.produit?.categorie?.label"
                                  class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-200">
                                {{ data.produit.categorie.label }}
                            </span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column field="qte" header="Quantité" sortable>
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums text-gray-900">{{ data.qte }}</span>
                        </template>
                    </Column>

                    <Column field="prix_achat" header="Prix Achat" sortable>
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-600 text-sm">{{ formatPrice(data.prix_achat) }}</span>
                        </template>
                    </Column>

                    <Column header="Perte">
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums text-red-600">
                                {{ formatPrice(data.qte * parseFloat(data.prix_achat ?? 0)) }}
                            </span>
                        </template>
                    </Column>

                    <Column field="expirationDate" header="Péremption" sortable style="min-width: 11rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full"
                                         :class="{
                                             'bg-amber-500': daysSinceExpiry(data.expirationDate) < 30,
                                             'bg-orange-500': daysSinceExpiry(data.expirationDate) >= 30 && daysSinceExpiry(data.expirationDate) < 90,
                                             'bg-red-500': daysSinceExpiry(data.expirationDate) >= 90
                                         }"></div>
                                    <span class="text-sm text-gray-900 tabular-nums">{{ formatDate(data.expirationDate) }}</span>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                      :class="{
                                          'bg-amber-100 text-amber-700': daysSinceExpiry(data.expirationDate) < 30,
                                          'bg-orange-100 text-orange-700': daysSinceExpiry(data.expirationDate) >= 30 && daysSinceExpiry(data.expirationDate) < 90,
                                          'bg-red-100 text-red-700': daysSinceExpiry(data.expirationDate) >= 90
                                      }">
                                    {{ expiryLabel(data.expirationDate) }}
                                </span>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
