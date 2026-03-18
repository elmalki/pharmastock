<script setup>
import {useForm, usePage, Link} from "@inertiajs/vue3";
import {computed, ref, watch} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import SelectClient from "@/Pages/Clients/SelectClient.vue";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';

const props = defineProps({nextNumero: String});
const page = usePage();
const modes = page.props.enums.modes_paiement;

const form = useForm({
    n_facture: props.nextNumero,
    client_id: 1,
    date: new Date(),
    paiement: modes[0],
    dateEcheance: null,
    montantPaye: 0,
    remise: 0,
    produits: []
});

function clientSelected(item) {
    form.client_id = item.id;
}

const subtotal = computed(() => {
    let s = 0;
    form.produits.forEach(p => (p.lots || []).forEach(l => s += (l.sortie || 0) * (l.prix_vente || 0)));
    return s;
});
const discountAmount = computed(() => (subtotal.value * (parseFloat(form.remise) || 0)) / 100);
const total = computed(() => subtotal.value - discountAmount.value);
const totalArticles = computed(() => form.produits.length);
const totalUnites = computed(() => {
    let s = 0;
    form.produits.forEach(p => (p.lots || []).forEach(l => s += (l.sortie || 0)));
    return s;
});

// Cash payment helper (not stored in DB)
const montantRecu = ref(null);
const isEspece = computed(() => form.paiement === 'Éspèce');
const monnaie = computed(() => {
    const recu = parseFloat(montantRecu.value) || 0;
    if (recu <= 0) return null;
    return recu - total.value;
});

// Reset montantRecu when switching away from cash
watch(() => form.paiement, () => { montantRecu.value = null; });

function fmt(v) {
    return new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(v || 0);
}

function productSubtotal(product) {
    return (product.lots || []).reduce((s, l) => s + (l.sortie || 0) * (l.prix_vente || 0), 0);
}

function increaseQuantity(ii, li) {
    const lot = form.produits[ii].lots[li];
    if (lot.sortie < lot.qte) lot.sortie++;
}

function decreaseQuantity(ii, li) {
    const lot = form.produits[ii].lots[li];
    if (lot.sortie > 0) lot.sortie--;
}

function removeProduct(i) {
    form.produits.splice(i, 1);
}

async function selectedItems(items) {
    const selectedIds = new Set(items.map(i => i.id));
    const existingMap = new Map(form.produits.map(p => [p.id, p]));
    const kept = form.produits.filter(p => selectedIds.has(p.id));
    const newItems = items.filter(i => !existingMap.has(i.id));
    await Promise.all(newItems.map(item =>
        axios.post('/api/getLots', {id: item.id}).then(r => {
            r.data.forEach(el => el.sortie = 1);
            item.lots = r.data;
        })
    ));
    form.produits = [...kept, ...newItems];
}

function submit() {
    form.montantPaye = total.value;
    form.post(route('ventes.store'));
}
</script>

