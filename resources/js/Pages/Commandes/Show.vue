<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import {Link} from "@inertiajs/vue3";
import {computed} from "vue";
import html2pdf from "html2pdf.js";

const props = defineProps({commande: Object});

const produits = computed(() => {
    return (props.commande.produits ?? []).map(p => ({
        id: p.id,
        label: p.label,
        n_lot: p.pivot.n_lot,
        qte_achete: p.pivot.qte_achete,
        qte: p.pivot.qte,
        prix_achat: parseFloat(p.pivot.prix_achat),
        prix_vente: parseFloat(p.pivot.prix_vente),
        tva: parseFloat(p.pivot.tva),
        expirationDate: p.pivot.expirationDate,
        totalHT: parseFloat(p.pivot.prix_achat) * p.pivot.qte_achete,
        totalTVA: parseFloat(p.pivot.prix_achat) * p.pivot.qte_achete * parseFloat(p.pivot.tva) / 100,
    }));
});

const sousTotal = computed(() => produits.value.reduce((s, p) => s + p.totalHT, 0));
const totalTVA = computed(() => produits.value.reduce((s, p) => s + p.totalTVA, 0));
const totalTTC = computed(() => sousTotal.value + totalTVA.value);
const totalArticles = computed(() => produits.value.length);
const totalUnites = computed(() => produits.value.reduce((s, p) => s + p.qte_achete, 0));

const tvaGroups = computed(() => {
    const groups = {};
    produits.value.forEach(p => {
        if (p.tva > 0) groups[p.tva] = (groups[p.tva] || 0) + p.totalTVA;
    });
    return groups;
});

const paymentProgress = computed(() => {
    if (!totalTTC.value) return 0;
    return Math.min(100, Math.round((parseFloat(props.commande.montantPaye) || 0) / totalTTC.value * 100));
});

function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function formatPrice(val) {
    if (val == null) return '—';
    return parseFloat(val).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Dhs';
}

function situationVal(s) {
    if (!s) return '';
    return typeof s === 'object' ? s?.value ?? s : s;
}

function paiementVal(p) {
    if (!p) return '';
    return typeof p === 'object' ? p?.value ?? p : p;
}

function paiementColor(p) {
    const val = paiementVal(p);
    const map = {
        'Éspèce': 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'Chèque': 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'Effet': 'bg-cyan-50 text-cyan-700 ring-cyan-600/20',
        'Virement': 'bg-purple-50 text-purple-700 ring-purple-600/20',
        'En compte': 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'Crédit': 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'TPE': 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'Multi Réglement': 'bg-violet-50 text-violet-700 ring-violet-600/20',
    };
    return map[val] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
}

function situationColor(s) {
    const val = situationVal(s);
    const map = {
        'Payé': 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'En compte': 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'Crédit': 'bg-rose-50 text-rose-700 ring-rose-600/20',
    };
    return map[val] ?? 'bg-gray-50 text-gray-700 ring-gray-600/10';
}

function expirationStatus(date) {
    if (!date) return null;
    const d = new Date(date);
    const now = new Date();
    if (d < now) return 'expired';
    const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
    if (diff <= 30) return 'soon';
    return 'ok';
}

function stockPercent(p) {
    if (!p.qte_achete) return 0;
    return Math.round((p.qte / p.qte_achete) * 100);
}

function exportToPDF() {
    html2pdf(document.getElementById("commande-content"), {
        margin: [10, 10],
        filename: `commande_${props.commande.n_bon || props.commande.n_facture}.pdf`,
        html2canvas: {scale: 2},
        jsPDF: {unit: 'mm', format: 'a4', orientation: 'portrait'},
    });
}
</script>

