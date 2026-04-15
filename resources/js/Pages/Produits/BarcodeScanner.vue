<script setup>
import {ref, computed, onMounted, onUnmounted, nextTick} from 'vue';
import AutoComplete from 'primevue/autocomplete';
import Button from 'primevue/button'
const emit = defineEmits(['added', 'updated', 'removed']);

const props = defineProps({
    produits: {type: Array, default: () => []}
});

// All products cache
const allProducts = ref([]);
const suggestions = ref([]);
const selectedProduct = ref(null);

// Barcode input
const barcodeInput = ref(null);
const barcodeValue = ref('');
const scanStatus = ref(null); // 'success' | 'error' | null
const scanMessage = ref('');
let scanTimeout = null;
let statusTimeout = null;

// Stats
const totalItems = computed(() => props.produits.length);
const totalUnits = computed(() => {
    let s = 0;
    props.produits.forEach(p => (p.lots || []).forEach(l => s += (l.sortie || 0)));
    return s;
});

// Load products
onMounted(async () => {
    const res = await axios.get('/api/produits');
    allProducts.value = res.data;
    focusBarcode();
});

function focusBarcode() {
    nextTick(() => barcodeInput.value?.focus());
}

// Barcode scanning — accumulate characters, process on Enter
function onBarcodeKeydown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = barcodeValue.value.trim();
        if (code) {
            processBarcode(code);
            barcodeValue.value = '';
        }
    }
}

async function processBarcode(code) {
    clearTimeout(statusTimeout);

    // Find product by barcode
    const product = allProducts.value.find(p => p.barcode === code);

    if (!product) {
        showStatus('error', `Aucun produit trouvé pour: ${code}`);
        return;
    }

    // Check if already in cart
    const existing = props.produits.find(p => p.id === product.id);

    if (existing) {
        // Increment first lot with available stock
        const lot = (existing.lots || []).find(l => l.sortie < l.qte);
        if (lot) {
            lot.sortie++;
            showStatus('success', `${product.label} — qté augmentée`);
            emit('updated');
        } else {
            showStatus('error', `${product.label} — stock max atteint`);
        }
    } else {
        // Fetch lots and add product
        try {
            const res = await axios.post('/api/getLots', {id: product.id});
            if (res.data.length === 0) {
                showStatus('error', `${product.label} — aucun lot en stock`);
                return;
            }
            res.data.forEach(el => el.sortie = 1);
            // Only set sortie=1 on first lot, 0 on rest
            res.data.forEach((el, i) => el.sortie = i === 0 ? 1 : 0);
            product.lots = res.data;
            emit('added', product);
            showStatus('success', `${product.label} ajouté`);
        } catch {
            showStatus('error', 'Erreur de chargement des lots');
        }
    }
}

function showStatus(type, msg) {
    scanStatus.value = type;
    scanMessage.value = msg;
    statusTimeout = setTimeout(() => {
        scanStatus.value = null;
        scanMessage.value = '';
    }, 3000);
}

// Autocomplete search
function searchProducts(event) {
    const query = (event.query || '').toLowerCase();
    suggestions.value = allProducts.value.filter(p =>
        p.label.toLowerCase().includes(query) ||
        (p.barcode && p.barcode.includes(query)) ||
        (p.dci && p.dci.toLowerCase().includes(query))
    ).slice(0, 10);
}

async function onProductSelect(e) {
    const product = e.value;
    if (!product) return;

    // Simulate barcode scan
    await processBarcode(product.barcode || '__id_' + product.id);

    // If no barcode, handle by id directly
    if (!product.barcode) {
        const existing = props.produits.find(p => p.id === product.id);
        if (!existing) {
            try {
                const res = await axios.post('/api/getLots', {id: product.id});
                if (res.data.length === 0) {
                    showStatus('error', `${product.label} — aucun lot en stock`);
                    selectedProduct.value = null;
                    return;
                }
                res.data.forEach((el, i) => el.sortie = i === 0 ? 1 : 0);
                product.lots = res.data;
                emit('added', product);
                showStatus('success', `${product.label} ajouté`);
            } catch {
                showStatus('error', 'Erreur de chargement des lots');
            }
        } else {
            const lot = (existing.lots || []).find(l => l.sortie < l.qte);
            if (lot) {
                lot.sortie++;
                showStatus('success', `${product.label} — qté augmentée`);
                emit('updated');
            }
        }
    }

    selectedProduct.value = null;
    focusBarcode();
}

