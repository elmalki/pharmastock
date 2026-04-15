<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import PermissionSelector from "@/Pages/Roles/PermissionSelector.vue";

const props = defineProps({role: Object, permissions: Array});

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.id),
});

const annuler = () => router.get(route('roles.index'));

const submit = () => {
    form.patch(route('roles.update', {role: props.role.id}));
};
</script>

<template>
    <AppLayout title="Modifier Rôle">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Rôles', href: route('roles.index'), current: false},
                {name: role.name, href: route('roles.edit', {role: role.id}), current: true}
            ]"/>

            <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-6">Modifier le rôle</h1>

            <form @submit.prevent="submit()" class="space-y-5">
                <!-- Name card -->
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Identité du rôle</div>
                            <div class="text-xs text-gray-500">Nom affiché pour ce groupe de permissions</div>
                        </div>
                    </div>
                    <div class="p-5 max-w-xl">
                        <label for="role-name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                            Nom du rôle
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.549 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </span>
                            <input id="role-name" v-model="form.name" type="text"
                                   class="block w-full rounded-lg border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"/>
                        </div>
                        <InputError :message="form.errors.name" class="mt-2"/>
                    </div>
                </div>

                <PermissionSelector v-model="form.permissions" :permissions="permissions"/>
                <InputError :message="form.errors.permissions" class="-mt-2"/>

                <div class="flex items-center justify-end gap-x-3 pt-2">
                    <SecondaryButton @click="annuler()">Annuler</SecondaryButton>
                    <PrimaryButton class="ms-3" :class="{'opacity-25': form.processing}" :disabled="form.processing">
                        Modifier
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
