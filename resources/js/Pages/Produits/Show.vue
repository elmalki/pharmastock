<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import {Link, useForm} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import {computed, ref, watch, nextTick} from "vue";

const props = defineProps({produit: Object, categories: Array})

// ── Edit Modal ──
const showEditModal = ref(false)
const form = useForm({
    label: '',
    barcode: '',
    dci: '',
    forme: '',
    dosage: '',
    laboratoire: '',
    unite: '',
    description: '',
    perissable: false,
    ordonnance_requise: false,
    prix_public: '',
    limit_command: '',
    categorie_id: '',
})

function openEditModal() {
    form.label = props.produit.label ?? ''
    form.barcode = props.produit.barcode ?? ''
    form.dci = props.produit.dci ?? ''
    form.forme = props.produit.forme ?? ''
    form.dosage = props.produit.dosage ?? ''
    form.laboratoire = props.produit.laboratoire ?? ''
    form.unite = props.produit.unite ?? ''
    form.description = props.produit.description ?? ''
    form.perissable = props.produit.perissable ?? false
    form.ordonnance_requise = props.produit.ordonnance_requise ?? false
    form.prix_public = props.produit.prix_public ?? ''
    form.limit_command = props.produit.limit_command ?? ''
    form.categorie_id = props.produit.categorie_id ?? ''
    currentStep.value = 1
    showEditModal.value = true
}

function closeEditModal() {
    showEditModal.value = false
    form.clearErrors()
}

function submitEdit() {
    form.put(route('produits.update', {produit: props.produit.id}), {
        preserveScroll: true,
        headers: {'X-From-Show': '1'},
        onSuccess: () => closeEditModal(),
    })
}

// ── Lots data ──
const lots = computed(() => {
    return (props.produit.commandes ?? []).map(c => ({
        n_lot: c.pivot.n_lot,
        n_facture: c.n_facture,
        date_achat: c.pivot.created_at?.substring(0, 10),
        expirationDate: c.pivot.expirationDate,
        qte_achete: c.pivot.qte_achete,
        qte: c.pivot.qte,
        prix_achat: parseFloat(c.pivot.prix_achat),
        prix_vente: parseFloat(c.pivot.prix_vente),
        tva: c.pivot.tva,
        expired: c.pivot.expirationDate && new Date(c.pivot.expirationDate) < new Date(),
        expiringSoon: c.pivot.expirationDate && !isExpired(c.pivot.expirationDate) && daysUntil(c.pivot.expirationDate) <= 30,
    }))
})

const totalStock = computed(() => lots.value.reduce((sum, l) => sum + l.qte, 0))
const totalExpired = computed(() => lots.value.filter(l => l.expired).reduce((sum, l) => sum + l.qte, 0))
const totalValue = computed(() => lots.value.reduce((sum, l) => sum + l.qte * l.prix_achat, 0))
const isLowStock = computed(() => totalStock.value <= props.produit.limit_command)
const margin = computed(() => {
    if (lots.value.length === 0) return 0
    const avgBuy = lots.value.reduce((s, l) => s + l.prix_achat, 0) / lots.value.length
    const avgSell = lots.value.reduce((s, l) => s + l.prix_vente, 0) / lots.value.length
    if (avgBuy === 0) return 0
    return ((avgSell - avgBuy) / avgBuy * 100).toFixed(1)
})

function isExpired(date) {
    return date && new Date(date) < new Date()
}
function daysUntil(date) {
    if (!date) return null
    const diff = new Date(date) - new Date()
    return Math.ceil(diff / (1000 * 60 * 60 * 24))
}
function formatDate(date) {
    if (!date) return '—'
    return date.split('-').reverse().join('/')
}
function formatPrice(val) {
    return parseFloat(val).toFixed(2) + ' Dhs'
}
function expirationSeverity(lot) {
    if (lot.expired) return 'danger'
    if (lot.expiringSoon) return 'warn'
    return 'success'
}
function expirationLabel(lot) {
    if (lot.expired) return 'Périmé'
    if (lot.expiringSoon) return 'Bientôt'
    if (!lot.expirationDate) return '—'
    return 'OK'
}
function stockPercent(lot) {
    if (!lot.qte_achete) return 0
    return Math.round((lot.qte / lot.qte_achete) * 100)
}

