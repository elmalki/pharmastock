<template>
    <AppLayout title="Dashboard">
        <div class="px-4 sm:px-6 lg:px-8 bg-transparent py-10  max-w-7xl mx-auto">
            <div class="sm:flex sm:items-center">
                <Breadcrumbs class="mb-4"
                             :pages="[{name:'Approvisionnement',href:route('commandes.index'),current:false},{name:'Recherche',href:route('commandes.search'),current:true}]"></Breadcrumbs>
            </div>
            <div @keyup.enter="search()" class="mx-auto mt-5 max-w-5xl flex flex-col gap-4">
                <div class="">
                    <div class="w-full">
                        <label for="first-name"
                               class="block text-sm/6 font-medium text-gray-900">Fournisseur</label>
                        <div class="mt-2">
                            <Select v-model="form.founisseur_id" :options="fournisseurs" option-label="societe"
                                    option-value="id" placeholder="Fournisseur" class="w-full" filter>

                            </Select>
                        </div>
                    </div>
                </div>
                <div class="flex gap-1 mt-3">
                    <FloatLabel class="w-full">
                        <InputText v-model="form.n_bon" type="text" class="w-full"/>
                        <label>N° Bon de commande/N°Marché</label>
                        <Message v-if="errors.n_bon" severity="error" size="small" variant="simple">{{
                                errors.n_bon
                            }}
                        </Message>
                    </FloatLabel>
                    <FloatLabel class="w-full">
                        <InputText v-model="form.n_facture" type="text" class="w-full"/>
                        <label>N° Facture/Engagement</label>
                        <Message v-if="errors.n_facture" severity="error" size="small" variant="simple">{{
                                errors.n_facture
                            }}
                        </Message>
                    </FloatLabel>
                </div>

                <div class="flex gap-1 mt-3">
                    <FloatLabel class="w-full">
                        <DatePicker v-model="form.startDate" lang="" date-format="yy-mm-dd" class="w-full"/>
                        <label for="dateDébut">Date Début</label>
                    </FloatLabel>
                    <FloatLabel class="w-full">
                        <DatePicker v-model="form.endDate" lang="" date-format="yy-mm-dd"
                                    class="w-full"/>
                        <label for="dateFin">Date Fin</label>
                    </FloatLabel>
                </div>
                <div class="flex justify-end">
                    <SecondaryButton @click="form.reset()" >Vider</SecondaryButton>
                    <PrimaryButton @click="search()"
                                   class="mx-2 bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-700">
                        Recherche
                    </PrimaryButton>
                </div>
            </div>
        </div>
        <div class=" bg-transparent pb-10 h-screen max-w-8xl mx-auto" v-if="items?.data.length">
            <DataTable :value="items.data" tableStyle="min-width: 50rem">
                <template #empty>
                    <div class="w-full flex text-lg justify-center items-center text-red-500 font-bold">Aucune
                        commande trouvée
                    </div>
                </template>
                <Column field="n_facture" header="N° Facture" sortable></Column>
                <Column field="n_bon" header="N° Bon/Marché" sortable></Column>
                <Column field="fournisseur.societe" header="Fournisseur"></Column>
                <Column header="Date" sortable sort-field="date">
                    <template #body="slotProps">
                        {{ slotProps.data.date.split("-").reverse().join("-") }}
                    </template>
                </Column>
                <Column field="paiement" header="Mode" sortable></Column>
                <Column field="dateEcheance" header="Date d'écheance" sortable></Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-1">
                            <Link :href="route('commandes.show',{commande:slotProps.data.id})"
                                  class="text-sky-600 px-3 hover:text-sky-700"
                            >Détail<span class="sr-only">, {{ slotProps.data.label }}</span>
                            </Link>
                            <Link :href="route('commandes.edit',{commande:slotProps.data.id})"
                                  class="text-green-600 px-3 hover:text-green-700">
                                Modifier<span class="sr-only">, {{ slotProps.data.label }}</span>
                            </Link>
                            <button @click="deleteModal=true,item_id=slotProps.data.id"
                                    class="text-red-600  hover:text-red-7r00">Supprimer<span
                                class="sr-only">, {{ slotProps.data.label }}</span></button>
                        </div>
                    </template>
                </Column>
            </DataTable>
            <div class="card">
                <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                           @page="event=>search((1+event.page))">
                    <template #start="slotProps">
                    </template>
                    <template #end>
                        <Button type="button" icon="pi pi-search"/>
                    </template>
                </Paginator>

            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref} from "vue";
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import Message from 'primevue/message';
import FloatLabel from 'primevue/floatlabel';
import {Link, router, useForm, usePage} from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import Paginator from "primevue/paginator";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

const page = usePage();
const items = ref(null)
const props = defineProps({errors: Object, items: [], fournisseurs: Array,sort_fields:Array});
const form = useForm({
    fournisseur_id: null,
    endDate: null,
    startDate: null,
    n_bon: null,
    n_facture: null,
})

const search = (page = 1) => {
    axios
        .post("/commandes/search?page=" + page, form)
        .then(response => {
            items.value = response.data
        }).catch(function (error) {
            console.error(error)
        }
    );
}

function can(permission) {
    return page.props.roles[0] === 'Administrateur' || page.props.permissions.some(el => el === permission);
}

</script>


<style>

input:focus {
    --tw-ring-color: white !important;
    --tw-shadow-colored: white !important;
}
</style>
