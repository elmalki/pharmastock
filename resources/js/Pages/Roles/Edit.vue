<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";

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

            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <form @submit.prevent="submit()">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du rôle</label>
                                <InputText v-model="form.name" class="w-full"/>
                                <InputError :message="form.errors.name" class="mt-2"/>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permissions</label>
                                <MultiSelect v-model="form.permissions" :options="permissions"
                                             optionLabel="name" optionValue="id"
                                             filter placeholder="Sélectionner les permissions"
                                             display="chip" class="w-full"/>
                                <InputError :message="form.errors.permissions" class="mt-2"/>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-x-3 pt-6 border-t border-gray-200 mt-6">
                            <SecondaryButton @click="annuler()">Annuler</SecondaryButton>
                            <PrimaryButton class="ms-3" :class="{'opacity-25': form.processing}" :disabled="form.processing">
                                Modifier
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
