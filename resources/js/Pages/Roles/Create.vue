<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import InputText from "primevue/inputtext";
import MultiSelect from "primevue/multiselect";

const props = defineProps({permissions: Array});

const form = useForm({
    name: '',
    permissions: [],
});

const vider = () => form.reset();

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <AppLayout title="Nouveau Rôle">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Rôles', href: route('roles.index'), current: false},
                {name: 'Nouveau Rôle', href: route('roles.create'), current: true}
            ]"/>

            <h1 class="text-2xl font-bold text-gray-900 mt-4 mb-6">Nouveau Rôle</h1>

            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <form @submit.prevent="submit()">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du rôle</label>
                                <InputText v-model="form.name" class="w-full" placeholder="Ex: Pharmacien"/>
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
                            <SecondaryButton @click="vider()">Vider</SecondaryButton>
                            <PrimaryButton class="ms-3" :class="{'opacity-25': form.processing}" :disabled="form.processing">
                                Ajouter
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
