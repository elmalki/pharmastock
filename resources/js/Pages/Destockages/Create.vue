<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from "@inertiajs/vue3";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import InputText from "primevue/inputtext";
import Editor from 'primevue/editor';

const props = defineProps({number: Number});

const form = useForm({
    motifs: '',
    fonctionnaire: '',
    n_destockage: props.number,
    produits: [],
});

const modules = {
    toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{'list': 'ordered'}, {'list': 'bullet'}, {'list': 'check'}],
        [{'indent': '-1'}, {'indent': '+1'}],
        [{'direction': 'rtl'}],
        [{'size': ['small', false, 'large', 'huge']}],
        [{'header': [1, 2, 3, 4, 5, 6, false]}],
        [{'color': []}, {'background': []}],
        [{'font': []}],
        [{'align': []}],
    ],
};

function selectedItems(items) {
    items.forEach(item => {
        if (!item.lots) {
            axios.post('/api/getLots', {id: item.id}).then(response => {
                response.data.forEach(el => el.sortie = 0);
                item.lots = response.data;
            });
        }
    });
    form.produits = items;
}

function submit() {
    form.post(route('destockages.store'));
}
</script>

<template>
    <AppLayout title="Nouveau Destockage">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Destockages', href: route('destockages.index'), current: false},
                {name: 'Nouveau Destockage', href: route('destockages.create'), current: true}
            ]"/>

            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Nouveau Destockage</h1>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N°</label>
                        <InputText v-model="form.n_destockage" type="text" class="w-full" placeholder="N°"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fonctionnaire</label>
                        <InputText v-model="form.fonctionnaire" type="text" class="w-full" placeholder="Fonctionnaire"/>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produits</label>
                    <SelectProductsOut @selected="selectedItems"/>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motifs</label>
                    <Editor v-model="form.motifs" :modules="modules" editorStyle="height: 320px"/>
                </div>
            </div>

            <!-- Selected products with lots -->
            <div v-for="item in form.produits" :key="item.id"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-6">
                <div class="flex items-center justify-between px-6 py-3 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">{{ item.label }}</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    <div class="grid grid-cols-4 gap-4 px-6 py-2 bg-gray-50 border-b border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lot</p>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">En stock</p>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Péremption</p>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantité</p>
                    </div>
                    <div v-for="lot in item.lots" :key="lot.id"
                         class="grid grid-cols-4 gap-4 px-6 py-3 items-center hover:bg-gray-50/50">
                        <div>
                            <span class="font-mono text-sm font-medium text-gray-700">{{ lot.n_lot }}</span>
                            <p class="mt-0.5 text-xs text-gray-400">{{ lot.created_at?.substring(0, 10) }}</p>
                        </div>
                        <span class="text-sm tabular-nums" :class="lot.qte <= 5 ? 'text-red-500 font-medium' : 'text-gray-700'">{{ lot.qte }}</span>
                        <span class="text-sm text-gray-700">{{ lot.expirationDate?.substring?.(0, 10) ?? lot.expirationDate ?? '—' }}</span>
                        <div>
                            <InputText type="number" v-model.number="lot.sortie" :max="lot.qte" min="0" class="w-full" size="small"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action button row -->
            <div v-show="form.produits?.length"
                 class="flex items-center justify-end gap-x-3 pt-6 border-t border-gray-200 mt-6">
                <PrimaryButton :class="{'opacity-25': form.processing}" :disabled="form.processing" @click="submit()">
                    Valider
                </PrimaryButton>
            </div>
        </div>
    </AppLayout>
</template>
