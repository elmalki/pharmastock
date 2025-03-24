<template>
    <div class=" bg-transparent pb-10 h-screen max-w-8xl mx-auto" v-if="items.data.length">
        <DataTable :value="items.data" tableStyle="min-width: 50rem"
                   @update:sort-order="value => loadPage(value)" @update:sort-field="value => field = value"
                   :sort-field="sort_fields.field" :sortOrder="sort_fields.order">
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
                    </div>
                </template>
            </Column>
        </DataTable>
        <div class="card">
            <Paginator :rows="items.per_page" v-model:first="page" :totalRecords="items.total"
                       @page="event=>router.get(route('commandes.index'),{page:1+event.page})">
                <template #start="slotProps">
                </template>
                <template #end>
                    <Button type="button" icon="pi pi-search"/>
                </template>
            </Paginator>

        </div>
    </div>
</template>
<script setup lang="ts">
import {Link, router} from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Column from "primevue/column";
import {ref} from "vue";
const props = defineProps({items:Object,sort_fields:Array})
const field = ref(null)
const loadPage = (v) => {
    router.get(route('commandes.index'), {field: field.value, order: v})
}


</script>
