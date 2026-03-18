<script setup>
import {ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm, usePage, Link} from '@inertiajs/vue3';
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import SelectProducts from "@/Pages/Produits/SelectProducts.vue";
import SelectFournisseur from "@/Pages/Fournisseurs/SelectFournisseur.vue";
import DatePicker from "primevue/datepicker";
import Select from "primevue/select";

const props = defineProps({commande: Object});
const fournisseur = ref(null);

const page = usePage();
const modes = page.props.enums.modes_paiement;

const form = useForm({...props.commande});

const fournisseurSelected = (item) => {
    fournisseur.value = item;
    form.fournisseur_id = item.id;
};

const submit = () => {
    form.put(route('commandes.update', {commande: props.commande.id}));
};

function selectedItems(items) {
    form.produits = items;
}
</script>

<template>
    <AppLayout title="Modifier Approvisionnement">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" @keydown.enter.prevent="">
            <Breadcrumbs :pages="[
                {name: 'Approvisionnements', href: route('commandes.index'), current: false},
                {name: commande.n_bon || commande.n_facture || 'Modifier', href: route('commandes.edit', {commande: commande.id}), current: true}
            ]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Modifier l'approvisionnement</h1>
                            <p class="text-amber-100 text-sm mt-0.5">
                                {{ commande.n_bon || commande.n_facture }}
                                <span v-if="commande.fournisseur?.societe" class="mx-1.5 text-amber-200/50">|</span>
                                <span v-if="commande.fournisseur?.societe">{{ commande.fournisseur.societe }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('commandes.show', {commande: commande.id})"
                              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 shadow-sm hover:bg-amber-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Voir
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

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900">Informations de la commande</h2>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-6">
                        <!-- Fournisseur -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fournisseur</label>
                            <SelectFournisseur @selected-fournisseur="fournisseurSelected" :fournisseur="form.fournisseur"/>
                        </div>
                        <!-- Date -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date</label>
                            <DatePicker v-model="form.date" lang="fr" date-format="yy-mm-dd" class="w-full" showIcon iconDisplay="input"/>
                        </div>
                        <!-- N° Bon -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Bon / Marché</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/>
                                    </svg>
                                </div>
                                <input v-model="form.n_bon" type="text"
                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                       placeholder="Numéro de bon">
                            </div>
                        </div>
                        <!-- N° Facture -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Facture / Engagement</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                </div>
                                <input v-model="form.n_facture" type="text"
                                       class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm py-2.5 pl-10 pr-4 transition-colors"
                                       placeholder="Numéro de facture">
                            </div>
                        </div>
                        <!-- Mode de paiement -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Mode de paiement</label>
                            <Select :options="modes" v-model="form.paiement" placeholder="Mode de paiement" class="w-full"/>
                        </div>
                        <!-- Date Échéance -->
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date échéance</label>
                            <DatePicker v-model="form.dateEcheance" lang="fr" date-format="yy-mm-dd" class="w-full" showIcon iconDisplay="input"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-100">
                                <svg class="w-4 h-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold text-gray-900">Produits commandés</h2>
                        </div>
                        <span v-if="form.produits.length" class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-inset ring-cyan-600/20">
                            {{ form.produits.length }} produit{{ form.produits.length > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <SelectProducts @selected="selectedItems" :produits="form.produits"/>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <Link :href="route('commandes.index')"
                      class="inline-flex items-center gap-1.5 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors">
                    Annuler
                </Link>
                <button @click="submit()" :disabled="form.processing" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-200 hover:from-amber-400 hover:to-orange-400 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer">
                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Traitement...' : 'Enregistrer les modifications' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