// Stepper for modal
const currentStep = ref(1)
const totalSteps = 3

const steps = [
    {number: 1, label: 'Général', description: 'Infos de base'},
    {number: 2, label: 'Pharmacie', description: 'Détails pharma'},
    {number: 3, label: 'Prix & Stock', description: 'Tarification'},
]

function nextStep() {
    if (currentStep.value < totalSteps) currentStep.value++
}
function prevStep() {
    if (currentStep.value > 1) currentStep.value--
}
function goToStep(step) {
    currentStep.value = step
}
</script>

<template>
    <AppLayout :title="produit.label">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Produits', href: route('produits.index'), current: false},
                {name: produit.label, href: route('produits.show', {produit: produit.id}), current: true}
            ]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ produit.label }}</h1>
                                <p class="text-indigo-200 text-sm mt-0.5">
                                    {{ produit.categorie?.label ?? 'Sans catégorie' }}
                                    <span v-if="produit.dci" class="ml-2 text-indigo-300">| {{ produit.dci }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span v-if="produit.barcode" class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white rounded-full px-3 py-1 text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                                </svg>
                                {{ produit.barcode }}
                            </span>
                            <span v-if="produit.perissable" class="inline-flex items-center bg-amber-400/20 text-amber-100 rounded-full px-3 py-1 text-xs font-medium">
                                Périssable
                            </span>
                            <span v-if="produit.ordonnance_requise" class="inline-flex items-center bg-rose-400/20 text-rose-100 rounded-full px-3 py-1 text-xs font-medium">
                                Ordonnance requise
                            </span>
                            <span v-if="isLowStock" class="inline-flex items-center gap-1 bg-red-500/30 text-red-100 rounded-full px-3 py-1 text-xs font-semibold animate-pulse">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                                </svg>
                                Stock bas
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="openEditModal"
                              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                            Modifier
                        </button>
                        <Link :href="route('produits.index')"
                              class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                            </svg>
                            Retour
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Stock total -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                                </svg>
                            </div>
                            <span v-if="isLowStock" class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Stock total</p>
                        <p class="mt-1 text-3xl font-bold" :class="isLowStock ? 'text-red-600' : 'text-emerald-700'">
                            {{ totalStock }}
                        </p>
                        <p v-if="isLowStock" class="mt-1 text-xs text-red-500 font-medium">Sous le seuil ({{ produit.limit_command }})</p>
                        <p v-else class="mt-1 text-xs text-gray-400">Seuil: {{ produit.limit_command }}</p>
                    </div>
                </div>

                <!-- Lots -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Lots actifs</p>
                        <p class="mt-1 text-3xl font-bold text-blue-700">{{ lots.length }}</p>
                        <p class="mt-1 text-xs text-gray-400">Lots en stock</p>
                    </div>
                </div>

                <!-- Expired -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-rose-100">
                                <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Qté périmée</p>
                        <p class="mt-1 text-3xl font-bold" :class="totalExpired > 0 ? 'text-rose-600' : 'text-gray-400'">
                            {{ totalExpired }}
                        </p>
                        <p class="mt-1 text-xs" :class="totalExpired > 0 ? 'text-rose-400' : 'text-gray-400'">
                            {{ totalExpired > 0 ? 'Action requise' : 'Aucun périmé' }}
                        </p>
                    </div>
                </div>

                <!-- Value -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100">
                                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Valeur en stock</p>
                        <p class="mt-1 text-3xl font-bold text-violet-700">{{ formatPrice(totalValue) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Marge moy. {{ margin }}%</p>
                    </div>
                </div>
            </div>

            <!-- Product details -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900">Informations du produit</h2>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5 px-6 py-6">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 shrink-0">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Code-barres</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900 font-mono">{{ produit.barcode || '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 shrink-0">
                            <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Catégorie</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.categorie?.label ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-50 shrink-0">
                            <svg class="w-4 h-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-1.386 4.16a2.25 2.25 0 0 1-2.137 1.59H8.523a2.25 2.25 0 0 1-2.136-1.59L5 14.5m14 0H5"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">DCI</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.dci ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-teal-50 shrink-0">
                            <svg class="w-4 h-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Forme</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.forme ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 shrink-0">
                            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Dosage</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.dosage ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 shrink-0">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Laboratoire</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.laboratoire ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-sky-50 shrink-0">
                            <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Unité par boîte</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.unite }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 shrink-0">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Prix public</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900">{{ produit.prix_public ? formatPrice(produit.prix_public) : '—' }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 shrink-0">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Périssable</dt>
                            <dd class="mt-1">
                                <span :class="[produit.perissable ? 'bg-teal-100 text-teal-700 ring-teal-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/10', 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset']">
                                    {{ produit.perissable ? 'Oui' : 'Non' }}
                                </span>
                            </dd>
                        </div>
                    </div>
                    <div v-if="produit.description" class="sm:col-span-2 lg:col-span-3 flex items-start gap-3">
                        <div class="mt-0.5 flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 shrink-0">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                            </svg>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Description</dt>
                            <dd class="mt-0.5 text-sm text-gray-700 leading-relaxed">{{ produit.description }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lots table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M10.875 12h-7.5c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-7.5M12 12v-1.5"/>
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold text-gray-900">Lots en stock</h2>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                            {{ lots.length }} lot{{ lots.length > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <DataTable :value="lots" tableStyle="min-width: 50rem" sortField="expirationDate" :sortOrder="1"
                           :rowClass="(data) => data.expired ? 'bg-red-50/50' : data.qte === 0 ? 'bg-gray-50/50' : ''"
                           stripedRows>
                    <template #empty>
                        <div class="w-full flex flex-col items-center justify-center text-gray-400 py-12 gap-3">
                            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                            <span class="text-base">Aucun lot trouvé pour ce produit</span>
                        </div>
                    </template>
                    <Column field="n_lot" header="N° Lot" sortable>
                        <template #body="{ data }">
                            <span class="font-mono text-sm font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">{{ data.n_lot }}</span>
                        </template>
                    </Column>
                    <Column field="n_facture" header="Facture" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-gray-700">{{ data.n_facture }}</span>
                        </template>
                    </Column>
                    <Column field="qte" header="En stock" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="w-16">
                                    <div class="flex items-baseline gap-1">
                                        <span :class="data.qte === 0 ? 'text-red-600 font-bold' : 'text-gray-900 font-semibold'" class="text-sm">
                                            {{ data.qte }}
                                        </span>
                                        <span class="text-gray-400 text-xs">/ {{ data.qte_achete }}</span>
                                    </div>
                                    <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all"
                                             :class="stockPercent(data) > 50 ? 'bg-emerald-500' : stockPercent(data) > 20 ? 'bg-amber-500' : 'bg-red-500'"
                                             :style="{width: stockPercent(data) + '%'}"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="prix_achat" header="P. Achat" sortable>
                        <template #body="{ data }">
                            <span class="text-sm text-gray-700">{{ formatPrice(data.prix_achat) }}</span>
                        </template>
                    </Column>
                    <Column field="prix_vente" header="P. Vente" sortable>
                        <template #body="{ data }">
                            <span class="text-sm font-medium text-gray-900">{{ formatPrice(data.prix_vente) }}</span>
                        </template>
                    </Column>
                    <Column field="tva" header="TVA" sortable>
                        <template #body="{ data }">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                {{ data.tva }}%
                            </span>
                        </template>
                    </Column>
                    <Column field="expirationDate" header="Péremption" sortable>
                        <template #body="{ data }">
                            <div v-if="data.expirationDate" class="flex items-center gap-2">
                                <span class="text-sm">{{ formatDate(data.expirationDate) }}</span>
                                <Tag :severity="expirationSeverity(data)" :value="expirationLabel(data)" class="text-xs" />
                            </div>
                            <span v-else class="text-gray-400">—</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- EDIT MODAL -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeEditModal"></div>

                    <!-- Modal panel -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div v-if="showEditModal" class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                                <!-- Modal Header with gradient -->
                                <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 px-6 py-5 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 -mt-3 -mr-3 w-24 h-24 bg-white/10 rounded-full"></div>
                                    <div class="absolute bottom-0 right-16 -mb-4 w-16 h-16 bg-white/5 rounded-full"></div>
                                    <div class="relative flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-white">Modifier le produit</h3>
                                                <p class="text-indigo-200 text-sm">{{ produit.label }}</p>
                                            </div>
                                        </div>
                                        <button @click="closeEditModal" class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15 hover:bg-white/25 transition-colors cursor-pointer">
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
                                            <!-- Step circle + label -->
                                            <button @click="goToStep(step.number)" class="flex flex-col items-center gap-1.5 group cursor-pointer relative z-10">
                                                <!-- Circle -->
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                                                     :class="currentStep === step.number
                                                         ? 'bg-gradient-to-br from-indigo-500 to-purple-600 border-indigo-500 text-white shadow-lg shadow-indigo-200 scale-110'
                                                         : currentStep > step.number
                                                             ? 'bg-emerald-500 border-emerald-500 text-white'
                                                             : 'bg-white border-gray-300 text-gray-400 group-hover:border-indigo-300 group-hover:text-indigo-400'">
                                                    <!-- Checkmark for completed -->
                                                    <svg v-if="currentStep > step.number" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                    </svg>
                                                    <!-- Number for current/upcoming -->
                                                    <span v-else class="text-sm font-bold">{{ step.number }}</span>
                                                </div>
                                                <!-- Label -->
                                                <div class="text-center">
                                                    <p class="text-xs font-semibold transition-colors"
                                                       :class="currentStep === step.number ? 'text-indigo-700' : currentStep > step.number ? 'text-emerald-600' : 'text-gray-400'">
                                                        {{ step.label }}
                                                    </p>
                                                    <p class="text-[10px] transition-colors hidden sm:block"
                                                       :class="currentStep === step.number ? 'text-indigo-400' : 'text-gray-300'">
                                                        {{ step.description }}
                                                    </p>
                                                </div>
                                            </button>

                                            <!-- Connector line -->
                                            <div v-if="index < steps.length - 1" class="flex-1 mx-2 mt-[-18px]">
                                                <div class="h-0.5 rounded-full transition-all duration-500"
                                                     :class="currentStep > step.number ? 'bg-emerald-400' : 'bg-gray-200'">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Modal Body -->
                                <form @submit.prevent="submitEdit" class="max-h-[60vh] overflow-y-auto">
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
                                        <!-- Step header -->
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
                                            <input v-model="form.label" type="text"
                                                   class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
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
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z"/>
                                                    </svg>
                                                </div>
                                                <input v-model="form.barcode" type="text"
                                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-10 pr-4 font-mono transition-colors"
                                                       placeholder="Code-barres">
                                            </div>
                                            <p v-if="form.errors.barcode" class="mt-1.5 text-sm text-red-500">{{ form.errors.barcode }}</p>
                                        </div>

                                        <!-- Category -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catégorie</label>
                                            <select v-model="form.categorie_id"
                                                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors">
                                                <option value="">— Aucune catégorie —</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.label }}</option>
                                            </select>
                                            <p v-if="form.errors.categorie_id" class="mt-1.5 text-sm text-red-500">{{ form.errors.categorie_id }}</p>
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                                            <textarea v-model="form.description" rows="3"
                                                      class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors resize-none"
                                                      placeholder="Description optionnelle du produit..."></textarea>
                                            <p v-if="form.errors.description" class="mt-1.5 text-sm text-red-500">{{ form.errors.description }}</p>
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
                                        <!-- Step header -->
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
                                            <!-- DCI -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">DCI</label>
                                                <input v-model="form.dci" type="text"
                                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Dénomination commune">
                                                <p v-if="form.errors.dci" class="mt-1.5 text-sm text-red-500">{{ form.errors.dci }}</p>
                                            </div>

                                            <!-- Forme -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Forme</label>
                                                <input v-model="form.forme" type="text"
                                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Comprimé, sirop...">
                                                <p v-if="form.errors.forme" class="mt-1.5 text-sm text-red-500">{{ form.errors.forme }}</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- Dosage -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dosage</label>
                                                <input v-model="form.dosage" type="text"
                                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="500mg, 10ml...">
                                                <p v-if="form.errors.dosage" class="mt-1.5 text-sm text-red-500">{{ form.errors.dosage }}</p>
                                            </div>

                                            <!-- Laboratoire -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Laboratoire</label>
                                                <input v-model="form.laboratoire" type="text"
                                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                       placeholder="Nom du laboratoire">
                                                <p v-if="form.errors.laboratoire" class="mt-1.5 text-sm text-red-500">{{ form.errors.laboratoire }}</p>
                                            </div>
                                        </div>

                                        <!-- Unité -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unité par boîte</label>
                                            <input v-model="form.unite" type="text"
                                                   class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 px-4 transition-colors"
                                                   placeholder="Ex: 30 comprimés">
                                            <p v-if="form.errors.unite" class="mt-1.5 text-sm text-red-500">{{ form.errors.unite }}</p>
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
                                        <!-- Step header -->
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
                                            <!-- Prix public -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Prix public (Dhs)</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <span class="text-gray-400 text-sm font-medium">Dhs</span>
                                                    </div>
                                                    <input v-model="form.prix_public" type="number" step="0.01" min="0"
                                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-12 pr-4 transition-colors"
                                                           placeholder="0.00">
                                                </div>
                                                <p v-if="form.errors.prix_public" class="mt-1.5 text-sm text-red-500">{{ form.errors.prix_public }}</p>
                                            </div>

                                            <!-- Seuil critique -->
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Seuil critique</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                                        </svg>
                                                    </div>
                                                    <input v-model="form.limit_command" type="number" min="0"
                                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                                           placeholder="Quantité minimum">
                                                </div>
                                                <p v-if="form.errors.limit_command" class="mt-1.5 text-sm text-red-500">{{ form.errors.limit_command }}</p>
                                            </div>
                                        </div>

                                        <!-- Current stock info card -->
                                        <div class="rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 p-4">
                                            <h4 class="text-sm font-semibold text-indigo-700 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                                                </svg>
                                                État actuel du stock
                                            </h4>
                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="text-center p-2 bg-white rounded-lg">
                                                    <p class="text-lg font-bold" :class="isLowStock ? 'text-red-600' : 'text-emerald-600'">{{ totalStock }}</p>
                                                    <p class="text-xs text-gray-500">En stock</p>
                                                </div>
                                                <div class="text-center p-2 bg-white rounded-lg">
                                                    <p class="text-lg font-bold text-blue-600">{{ lots.length }}</p>
                                                    <p class="text-xs text-gray-500">Lots</p>
                                                </div>
                                                <div class="text-center p-2 bg-white rounded-lg">
                                                    <p class="text-lg font-bold text-violet-600">{{ formatPrice(totalValue) }}</p>
                                                    <p class="text-xs text-gray-500">Valeur</p>
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
                                            <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-500 ease-out"
                                                 :style="{width: (currentStep / totalSteps * 100) + '%'}"></div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <!-- Left: status -->
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
                                            <button v-if="!form.processing" @click="closeEditModal" type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors cursor-pointer">
                                                Annuler
                                            </button>
                                        </div>

                                        <!-- Right: navigation -->
                                        <div class="flex items-center gap-3">
                                            <!-- Previous -->
                                            <button v-if="currentStep > 1" @click="prevStep" type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                                                </svg>
                                                Précédent
                                            </button>

                                            <!-- Next -->
                                            <button v-if="currentStep < totalSteps" @click="nextStep" type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-purple-500 transition-all cursor-pointer">
                                                Suivant
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                                                </svg>
                                            </button>

                                            <!-- Submit (last step only) -->
                                            <button v-if="currentStep === totalSteps" @click="submitEdit" type="button" :disabled="form.processing"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 hover:from-emerald-400 hover:to-teal-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer">
                                                <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                                </svg>
                                                <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Enregistrer
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