// Global keyboard listener — redirect keystrokes to barcode input when not typing elsewhere
function onGlobalKeydown(e) {
    const tag = e.target.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
    if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        barcodeValue.value += e.key;
        focusBarcode();
    }
}

onMounted(() => document.addEventListener('keydown', onGlobalKeydown));
onUnmounted(() => document.removeEventListener('keydown', onGlobalKeydown));
</script>

<template>
    <div class="space-y-4">
        <!-- Scanner + Search area -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-5 py-3.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-2 h-2 rounded-lg bg-emerald-100">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Scanner / Rechercher</h3>
                            <p class="text-[11px] text-gray-400">Scannez un code-barres ou recherchez par nom</p>
                        </div>
                    </div>
                    <div v-if="totalItems > 0" class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                            {{ totalItems }} article{{ totalItems > 1 ? 's' : '' }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                            {{ totalUnits }} unité{{ totalUnits > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-3">
                <!-- Barcode input -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5Z"/>
                            </svg>
                            Code-barres (scanner)
                        </span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5" :class="scanStatus === 'success' ? 'text-emerald-500' : scanStatus === 'error' ? 'text-red-500' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z"/>
                            </svg>
                        </div>
                        <input ref="barcodeInput"
                               v-model="barcodeValue"
                               @keydown="onBarcodeKeydown"
                               type="text"
                               inputmode="none"
                               autocomplete="off"
                               class="block w-full rounded-xl shadow-sm sm:text-sm py-3 pl-11 pr-4 font-mono text-lg tracking-wider transition-all duration-200 border border-gray-400"
                               :class="scanStatus === 'success'
                                   ? 'border-emerald-400 ring-2 ring-emerald-200 bg-emerald-50/50'
                                   : scanStatus === 'error'
                                       ? 'border-red-400 ring-2 ring-red-200 bg-red-50/50'
                                       : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500'"
                               placeholder="Scannez ou tapez le code-barres...">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <kbd class="hidden sm:inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 ring-1 ring-inset ring-gray-300">
                                Entrée
                            </kbd>
                        </div>
                    </div>
                </div>

                <!-- Status toast -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div v-if="scanStatus" class="flex items-center gap-2.5 rounded-xl px-4 py-2.5 text-sm font-medium"
                         :class="scanStatus === 'success' ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'bg-red-50 text-red-800 ring-1 ring-red-200'">

                        <Button v-if="scanStatus === 'success'" severity="success" size="small" icon="pi pi-check" rounded aria-label="Filter" />
                        <Button v-else icon="pi pi-times" rounded  size="small" severity="danger" aria-label="Cancel" />
                        {{ scanMessage }}
                    </div>
                </Transition>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs">
                        <span class="bg-white px-3 text-gray-400 font-medium">ou rechercher par nom</span>
                    </div>
                </div>

                <!-- Autocomplete search -->
                <AutoComplete
                    v-model="selectedProduct"
                    :suggestions="suggestions"
                    @complete="searchProducts"
                    @item-select="onProductSelect"
                    optionLabel="label"
                    placeholder="Rechercher un produit..."
                    class="w-full"
                    inputClass="w-full"
                    :pt="{
                        root: { class: 'w-full' },
                        pcInputText: { root: { class: 'w-full rounded-xl' } }
                    }"
                >
                    <template #option="{ option }">
                        <div class="flex items-center gap-3 py-1">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <span class="text-[10px] font-bold text-indigo-700">{{ option.label.substring(0, 2).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ option.label }}</p>
                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    <span v-if="option.barcode" class="font-mono">{{ option.barcode }}</span>
                                    <span v-if="option.dci">{{ option.dci }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-semibold tabular-nums" :class="option.qte > 0 ? 'text-emerald-600' : 'text-red-500'">
                                    {{ option.qte ?? 0 }}
                                </span>
                                <span class="text-[10px] text-gray-400 ml-0.5">en stock</span>
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        <div class="px-4 py-3 text-sm text-gray-400 text-center">Aucun produit trouvé</div>
                    </template>
                </AutoComplete>
            </div>
        </div>
    </div>
</template>
