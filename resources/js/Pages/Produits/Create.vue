<template>
    <AppLayout title="Ajout-Produit">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <Breadcrumbs :pages="[{name:'Produits',href:route('produits.index'),current:false},{name:'Nouveau Produit',href:route('produits.create'),current:true}]"></Breadcrumbs>
            </div>
            <div class="max-w-2xl mx-auto"  @keydown.enter.prevent="">
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="mt-10  gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <div class="mt-2">
                                    <button class="px-3 my-3 py-1 min-w-fit bg-teal-500 text-white rounded-lg hover:bg-teal-600" @click="generate()"  @submit.prevent="">Générer</button>
                                    <InputText class="w-full" v-model="form.barcode" placeholder="Code à a barre"></InputText>
                                </div>
                                <span>
                                <InputError :message="form.errors.label" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="label" class="block text-sm/6 font-medium text-gray-900">Libellé</label>
                                <div class="mt-2">
                                    <InputText class="w-full" v-model="form.label"></InputText>
                                </div>
                                <span>
                                <InputError :message="form.errors.label" class="mt-2"/>
                            </span>
                            </div>
                            <div class="sm:col-span-4">
                                <label for="unite" class="block text-sm/6 font-medium text-gray-900">Unité</label>
                                <div class="mt-2">
                                    <InputText class="w-full" v-model="form.unite" type="number"></InputText>

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
                                <label for="categorie" class="block text-sm/6 font-medium text-gray-900">Catégorie</label>
                                <Listbox as="div" v-model="form.categorie">
                                    <div class="relative mt-2">
                                        <ListboxButton
                                            class="relative w-full h-10 cursor-default rounded-md bg-white py-1.5 pl-3 pr-10 text-left text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-600 sm:text-sm/6">
                                            <span class="block truncate">{{ form.categorie?.label }}</span>
                                            <span
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronUpDownIcon class="size-5 text-gray-400" aria-hidden="true"/>
        </span>
                                        </ListboxButton>

                                        <transition leave-active-class="transition ease-in duration-100"
                                                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                                            <ListboxOptions
                                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm">
                                                <ListboxOption as="template" v-for="categorie in props.categories" :key="categorie.id"
                                                               :value="categorie" v-slot="{ active, selected }">
                                                    <li :class="[active ? 'bg-indigo-600 text-white' : 'text-gray-900', 'relative cursor-default select-none py-2 pl-3 pr-9']">
                                                    <span
                                                        :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{
                                                            categorie.label
                                                        }}</span>

                                                        <span v-if="selected"
                                                              :class="[active ? 'text-white' : 'text-indigo-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                <CheckIcon class="size-5" aria-hidden="true"/>
              </span>
                                                    </li>
                                                </ListboxOption>
                                            </ListboxOptions>
                                        </transition>
                                    </div>
                                </Listbox>
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
                    <SecondaryButton @click="vider()">
                        Vider
                    </SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="submit()"
                    >
                        Ajouter
                    </PrimaryButton>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import {Switch} from '@headlessui/vue';
import InputText from 'primevue/inputtext';
import {Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions} from '@headlessui/vue'
import {CheckIcon, ChevronUpDownIcon} from '@heroicons/vue/20/solid'
import {computed} from "vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
const props = defineProps({categories:Array})
const form = useForm({
    label: '',
    description: '',
    perissable: false,
    unite: 1,
    limit_command: '',
    categorie: null,
    processing: false,
    generated:false,
    errors: []
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
    form.post('/produits', form);
}
</script>
<style scoped>

</style>
