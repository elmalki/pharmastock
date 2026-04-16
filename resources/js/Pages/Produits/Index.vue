<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link, useForm} from '@inertiajs/vue3';
import {ref, computed, nextTick} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import {FilterMatchMode} from '@primevue/core/api';
import Select from 'primevue/select';


const props = defineProps({
    items: Object,
    sort_fields: Object,
    categories: Array,
    stats: Object,
    applied_filters: Object,
});

// Table state
const deleteModal = ref(false);
const item_id = ref(null);
const item_label = ref('');
const field = ref(null);
const page = ref((props.items.current_page - 1) * props.items.per_page);
const downloading = ref(false);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

const searchTerm = ref(props.applied_filters?.search || '');
let searchTimer = null;
function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const params = {};
        if (searchTerm.value) params.search = searchTerm.value;
        if (selectedCategorie.value) params.categorie_id = selectedCategorie.value;
        if (selectedStockStatus.value) params.stock_status = selectedStockStatus.value;
        router.get(route('produits.index'), params, {preserveState: true, preserveScroll: true, replace: true});
    }, 350);
}

// Filter state
const selectedCategorie = ref(props.applied_filters?.categorie_id ? Number(props.applied_filters.categorie_id) : null);
const selectedStockStatus = ref(props.applied_filters?.stock_status || null);
const showFilters = ref(!!(props.applied_filters?.categorie_id || props.applied_filters?.stock_status));

const stockStatusOptions = [
    {label: 'Tous', value: null},
    {label: 'Disponible', value: 'disponible'},
    {label: 'Critique', value: 'critique'},
    {label: 'Rupture', value: 'rupture'},
];

const categorieOptions = computed(() => [
    {label: 'Toutes', id: null},
    ...props.categories,
]);

const hasActiveFilters = computed(() => selectedCategorie.value || selectedStockStatus.value);

function applyFilters() {
    const params = {};
    if (searchTerm.value) params.search = searchTerm.value;
    if (selectedCategorie.value) params.categorie_id = selectedCategorie.value;
    if (selectedStockStatus.value) params.stock_status = selectedStockStatus.value;
    if (field.value) params.field = field.value;
    router.get(route('produits.index'), params, {preserveState: true});
}

function clearFilters() {
    selectedCategorie.value = null;
    selectedStockStatus.value = null;
    searchTerm.value = '';
    router.get(route('produits.index'), {}, {preserveState: true});
}

function filterByStatus(status) {
    selectedStockStatus.value = status;
    showFilters.value = true;
    applyFilters();
}

// Modal state
const showModal = ref(false);
const editingProduct = ref(null);
const isEditing = computed(() => editingProduct.value !== null);
const labelInput = ref(null);

// Stepper
const currentStep = ref(1);
const totalSteps = 3;
const steps = [
    {number: 1, label: 'Général', description: 'Infos de base'},
    {number: 2, label: 'Pharmacie', description: 'Détails pharma'},
    {number: 3, label: 'Prix & Stock', description: 'Tarification'},
];
function nextStep() { if (currentStep.value < totalSteps) currentStep.value++ }
function prevStep() { if (currentStep.value > 1) currentStep.value-- }
function goToStep(step) { currentStep.value = step }

const form = useForm({
    barcode: '',
    label: '',
    dci: '',
    forme: '',
    dosage: '',
    laboratoire: '',
    description: '',
    perissable: false,
    ordonnance_requise: false,
    prix_public: null,
    unite: 1,
    limit_command: '',
    categorie: null,
});
const categorie_id = computed(() => form.categorie ? form.categorie.id : null);

function openCreate() {
    editingProduct.value = null;
    form.reset();
    form.clearErrors();
    currentStep.value = 1;
    showModal.value = true;
    nextTick(() => labelInput.value?.focus());
}

