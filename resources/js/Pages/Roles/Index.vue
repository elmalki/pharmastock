<template>
    <AppLayout title="Rôles">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900">Rôles</h1>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Link :href="route('roles.create')"
                          class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Nouveau Rôle
                    </Link>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div>
                            <div class="relative mt-2 md:w-1/4 xs:w-1/2 sm:w-1/2">
                                <input type="text" name="name" id="name" v-model="search"
                                       class="peer block w-full border-0 bg-gray-50 py-1.5 text-gray-900 focus:ring-0 sm:text-sm/6"
                                       placeholder="Recherche"/>
                                <div
                                    class="absolute inset-x-0 bottom-0 border-t border-gray-300 peer-focus:border-t-2 peer-focus:border-indigo-600"
                                    aria-hidden="true"/>
                                <div class="absolute inset-y-0 right-0 flex py-1.5 pr-1.5">
                                    <kbd
                                        class="inline-flex items-center rounded border border-gray-200 px-1 font-sans text-xs text-gray-400">⌘K</kbd>
                                </div>
                            </div>
                        </div>
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Rôle
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Permissions
                                </th>
                                <th scope="col"
                                    class="py-3 pl-4 pr-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 sm:pl-0">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="item in filtered" :key="item.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    {{ item.name }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    <div class="grid gap-1 md:grid-cols-4 lg:grid-cols-4">
                                         <span  v-if="item.name=='Administrateur'" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-sm font-medium justify-center text-green-700 ring-1 ring-inset ring-green-600/20">
                                            Toutes les permissions</span>
                                        <span  v-for="item in item.permissions" :key="item.id" class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-sm font-medium justify-center text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                            {{ item.name }}</span>
                                    </div>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                    <Link v-if="item.id!==1"  :href="route('roles.edit',{role:item.id})"
                                           class="text-green-600 px-3 hover:text-green-700"
                                    >Modifier</Link>

                                    <button v-if="item.id!==1" @click="deleteModal=true,item_id=item.id"
                                            class="text-red-600  hover:text-red-7r00"
                                    >Supprimer<span class="sr-only">, {{ item.name }}</span></button
                                    >
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal :show="deleteModal" @close="deleteModal=false">
            <template #title>
                Supprimer la rôle
            </template>
            <template #content>
                Voulez-vous vraiment supprimer cette rôle?
            </template>
            <template #footer>
                <SecondaryButton @click="deleteModal = false">
                    Annuler
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    @click="deleteCategory()"
                >
                    Supprimer
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router,Link} from '@inertiajs/vue3';
import {computed, nextTick, reactive, ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({items: Array,permissions:Array});
const deleteModal = ref(false)
const item_id = ref(null)
const search = ref(null)
let filtered = computed(() => props.items.filter(item =>
    !search.value || item.name.toLowerCase().includes(search.value.toLowerCase())
));
const form = reactive({
    name: '',
    permissions:[],
    processing: false,
    errors: []
});
function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/roles/${item_id.value}`);
}

</script>
<style scoped>

</style>
