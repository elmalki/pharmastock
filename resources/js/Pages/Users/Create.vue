<template>
    <AppLayout title="Ajout-Utilisateur">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-2xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900">Utilisateur</h1>
                </div>
            </div>
            <form class="max-w-2xl mx-auto" @submit.prevent="submit()">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Nom</label>
                                <div class="mt-2">
                                    <input v-model="form.name" type="text" autocomplete="name"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.name" class="mt-2"/>
                            </span>
                                <div class="sm:col-span-4">
                                    <label for="email"
                                           class="block text-sm/6 font-medium text-gray-900">Email</label>
                                    <div class="mt-2">
                                        <input v-model="form.email" type="text" autocomplete="email"
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                    </div>
                                    <span>
                                <InputError :message="form.errors.email" class="mt-2"/>
                            </span>
                                </div>
                                <div class="sm:col-span-4">
                                    <label for="password"
                                           class="block text-sm/6 font-medium text-gray-900">Mot
                                        de passe</label>
                                    <div class="mt-2">
                                        <input v-model="form.password" type="password" required
                                               autocomplete="new-password"
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                        <InputError class="mt-2" :message="form.errors.password"/>
                                    </div>
                                </div>
                                <div class="sm:col-span-4">
                                    <label for="password_confirmation"
                                           class="block text-sm/6 font-medium text-gray-900">Confirmez le mot de
                                        passe</label>
                                    <div class="mt-2">
                                        <TextInput
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="block w-full border-1 border border-slate-500"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password_confirmation"/>
                                    </div>
                                </div>

                                <div class="sm:col-span-4">
                                    <label for="Rôles"
                                           class="block text-sm/6 font-medium text-gray-900">Rôles</label>
                                    <div class="mt-2">
                                        <VAutocomplete
                                            clear-on-select
                                            label="Rôles"
                                            class=" bg-white text-lg" variant="outlined" :auto-select-first="true"
                                            density="compact" v-model="form.roles" :items="roles"
                                            item-title="name" item-value="id"
                                            clearable
                                            chips
                                            closable-chips>
                                            <template v-slot:chip="{ props, item }">
                                                <v-chip
                                                    v-bind="props"
                                                    :text="item.raw.name"
                                                    size="large"
                                                    color="amber" variant="flat"
                                                    closable
                                                ></v-chip>
                                            </template>
                                            <template v-slot:item="{ props, item }">
                                                <v-list-item
                                                    v-bind="props"
                                                    :title="item.raw.name"
                                                ></v-list-item>
                                            </template>
                                        </VAutocomplete>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <SecondaryButton @click="vider()">
                        Vider
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"

                    >
                        Ajouter
                    </PrimaryButton>
                </div>
            </form>

        </div>
    </AppLayout>
</template>

<script setup>
import 'vuetify/styles'
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";

const props = defineProps({roles: Array})
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: null,
    processing: false,
    errors: []
});
const vider = () => {
    form.reset();
}
const submit = () => {
    form.post('/users', form);
}
</script>
<style scoped>

</style>