function openEdit(produit) {
    editingProduct.value = produit;
    form.barcode = produit.barcode || '';
    form.label = produit.label || '';
    form.dci = produit.dci || '';
    form.forme = produit.forme || '';
    form.dosage = produit.dosage || '';
    form.laboratoire = produit.laboratoire || '';
    form.description = produit.description || '';
    form.perissable = !!produit.perissable;
    form.ordonnance_requise = !!produit.ordonnance_requise;
    form.prix_public = produit.prix_public;
    form.unite = produit.unite || 1;
    form.limit_command = produit.limit_command || '';
    form.categorie = produit.categorie || null;
    form.clearErrors();
    currentStep.value = 1;
    showModal.value = true;
    nextTick(() => labelInput.value?.focus());
}

function closeModal() {
    showModal.value = false;
    editingProduct.value = null;
}

function generateBarcode() {
    form.barcode = Math.floor(100000 + Math.random() * 9999999999) + '';
}

function submitForm() {
    if (isEditing.value) {
        form.transform(data => ({...data, categorie_id: categorie_id.value}))
            .patch(route('produits.update', {produit: editingProduct.value.id}), {
                onSuccess: () => closeModal(),
                preserveScroll: true,
            });
    } else {
        form.transform(data => ({...data, categorie_id: categorie_id.value}))
            .post(route('produits.store'), {
                onSuccess: () => closeModal(),
                preserveScroll: true,
            });
    }
}

// Table functions
function loadPage(order) {
    const params = {field: field.value, order};
    if (selectedCategorie.value) params.categorie_id = selectedCategorie.value;
    if (selectedStockStatus.value) params.stock_status = selectedStockStatus.value;
    router.get(route('produits.index'), params, {preserveState: true});
}

function confirmDelete(produit) {
    item_id.value = produit.id;
    item_label.value = produit.label;
    deleteModal.value = true;
}

function deleteProduit() {
    deleteModal.value = false;
    router.delete(`/produits/${item_id.value}`);
}

function downloadPDF() {
    window.open('/stock', '_blank');
}

function stockSeverity(produit) {
    if (produit.qte === 0) return 'danger';
    if (produit.qte <= produit.limit_command) return 'warn';
    return 'success';
}

function stockLabel(produit) {
    if (produit.qte === 0) return 'Rupture';
    if (produit.qte <= produit.limit_command) return 'Critique';
    return null;
}

