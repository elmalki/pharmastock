<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router} from '@inertiajs/vue3';
import {nextTick, ref, computed} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import {FilterMatchMode} from '@primevue/core/api';

const props = defineProps({items: Array});

const deleteModal = ref(false);
const item_id = ref(null);
const item_name = ref('');
const label = ref('');
const errors = ref([]);
const processing = ref(false);
const labelInput = ref(null);

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Stats
const totalCategories = computed(() => props.items.length);
const totalProduits = computed(() => props.items.reduce((s, c) => s + (c.total ?? 0), 0));
const withProduits = computed(() => props.items.filter(c => (c.total ?? 0) > 0).length);
const emptyCategories = computed(() => props.items.filter(c => (c.total ?? 0) === 0).length);

// Color palette for category badges
const colors = ['indigo', 'violet', 'blue', 'emerald', 'teal', 'amber', 'rose', 'cyan', 'fuchsia', 'lime'];
function getCatColor(index) {
    return colors[index % colors.length];
}
const colorClasses = {
    indigo: 'bg-indigo-100 text-indigo-700',
    violet: 'bg-violet-100 text-violet-700',
    blue: 'bg-blue-100 text-blue-700',
    emerald: 'bg-emerald-100 text-emerald-700',
    teal: 'bg-teal-100 text-teal-700',
    amber: 'bg-amber-100 text-amber-700',
    rose: 'bg-rose-100 text-rose-700',
    cyan: 'bg-cyan-100 text-cyan-700',
    fuchsia: 'bg-fuchsia-100 text-fuchsia-700',
    lime: 'bg-lime-100 text-lime-700',
};

// Modal state
const showModal = ref(false);
const isEditing = computed(() => item_id.value !== null);

function openCreate() {
    item_id.value = null;
    label.value = '';
    errors.value = [];
    showModal.value = true;
    nextTick(() => labelInput.value?.focus());
}

function openEdit(cat) {
    item_id.value = cat.id;
    label.value = cat.label;
    errors.value = [];
    showModal.value = true;
    nextTick(() => labelInput.value?.focus());
}

function closeModal() {
    showModal.value = false;
    item_id.value = null;
    label.value = '';
    errors.value = [];
}

function confirmDelete(cat) {
    item_id.value = cat.id;
    item_name.value = cat.label;
    deleteModal.value = true;
}

function deleteCategory() {
    deleteModal.value = false;
    router.delete(route('categories.destroy', {category: item_id.value}));
    item_id.value = null;
}

function submitForm() {
    processing.value = true;
    errors.value = [];

    if (!isEditing.value) {
        axios.post(route('categories.store'), {label: label.value})
            .then((response) => {
                processing.value = false;
                props.items.push(response.data);
                closeModal();
            })
            .catch(error => {
                processing.value = false;
                errors.value = error.response?.data?.errors?.label ?? ['Erreur inconnue'];
            });
    } else {
        axios.put(route('categories.update', {category: item_id.value}), {label: label.value})
            .then((response) => {
                processing.value = false;
                const item = props.items.find(el => response.data.id === el.id);
                if (item) item.label = response.data.label;
                closeModal();
            })
            .catch(error => {
                processing.value = false;
                errors.value = error.response?.data?.errors?.label ?? ['Erreur inconnue'];
            });
    }
}
</script>

