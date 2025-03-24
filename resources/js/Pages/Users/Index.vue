<template>
    <AppLayout title="Utilisateurs">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <h1 class="text-base font-semibold text-gray-900">Utilisateur</h1>
                        <p class="mt-2 text-sm text-gray-700">
                            Une liste de tous les utilisateurs de votre application, y compris leur nom, leur e-mail,
                            leur rôle et leurs permissions.</p>
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <Link :href="route('users.create')" type="button"
                              class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Ajouter
                        </Link>
                    </div>
                </div>
                <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                        Nom
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Rôle
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Permission
                                    </th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <Link href="#" class="sr-only">Modifier</Link>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="person in items" :key="person.id">
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                        <div class="flex items-center">
                                            <div class="shrink-0">
                                                <img class="w-14 h-14 rounded-full border border-1 border-blue-500"
                                                     :src="person.profile_photo_url" alt=""/>
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900">{{ person.name }}</div>
                                                <div class="mt-1 text-gray-500">{{ person.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <span
                                            class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-md font-medium text-green-700 ring-1 ring-inset ring-green-600/20"
                                            v-for="role in person.roles" :key="role.id">{{ role.name }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                    <span v-if="person.id===1||person.roles[0]?.name==='Administrateur'"
                                          class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-md font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                            Toutes les permissions</span>
                                        <div class="grid grid-cols-3 gap-1">
                                    <span v-for="item in person.roles[0].permissions" :key="item.id"
                                          class="inline-flex justify-center items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                            {{ item.name }}</span>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <Link :href="route('users.edit',{user:person.id})" class="text-green-600 hover:text-indigo-900"
                                        >Modifier<span class="sr-only">, {{ person.name }}</span></Link
                                        >
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal :show="deleteModal" @close="deleteModal=false">
            <template #title>
                Supprimer la utilisateur
            </template>
            <template #content>
                Voulez-vous vraiment supprimer cette utilisateur?
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
import {router, Link} from '@inertiajs/vue3';
import {computed, nextTick, reactive, ref} from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({items: Array, permissions: Array});
const deleteModal = ref(false)
const item_id = ref(null)
const search = ref(null)

const form = reactive({
    name: '',
    permissions: [],
    processing: false,
    errors: []
});

function deleteCategory() {
    deleteModal.value = false;
    router.delete(`/users/${item_id.value}`);
}

</script>
<style scoped>

</style>
