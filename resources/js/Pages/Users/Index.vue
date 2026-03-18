<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, Link} from '@inertiajs/vue3';
import {ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
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

function confirmDelete(user) {
    item_id.value = user.id;
    item_name.value = user.name;
    deleteModal.value = true;
}

function deleteUser() {
    deleteModal.value = false;
    router.delete(route('users.destroy', {user: item_id.value}));
}
</script>

<template>
    <AppLayout title="Utilisateurs">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Utilisateurs', href: route('users.index'), current: true}]"/>

            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Utilisateurs</h1>
                <Link :href="route('users.create')"
                      class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
                    </svg>
                    Ajouter
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <span class="text-sm text-gray-500">{{ items.length }} utilisateur{{ items.length > 1 ? 's' : '' }}</span>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <InputText v-model="filters['global'].value" placeholder="Rechercher..." class="pl-9 w-64 text-sm"/>
                    </div>
                </div>

                <DataTable :value="items" tableStyle="min-width: 50rem"
                           v-model:filters="filters"
                           :globalFilterFields="['name', 'email']"
                           paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                            <span class="text-base font-medium">Aucun utilisateur trouvé</span>
                        </div>
                    </template>

                    <Column header="Nom" sortable field="name">
                        <template #body="{ data }">
                            <div class="flex items-center">
                                <img class="w-10 h-10 rounded-full border border-gray-200"
                                     :src="data.profile_photo_url" alt=""/>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ data.name }}</div>
                                    <div class="text-xs text-gray-500">{{ data.email }}</div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column header="Rôle">
                        <template #body="{ data }">
                            <span v-for="role in data.roles" :key="role.id"
                                  class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                {{ role.name }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Permissions">
                        <template #body="{ data }">
                            <span v-if="data.id === 1 || data.roles[0]?.name === 'Administrateur'"
                                  class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                Toutes les permissions
                            </span>
                            <div v-else class="flex flex-wrap gap-1">
                                <span v-for="perm in data.roles[0]?.permissions" :key="perm.id"
                                      class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    {{ perm.name }}
                                </span>
                            </div>
                        </template>
                    </Column>
                    <Column header="" style="width: 5rem">
                        <template #body="{ data }">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('users.edit', {user: data.id})"
                                      class="p-1.5 rounded-md text-gray-400 hover:text-green-600 hover:bg-green-50"
                                      title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                    </svg>
                                </Link>
                                <button v-if="data.id !== 1" @click="confirmDelete(data)"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50"
                                        title="Supprimer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <ConfirmationModal :show="deleteModal" @close="deleteModal = false">
            <template #title>Supprimer cet utilisateur</template>
            <template #content>
                Voulez-vous vraiment supprimer <span class="font-semibold">{{ item_name }}</span> ?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">Annuler</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteUser()">Supprimer</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
