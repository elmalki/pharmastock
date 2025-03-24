<template>
    <AppLayout title="Stockage">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto">
            <div class="max-w-7xl mx-auto" @keydown.enter.prevent="">
                <div class="space-y-5">
                    <div class="">
                        <div class="mt-3 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first-name"
                                       class="block text-sm/6 font-medium text-gray-900">Fournisseur</label>
                                <div class="mt-2">
                                    <SelectFournisseur
                                        @selected-fournisseur="(item)=>fournisseurSelected(item)"></SelectFournisseur>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Date</label>
                                <div class="mt-2">
                                    <DatePicker  v-model="form.date"  lang="fr" date-format="yy-mm-dd" class="h-14 w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Bon de commande/N°Marché
                                    </label>
                                <div class="mt-2">
                                    <InputText v-model="form.n_bon" type="text" class="w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="first-name" class="block text-sm/6 font-medium text-gray-900">N°
                                    Facture/Engagement</label>
                                <div class="mt-2">
                                    <InputText type="text" v-model="form.n_facture" id="n_facture"
                                           class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Mode de
                                    paiement</label>
                                <div class="mt-2">
                                    <VAutocomplete :items="modes" v-model="form.paiement" variant="outlined" density="compact"></VAutocomplete>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="first-name" class="block text-sm/6 font-medium text-gray-900">Date
                                    Echéance</label>
                                <div class="mt-2">
                                    <DatePicker  v-model="form.dateEcheance"  lang="fr" date-format="yy-mm-dd" class="w-full"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <SelectProducts @selected="(data)=>selectedItems(data)"></SelectProducts>

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
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {ref} from "vue";
import SelectProducts from "@/Pages/Produits/SelectProducts.vue";
import SelectFournisseur from "@/Pages/Fournisseurs/SelectFournisseur.vue";
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
const fournisseur = ref(null)

const modes = [ 'Virement', 'Virsement']
const situations = []
const form = useForm({
    fournisseur_id: null,
    n_bon: null,
    paiement: 'Virement',
    situation: 'Payé',
    n_facture: null,
    date: null,
    dateEcheance: null,
    description: '',
    produits: [],
    processing: false,
    errors: []
});
const fournisseurSelected = (item) => {
    fournisseur.value = item;
    form.fournisseur_id = item.id;
}
const vider = () => {
    form.reset()
}
const submit = () => {
    form.post('/commandes', form);
}

function selectedItems(items) {
    form.produits = items;
}

</script>
<style>
#headlessui-listbox-options-10 {
    z-index: 999;
}

input:focus {
    --tw-ring-color: white !important;
    --tw-shadow-colored: white !important;
}
</style>
