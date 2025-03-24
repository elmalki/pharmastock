<template>
    <AppLayout title="Ajout-Fournisseur">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-6xl mx-auto">
            <Breadcrumbs :pages="[{name:'Clients',href:route('clients.index'),current:false},{name:'Nouveau Client',href:route('clients.create'),current:true}]"></Breadcrumbs>
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                </div>
            </div>
            <form class="max-w-2xl mx-auto" @submit.prevent="submit()" @keydown.enter="submit()">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Nom</label>
                                <div class="mt-2">
                                    <input v-model="form.nom" type="text" autocomplete="email"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.nom" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Téléphone</label>
                                <div class="mt-2">
                                    <input v-model="form.tel" type="text" autocomplete="email"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.tel" class="mt-2"/>
                            </span>
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
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";

const form = useForm({
    nom: '',
    tel: '',
    processing: false,
    errors: []
});
const vider = () => {
    form.value = {
        societe: '',
        contact: '',
        adresse: '',
        email: '',
        errors: []
    }
}
const submit = () => {
    form.post('/clients', form);
}
</script>
<style scoped>

</style>