<template>
    <AppLayout title="Catégories">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Catégories', href: route('categories.index'), current: true}]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Catégories</h1>
                    <p class="mt-1 text-sm text-gray-500">Organisez vos produits par catégorie</p>
                </div>
                <button @click="openCreate"
                        class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-500 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                    </svg>
                    Ajouter
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-violet-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ totalCategories }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total catégories</p>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-indigo-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-indigo-700">{{ totalProduits }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total produits</p>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-emerald-700">{{ withProduits }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Avec produits</p>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-amber-700">{{ emptyCategories }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Vides</p>
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-violet-100 text-violet-700 text-xs font-bold">
                            {{ items.length }}
                        </span>
                        <span class="text-sm text-gray-600 font-medium">catégorie{{ items.length > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-72 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items" tableStyle="min-width: 40rem"
                           v-model:filters="filters"
                           :globalFilterFields="['label']"
                           paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-violet-50 flex items-center justify-center">
                                <svg class="w-8 h-8 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucune catégorie trouvée</span>
                            <p class="text-sm text-gray-400 mt-1">Commencez par ajouter une catégorie</p>
                        </div>
                    </template>

                    <Column field="label" header="Catégorie" sortable style="min-width: 16rem">
                        <template #body="{ data, index }">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold"
                                     :class="colorClasses[getCatColor(index)]">
                                    {{ data.label.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900">{{ data.label }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="total" header="Produits" sortable style="width: 10rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 max-w-[80px]" v-if="totalProduits > 0">
                                    <div class="bg-gradient-to-r from-violet-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                                         :style="{width: Math.min((data.total / totalProduits) * 100, 100) + '%'}"></div>
                                </div>
                                <span v-if="data.total > 0"
                                      class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200 tabular-nums">
                                    {{ data.total }}
                                </span>
                                <span v-else class="text-gray-300 text-sm">0</span>
                            </div>
                        </template>
                    </Column>
                    <Column header="" style="width: 6rem">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-0.5">
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
            </div>
        </div>

        <!-- Delete modal -->
        <ConfirmationModal :show="deleteModal" @close="deleteModal = false">
            <template #title>Supprimer la catégorie</template>
            <template #content>
                Voulez-vous vraiment supprimer <span class="font-semibold">{{ item_name }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteCategory()">Supprimer</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Create/Edit Modal -->
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
                            <div v-if="showModal" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                                 @keydown.enter="submitForm">
                                <!-- Header with gradient accent -->
                                <div class="relative overflow-hidden">
                                    <div :class="[
                                        'absolute inset-0',
                                        isEditing
                                            ? 'bg-gradient-to-r from-amber-500 to-orange-500'
                                            : 'bg-gradient-to-r from-violet-600 to-purple-600'
                                    ]"></div>
                                    <div class="relative px-6 py-5 text-white">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/20 backdrop-blur-sm">
                                                    <svg v-if="!isEditing" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                                                    </svg>
                                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-semibold">
                                                        {{ isEditing ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                                                    </h3>
                                                    <p class="text-sm text-white/70">
                                                        {{ isEditing ? 'Renommez cette catégorie' : 'Créez une nouvelle catégorie de produits' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <button @click="closeModal"
                                                    class="p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="px-6 py-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nom de la catégorie <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z"/>
                                            </svg>
                                        </div>
                                        <input ref="labelInput" v-model="label" type="text"
                                               class="block w-full pl-11 pr-4 py-3 bg-white border border-gray-300 rounded-xl text-sm text-gray-900 placeholder-gray-400 transition duration-200
                                                      focus:outline-none focus:ring-2 focus:border-violet-500"
                                               :class="[
                                                   errors.length > 0 ? 'focus:ring-red-500/20 border-red-400' : 'focus:ring-violet-500/20',
                                                   'hover:border-gray-400'
                                               ]"
                                               placeholder="Ex: Médicaments, Parapharmacie..."/>
                                    </div>
                                    <div v-for="(error, i) in errors" :key="i">
                                        <InputError :message="error" class="mt-2"/>
                                    </div>

                                    <!-- Visual hint -->
                                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                        </svg>
                                        Les catégories permettent de classer et filtrer vos produits
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                                    <button type="button" @click="closeModal"
                                            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 shadow-sm hover:bg-gray-50 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="button" @click="submitForm"
                                            :disabled="processing"
                                            :class="[
                                                'inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200',
                                                isEditing
                                                    ? 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500'
                                                    : 'bg-violet-600 hover:bg-violet-700 focus:ring-violet-500',
                                                'focus:outline-none focus:ring-2 focus:ring-offset-2',
                                                processing ? 'opacity-50 cursor-not-allowed' : ''
                                            ]">
                                        <svg v-if="processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                        </svg>
                                        {{ isEditing ? 'Enregistrer' : 'Ajouter' }}
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
