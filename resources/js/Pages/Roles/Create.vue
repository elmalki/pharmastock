<template>
    <AppLayout title="Ajout-Rôle">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-2xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900">Rôle</h1>
                </div>
            </div>
            <form class="max-w-2xl mx-auto">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Rôle</label>
                                <div class="mt-2">
                                    <input  v-model="form.name" type="text" autocomplete="email" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.name" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Permissions</label>
                                <div class="mt-2">
                                    <VAutocomplete

                                        clear-on-select
                                        label="Permissions"
                                        class="bg-white" variant="outlined" :auto-select-first="true" density="compact" v-model="form.permissions"  :items="permissions"  item-title="name" item-value="id"   multiple
                                                   clearable
                                                   chips
                                                   closable-chips>
                                        <template v-slot:chip="{ props, item }">
                                            <v-chip
                                                v-bind="props"
                                                :text="item.raw.name"
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

const props = defineProps({permissions:Array})
const form = useForm({
    name: '',
    permissions: null,
    processing:false,
    errors: []
});
const vider = ()=>{
    form.value = {
        name: '',
        permissions: null,
        processing:false,
        errors: []
    }
}
const submit = ()=>{
    form.post('/roles',form);
}
</script>
