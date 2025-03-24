<template>
<AppLayout title="Vente">
    <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
        <div class="max-w-7xl mx-auto" @keydown.enter.prevent="">
            <div class="space-y-5">
                <div class="">
                    <div class="mt-3 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="first-name"
                                   class="block text-sm/6 font-medium text-gray-900">Client</label>
                            <div class="mt-2">
                                <SelectClient
                                    @selected-client="(item)=>clientSelected(item)"></SelectClient>
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Date</label>
                            <div class="mt-2">
                                <input type="date" v-model="form.date"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="first-name" class="block text-sm/6 font-medium text-gray-900">Numéro
                                Facture</label>
                            <div class="mt-2">
                                <input type="text" v-model="form.n_facture" id="n_facture"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="first-name" class="block text-sm/6 font-medium text-gray-900">
                                Remise</label>
                            <div class="mt-2">
                                <input type="text" v-model="form.remise" id="n_facture"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Mode de
                                paiement</label>
                            <div class="mt-2">
                                <Listbox v-model="form.paiement">
                                    <div class="relative mt-1">
                                        <ListboxButton
                                            class="relative border border-1 w-full cursor-default rounded-lg bg-white py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-orange-300 sm:text-sm"
                                        >
                                            <span class="block truncate">{{ form.paiement }}</span>
                                            <span
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                                            >
            <ChevronUpDownIcon
                class="h-5 w-5 text-gray-400"
                aria-hidden="true"
            />
          </span>
                                        </ListboxButton>

                                        <transition
                                            leave-active-class="transition duration-100 ease-in"
                                            leave-from-class="opacity-100"
                                            leave-to-class="opacity-0"
                                        >
                                            <ListboxOptions
                                                class="absolute z-999 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                                            >
                                                <ListboxOption
                                                    v-slot="{ active, selected }"
                                                    v-for="item in modes"
                                                    :key="item"
                                                    :value="item"
                                                    as="template"
                                                >
                                                    <li
                                                        :class="[
                  active ? 'bg-amber-100 text-amber-900' : 'text-gray-900',
                  'relative cursor-default select-none py-2 pl-10 pr-4',
                ]"
                                                    >
                <span
                    :class="[
                    selected ? 'font-medium' : 'font-normal',
                    'block truncate',
                  ]"
                >{{ item }}</span
                >
                                                        <span
                                                            v-if="selected"
                                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600"
                                                        >
                  <CheckIcon class="h-5 w-5" aria-hidden="true"/>
                </span>
                                                    </li>
                                                </ListboxOption>
                                            </ListboxOptions>
                                        </transition>
                                    </div>
                                </Listbox>

                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="first-name" class="block text-sm/6 font-medium text-gray-900">Date
                                Echéance</label>
                            <div class="mt-2">
                                <input type="date" v-model="form.dateEcheance" id="first-name"
                                       autocomplete="given-name"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <SelectProductsOut @selected="(data)=>selectedItems(data)"></SelectProductsOut>
        <div v-for="item in form.produits" class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
            <div
                class="-ml-4 bg-amber-100 p-2 border-b border-b-cyan-500 pb-3 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap">
                <div class="ml-4 mt-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ item.label }}</h3>
                </div>
                <div class="ml-4 mt-2 shrink-0">
                    <button type="button"
                            class="relative inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Create new job
                    </button>
                </div>
            </div>
            <ul role="list" class="divide-y pt-5 bt-1 divide-gray-100">
                <li class="flex justify-between gap-x-6 py-3">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold text-gray-900">Lot</p>
                        </div>
                    </div>
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold text-gray-900">En stock</p>
                        </div>
                    </div>
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Date péremption</p>
                        </div>
                    </div>
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Quantité</p>
                        </div>
                    </div>
                </li>
                <li class="flex justify-between gap-x-6 py-3" v-for="lot in item.lots">
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">Lot N°{{ lot.n_lot }}</p>
                            <p class="mt-1 truncate text-xs/5 text-gray-500">
                                {{ lot.created_at.substring(0, 10) }}</p>
                        </div>
                    </div>
                    <div class="flex min-w-0 gap-x-4">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm/6 font-semibold text-gray-900">{{ lot.qte }}</p>
                        </div>
                    </div>
                    <div class="flex min-w-0 gap-x-4 text-center">
                        <p class="text-sm/6 font-semibold text-gray-900">{{ lot.expirationDate ?? '-' }}</p>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <input type="number" v-model="lot.sortie" :max="lot.qte" min="0" id="name"
                               class=" w-full items-center border border-2 px-3 py-1.5 text-gray-900 placeholder:text-gray-500 focus:outline focus:outline-0 sm:text-sm/6 focus:border-b-green-700">
                    </div>
                </li>
            </ul>
        </div>

        <div class="border-t border-gray-900/10  mt-6 py-3 flex items-center justify-end gap-x-6">
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

</AppLayout>
</template>
<script setup>
import {useForm} from "@inertiajs/vue3";
import {ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {Listbox, ListboxButton, ListboxOption, ListboxOptions} from "@headlessui/vue";
import {ChevronUpDownIcon} from "@heroicons/vue/20/solid/index.js";
import SelectProducts from "@/Pages/Produits/SelectProducts.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectClient from "@/Pages/Clients/SelectClient.vue";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
const client = ref(null)
const modes = ['Éspèce', 'Chèque', 'Effet', 'Virement', 'Virsement', 'En compte', 'Multi Réglement']
const situations = []
const form = useForm({
    n_facture:null,
    client_id:null,
    date:null,
    paiement:null,
    dateEcheance:null,
    montantPaye:0,
    remise:0,
    produits:[]
});
const clientSelected = (item) => {
    client.value = item;
    form.client_id = item.id;
}
const vider = () => {
    form.reset()
}
const submit = () => {
    form.post('/ventes', form);
}


function selectedItems(items) {
    items.forEach(item => {
        axios.post('/api/getLots', {id: item.id})
            .then(response => {
                response.data.forEach(el => el.sortie = 0)
                item.lots = response.data
            })
    })
    form.produits = items;
}
</script>
<style scoped>

</style>
