<script setup>
import {ref, watch} from 'vue';
import {router, useForm} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import InputError from "@/Components/InputError.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import Tag from "primevue/tag";
import DatePicker from "primevue/datepicker";
import Select from "primevue/select";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    kpis: Object,
    mouvements: Object,
    filters: Object,
    sort: Object,
});

const fmt = (v) => new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(v || 0);
const fmtDateTime = (d) => d ? new Date(d).toLocaleString('fr-FR', {dateStyle: 'short', timeStyle: 'short'}) : '—';

// Filters state
const typeFilter = ref(props.filters.type || null);
const dateFrom = ref(props.filters.dateFrom ? new Date(props.filters.dateFrom) : null);
const dateTo = ref(props.filters.dateTo ? new Date(props.filters.dateTo) : null);
const dateFromRef = ref(null);
const dateToRef = ref(null);

const typeOptions = [
    {label: 'Tous', value: null},
    {label: 'Entrées', value: 'entree'},
    {label: 'Sorties', value: 'sortie'},
];

function toISO(d) {
    if (!d) return null;
    const date = new Date(d);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
}

function reload(params = {}) {
    router.get(route('caisse.index'), {
        type: typeFilter.value || undefined,
        date_from: toISO(dateFrom.value) || undefined,
        date_to: toISO(dateTo.value) || undefined,
        field: props.sort.field,
        order: props.sort.order,
        per_page: props.sort.per_page,
        page: props.mouvements.current_page,
        ...params,
    }, {preserveState: true, preserveScroll: true, replace: true});
}

let filterTimer = null;
watch([typeFilter, dateFrom, dateTo], () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => reload({page: 1}), 400);
});

function onSort(e) {
    reload({field: e.sortField, order: e.sortOrder === 1 ? 'asc' : 'desc', page: 1});
}

const page = ref((props.mouvements.current_page - 1) * props.mouvements.per_page);
watch(() => props.mouvements.current_page, v => { page.value = (v - 1) * props.mouvements.per_page; });

function onPage(e) {
    reload({page: 1 + e.page, per_page: e.rows});
}

// Vidage modal
const showModal = ref(false);
const form = useForm({montant: 0, motif: ''});

function openModal() {
    form.reset();
    form.montant = props.kpis.solde;
    form.motif = '';
    showModal.value = true;
}

function submit() {
    form.post(route('caisse.vider'), {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; },
    });
}

function setPartial(ratio) {
    form.montant = Math.round(props.kpis.solde * ratio * 100) / 100;
}
</script>

