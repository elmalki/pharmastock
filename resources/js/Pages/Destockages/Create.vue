<template>
    <AppLayout title="Modifier-Produit">
        <div class="max-w-6xl mx-auto mt-4">
            <InputText v-model="form.n_destockage" type="text" class="my-2 w-full" placeholder="N°"/>
            <InputText v-model="form.fonctionnaire" type="text" class="my-2 w-full" placeholder="Fonctionnaire"/>
            <SelectProductsOut @selected="(data)=>selectedItems(data)"></SelectProductsOut>
            <Editor v-model="form.motifs" :modules="modules" editorStyle="height: 320px" />
            <div v-for="item in form.produits" class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                <div
                    class="-ml-4 bg-amber-100 p-2 border-b border-b-cyan-500 pb-3 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap">
                    <div class="ml-4 mt-2">
                        <h3 class="text-base font-semibold text-gray-900">{{ item.label }}</h3>
                    </div>
                    <div class="ml-4 mt-2 shrink-0">
                        <!--button type="button"
                                class="relative inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Create new job
                        </button-->
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
            <div class="mt-6 flex items-center justify-end gap-x-6" v-show="form.produits?.length">
                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="submit()"
                >
                    Valider
                </PrimaryButton>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {useForm} from "@inertiajs/vue3";
import SelectProductsOut from "@/Pages/Produits/SelectProductsOut.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputText from "primevue/inputtext";
import Editor from 'primevue/editor';
const props = defineProps({number:Number})
const form = useForm({motifs:'',fonctionnaire:'',n_destockage:props.number,produits:[]})
const modules = {
    toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
        [{ 'direction': 'rtl' }],                         // text direction

        [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

        [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
        [{ 'font': [] }],
        [{ 'align': [] }],
    ],
};

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
function submit(){
    form.post('/destockages',form)
}
</script>
<style scoped>
</style>