function formatCurrency(value) {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>

<template>
    <AppLayout title="Produits">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Produits', href: route('produits.index'), current: true}]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Produits</h1>
                    <p class="mt-1 text-sm text-gray-500">Gérez votre catalogue de produits pharmaceutiques</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        :disabled="downloading"
                        @click="downloadPDF">
                        <svg class="w-4 h-4 mr-1.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zM10 8a.75.75 0 01.75.75v3.69l1.22-1.22a.75.75 0 111.06 1.06l-2.5 2.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.22 1.22V8.75A.75.75 0 0110 8z" clip-rule="evenodd"/>
                        </svg>
                        PDF
                    </button>
                    <a target="_blank"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                       href="/export">
                        <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zM10 8a.75.75 0 01.75.75v3.69l1.22-1.22a.75.75 0 111.06 1.06l-2.5 2.5a.75.75 0 01-1.06 0l-2.5-2.5a.75.75 0 111.06-1.06l1.22 1.22V8.75A.75.75 0 0110 8z" clip-rule="evenodd"/>
                        </svg>
                        Excel
                    </a>
                    <a target="_blank"
                       :href="route('produits.barcodes')"
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875v14.25M6.75 4.875v14.25M10.5 4.875v14.25M14.25 4.875v14.25M17.25 4.875v14.25M20.25 4.875v14.25"/>
                        </svg>
                        Codes-barres
                    </a>
                    <button @click="openCreate"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                        </svg>
                        Ajouter
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Products -->
                <button @click="clearFilters"
                        class="group relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 hover:shadow-md hover:border-indigo-200 transition-all duration-200 text-left overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-indigo-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ stats.total }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total produits</p>
                    </div>
                </button>

                <!-- Disponible -->
                <button @click="filterByStatus('disponible')"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedStockStatus === 'disponible'
                                ? 'bg-emerald-50 border-emerald-300 shadow-md ring-2 ring-emerald-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-emerald-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-emerald-700">{{ stats.disponible }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">En stock</p>
                    </div>
                </button>

                <!-- Critique -->
                <button @click="filterByStatus('critique')"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedStockStatus === 'critique'
                                ? 'bg-amber-50 border-amber-300 shadow-md ring-2 ring-amber-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-amber-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-700">{{ stats.critique }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Stock critique</p>
                    </div>
                </button>

                <!-- Rupture -->
                <button @click="filterByStatus('rupture')"
                        :class="[
                            'group relative rounded-2xl p-5 shadow-sm border transition-all duration-200 text-left overflow-hidden',
                            selectedStockStatus === 'rupture'
                                ? 'bg-red-50 border-red-300 shadow-md ring-2 ring-red-500/20'
                                : 'bg-white border-gray-200 hover:shadow-md hover:border-red-200'
                        ]">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-red-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg shadow-red-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-700">{{ stats.rupture }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">En rupture</p>
                    </div>
                </button>
            </div>

            <!-- Valeur totale du stock -->
            <div class="mb-6 bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-5 shadow-lg text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-10 -mt-10"></div>
                <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/5 rounded-full -mb-16"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/10 backdrop-blur-sm">
                            <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-400">Valeur totale du stock</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_value) }} <span class="text-lg font-normal text-slate-400">Dhs</span></p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-6 text-sm">
                        <div class="text-center">
                            <p class="text-slate-400">Catégories</p>
                            <p class="text-lg font-semibold">{{ categories.length }}</p>
                        </div>
                        <div class="w-px h-10 bg-slate-700"></div>
                        <div class="text-center">
                            <p class="text-slate-400">Réf. actives</p>
                            <p class="text-lg font-semibold">{{ stats.total }}</p>
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
                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-200 shadow-sm'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                            ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtres
                        <span v-if="hasActiveFilters"
                              class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-xs font-bold">
                            {{ (selectedCategorie ? 1 : 0) + (selectedStockStatus ? 1 : 0) }}
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
                        <span v-if="selectedStockStatus" key="stock-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                              :class="{
                                  'bg-emerald-100 text-emerald-800': selectedStockStatus === 'disponible',
                                  'bg-amber-100 text-amber-800': selectedStockStatus === 'critique',
                                  'bg-red-100 text-red-800': selectedStockStatus === 'rupture',
                              }">
                            {{ selectedStockStatus === 'disponible' ? 'En stock' : selectedStockStatus === 'critique' ? 'Critique' : 'Rupture' }}
                            <button @click="selectedStockStatus = null; applyFilters()" class="ml-0.5 hover:opacity-70">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                                </svg>
                            </button>
                        </span>
                        <span v-if="selectedCategorie" key="cat-chip"
                              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-800 text-xs font-medium">
                            {{ categories.find(c => c.id === selectedCategorie)?.label }}
                            <button @click="selectedCategorie = null; applyFilters()" class="ml-0.5 hover:opacity-70">
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
                            <!-- Category filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Catégorie</label>
                                <select v-model="selectedCategorie" @change="applyFilters"
                                        class="block w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option :value="null">Toutes les catégories</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Stock status filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">État du stock</label>
                                <select v-model="selectedStockStatus" @change="applyFilters"
                                        class="block w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option v-for="opt in stockStatusOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Quick action -->
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
                <!-- Search bar -->
                <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold">
                            {{ items.total }}
                        </span>
                        <span class="text-sm text-gray-600 font-medium">
                            produit{{ items.total > 1 ? 's' : '' }}
                            <span v-if="hasActiveFilters" class="text-gray-400">filtrés</span>
                        </span>
                    </div>
                    <div class="relative">
                        <svg v-if="!searchTerm" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="searchTerm" @input="onSearchInput" class="pl-9 w-72 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items.data" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['label', 'dci', 'barcode', 'categorie.label', 'laboratoire']"
                           @update:sort-order="value => loadPage(value)"
                           @update:sort-field="value => field = value"
                           :sort-field="sort_fields.field" :sortOrder="sort_fields.order"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucun produit trouvé</span>
                            <p class="text-sm text-gray-400 mt-1">
                                <span v-if="hasActiveFilters">Essayez de modifier vos filtres</span>
                                <span v-else>Commencez par ajouter un produit</span>
                            </p>
                            <button v-if="hasActiveFilters" @click="clearFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Réinitialiser les filtres
                            </button>
                        </div>
                    </template>

                    <Column field="label" header="Produit" sortable style="min-width: 14rem">
                        <template #body="{ data }">
                            <Link :href="route('produits.show', {produit: data.id})" class="group flex items-center gap-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold"
                                     :class="{
                                         'bg-indigo-100 text-indigo-700': !data.categorie,
                                         'bg-violet-100 text-violet-700': data.categorie
                                     }">
                                    {{ data.label.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">{{ data.label }}</div>
                                    <div v-if="data.dci" class="text-xs text-gray-400">{{ data.dci }}</div>
                                </div>
                            </Link>
                        </template>
                    </Column>

                    <Column field="barcode" header="Code" sortable>
                        <template #body="{ data }">
                            <span v-if="data.barcode" class="inline-flex items-center gap-1.5 font-mono text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                                {{ data.barcode }}
                            </span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column field="categorie.label" header="Catégorie">
                        <template #body="{ data }">
                            <span v-if="data.categorie"
                                  class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-200">
                                {{ data.categorie.label }}
                            </span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column field="qte" header="Stock" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full"
                                     :class="{
                                         'bg-red-500': data.qte === 0,
                                         'bg-amber-500': data.qte > 0 && data.qte <= data.limit_command,
                                         'bg-emerald-500': data.qte > data.limit_command
                                     }"></div>
                                <span class="font-semibold tabular-nums" :class="{
                                    'text-red-600': data.qte === 0,
                                    'text-amber-600': data.qte > 0 && data.qte <= data.limit_command,
                                    'text-gray-900': data.qte > data.limit_command
                                }">{{ data.qte }}</span>
                                <Tag v-if="stockLabel(data)" :severity="stockSeverity(data)" :value="stockLabel(data)" class="text-xs"/>
                            </div>
                        </template>
                    </Column>

                    <Column field="limit_command" header="Seuil" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-500 tabular-nums">{{ data.limit_command }}</span>
                        </template>
                    </Column>

                    <Column field="prix_public" header="Prix" sortable>
                        <template #body="{ data }">
                            <span v-if="data.prix_public" class="text-gray-900 font-medium tabular-nums">{{ formatCurrency(data.prix_public) }} <span class="text-gray-400 font-normal text-xs">Dhs</span></span>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>

                    <Column header="" style="width: 8rem">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-0.5">
                                <Link :href="route('produits.show', {produit: data.id})"
                                      class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all"
                                      title="Détail">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </Link>
                                <button @click="openEdit(data)"
                                        class="p-2 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all"
                                        title="Modifier">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    </svg>
                                </button>
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
                               @page="event => {
                                   const params = {page: 1 + event.page};
                                   if (selectedCategorie) params.categorie_id = selectedCategorie;
                                   if (selectedStockStatus) params.stock_status = selectedStockStatus;
                                   if (field) params.field = field;
                                   router.get(route('produits.index'), params);
                               }"/>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <ConfirmationModal :show="deleteModal" @close="deleteModal = false">
            <template #title>Supprimer ce produit</template>
            <template #content>
                Voulez-vous vraiment supprimer <span class="font-semibold">{{ item_label }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteProduit()">Supprimer</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Create/Edit Product Modal with Stepper -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="duration-300 ease-out"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="duration-200 ease-in"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            appear
                        >
                            <div v-if="showModal"
                                 class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
                                 @keydown.enter.prevent="">

                                <!-- Modal Header with gradient -->
                                <div class="px-6 py-5 relative overflow-hidden"
                                     :class="isEditing
                                         ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600'
                                         : 'bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700'">
                                    <div class="absolute top-0 right-0 -mt-3 -mr-3 w-24 h-24 bg-white/10 rounded-full"></div>
                                    <div class="absolute bottom-0 right-16 -mb-4 w-16 h-16 bg-white/5 rounded-full"></div>
                                    <div class="relative flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm">
                                                <svg v-if="!isEditing" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                                </svg>
                                                <svg v-else class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-white">
                                                    {{ isEditing ? 'Modifier le produit' : 'Nouveau produit' }}
                                                </h3>
                                                <p class="text-white/70 text-sm">
                                                    {{ isEditing ? editingProduct.label : 'Remplissez les informations du nouveau produit' }}
                                                </p>
                                            </div>
                                        </div>
                                        <button @click="closeModal" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15 hover:bg-white/25 transition-colors cursor-pointer">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Stepper Navigation -->
                                <div class="px-6 py-5 bg-gray-50/80 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <template v-for="(step, index) in steps" :key="step.number">
                                            <button @click="goToStep(step.number)" class="flex flex-col items-center gap-1.5 group cursor-pointer relative z-10">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                                                     :class="currentStep === step.number
                                                         ? (isEditing
                                                             ? 'bg-gradient-to-br from-amber-500 to-orange-600 border-amber-500 text-white shadow-lg shadow-amber-200 scale-110'
                                                             : 'bg-gradient-to-br from-indigo-500 to-purple-600 border-indigo-500 text-white shadow-lg shadow-indigo-200 scale-110')
                                                         : currentStep > step.number
                                                             ? 'bg-emerald-500 border-emerald-500 text-white'
                                                             : 'bg-white border-gray-300 text-gray-400 group-hover:border-indigo-300 group-hover:text-indigo-400'">
                                                    <svg v-if="currentStep > step.number" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                    </svg>
                                                    <span v-else class="text-sm font-bold">{{ step.number }}</span>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs font-semibold transition-colors"
                                                       :class="currentStep === step.number
                                                           ? (isEditing ? 'text-amber-700' : 'text-indigo-700')
                                                           : currentStep > step.number ? 'text-emerald-600' : 'text-gray-400'">
                                                        {{ step.label }}
                                                    </p>
                                                    <p class="text-[10px] transition-colors hidden sm:block"
                                                       :class="currentStep === step.number ? (isEditing ? 'text-amber-400' : 'text-indigo-400') : 'text-gray-300'">
                                                        {{ step.description }}
                                                    </p>
                                                </div>
                                            </button>
                                            <div v-if="index < steps.length - 1" class="flex-1 mx-2 mt-[-18px]">
                                                <div class="h-0.5 rounded-full transition-all duration-500"
                                                     :class="currentStep > step.number ? 'bg-emerald-400' : 'bg-gray-200'"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Modal Body -->
                                <form @submit.prevent="submitForm" class="max-h-[60vh] overflow-y-auto">
                                    <!-- Step 1: Général -->
                                    <Transition
                                        enter-active-class="transition ease-out duration-200"
                                        enter-from-class="opacity-0 translate-x-4"
                                        enter-to-class="opacity-100 translate-x-0"
                                        leave-active-class="transition ease-in duration-150"
                                        leave-from-class="opacity-100 translate-x-0"
                                        leave-to-class="opacity-0 -translate-x-4"
                                    >
                                    <div v-if="currentStep === 1" class="p-6 space-y-5">
                                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-100">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900">Informations générales</h4>
                                                <p class="text-xs text-gray-400">Identité et classification du produit</p>
                                            </div>
                                        </div>

                                        <!-- Label -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                Désignation <span class="text-red-500">*</span>
                                            </label>
                                            <input ref="labelInput" v-model="form.label" type="text"
                                                   class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                   :class="form.errors.label ? 'border-red-300 ring-1 ring-red-300' : ''"
                                                   placeholder="Nom du produit">
                                            <p v-if="form.errors.label" class="mt-1.5 text-sm text-red-500 flex items-center gap-1">
                                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                                {{ form.errors.label }}
                                            </p>
                                        </div>

                                        <!-- Barcode -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Code-barres</label>
                                            <div class="flex gap-2">
                                                <div class="relative flex-1">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z"/>
                                                        </svg>
                                                    </div>
                                                    <input v-model="form.barcode" type="text" :disabled="isEditing"
                                                           class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-10 pr-4 font-mono transition-colors disabled:bg-gray-50 disabled:text-gray-500"
                                                           placeholder="Code-barres">
                                                </div>
                                                <button v-if="!isEditing" type="button" @click="generateBarcode"
                                                        class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-teal-500 px-3.5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-teal-600 transition-colors cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    Générer
                                                </button>
                                            </div>
                                            <InputError :message="form.errors.barcode" class="mt-1.5"/>
                                        </div>

                                        <!-- Category -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catégorie</label>
                                            <Select class="w-full" v-model="form.categorie"
                                                    :options="props.categories" option-label="label" filter showClear placeholder="Sélectionner une catégorie"/>
                                            <InputError :message="form.errors.categorie_id" class="mt-1.5"/>
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                                            <textarea v-model="form.description" rows="3"
                                                      class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors resize-none"
                                                      placeholder="Description optionnelle du produit..."></textarea>
                                            <InputError :message="form.errors.description" class="mt-1.5"/>
                                        </div>
                                    </div>
                                    </Transition>

                                    <!-- Step 2: Pharmacie -->
                                    <Transition
                                        enter-active-class="transition ease-out duration-200"
                                        enter-from-class="opacity-0 translate-x-4"
                                        enter-to-class="opacity-100 translate-x-0"
                                        leave-active-class="transition ease-in duration-150"
                                        leave-from-class="opacity-100 translate-x-0"
                                        leave-to-class="opacity-0 -translate-x-4"
                                    >
                                    <div v-if="currentStep === 2" class="p-6 space-y-5">
                                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-cyan-100">
                                                <svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-1.386 4.16a2.25 2.25 0 0 1-2.137 1.59H8.523a2.25 2.25 0 0 1-2.136-1.59L5 14.5m14 0H5"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900">Détails pharmaceutiques</h4>
                                                <p class="text-xs text-gray-400">Composition et caractéristiques</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">DCI</label>
                                                <input v-model="form.dci" type="text"
                                                       class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Dénomination commune">
                                                <InputError :message="form.errors.dci" class="mt-1.5"/>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Forme</label>
                                                <input v-model="form.forme" type="text"
                                                       class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Comprimé, sirop...">
                                                <InputError :message="form.errors.forme" class="mt-1.5"/>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dosage</label>
                                                <input v-model="form.dosage" type="text"
                                                       class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="500mg, 10ml...">
                                                <InputError :message="form.errors.dosage" class="mt-1.5"/>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Laboratoire</label>
                                                <input v-model="form.laboratoire" type="text"
                                                       class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Nom du laboratoire">
                                                <InputError :message="form.errors.laboratoire" class="mt-1.5"/>
                                            </div>
                                        </div>

                                        <!-- Unité -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unité par boîte</label>
                                            <input v-model="form.unite" type="number"
                                                   class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                   placeholder="Ex: 30">
                                            <InputError :message="form.errors.unite" class="mt-1.5"/>
                                        </div>

                                        <!-- Toggles -->
                                        <div class="grid grid-cols-2 gap-4 pt-2">
                                            <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                                   :class="form.perissable ? 'border-teal-300 bg-teal-50' : 'border-gray-200 bg-white hover:border-gray-300'">
                                                <div class="relative">
                                                    <input type="checkbox" v-model="form.perissable" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-teal-500 transition-colors"></div>
                                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-semibold text-gray-700">Périssable</span>
                                                    <p class="text-xs text-gray-400 mt-0.5">Date d'expiration requise</p>
                                                </div>
                                            </label>

                                            <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
                                                   :class="form.ordonnance_requise ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-white hover:border-gray-300'">
                                                <div class="relative">
                                                    <input type="checkbox" v-model="form.ordonnance_requise" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-amber-500 transition-colors"></div>
                                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-semibold text-gray-700">Ordonnance</span>
                                                    <p class="text-xs text-gray-400 mt-0.5">Prescription obligatoire</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    </Transition>

                                    <!-- Step 3: Prix & Stock -->
                                    <Transition
                                        enter-active-class="transition ease-out duration-200"
                                        enter-from-class="opacity-0 translate-x-4"
                                        enter-to-class="opacity-100 translate-x-0"
                                        leave-active-class="transition ease-in duration-150"
                                        leave-from-class="opacity-100 translate-x-0"
                                        leave-to-class="opacity-0 -translate-x-4"
                                    >
                                    <div v-if="currentStep === 3" class="p-6 space-y-5">
                                        <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-100">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-gray-900">Prix & Gestion du stock</h4>
                                                <p class="text-xs text-gray-400">Tarification et seuils d'alerte</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Prix public (Dhs)</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <span class="text-gray-400 text-sm font-medium">Dhs</span>
                                                    </div>
                                                    <input v-model="form.prix_public" type="number" step="0.01" min="0"
                                                           class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-12 pr-4 transition-colors"
                                                           placeholder="0.00">
                                                </div>
                                                <InputError :message="form.errors.prix_public" class="mt-1.5"/>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Seuil critique</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                                        </svg>
                                                    </div>
                                                    <input v-model="form.limit_command" type="number" min="0"
                                                           class="block w-full rounded-xl border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                                           placeholder="Quantité minimum">
                                                </div>
                                                <InputError :message="form.errors.limit_command" class="mt-1.5"/>
                                            </div>
                                        </div>

                                        <!-- Summary card -->
                                        <div class="rounded-xl border border-gray-100 p-4"
                                             :class="isEditing ? 'bg-gradient-to-r from-amber-50 to-orange-50 border-amber-100' : 'bg-gradient-to-r from-indigo-50 to-purple-50 border-indigo-100'">
                                            <h4 class="text-sm font-semibold mb-3 flex items-center gap-2"
                                                :class="isEditing ? 'text-amber-700' : 'text-indigo-700'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                </svg>
                                                Récapitulatif
                                            </h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="bg-white rounded-lg p-3">
                                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Produit</p>
                                                    <p class="text-sm font-semibold text-gray-900 mt-0.5 truncate">{{ form.label || '—' }}</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3">
                                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Catégorie</p>
                                                    <p class="text-sm font-semibold text-gray-900 mt-0.5 truncate">{{ form.categorie?.label || '—' }}</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3">
                                                    <p class="text-xs text-gray-400 uppercase tracking-wider">DCI</p>
                                                    <p class="text-sm font-semibold text-gray-900 mt-0.5 truncate">{{ form.dci || '—' }}</p>
                                                </div>
                                                <div class="bg-white rounded-lg p-3">
                                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Forme / Dosage</p>
                                                    <p class="text-sm font-semibold text-gray-900 mt-0.5 truncate">{{ [form.forme, form.dosage].filter(Boolean).join(' - ') || '—' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </Transition>
                                </form>

                                <!-- Modal Footer with stepper controls -->
                                <div class="border-t border-gray-200 bg-gray-50/80 px-6 py-4">
                                    <!-- Progress bar -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                                            <span>Étape {{ currentStep }} sur {{ totalSteps }}</span>
                                            <span>{{ Math.round((currentStep / totalSteps) * 100) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all duration-500 ease-out"
                                                 :class="isEditing ? 'bg-gradient-to-r from-amber-500 to-orange-500' : 'bg-gradient-to-r from-indigo-500 to-purple-500'"
                                                 :style="{width: (currentStep / totalSteps * 100) + '%'}"></div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <!-- Left: status + cancel -->
                                        <div class="flex items-center gap-2">
                                            <div v-if="form.processing" class="flex items-center gap-2 text-sm text-indigo-600">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Enregistrement...
                                            </div>
                                            <div v-else-if="form.recentlySuccessful" class="flex items-center gap-1.5 text-sm text-emerald-600">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                                                </svg>
                                                Enregistré !
                                            </div>
                                            <button v-if="!form.processing" @click="closeModal" type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors cursor-pointer">
                                                Annuler
                                            </button>
                                        </div>

                                        <!-- Right: navigation -->
                                        <div class="flex items-center gap-3">
                                            <button v-if="currentStep > 1" @click="prevStep" type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                                                </svg>
                                                Précédent
                                            </button>

                                            <button v-if="currentStep < totalSteps" @click="nextStep" type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all cursor-pointer"
                                                    :class="isEditing
                                                        ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400'
                                                        : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500'">
                                                Suivant
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                                </svg>
                                            </button>

                                            <button v-if="currentStep === totalSteps" @click="submitForm" type="button" :disabled="form.processing"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 hover:from-emerald-400 hover:to-teal-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer">
                                                <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                </svg>
                                                <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ isEditing ? 'Enregistrer' : 'Ajouter' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