<template>
    <AppLayout title="Caisse">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <Breadcrumbs :pages="[{name: 'Caisse', href: route('caisse.index'), current: true}]"/>

            <div class="mt-4 flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Caisse</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Suivi des paiements en espèce et des retraits</p>
                </div>
                <button @click="openModal" :disabled="kpis.solde <= 0"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                    Vider la caisse
                </button>
            </div>

            <!-- KPI cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Solde (big) -->
                <div class="md:col-span-2 lg:col-span-1 row-span-2 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 shadow-sm text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-12 -mt-12"></div>
                    <div class="relative">
                        <div class="flex items-center gap-2 text-emerald-100 mb-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                            <span class="text-xs font-semibold uppercase tracking-wider">Solde en caisse</span>
                        </div>
                        <div class="text-4xl font-bold tabular-nums">{{ fmt(kpis.solde) }}</div>
                        <div class="text-xs text-emerald-100 mt-2">Disponible pour retrait</div>
                    </div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2 text-emerald-600 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Entrées aujourd'hui</span>
                    </div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.entrees_jour) }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2 text-rose-600 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Sorties aujourd'hui</span>
                    </div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.sorties_jour) }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2 text-indigo-600 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Entrées ce mois</span>
                    </div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.entrees_mois) }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2 text-amber-600 mb-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25"/>
                        </svg>
                        <span class="text-[11px] font-semibold uppercase tracking-wider">Sorties ce mois</span>
                    </div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.sorties_mois) }}</div>
                </div>
            </div>

            <!-- Filters + Table -->
            <div class="mt-6 rounded-xl border border-gray-200 bg-white overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Historique des mouvements</div>
                            <div class="text-xs text-gray-500 tabular-nums">{{ mouvements.total }} au total</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <Select v-model="typeFilter" :options="typeOptions" optionLabel="label" optionValue="value"
                                placeholder="Type" class="w-32" size="small"/>
                        <DatePicker ref="dateFromRef" v-model="dateFrom" dateFormat="dd/mm/yy" placeholder="Du" size="small" showIcon/>
                        <DatePicker ref="dateToRef" v-model="dateTo" dateFormat="dd/mm/yy" placeholder="Au" size="small" showIcon/>
                    </div>
                </div>

                <div v-if="mouvements.total === 0" class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-3 text-sm text-gray-500">Aucun mouvement</p>
                </div>

                <DataTable v-else :value="mouvements.data" lazy :sort-field="sort.field"
                           :sort-order="sort.order === 'asc' ? 1 : -1" @sort="onSort"
                           removable-sort tableClass="text-sm">
                    <Column field="created_at" header="Date" sortable style="width: 12rem">
                        <template #body="{ data }">
                            <span class="text-gray-700 tabular-nums">{{ fmtDateTime(data.created_at) }}</span>
                        </template>
                    </Column>
                    <Column field="type" header="Type" sortable style="width: 8rem">
                        <template #body="{ data }">
                            <span v-if="data.type === 'entree'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Entrée
                            </span>
                            <span v-else class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                                </svg>
                                Sortie
                            </span>
                        </template>
                    </Column>
                    <Column field="montant" header="Montant" sortable bodyClass="text-right" headerClass="text-right">
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums" :class="data.type === 'entree' ? 'text-emerald-700' : 'text-rose-700'">
                                {{ data.type === 'entree' ? '+' : '−' }} {{ fmt(data.montant) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="motif" header="Motif" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-700">{{ data.motif || '—' }}</span>
                        </template>
                    </Column>
                    <Column header="Vente">
                        <template #body="{ data }">
                            <a v-if="data.vente" :href="route('ventes.show', {vente: data.vente.id})"
                               class="text-xs font-mono text-indigo-600 hover:underline">
                                #{{ data.vente.n_facture }}
                            </a>
                            <span v-else class="text-gray-300">—</span>
                        </template>
                    </Column>
                    <Column header="Utilisateur">
                        <template #body="{ data }">
                            <span class="text-xs text-gray-500">{{ data.user?.name || '—' }}</span>
                        </template>
                    </Column>
                </DataTable>

                <div v-if="mouvements.total > 0" class="border-t border-gray-200">
                    <Paginator :rows="mouvements.per_page" v-model:first="page" :totalRecords="mouvements.total"
                               :rowsPerPageOptions="[15, 25, 50, 100]"
                               template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                               currentPageReportTemplate="{first}–{last} / {totalRecords}"
                               @page="onPage"/>
                </div>
            </div>
        </div>

        <!-- Vidage modal -->
        <DialogModal :show="showModal" @close="showModal = false">
            <template #title>Vider la caisse</template>
            <template #content>
                <div class="space-y-4">
                    <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Solde disponible</div>
                        <div class="text-2xl font-bold text-emerald-800 tabular-nums mt-1">{{ fmt(kpis.solde) }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Montant à retirer</label>
                        <div class="relative">
                            <input v-model.number="form.montant" type="number" step="0.01" min="0.01" :max="kpis.solde"
                                   class="block w-full rounded-lg border border-gray-300 pl-3 pr-14 py-2.5 text-lg font-semibold tabular-nums shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"/>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">MAD</span>
                        </div>
                        <div class="flex gap-1.5 mt-2">
                            <button type="button" @click="setPartial(0.25)"
                                    class="flex-1 py-1 px-2 text-[11px] font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md ring-1 ring-inset ring-gray-200">25%</button>
                            <button type="button" @click="setPartial(0.5)"
                                    class="flex-1 py-1 px-2 text-[11px] font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md ring-1 ring-inset ring-gray-200">50%</button>
                            <button type="button" @click="setPartial(0.75)"
                                    class="flex-1 py-1 px-2 text-[11px] font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md ring-1 ring-inset ring-gray-200">75%</button>
                            <button type="button" @click="setPartial(1)"
                                    class="flex-1 py-1 px-2 text-[11px] font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-md ring-1 ring-inset ring-indigo-200">Tout</button>
                        </div>
                        <InputError :message="form.errors.montant" class="mt-2"/>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Motif <span class="text-rose-500">*</span></label>
                        <textarea v-model="form.motif" rows="3"
                                  placeholder="Ex: Dépôt bancaire, Achat fournisseur, Autre..."
                                  class="block w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                        <InputError :message="form.errors.motif" class="mt-2"/>
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                <PrimaryButton class="ms-3" @click="submit" :class="{'opacity-50': form.processing}" :disabled="form.processing">
                    Confirmer le retrait
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
