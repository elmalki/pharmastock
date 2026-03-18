<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
        <!-- Product header -->
        <div class="flex items-center justify-between px-5 py-3.5 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md shadow-indigo-200">
                    <span class="text-xs font-bold text-white">{{ label.substring(0, 2).toUpperCase() }}</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ label }}</h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span v-if="perissable" class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                            Périssable
                        </span>
                        <span v-if="entree.n_lot" class="text-[10px] text-gray-400 font-mono">Lot: {{ entree.n_lot }}</span>
                    </div>
                </div>
            </div>
            <Button    @click="remove()" icon="pi pi-times" severity="danger" rounded aria-label="Cancel" size="small" />
        </div>

        <!-- Fields -->
        <div class="p-5">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-6">
                <!-- Quantité -->
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Quantité</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                        </div>
                        <input type="number" v-model="entree.qte" min="1"
                               class="block w-full rounded-xl border-gray-500 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-9 pr-4 tabular-nums font-semibold transition-colors"
                               placeholder="0">
                    </div>
                </div>

                <!-- N° Lot -->
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">N° Lot</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/>
                            </svg>
                        </div>
                        <input type="text" v-model="entree.n_lot"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-9 pr-4 font-mono transition-colors"
                               placeholder="Numéro de lot">
                    </div>
                </div>

                <!-- Prix d'achat -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Prix achat (HT)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-xs font-medium">Dhs</span>
                        </div>
                        <input type="number" step="0.01" v-model="entree.prix_achat"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm py-2.5 pl-10 pr-4 tabular-nums transition-colors"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Prix de vente -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Prix vente (TTC)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-xs font-medium">Dhs</span>
                        </div>
                        <input type="number" step="0.01" v-model="entree.prix_vente"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm py-2.5 pl-10 pr-4 tabular-nums transition-colors"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- TVA -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">TVA</label>
                    <div class="relative">
                        <input type="number" v-model="entree.tva" min="0" max="100"
                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm py-2.5 pl-4 pr-9 tabular-nums transition-colors"
                               placeholder="20">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm font-semibold">%</span>
                        </div>
                    </div>
                </div>

                <!-- Date péremption -->
                <div v-if="perissable" class="col-span-2 sm:col-span-6">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            Date de péremption
                        </span>
                    </label>
                    <DatePicker :showTime="false" v-model="entree.expirationDate" class="w-full" lang="fr" date-format="yy-mm-dd" showIcon iconDisplay="input"/>
                </div>
            </div>

            <!-- Quick summary bar -->
            <div v-if="entree.qte && entree.prix_achat" class="mt-4 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 px-4 py-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-4 text-xs">
                        <span class="text-gray-500">Total HT:</span>
                        <span class="font-bold text-indigo-700 tabular-nums">{{ formatPrice(totalHT) }}</span>
                    </div>
                    <div v-if="entree.tva > 0" class="flex items-center gap-4 text-xs">
                        <span class="text-gray-500">TVA ({{ entree.tva }}%):</span>
                        <span class="font-bold text-amber-700 tabular-nums">{{ formatPrice(totalTVA) }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="text-gray-500">Total TTC:</span>
                        <span class="font-bold text-emerald-700 tabular-nums text-sm">{{ formatPrice(totalTTC) }}</span>
                    </div>
                    <div v-if="entree.prix_vente && entree.prix_achat" class="flex items-center gap-1.5 text-xs">
                        <span class="text-gray-500">Marge:</span>
                        <span class="font-bold tabular-nums" :class="marge >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ marge >= 0 ? '+' : '' }}{{ marge.toFixed(1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import DatePicker from "primevue/datepicker";
import {computed} from "vue";
import Button from 'primevue/button'

const props = defineProps({entree: Object, label: String, id: Number, perissable: Boolean});
const emit = defineEmits(['removeItem']);

const remove = () => emit('remove-item', props.id);

const totalHT = computed(() => (parseFloat(props.entree.qte) || 0) * (parseFloat(props.entree.prix_achat) || 0));
const totalTVA = computed(() => totalHT.value * (parseFloat(props.entree.tva) || 0) / 100);
const totalTTC = computed(() => totalHT.value + totalTVA.value);
const marge = computed(() => {
    const achat = parseFloat(props.entree.prix_achat) || 0;
    const vente = parseFloat(props.entree.prix_vente) || 0;
    if (achat === 0) return 0;
    return ((vente - achat) / achat) * 100;
});

function formatPrice(val) {
    return parseFloat(val).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' Dhs';
}
</script>
