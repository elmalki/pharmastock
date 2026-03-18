<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import {ref, computed, nextTick} from "vue";
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

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
});

// Stats
const totalClients = computed(() => props.items.length);
const withVentes = computed(() => props.items.filter(c => c.ventes_count > 0).length);
const withoutVentes = computed(() => props.items.filter(c => c.ventes_count === 0).length);
const totalVentes = computed(() => props.items.reduce((s, c) => s + (c.ventes_count ?? 0), 0));

// Modal state
const showModal = ref(false);
const editingItem = ref(null);
const isEditing = computed(() => editingItem.value !== null);
const nomInput = ref(null);

const form = useForm({
    nom: '',
    tel: '',
});

function openCreate() {
    editingItem.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
    nextTick(() => nomInput.value?.focus());
}

function openEdit(client) {
    editingItem.value = client;
    form.nom = client.nom || '';
    form.tel = client.tel || '';
    form.clearErrors();
    showModal.value = true;
    nextTick(() => nomInput.value?.focus());
}

function closeModal() {
    showModal.value = false;
    editingItem.value = null;
}

function submitForm() {
    if (isEditing.value) {
        form.patch(route('clients.update', {client: editingItem.value.id}), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('clients.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
}

function confirmDelete(client) {
    item_id.value = client.id;
    item_name.value = client.nom;
    deleteModal.value = true;
}

function deleteClient() {
    deleteModal.value = false;
    router.delete(route('clients.destroy', {client: item_id.value}));
}
</script>

<template>
    <AppLayout title="Clients">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Clients', href: route('clients.index'), current: true}]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
                    <p class="mt-1 text-sm text-gray-500">Gérez votre portefeuille clients</p>
                </div>
                <button @click="openCreate"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                    </svg>
                    Ajouter
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ totalClients }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total clients</p>
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
                        <p class="text-3xl font-bold text-emerald-700">{{ withVentes }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Clients actifs</p>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-amber-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-amber-700">{{ withoutVentes }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Sans achat</p>
                    </div>
                </div>

                <div class="relative bg-white rounded-2xl p-5 shadow-sm border border-gray-200 overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-violet-50 to-transparent rounded-bl-[4rem] -mr-2 -mt-2"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-lg shadow-violet-500/25 mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold text-violet-700">{{ totalVentes }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total ventes</p>
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-200 bg-gray-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-blue-100 text-blue-700 text-xs font-bold">
                            {{ items.length }}
                        </span>
                        <span class="text-sm text-gray-600 font-medium">client{{ items.length > 1 ? 's' : '' }}</span>
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
                           :globalFilterFields="['nom', 'tel']"
                           paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <div class="w-16 h-16 mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-500">Aucun client trouvé</span>
                            <p class="text-sm text-gray-400 mt-1">Commencez par ajouter un client</p>
                        </div>
                    </template>

                    <Column field="nom" header="Nom" sortable style="min-width: 14rem">
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-xs font-bold text-white shadow-sm">
                                    {{ data.nom.substring(0, 2).toUpperCase() }}
                                </div>
                                <span class="font-medium text-gray-900">{{ data.nom }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="tel" header="Téléphone" sortable>
                        <template #body="{ data }">
                            <div v-if="data.tel" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                </svg>
                                <span class="text-gray-700 tabular-nums">{{ data.tel }}</span>
                            </div>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>
                    <Column field="ventes_count" header="Ventes" sortable style="width: 8rem">
                        <template #body="{ data }">
                            <span v-if="data.ventes_count > 0"
                                  class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">
                                {{ data.ventes_count }}
                            </span>
                            <span v-else class="text-gray-300">0</span>
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
            <template #title>Supprimer ce client</template>
            <template #content>
                Voulez-vous vraiment supprimer <span class="font-semibold">{{ item_name }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteClient()">Supprimer</DangerButton>
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
                            <div v-if="showModal" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                                <!-- Header -->
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <div :class="[
                                            'flex items-center justify-center w-10 h-10 rounded-xl',
                                            isEditing ? 'bg-amber-100' : 'bg-blue-100'
                                        ]">
                                            <svg v-if="!isEditing" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ isEditing ? 'Modifier le client' : 'Nouveau client' }}
                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                {{ isEditing ? 'Modifiez les informations du client' : 'Ajoutez un nouveau client' }}
                                            </p>
                                        </div>
                                    </div>
                                    <button @click="closeModal"
                                            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Body -->
                                <div class="px-6 py-5 space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                            Nom <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                                </svg>
                                            </div>
                                            <input ref="nomInput" v-model="form.nom" type="text"
                                                   class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                                                   placeholder="Nom du client"/>
                                        </div>
                                        <InputError :message="form.errors.nom" class="mt-1.5"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                                </svg>
                                            </div>
                                            <input v-model="form.tel" type="text"
                                                   class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                                                   placeholder="06 00 00 00 00"/>
                                        </div>
                                        <InputError :message="form.errors.tel" class="mt-1.5"/>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                                    <button type="button" @click="closeModal"
                                            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 shadow-sm hover:bg-gray-50 transition-colors">
                                        Annuler
                                    </button>
                                    <button type="button" @click="submitForm"
                                            :disabled="form.processing"
                                            :class="[
                                                'inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200',
                                                isEditing ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700',
                                                'focus:outline-none focus:ring-2 focus:ring-offset-2',
                                                isEditing ? 'focus:ring-amber-500' : 'focus:ring-blue-500',
                                                form.processing ? 'opacity-50 cursor-not-allowed' : ''
                                            ]">
                                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
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
