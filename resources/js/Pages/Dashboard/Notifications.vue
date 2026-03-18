<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {TrashIcon} from "@heroicons/vue/24/outline/index.js";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const props = defineProps({items: Array});

function deleteItem(id) {
    router.delete('/notifications/' + id);
}

function deleteAll() {
    router.delete('/notifications');
}
</script>

<template>
    <AppLayout title="Notifications">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[{name: 'Notifications', href: '#', current: true}]"/>

            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                <button v-if="items.length > 0"
                        class="inline-flex items-center gap-x-1.5 rounded-md bg-rose-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-600 transition-colors"
                        @click="deleteAll">
                    <TrashIcon class="w-4 h-4"/>
                    Tout supprimer
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <DataTable :value="items" tableStyle="min-width: 50rem"
                           paginator :rows="10" :rowsPerPageOptions="[5, 10, 20]"
                           stripedRows rowHover>
                    <template #empty>
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            <span class="text-base font-medium">Aucune notification</span>
                        </div>
                    </template>

                    <Column field="data.title" header="Titre" sortable>
                        <template #body="{ data }">
                            <span class="font-medium text-gray-900">{{ data.data?.title }}</span>
                        </template>
                    </Column>
                    <Column field="data.message" header="Message">
                        <template #body="{ data }">
                            <span class="text-sm text-gray-700">{{ data.data?.message }}</span>
                        </template>
                    </Column>
                    <Column header="Date" sortable field="created_at" style="width: 10rem">
                        <template #body="{ data }">
                            <span class="text-sm text-gray-500 tabular-nums">{{ data.created_at?.substring(0, 10) }}</span>
                        </template>
                    </Column>
                    <Column header="" style="width: 4rem">
                        <template #body="{ data }">
                            <button @click="deleteItem(data.id)"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50"
                                    title="Supprimer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
