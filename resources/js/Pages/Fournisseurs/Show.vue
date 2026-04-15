<script setup>
import {computed, ref, watch} from 'vue';
import {router, Link} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import Tag from "primevue/tag";

const props = defineProps({
    fournisseur: Object,
    kpis: Object,
    commandes: Object,
    sort: Object,
});

const fmt = (v) => new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(v || 0);
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR', {day: '2-digit', month: '2-digit', year: 'numeric'}) : '—';
const fmtDateLong = (d) => d ? new Date(d).toLocaleDateString('fr-FR', {day: 'numeric', month: 'long', year: 'numeric'}) : '—';
const initials = computed(() => (props.fournisseur.societe || '?').substring(0, 2).toUpperCase());

const page = ref((props.commandes.current_page - 1) * props.commandes.per_page);
watch(() => props.commandes.current_page, v => { page.value = (v - 1) * props.commandes.per_page; });

function reload(params = {}) {
    router.get(route('fournisseurs.show', {fournisseur: props.fournisseur.id}), {
        field: props.sort.field,
        order: props.sort.order,
        per_page: props.sort.per_page,
        page: props.commandes.current_page,
        ...params,
    }, {preserveState: true, preserveScroll: true, replace: true});
}

function onSort(e) {
    reload({field: e.sortField, order: e.sortOrder === 1 ? 'asc' : 'desc', page: 1});
}

function onPage(e) {
    reload({page: 1 + e.page, per_page: e.rows});
}

function situationSeverity(s) {
    if (!s) return 'secondary';
    const v = (s.value ?? s).toString().toLowerCase();
    if (v.includes('pay')) return 'success';
    if (v.includes('part')) return 'warn';
    if (v.includes('imp') || v.includes('non')) return 'danger';
    return 'info';
}

function paiementLabel(p) {
    return p?.value ?? p ?? '—';
}
</script>

