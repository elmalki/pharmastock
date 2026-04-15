<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm, Link} from "@inertiajs/vue3";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Editor from 'primevue/editor';

const props = defineProps({number: Number});

const form = useForm({
    motifs: '',
    n_destockage: props.number,
    produits: [],
});

function selectedItems(items) {
    items.forEach(item => {
        if (!item.lots) {
            axios.post('/api/getLots', {id: item.id}).then(response => {
                response.data.forEach(el => el.sortie = 0);
                item.lots = response.data;
            });
        }
    });
    form.produits = items;
}

function submit() {
    form.post(route('destockages.store'));
}

function totalSortie(item) {
    return (item.lots || []).reduce((s, l) => s + (l.sortie || 0), 0);
}
</script>

<template>
    <AppLayout title="Nouveau Destockage">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Destockages', href: route('destockages.index'), current: false},
                {name: 'Nouveau Destockage', href: route('destockages.create'), current: true}
            ]"/>

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
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Nouveau Destockage</h1>
                            <p class="text-orange-200 text-sm mt-0.5">Enregistrez une sortie de stock</p>
                        </div>
                    </div>
                    <Link :href="route('destockages.index')"
                          class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                        </svg>
                        Retour
                    </Link>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100">
                            <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900">Informations du destockage</h2>
                    </div>
                </div>

                <div class="p-6">
                    <!-- N° -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Destockage</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/>
                                </svg>
                            </div>
                            <input v-model="form.n_destockage" type="text"
                                   class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm py-2.5 pl-10 pr-4 font-mono transition-colors"
                                   placeholder="Numéro">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100">
                                <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold text-gray-900">Sélection des produits</h2>
                        </div>
                        <span v-if="form.produits.length" class="inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-600/20">
                            {{ form.produits.length }} produit{{ form.produits.length > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <SelectProductsOut @selected="selectedItems"/>
                </div>
            </div>

            <!-- Selected products with lots -->
            <div v-for="item in form.produits" :key="item.id"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
                <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-md shadow-violet-200">
                            <span class="text-xs font-bold text-white">{{ item.label.substring(0, 2).toUpperCase() }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">{{ item.label }}</h3>
                            <p v-if="item.barcode" class="text-[11px] text-gray-400 font-mono">{{ item.barcode }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold tabular-nums"
                          :class="totalSortie(item) > 0 ? 'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20' : 'bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-200'">
                        {{ totalSortie(item) }} à sortir
                    </span>
                </div>
                <!-- Lots header -->
                <div class="grid grid-cols-4 gap-4 px-5 py-2.5 bg-gray-50/80 border-b border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Lot</p>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">En stock</p>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Péremption</p>
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Quantité sortie</p>
                </div>
                <!-- Lots rows -->
                <div class="divide-y divide-gray-50">
                    <div v-for="lot in item.lots" :key="lot.id"
                         class="grid grid-cols-4 gap-4 px-5 py-3 items-center hover:bg-orange-50/30 transition-colors">
                        <div>
                            <span class="font-mono text-sm font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded">{{ lot.n_lot }}</span>
                            <p class="mt-0.5 text-[10px] text-gray-400">{{ lot.created_at?.substring(0, 10) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium tabular-nums" :class="lot.qte <= 5 ? 'text-red-600' : 'text-gray-900'">{{ lot.qte }}</span>
                            <div class="mt-1 w-14 bg-gray-200 rounded-full h-1">
                                <div class="h-1 rounded-full transition-all"
                                     :class="lot.qte > 20 ? 'bg-emerald-500' : lot.qte > 5 ? 'bg-amber-500' : 'bg-red-500'"
                                     :style="{width: Math.min(100, lot.qte * 2) + '%'}"></div>
                            </div>
                        </div>
                        <div>
                            <span v-if="lot.expirationDate" class="text-sm text-gray-700 tabular-nums">{{ (lot.expirationDate?.substring?.(0, 10) ?? lot.expirationDate)?.split('-').reverse().join('/') }}</span>
                            <span v-else class="text-gray-300 text-sm">—</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="lot.sortie = Math.max(0, (lot.sortie || 0) - 1)"
                                    :disabled="(lot.sortie || 0) <= 0"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>
                            <input type="number" v-model.number="lot.sortie" :max="lot.qte" min="0"
                                   class="w-14 text-center rounded-lg border-gray-200 py-1.5 text-sm font-semibold tabular-nums focus:border-orange-500 focus:ring-1 focus:ring-orange-500"/>
                            <button type="button" @click="lot.sortie = Math.min(lot.qte, (lot.sortie || 0) + 1)"
                                    :disabled="(lot.sortie || 0) >= lot.qte"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motifs -->
            <div v-if="form.produits.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-rose-100">
                            <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900">Motifs du destockage</h2>
                    </div>
                </div>
                <div class="p-6">
                    <Editor v-model="form.motifs" editorStyle="height: 200px"/>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="form.produits.length" class="flex items-center justify-between pt-6 border-t border-gray-200">
                <Link :href="route('destockages.index')"
                      class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                    </svg>
                    Annuler
                </Link>
                <button @click="submit()" :disabled="form.processing" type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-rose-600 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-orange-200 hover:from-orange-400 hover:to-rose-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer">
                    <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Traitement...' : 'Valider le destockage' }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
