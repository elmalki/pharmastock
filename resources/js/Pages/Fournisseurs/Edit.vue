<template>
    <AppLayout title="Modification-Fournisseur">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-2xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900">Fournisseurs</h1>
                </div>
            </div>
            <form class="max-w-2xl mx-auto" @submit.prevent="submit()" @keydown.enter="submit()">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <FloatLabel variant="on">
                                    <InputText v-model="form.societe" class="w-full"></InputText>
                                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Société</label>
                                </FloatLabel>
                                <span>
                                <InputError :message="form.errors.societe" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Contact</label>
                                <div class="mt-2">
                                    <InputText v-model="form.contact" class="w-full"></InputText>
                                </div>
                                <span>
                                <InputError :message="form.errors.contact" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Adresse</label>
                                <div class="mt-2">
                                    <InputText v-model="form.adresse" class="w-full"></InputText>
                                </div>
                                <span>
                                <InputError :message="form.errors.adresse" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm/6 font-medium text-gray-900">Email</label>
                                <div class="mt-2">
                                    <InputText type="email" v-model="form.email" class="w-full"></InputText>
                                </div>
                                <span>
                                <InputError :message="form.errors.email" class="mt-2"/>
                            </span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <SecondaryButton @click="annuler()">
                        Annuler
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"

                    >
                        Modifier
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
import InputText from "primevue/inputtext";
import FloatLabel from "primevue/floatlabel";

const props = defineProps({item: Array});

const form = useForm({
    processing:false,
    errors: [],
    ...props.item
});
const vider = ()=>{
    form.value = {
        societe: '',
        contact: '',
        adresse: '',
        email: '',
        errors: []
    }
}
const submit = ()=>{
    form.patch(`/fournisseurs/${form.id}`,form);
}
const annuler = ()=>{
    router.get('/fournisseurs');
}
</script>
<style scoped>

</style>
