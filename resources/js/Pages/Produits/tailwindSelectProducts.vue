<template>
    <div class="mt-6">
        <div class="block text-sm/6 font-medium text-gray-900">Produits</div>
        <Combobox v-model="selected" multiple @change="send()" @keyup.enter.prevent="send(),query=''" as="div">
            <div class=" mt-1">
                <div
                    class="flex border border-1  w-full cursor-default overflow-hidden rounded-lg bg-white text-left  focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75 focus-visible:ring-offset-2 focus-visible:ring-offset-teal-300 sm:text-sm"
                >
                    <ComboboxInput
                        class="w-full  rounded-md border-0  py-2 pl-3 pr-10 text-sm leading-5 text-gray-900 focus:ring-0"
                        @change="change($event.target.value)"
                        id="input"
                        :ref="comboInput"
                        placeholder="Choisir les produits"
                    />
                    <ComboboxButton
                        class=" flex items-center pr-2"
                    >
                        <ChevronUpDownIcon
                            class="h-5 w-5 text-gray-400"
                            aria-hidden="true"
                        />
                    </ComboboxButton>
                </div>
                <TransitionRoot
                    leave="transition ease-in duration-100"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                    @after-leave="query = ''"
                >
                    <ComboboxOptions
                        class=" mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                    >
                        <div
                            v-if="filteredPeople.length === 0 && query !== ''"
                            class="relative cursor-default select-none px-4 py-2 text-gray-700"
                        >
                            Nothing found.
                        </div>

                        <ComboboxOption
                            v-for="person in filteredPeople"
                            as="template"
                            :key="person.id"
                            :value="person"
                            @click="send()"
                            v-slot="{ selected, active }"
                        >
                            <li
                                class="relative cursor-default select-none py-2 pl-10 pr-4"
                                :class="{
                  'bg-teal-600 text-white': active,
                  'text-gray-900': !active,
                }"
                            >
                <span
                    class="block truncate"
                    :class="{ 'font-medium': selected, 'font-normal': !selected }"
                >
                  {{ person.label }}
                </span>
                                <span
                                    v-if="selected"
                                    class="absolute inset-y-0 left-0 flex items-center pl-3"
                                    :class="{ 'text-white': active, 'text-teal-600': !active }"
                                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true"/>
                </span>
                            </li>
                        </ComboboxOption>
                    </ComboboxOptions>
                </TransitionRoot>
            </div>
        </Combobox>
        <div class="mt-10 grid grid-cols-1 gap-x-3 gap-y-3 sm:grid-cols-2">

            <ProductInput v-for="product in selected" :entree="product.entree" :id="product.id"
                          :label="product.label" @remove-item="(id)=>removeProduct(id)"></ProductInput>
        </div>
    </div>
</template>

<script setup>
import {ref, computed, watch, nextTick} from 'vue'
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue'
import {CheckIcon, ChevronUpDownIcon} from '@heroicons/vue/20/solid'
import ProductInput from "@/Pages/Produits/product-input.vue";

const emit = defineEmits(['selected'])
let produits = []
const comboInput = ref(null);
const expirationDate = {}
axios.get('/api/produits').then(response => {
    produits = response.data;
    produits.forEach((el) => {
        el.entree = {expirationDate: null, qte: null, n_lot: null, prix_achat: null, prix_vente: null};
        let id = el.label;
        expirationDate[id] = false;
    });
})
const selected = ref(produits)
const query = ref('')
const change = (val) => {
    query.value = val
}
watch(selected, (val) => {
    if (query.value.length > 11){
        nextTick(_=> {
            query.value = '';
        })
    }

})
let filteredPeople = computed(() =>
    query.value === ''
        ? produits
        : produits.filter((item) =>
            item.label
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(query.value.toLowerCase().replace(/\s+/g, ''))
        )
)
const send = () => {
    emit('selected', selected.value)
    query.value = ''
}
const removeProduct = (id)=>{
    selected.value=selected.value.filter(_=>_.id!==id)
    emit('selected', selected.value)
}
</script>
