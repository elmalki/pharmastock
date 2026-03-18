<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import InputText from "primevue/inputtext";
import 'vuetify/styles'

const props = defineProps({user: Object, roles: Array});
props.user.roles = props.user.roles.map(el => el.id);

const form = useForm({
    ...props.user,
    password: null,
    password_confirmation: null,
});

const submit = () => {
    form.patch(route('users.update', {user: props.user.id}));
};
</script>

<template>
    <AppLayout title="Modifier Utilisateur">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Utilisateurs', href: route('users.index'), current: false},
                {name: 'Modifier', href: route('users.edit', {user: user.id}), current: true}
            ]"/>

            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Modifier l'utilisateur</h1>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <form class="max-w-2xl" @submit.prevent="submit()">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                            <InputText v-model="form.name" type="text" autocomplete="name" class="w-full"/>
                            <InputError :message="form.errors.name" class="mt-2"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <InputText v-model="form.email" type="text" autocomplete="email" class="w-full"/>
                            <InputError :message="form.errors.email" class="mt-2"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                            <InputText v-model="form.password" type="password" autocomplete="new-password" class="w-full"/>
                            <InputError :message="form.errors.password" class="mt-2"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmez le mot de passe</label>
                            <InputText v-model="form.password_confirmation" type="password" autocomplete="new-password" class="w-full"/>
                            <InputError :message="form.errors.password_confirmation" class="mt-2"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rôles</label>
                            <VAutocomplete
                                clear-on-select
                                label="Rôles"
                                class="bg-white text-lg" variant="outlined" :auto-select-first="true"
                                density="compact" v-model="form.roles" :items="roles"
                                item-title="name" item-value="id"
                                clearable chips closable-chips>
                                <template v-slot:chip="{ props, item }">
                                    <v-chip v-bind="props" :text="item.raw.name" size="large" color="amber" variant="flat" closable/>
                                </template>
                                <template v-slot:item="{ props, item }">
                                    <v-list-item v-bind="props" :title="item.raw.name"/>
                                </template>
                            </VAutocomplete>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-x-3 pt-6 border-t border-gray-200 mt-6">
                        <SecondaryButton @click="router.get(route('users.index'))">Annuler</SecondaryButton>
                        <PrimaryButton class="ms-3" :class="{'opacity-25': form.processing}" :disabled="form.processing">
                            Modifier
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