<template>
    <AppLayout title="Détail Commande">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Approvisionnements', href: route('commandes.index'), current: false},
                {name: commande.n_bon || commande.n_facture, href: route('commandes.show', {commande: commande.id}), current: true}
            ]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Commande {{ commande.n_bon || commande.n_facture }}</h1>
                                <p class="text-sky-200 text-sm mt-0.5">
                                    {{ commande.fournisseur?.societe ?? 'Fournisseur inconnu' }}
                                    <span class="mx-1.5 text-sky-300/50">|</span>
                                    {{ formatDate(commande.date) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span :class="[paiementColor(commande.paiement), 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset']">
                                {{ paiementVal(commande.paiement) || '—' }}
                            </span>
                            <span :class="[situationColor(commande.situation), 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset']">
                                {{ situationVal(commande.situation) || '—' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white rounded-full px-3 py-1 text-xs font-medium">
                                {{ totalArticles }} article{{ totalArticles > 1 ? 's' : '' }} &middot; {{ totalUnites }} unité{{ totalUnites > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="exportToPDF()"
                                class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                            PDF
                        </button>
                        <Link :href="route('commandes.edit', {commande: commande.id})"
                              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-sky-700 shadow-sm hover:bg-sky-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                            Modifier
                        </Link>
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

            <div id="commande-content">
                <!-- Stats cards row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <!-- Total TTC -->
                    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-sky-50 to-transparent opacity-60"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-sky-100">
                                    <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Total TTC</p>
                            <p class="mt-1 text-2xl font-bold text-sky-700 tabular-nums">{{ formatPrice(totalTTC) }}</p>
                        </div>
                    </div>

                    <!-- Montant payé -->
                    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-60"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Montant payé</p>
                            <p class="mt-1 text-2xl font-bold text-emerald-700 tabular-nums">{{ formatPrice(commande.montantPaye) }}</p>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all bg-emerald-500" :style="{width: paymentProgress + '%'}"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">{{ paymentProgress }}% payé</p>
                        </div>
                    </div>

                    <!-- Articles -->
                    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-violet-50 to-transparent opacity-60"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100">
                                    <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Articles</p>
                            <p class="mt-1 text-2xl font-bold text-violet-700">{{ totalArticles }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ totalUnites }} unités commandées</p>
                        </div>
                    </div>

                    <!-- TVA -->
                    <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-transparent opacity-60"></div>
                        <div class="relative">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Total TVA</p>
                            <p class="mt-1 text-2xl font-bold text-amber-700 tabular-nums">{{ formatPrice(totalTVA) }}</p>
                            <p class="mt-1 text-xs text-gray-400">HT: {{ formatPrice(sousTotal) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Commande info -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100">
                                    <svg class="w-4 h-4 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Commande</h3>
                            </div>
                        </div>
                        <dl class="divide-y divide-gray-50 px-6">
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/></svg>
                                    N° Bon
                                </dt>
                                <dd class="text-sm font-semibold font-mono text-sky-700 bg-sky-50 px-2 py-0.5 rounded">{{ commande.n_bon || '—' }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    N° Facture
                                </dt>
                                <dd class="text-sm font-semibold font-mono text-gray-900">{{ commande.n_facture || '—' }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                    Date
                                </dt>
                                <dd class="text-sm font-medium text-gray-900 tabular-nums">{{ formatDate(commande.date) }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    Échéance
                                </dt>
                                <dd class="text-sm font-medium text-gray-900 tabular-nums">{{ formatDate(commande.dateEcheance) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Paiement info -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Paiement</h3>
                            </div>
                        </div>
                        <dl class="divide-y divide-gray-50 px-6">
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500">Mode</dt>
                                <dd>
                                    <span :class="[paiementColor(commande.paiement), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset']">
                                        {{ paiementVal(commande.paiement) || '—' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500">Situation</dt>
                                <dd>
                                    <span :class="[situationColor(commande.situation), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset']">
                                        {{ situationVal(commande.situation) || '—' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500">Montant payé</dt>
                                <dd class="text-sm font-bold text-emerald-600 tabular-nums">{{ formatPrice(commande.montantPaye) }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-3.5">
                                <dt class="text-sm text-gray-500">Reste à payer</dt>
                                <dd class="text-sm font-bold tabular-nums" :class="totalTTC - (commande.montantPaye || 0) > 0 ? 'text-rose-600' : 'text-gray-400'">
                                    {{ formatPrice(Math.max(0, totalTTC - (commande.montantPaye || 0))) }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Fournisseur info -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100">
                                    <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">Fournisseur</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <!-- Fournisseur avatar + name -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-200">
                                    <span class="text-sm font-bold text-white">{{ (commande.fournisseur?.societe || '?').substring(0, 2).toUpperCase() }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ commande.fournisseur?.societe ?? '—' }}</p>
                                    <p v-if="commande.fournisseur?.contact" class="text-xs text-gray-400">{{ commande.fournisseur.contact }}</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div v-if="commande.fournisseur?.telephone" class="flex items-center gap-2.5 text-sm">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 shrink-0">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                    </div>
                                    <span class="text-gray-700">{{ commande.fournisseur.telephone }}</span>
                                </div>
                                <div v-if="commande.fournisseur?.email" class="flex items-center gap-2.5 text-sm">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 shrink-0">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                    </div>
                                    <span class="text-gray-700">{{ commande.fournisseur.email }}</span>
                                </div>
                                <div v-if="commande.fournisseur?.adresse" class="flex items-start gap-2.5 text-sm">
                                    <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    </div>
                                    <span class="text-gray-700">{{ commande.fournisseur.adresse }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products table -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-100">
                                    <svg class="w-4 h-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                    </svg>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900">Produits commandés</h3>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-600/20">
                                {{ totalArticles }} article{{ totalArticles > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                    <DataTable :value="produits" stripedRows rowHover>
                        <Column field="n_lot" header="N° Lot">
                            <template #body="{ data }">
                                <span class="font-mono text-sm font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded">{{ data.n_lot }}</span>
                            </template>
                        </Column>
                        <Column field="label" header="Produit">
                            <template #body="{ data }">
                                <Link :href="route('produits.show', {produit: data.id})" class="text-sm font-medium text-gray-900 hover:text-sky-600 transition-colors">
                                    {{ data.label }}
                                </Link>
                            </template>
                        </Column>
                        <Column field="qte_achete" header="Qté" style="text-align: center">
                            <template #body="{ data }">
                                <span class="font-semibold text-gray-900 tabular-nums">{{ data.qte_achete }}</span>
                            </template>
                        </Column>
                        <Column field="qte" header="En stock" style="text-align: center">
                            <template #body="{ data }">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-sm tabular-nums" :class="data.qte === 0 ? 'text-red-600 font-bold' : 'text-gray-700 font-medium'">{{ data.qte }}</span>
                                    <div class="w-12 bg-gray-200 rounded-full h-1">
                                        <div class="h-1 rounded-full transition-all"
                                             :class="stockPercent(data) > 50 ? 'bg-emerald-500' : stockPercent(data) > 20 ? 'bg-amber-500' : 'bg-red-500'"
                                             :style="{width: stockPercent(data) + '%'}"></div>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column field="prix_achat" header="P. Achat HT" style="text-align: right">
                            <template #body="{ data }">
                                <span class="tabular-nums text-sm text-gray-700">{{ formatPrice(data.prix_achat) }}</span>
                            </template>
                        </Column>
                        <Column field="prix_vente" header="P. Vente" style="text-align: right">
                            <template #body="{ data }">
                                <span class="tabular-nums text-sm font-medium text-gray-900">{{ formatPrice(data.prix_vente) }}</span>
                            </template>
                        </Column>
                        <Column field="tva" header="TVA" style="text-align: center">
                            <template #body="{ data }">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ data.tva }}%</span>
                            </template>
                        </Column>
                        <Column field="expirationDate" header="Péremption">
                            <template #body="{ data }">
                                <div v-if="data.expirationDate" class="flex items-center gap-1.5">
                                    <span class="text-sm tabular-nums">{{ formatDate(data.expirationDate) }}</span>
                                    <span v-if="expirationStatus(data.expirationDate) === 'expired'"
                                          class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Périmé</span>
                                    <span v-else-if="expirationStatus(data.expirationDate) === 'soon'"
                                          class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">Bientôt</span>
                                </div>
                                <span v-else class="text-gray-300">—</span>
                            </template>
                        </Column>
                        <Column field="totalHT" header="Total HT" style="text-align: right">
                            <template #body="{ data }">
                                <span class="font-semibold tabular-nums text-gray-900">{{ formatPrice(data.totalHT) }}</span>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Totals -->
                    <div class="border-t border-gray-100">
                        <div class="max-w-sm ml-auto px-6 py-5 space-y-2.5">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Sous-total HT</span>
                                <span class="font-medium tabular-nums text-gray-900">{{ formatPrice(sousTotal) }}</span>
                            </div>
                            <div v-for="(amount, rate) in tvaGroups" :key="rate" class="flex justify-between text-sm">
                                <span class="text-gray-500 flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">{{ rate }}%</span>
                                    TVA
                                </span>
                                <span class="tabular-nums text-gray-700">{{ formatPrice(amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                <span class="font-bold text-gray-900">Total TTC</span>
                                <span class="font-bold tabular-nums text-xl text-sky-700">{{ formatPrice(totalTTC) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