<template>
    <AppLayout :title="fournisseur.societe">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Fournisseurs', href: route('fournisseurs.index'), current: false},
                {name: fournisseur.societe, href: route('fournisseurs.show', {fournisseur: fournisseur.id}), current: true}
            ]"/>

            <!-- Header: Info + Edit button -->
            <div class="mt-4 flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-md">
                        <span class="text-xl font-bold text-white tracking-wider">{{ initials }}</span>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 truncate">{{ fournisseur.societe }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            <span v-if="kpis.last_commande">Dernière commande le {{ fmtDateLong(kpis.last_commande) }}</span>
                            <span v-else>Aucune commande enregistrée</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('fournisseurs.index')"
                          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Retour
                    </Link>
                </div>
            </div>

            <!-- Info + KPI grid -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-5">
                <!-- Info card -->
                <div class="lg:col-span-4 rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="flex items-center gap-3 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Coordonnées</div>
                            <div class="text-xs text-gray-500">Informations du fournisseur</div>
                        </div>
                    </div>
                    <dl class="divide-y divide-gray-100">
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Contact</dt>
                                <dd class="text-sm text-gray-900 mt-0.5 truncate">{{ fournisseur.contact || '—' }}</dd>
                            </div>
                        </div>
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Téléphone</dt>
                                <dd class="text-sm text-gray-900 mt-0.5 font-mono">{{ fournisseur.telephone || '—' }}</dd>
                            </div>
                        </div>
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Email</dt>
                                <dd class="text-sm text-gray-900 mt-0.5 truncate">
                                    <a v-if="fournisseur.email" :href="'mailto:' + fournisseur.email" class="text-indigo-600 hover:underline">{{ fournisseur.email }}</a>
                                    <span v-else>—</span>
                                </dd>
                            </div>
                        </div>
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            <div class="min-w-0 flex-1">
                                <dt class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Adresse</dt>
                                <dd class="text-sm text-gray-900 mt-0.5">{{ fournisseur.adresse || '—' }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- KPI cards -->
                <div class="lg:col-span-4 grid grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-8 -mt-8"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 text-indigo-600 mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                                </svg>
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Commandes</span>
                            </div>
                            <div class="text-3xl font-bold text-gray-900 tabular-nums">{{ kpis.total_commandes }}</div>
                            <div class="text-xs text-gray-500 mt-1">commandes passées</div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-sky-50 rounded-full -mr-8 -mt-8"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 text-sky-600 mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4"/>
                                </svg>
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Chiffre total</span>
                            </div>
                            <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.total_montant) }}</div>
                            <div class="text-xs text-gray-500 mt-1">Panier moyen {{ fmt(kpis.panier_moyen) }}</div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-8 -mt-8"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 text-emerald-600 mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                                </svg>
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Payé</span>
                            </div>
                            <div class="text-xl font-bold text-gray-900 tabular-nums">{{ fmt(kpis.total_paye) }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ kpis.total_montant > 0 ? Math.round(kpis.total_paye / kpis.total_montant * 100) : 0 }}% du total
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-full -mr-8 -mt-8"></div>
                        <div class="relative">
                            <div class="flex items-center gap-2 text-amber-600 mb-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                </svg>
                                <span class="text-[11px] font-semibold uppercase tracking-wider">Reste à payer</span>
                            </div>
                            <div class="text-xl font-bold tabular-nums" :class="kpis.total_reste > 0 ? 'text-amber-700' : 'text-gray-900'">
                                {{ fmt(kpis.total_reste) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">impayés cumulés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commandes table -->
            <div class="mt-6 rounded-xl border border-gray-200 bg-white overflow-hidden">
                <div class="flex items-center justify-between border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-100">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Commandes</div>
                            <div class="text-xs text-gray-500 tabular-nums">{{ commandes.total }} au total</div>
                        </div>
                    </div>
                </div>

                <div v-if="commandes.total === 0" class="py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-3 text-sm text-gray-500">Aucune commande pour ce fournisseur</p>
                </div>

                <DataTable v-else :value="commandes.data" lazy :sort-field="sort.field"
                           :sort-order="sort.order === 'asc' ? 1 : -1" @sort="onSort"
                           removable-sort tableClass="text-sm">
                    <Column field="n_bon" header="N° Bon" sortable>
                        <template #body="{ data }">
                            <span class="font-semibold text-indigo-600 tabular-nums">{{ data.n_bon || '—' }}</span>
                        </template>
                    </Column>
                    <Column field="n_facture" header="N° Facture" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-700 tabular-nums">{{ data.n_facture || '—' }}</span>
                        </template>
                    </Column>
                    <Column field="date" header="Date" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-700 tabular-nums">{{ fmtDate(data.date) }}</span>
                        </template>
                    </Column>
                    <Column field="dateEcheance" header="Échéance" sortable>
                        <template #body="{ data }">
                            <span class="text-gray-500 tabular-nums">{{ fmtDate(data.dateEcheance) }}</span>
                        </template>
                    </Column>
                    <Column field="paiement" header="Paiement" sortable>
                        <template #body="{ data }">
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-200">
                                {{ paiementLabel(data.paiement) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="situation" header="Statut" sortable>
                        <template #body="{ data }">
                            <Tag :value="data.situation?.value ?? data.situation ?? '—'" :severity="situationSeverity(data.situation)"/>
                        </template>
                    </Column>
                    <Column field="total_ht" header="Total" sortable bodyClass="text-right" headerClass="text-right">
                        <template #body="{ data }">
                            <span class="font-semibold text-gray-900 tabular-nums">{{ fmt(data.total_ht) }}</span>
                        </template>
                    </Column>
                    <Column field="montantPaye" header="Payé" sortable bodyClass="text-right" headerClass="text-right">
                        <template #body="{ data }">
                            <span class="text-emerald-600 tabular-nums">{{ fmt(data.montantPaye) }}</span>
                        </template>
                    </Column>
                    <Column field="reste" header="Reste" sortable bodyClass="text-right" headerClass="text-right">
                        <template #body="{ data }">
                            <span :class="parseFloat(data.reste) > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400'" class="tabular-nums">
                                {{ fmt(data.reste) }}
                            </span>
                        </template>
                    </Column>
                </DataTable>

                <div v-if="commandes.total > 0" class="border-t border-gray-200">
                    <Paginator :rows="commandes.per_page" v-model:first="page" :totalRecords="commandes.total"
                               :rowsPerPageOptions="[10, 25, 50, 100]" template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink RowsPerPageDropdown"
                               currentPageReportTemplate="{first}–{last} / {totalRecords}"
                               @page="onPage"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
