<script setup>
import {useForm, usePage, Link} from "@inertiajs/vue3";
import {computed} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import SelectClient from "@/Pages/Clients/SelectClient.vue";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    vente: Object,
    venteProduits: Array,
});

const page = usePage();
const modes = page.props.enums.modes_paiement;

const form = useForm({
    n_facture: props.vente.n_facture ?? null,
    client_id: props.vente.client_id ?? null,
    date: props.vente.date ? new Date(props.vente.date) : null,
    paiement: props.vente.paiement ?? null,
    dateEcheance: props.vente.dateEcheance ? new Date(props.vente.dateEcheance) : null,
    montantPaye: 0,
    remise: props.vente.remise ?? 0,
    produits: props.venteProduits ?? [],
});

function clientSelected(item) {
    form.client_id = item.id;
}

const subtotal = computed(() => {
    let sum = 0;
    form.produits.forEach(product => {
        (product.lots || []).forEach(lot => {
            sum += (lot.sortie || 0) * (lot.prix_vente || 0);
        });
    });
    return sum;
});

const discountAmount = computed(() => (subtotal.value * (parseFloat(form.remise) || 0)) / 100);
const total = computed(() => subtotal.value - discountAmount.value);

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(amount || 0);
}

function increaseQuantity(itemIndex, lotIndex) {
    const lot = form.produits[itemIndex].lots[lotIndex];
    if (lot.sortie < lot.qte) lot.sortie++;
}

function decreaseQuantity(itemIndex, lotIndex) {
    const lot = form.produits[itemIndex].lots[lotIndex];
    if (lot.sortie > 0) lot.sortie--;
}

function removeProduct(index) {
    form.produits.splice(index, 1);
}

async function selectedItems(items) {
    const selectedIds = new Set(items.map(i => i.id));
    const existingMap = new Map(form.produits.map(p => [p.id, p]));

    // Keep existing products that are still selected (preserve their lots/sortie)
    const kept = form.produits.filter(p => selectedIds.has(p.id));

    // Find truly new products
    const newItems = items.filter(i => !existingMap.has(i.id));

    // Load lots only for new products
    const promises = newItems.map(item =>
        axios.post('/api/getLots', {id: item.id}).then(response => {
            response.data.forEach(el => el.sortie = 1);
            item.lots = response.data;
        })
    );
    await Promise.all(promises);

    form.produits = [...kept, ...newItems];
}

function submit() {
    form.montantPaye = total.value;
    form.put(route('ventes.update', props.vente.id));
}
</script>

<template>
    <AppLayout title="Modifier Vente">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Ventes', href: route('ventes.index'), current: false},
                {name: 'Modifier Vente', href: route('ventes.edit', vente.id), current: true}
            ]"/>

            <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-6">
                Modifier Vente
                <span v-if="vente.n_facture" class="text-gray-500 font-normal text-lg">#{{ vente.n_facture }}</span>
            </h1>

            <!-- Erreurs globales -->
            <div v-if="form.errors.error" class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
                <p class="text-sm text-red-700">{{ form.errors.error }}</p>
            </div>

            <!-- Formulaire -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" @keydown.enter.prevent="">
                <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                        <SelectClient :client="vente.client" @selected-client="clientSelected"/>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <DatePicker v-model="form.date" dateFormat="yy-mm-dd" class="w-full"/>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numéro Facture</label>
                        <InputText v-model="form.n_facture" class="w-full"/>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remise (%)</label>
                        <InputText v-model.number="form.remise" type="number" min="0" max="100" placeholder="0"
                                   class="w-full"/>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                        <Select :options="modes" v-model="form.paiement" class="w-full"/>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Échéance</label>
                        <DatePicker v-model="form.dateEcheance" dateFormat="yy-mm-dd" class="w-full"/>
                    </div>
                </div>
            </div>

            <!-- Ajouter des produits -->
            <SelectProductsOut :produits="venteProduits" @selected="selectedItems"/>

            <!-- Liste des produits avec lots -->
            <div v-for="(item, itemIndex) in form.produits" :key="itemIndex"
                 class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- En-tête produit -->
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ item.label }}</h3>
                        <p v-if="item.barcode" class="text-xs text-gray-500 mt-0.5">
                            Code-barres: {{ item.barcode }}
                        </p>
                    </div>
                    <button type="button" @click="removeProduct(itemIndex)"
                            class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 ring-1 ring-inset ring-red-600/20">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Retirer
                    </button>
                </div>

                <!-- Tableau des lots -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Lot</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Prix unitaire
                        </th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">En stock</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Péremption</th>
                        <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">Sous-total</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="(lot, lotIndex) in item.lots" :key="lotIndex" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ lot.n_lot }}</span>
                            <p class="text-xs text-gray-500 mt-0.5">{{ lot.created_at?.substring(0, 10) }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700">
                            {{ formatCurrency(lot.prix_vente || 0) }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium tabular-nums text-gray-900">
                            {{ lot.qte }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ lot.expirationDate ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="decreaseQuantity(itemIndex, lotIndex)"
                                        class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 12H4"/>
                                    </svg>
                                </button>
                                <input type="number" v-model.number="lot.sortie" :max="lot.qte" min="0"
                                       class="w-16 text-center rounded-md border-gray-300 px-1.5 py-1 text-sm tabular-nums focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"/>
                                <button type="button" @click="increaseQuantity(itemIndex, lotIndex)"
                                        class="w-7 h-7 flex items-center justify-center rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-indigo-600">
                            {{ formatCurrency((lot.sortie || 0) * (lot.prix_vente || 0)) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Résumé des totaux -->
            <div v-if="form.produits.length > 0"
                 class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Sous-total</span>
                        <span class="font-medium tabular-nums">{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Remise ({{ form.remise || 0 }}%)</span>
                        <span class="font-medium text-red-600 tabular-nums">-
                            {{ formatCurrency(discountAmount) }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex justify-between text-lg">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="font-bold text-indigo-600 tabular-nums">{{ formatCurrency(total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-x-3 pt-6 border-t border-gray-200 mt-6">
                <Link :href="route('ventes.index')"
                      class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Annuler
                </Link>
                <button type="button" @click="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                    Enregistrer
                </button>
            </div>
        </div>
    </AppLayout>
</template>
