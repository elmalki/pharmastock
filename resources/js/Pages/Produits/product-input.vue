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
                            <div class="sm:col-span-2">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Prix d'achat(HT)</label>
                                <div class="mt-2">
                                    <InputText type="number" step="0.01" v-model="entree.prix_achat" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Prix de Vente(TTC)</label>
                                <div class="mt-2">
                                    <InputText type="number" step="0.01" v-model="entree.prix_vente" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-2" >
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">TVA %</label>
                                <div class="mt-2">
                                    <InputText type="number" v-model="entree.tva" id="n_facture"
                                               class="block w-full"/>
                                </div>
                            </div>
                            <div class="sm:col-span-6" v-if="perissable">
                                <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Date péremption{{entree.expirationDate}}</label>
                                <div class="mt-2">
                                    <DatePicker :showTime="false" v-model="entree.expirationDate" class="w-full" lang="fr" date-format="yy-mm-dd"></DatePicker>
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
import DatePicker   from "primevue/datepicker";
const props = defineProps({entree: Object,label:String,id:Number,perissable:Boolean})
//props.entree.tva = 20;
const emit = defineEmits(['removeItem'])
const remove = ()=> {
    emit('remove-item', props.id)
}
</script>
