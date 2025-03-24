<template>
    <div class=" my-1 border-gray-200  bg-white ">
        <div class=" -mt-2 flex flex-wrap pb-3 items-center bg-amber-400   justify-between sm:flex-nowrap">
            <div class="ml-4 mt-2">
                <h3 class="text-base font-semibold ">{{ label }}</h3>
            </div>
            <div class="ml-4 mt-2 shrink-0">
                <v-btn
                    class="ma-2"
                    color="red-darken-1"
                    @click="remove()"
                >
                    Supprimer
                    <v-icon icon="mdi-close-circle" size="x-large" end></v-icon>
                </v-btn>
            </div>
        </div>
            <div class="border border-1 max-w-7xl mx-auto mx-4" @keydown.enter.prevent="">
                <div class="space-y-2">
                    <div class="pb-12 p-3">
                        <div class="mt-3 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Qantité</label>
                                <div class="mt-2">
                                    <InputText type="number" v-model="entree.qte" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Lot</label>
                                <div class="mt-2">
                                    <InputText type="text" v-model="entree.n_lot" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Prix d'achat(HT)</label>
                                <div class="mt-2">
                                    <InputText type="number" step="0.01" v-model="entree.prix_achat" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3" >
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">TVA %</label>
                                <div class="mt-2">
                                    <InputText type="number" v-model="entree.tva" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-3" v-if="perissable">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Date péremption</label>
                                <div class="mt-2">
                                    <input type="date" v-model="entree.expirationDate" id="last-name" autocomplete="family-name"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
        </div>
    </div>
</template>
<script setup>
import InputText from "primevue/inputtext";

const props = defineProps({entree: Object,label:String,id:Number,perissable:Boolean})
//props.entree.tva = 20;
const emit = defineEmits(['removeItem'])
const remove = ()=> {
    emit('remove-item', props.id)
}
</script>
