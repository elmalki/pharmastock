<template>
    <AppLayout title="Stockage">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10 h-screen max-w-7xl mx-auto mb-10">
            <div class="max-w-7xl mx-auto" @keydown.enter.prevent="">
                <div class="space-y-5">
                    <div class="">
                        <div class="mt-3 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first-name"
                                       class="block text-sm/6 font-medium text-gray-900">Fournisseur</label>
                                <div class="mt-2">
                                    <SelectFournisseur
                                        @selected-fournisseur="(item)=>fournisseurSelected(item)" :fournisseur="form.fournisseur"></SelectFournisseur>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Date</label>
                                <div class="mt-2">
                                    <DatePicker  v-model="form.date"  lang="fr" date-format="yy-mm-dd" class=" h-14 w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Numéro
                                    Bon/ N° Marché</label>
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
                            <div class="sm:col-span-3" >
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Mode de
                                    paiement</label>
                                <div class="mt-2">
                                    <v-select :items="modes" v-model="form.paiement" density="compact" variant="outlined" base-color="gray"></v-select>
                                </div>
                                <!--label for="last-name" class="block text-sm/6 font-medium text-gray-900">Situation</label>
                                <div class="mt-2">
                                    <v-select :items="situations" v-model="form.situation" density="compact" variant="outlined" base-color="gray"></v-select>
                                </div-->
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
            <SelectProducts @selected="(data)=>selectedItems(data)" :produits="form.produits"></SelectProducts>
            <div class="border-t border-gray-900/10  mt-6 py-3 flex items-center justify-end gap-x-6">
                <Link :href="route('commandes.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Annuler
                </Link>
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
    </AppLayout>
</template>

<script setup>
import {ref} from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm,Link} from '@inertiajs/vue3';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SelectProducts from "@/Pages/Produits/SelectProducts.vue";
import SelectFournisseur from "@/Pages/Fournisseurs/SelectFournisseur.vue";
import DatePicker from "primevue/datepicker";
import InputText from "primevue/inputtext";
const props  =defineProps({commande:Object})
const fournisseur = ref(null)

const modes = [ 'Virement', 'Virsement']
const situations = ['Payé','En compte','Crédit']
const form = useForm({...props.commande})

const fournisseurSelected = (item) => {
    fournisseur.value = item;
    form.fournisseur_id = item.id;
}

const submit = () => {
   form.put('/commandes/'+props.commande.id,form)
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
    border-color: white !important;
}
</style>