<template>
    <AppLayout title="Nouvelle Vente">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto" @keydown.enter.prevent="">
            <Breadcrumbs :pages="[
                {name: 'Ventes', href: route('ventes.index'), current: false},
                {name: 'Nouvelle Vente', href: route('ventes.create'), current: true}
            ]"/>

            <div v-if="form.errors.error" class="mt-4 rounded-lg bg-red-50 p-4 border border-red-200">
                <p class="text-sm text-red-700">{{ form.errors.error }}</p>
            </div>

            <div class="mt-4 lg:grid lg:grid-cols-12 lg:gap-6">
                <!-- ===== LEFT: CART ===== -->
                <div class="lg:col-span-5 xl:col-span-4 lg:mt-0">
                    <div class="lg:sticky lg:top-24 space-y-4">
                        <!-- Order details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Détails de la vente</h3>
                                <span class="text-sm font-bold text-indigo-600 tabular-nums">N° {{ form.n_facture }}</span>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
                                <SelectClient @selected-client="clientSelected"/>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                                    <DatePicker v-model="form.date" dateFormat="yy-mm-dd" class="w-full" size="small"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Échéance</label>
                                    <DatePicker v-model="form.dateEcheance" dateFormat="yy-mm-dd" class="w-full" size="small"/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Paiement</label>
                                    <Select :options="modes" v-model="form.paiement" class="w-full" size="small"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Remise (%)</label>
                                    <InputText v-model.number="form.remise" type="number" min="0" max="100" placeholder="0" class="w-full" size="small"/>
                                </div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Sous-total</span>
                                    <span class="font-medium tabular-nums text-gray-700">{{ fmt(subtotal) }}</span>
                                </div>
                                <div v-if="(form.remise || 0) > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-500">Remise ({{ form.remise }}%)</span>
                                    <span class="font-medium tabular-nums text-red-500">- {{ fmt(discountAmount) }}</span>
                                </div>
                            </div>
                            <div class="bg-slate-800 px-4 py-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-300">Total</span>
                                    <span class="text-2xl font-bold tabular-nums text-white">{{ fmt(total) }}</span>
                                </div>
                            </div>

                            <!-- Cash payment: amount received & change -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-2"
                            >
                                <div v-if="isEspece" class="border-t border-gray-200 bg-gradient-to-b from-amber-50 to-orange-50 p-4 space-y-3">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                        </svg>
                                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Calcul monnaie</span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-amber-800 mb-1">Montant reçu du client</label>
                                        <div class="relative">
                                            <input v-model.number="montantRecu" type="number" step="0.01" min="0"
                                                   class="block w-full rounded-lg border-amber-300 bg-white shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm py-2 pl-3 pr-12 tabular-nums font-semibold"
                                                   placeholder="0.00">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-amber-400 text-xs font-medium">Dhs</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Change display -->
                                    <div v-if="monnaie !== null"
                                         class="rounded-lg p-3 text-center transition-all"
                                         :class="monnaie >= 0 ? 'bg-emerald-100 border border-emerald-200' : 'bg-red-100 border border-red-200'">
                                        <p class="text-xs font-medium mb-0.5" :class="monnaie >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                            {{ monnaie >= 0 ? 'Monnaie à rendre' : 'Montant insuffisant' }}
                                        </p>
                                        <p class="text-2xl font-bold tabular-nums" :class="monnaie >= 0 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ fmt(Math.abs(monnaie)) }}
                                        </p>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <Link :href="route('ventes.index')" class="flex-1 text-center rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 bg-white shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Annuler
                            </Link>
                            <button type="button" @click="submit" :disabled="form.processing || form.produits.length === 0"
                                    class="flex-[2] rounded-xl px-4 py-3 text-sm font-semibold text-white bg-emerald-600 shadow-sm hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                {{ form.processing ? 'Traitement...' : 'Valider la vente' }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- ===== RIGHT: SIDEBAR ===== -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-4">
                    <!-- Product search -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ajouter des produits</label>
                        <SelectProductsOut @selected="selectedItems"/>
                    </div>

                    <!-- Cart header -->
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">
                            Panier
                            <span v-if="totalArticles > 0" class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                {{ totalArticles }} article{{ totalArticles > 1 ? 's' : '' }} &middot; {{ totalUnites }} unité{{ totalUnites > 1 ? 's' : '' }}
                            </span>
                        </h2>
                    </div>

                    <!-- Empty cart -->
                    <div v-if="form.produits.length === 0" class="bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-300 p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                        <p class="mt-3 text-sm text-gray-500">Recherchez et ajoutez des produits</p>
                    </div>

                    <!-- Cart items -->
                    <div v-for="(item, ii) in form.produits" :key="item.id" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Product header -->
                        <div class="flex items-center justify-between px-4 py-2.5 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-200">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ item.label }}</h3>
                                    <p v-if="item.barcode" class="text-xs text-gray-400 font-mono">{{ item.barcode }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="text-sm font-bold text-indigo-600 tabular-nums">{{ fmt(productSubtotal(item)) }}</span>
                                <button type="button" @click="removeProduct(ii)" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <!-- Lots -->
                        <div class="divide-y divide-gray-100">
                            <div v-for="(lot, li) in item.lots" :key="li" class="flex items-center gap-4 px-4 py-2.5 hover:bg-gray-50/50">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs font-medium text-gray-700">{{ lot.n_lot }}</span>
                                        <span class="text-xs text-gray-400">&middot;</span>
                                        <span class="text-xs text-gray-500 tabular-nums">{{ fmt(lot.prix_vente || 0) }}</span>
                                        <span class="text-xs text-gray-400">&middot;</span>
                                        <span class="text-xs tabular-nums" :class="lot.qte <= 5 ? 'text-red-500 font-medium' : 'text-gray-400'">{{ lot.qte }} en stock</span>
                                        <template v-if="lot.expirationDate">
                                            <span class="text-xs text-gray-400">&middot;</span>
                                            <span class="text-xs text-gray-400">Exp: {{ lot.expirationDate?.substring?.(0, 10) ?? lot.expirationDate }}</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button type="button" @click="decreaseQuantity(ii, li)" :class="{'opacity-30 cursor-not-allowed': lot.sortie <= 0}" class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 bg-white hover:bg-gray-50 text-gray-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>
                                    <input type="number" v-model.number="lot.sortie" :max="lot.qte" min="0" class="w-12 text-center rounded-md border-gray-200 py-1 text-sm font-semibold tabular-nums focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"/>
                                    <button type="button" @click="increaseQuantity(ii, li)" :class="{'opacity-30 cursor-not-allowed': lot.sortie >= lot.qte}" class="w-7 h-7 flex items-center justify-center rounded-md border border-gray-200 bg-white hover:bg-gray-50 text-gray-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                                <div class="w-24 text-right flex-shrink-0">
                                    <span class="text-sm font-semibold tabular-nums" :class="(lot.sortie || 0) > 0 ? 'text-gray-900' : 'text-gray-300'">{{ fmt((lot.sortie || 0) * (lot.prix_vente || 0)) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
