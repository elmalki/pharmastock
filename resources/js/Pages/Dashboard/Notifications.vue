<template>
    <AppLayout title="Notifications">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="uppercase text-base font-semibold text-gray-900">Notifications</h1>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="flex flex-row">

                            <button class="flex uppercase  px-2  bg-rose-500 hover:bg-rose-600 rounded text-white" @click="downloadFiles">
                                Supprimer toutes les notifcations
                                <TrashIcon class="size-5"></TrashIcon>
                            </button>

                        </div>
                        <table class="table-fixed min-w-full mt-2 divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Titre
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Message
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Date
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Action
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="item in items" :key="item.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{item.data.title}}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{item.data.message}}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.created_at.substring(0,10) }}
                                </td>
                                <td class="relative whitespace-nowrap items-start py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                    <button @click="deleteItem(item.id)"
                                            class="text-red-600  hover:text-red-7r00"
                                    >Supprimer</button
                                    >
                                </td>

                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {TrashIcon} from "@heroicons/vue/24/outline/index.js";
import {ref} from "vue";
import {router} from "@inertiajs/vue3";
const deleteModal = ref(false)
const item_id = ref(null)
const props = defineProps({items: Array});
function deleteItem(id){
    router.delete('/notifications/'+id)
}
</script>
<style scoped>

</style>
