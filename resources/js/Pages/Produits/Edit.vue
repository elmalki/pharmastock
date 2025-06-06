<template>
    <AppLayout title="Modifier-Produit">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <Breadcrumbs :pages="[{name:'Produits',href:route('produits.index'),current:false},{name:form.label,href:route('produits.edit',{produit:form.id}),current:true}]"></Breadcrumbs>
                </div>
            </div>
            <div class="max-w-2xl mx-auto"  @keydown.enter.prevent="">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="barcode" class="block text-sm/6 font-medium text-gray-900">Code</label>
                                <div class="mt-2">
                                    <input v-model="form.barcode" type="text" autocomplete="barcode" disabled="disabled"
                                           class="block w-full bg-gray-300 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.barcode" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="label" class="block text-sm/6 font-medium text-gray-900">Libellé</label>
                                <div class="mt-2">
                                    <input v-model="form.label" type="text" autocomplete="label"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.label" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="unite" class="block text-sm/6 font-medium text-gray-900">Unité</label>
                                <div class="mt-2">
                                    <input v-model="form.unite" type="number" autocomplete="unite"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.unite" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="limit_command" class="block text-sm/6 font-medium text-gray-900">Stock
                                    Critique</label>
                                <div class="mt-2">
                                    <input name="limit_command" v-model="form.limit_command" type="number" autocomplete="limit_command"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                                <span>
                                <InputError :message="form.errors.limit_command" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="perissable" class="block text-sm/6 font-medium text-gray-900">Périssable</label>
                                <div class="mt-2">
                                    <Switch v-model="form.perissable"
                                            :class="[form.perissable ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']">
                                        <span aria-hidden="true"
                                              :class="[form.perissable ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']"/>
                                    </Switch>

                                </div>
                                <span>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <FloatLabel class="w-full mt-4">
                                    <Select class="w-full"  v-model="form.categorie"
                                            :options="props.categories" option-label="label"  filter></Select>
                                    <label for="Catégorie">Catégorie</label>

                                </FloatLabel>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="description" class="block text-sm/6 font-medium text-gray-900">Description</label>
                                <div class="mt-2">
                                    <textarea name="email" v-model="form.description" type="text" autocomplete="description"
                                              class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"></textarea>
                                </div>
                                <span>
                                <InputError :message="form.errors.description" class="mt-2"/>
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
                        @click="submit()"
                    >
                        Modifier
                    </PrimaryButton>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {router, useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import {Switch} from '@headlessui/vue'
import Select from "primevue/select";
import FloatLabel from 'primevue/floatlabel';
import {Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions} from '@headlessui/vue'
import {CheckIcon, ChevronUpDownIcon} from '@heroicons/vue/20/solid'
import {computed} from "vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
const props = defineProps({item: Object,categories:Array})
const form = useForm({
    processing:false,
    errors: [],
    ...props.item
});
form.categorie_id = computed(()=>form.categorie?form.categorie.id:null)

const vider = () => {
    form.reset()
}
const generate = () =>{
    if (form.generated)  form.barcode='';
    form.generated = true;
    form.barcode =
        Math.floor(100000 + Math.random() * 9999999999) + "";
}
const submit = () => {
    form.patch(`/produits/${form.id}`,form);
}
const annuler = ()=>{
    router.get('/produits');
}
</script>
<style scoped>

</style>
