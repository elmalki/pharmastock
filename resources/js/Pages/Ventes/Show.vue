<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Tag from "primevue/tag";
import {Link} from "@inertiajs/vue3";
import {computed} from "vue";

const props = defineProps({vente: Object});

const lignes = computed(() => {
    return (props.vente.produits ?? []).map(p => ({
        id: p.id,
        label: p.label,
        dci: p.dci,
        lot_id: p.pivot.lot_id,
        n_lot: p.pivot.n_lot ?? null,
        qte: p.pivot.qte,
        prix: parseFloat(p.pivot.prix),
        prix_achat: parseFloat(p.pivot.prix_achat),
        remise: parseFloat(p.pivot.remise ?? 0),
        tva: parseFloat(p.pivot.tva),
        totalHT: parseFloat(p.pivot.prix) * p.pivot.qte,
        benefice: (parseFloat(p.pivot.prix) - parseFloat(p.pivot.prix_achat)) * p.pivot.qte,
    }));
});

const totalArticles = computed(() => lignes.value.reduce((s, l) => s + l.qte, 0));
const totalBenefice = computed(() => lignes.value.reduce((s, l) => s + l.benefice, 0));

const tvaGroups = computed(() => {
    const groups = {};
    lignes.value.forEach(l => {
        if (l.tva > 0) {
            groups[l.tva] = (groups[l.tva] || 0) + l.totalHT * l.tva / 100;
        }
    });
    return groups;
});

function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function fmt(val) {
    if (val == null) return '—';
    return new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(val);
}

function statutVal(s) {
    if (!s) return '';
    return typeof s === 'object' ? s?.value ?? s : s;
}
function statutSeverity(s) {
    const v = statutVal(s);
    return v === 'payé' ? 'success' : v === 'partiel' ? 'warn' : v === 'annulé' ? 'danger' : 'secondary';
}
function statutLabel(s) {
    const v = statutVal(s);
    return {'payé': 'Payé', 'partiel': 'Partiel', 'annulé': 'Annulé'}[v] ?? v;
}
function paiementVal(p) {
    if (!p) return '';
    return typeof p === 'object' ? p?.value ?? p : p;
}
function paiementSeverity(p) {
    const v = paiementVal(p);
    return {'Éspèce': 'success', 'TPE': 'success', 'Chèque': 'info', 'Effet': 'info', 'Virement': 'info', 'En compte': 'warn', 'Crédit': 'warn'}[v] ?? 'secondary';
}
</script>

<template>
    <AppLayout title="Détail Vente">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Ventes', href: route('ventes.index'), current: false},
                {name: vente.n_facture ?? 'Détail', href: route('ventes.show', vente.id), current: true}
            ]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Vente
                        <span v-if="vente.n_facture" class="font-mono">{{ vente.n_facture }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ formatDate(vente.date) }}
                        <span v-if="vente.user">&middot; par {{ vente.user.name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="route('ventes.pdf', vente.id)" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 shadow-sm ring-1 ring-inset ring-amber-300 hover:bg-amber-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                        Facture PDF
                    </a>
                    <Link :href="route('ventes.edit', vente.id)"
                          class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                        </svg>
                        Modifier
                    </Link>
                    <Link :href="route('ventes.index')"
                          class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Retour
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</div>
                    <div class="mt-1.5 text-2xl font-bold text-gray-900 tabular-nums">{{ fmt(vente.total) }}</div>
                    <div v-if="parseFloat(vente.remise) > 0" class="mt-0.5 text-xs text-gray-400">
                        <span class="line-through">{{ fmt(vente.subtotal) }}</span>
                        <span class="ml-1 text-emerald-500 font-medium">-{{ vente.remise }}%</span>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</div>
                    <div class="mt-1.5 text-2xl font-bold text-emerald-600 tabular-nums">{{ fmt(vente.montantPaye) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Reste</div>
                    <div class="mt-1.5 text-2xl font-bold tabular-nums"
                         :class="parseFloat(vente.reste) > 0 ? 'text-amber-600' : 'text-gray-400'">
                        {{ fmt(vente.reste) }}
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Bénéfice</div>
                    <div class="mt-1.5 text-2xl font-bold tabular-nums"
                         :class="totalBenefice >= 0 ? 'text-indigo-600' : 'text-red-600'">
                        {{ fmt(totalBenefice) }}
                    </div>
                </div>
            </div>

            <!-- Info cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <!-- Vente info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-2.5">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Vente</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-5">
                        <div class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">N° Facture</dt>
                            <dd class="text-sm font-medium font-mono text-gray-900">{{ vente.n_facture ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ formatDate(vente.date) }}</dd>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Articles</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ lignes.length }} produit{{ lignes.length > 1 ? 's' : '' }}
                                <span class="text-gray-400">({{ totalArticles }} unités)</span>
                            </dd>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Vendeur</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.user?.name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Paiement info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-2.5">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Paiement</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-5">
                        <div class="flex justify-between items-center py-2.5">
                            <dt class="text-sm text-gray-500">Mode</dt>
                            <dd>
                                <Tag :severity="paiementSeverity(vente.paiement)"
                                     :value="paiementVal(vente.paiement) || '—'" class="text-xs"/>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center py-2.5">
                            <dt class="text-sm text-gray-500">Statut</dt>
                            <dd>
                                <Tag :severity="statutSeverity(vente.statut)"
                                     :value="statutLabel(vente.statut)" class="text-xs"/>
                            </dd>
                        </div>
                        <div v-if="vente.dateEcheance" class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Échéance</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ formatDate(vente.dateEcheance) }}</dd>
                        </div>
                        <div v-if="parseFloat(vente.remise) > 0" class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Remise</dt>
                            <dd class="text-sm font-medium text-emerald-600">
                                {{ vente.remise }}%
                                <span class="text-gray-400">({{ fmt(vente.remise_amount) }})</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Client info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-2.5">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-5">
                        <div class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Nom</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.client?.nom ?? '—' }}</dd>
                        </div>
                        <div v-if="vente.client?.tel" class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Téléphone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.client.tel }}</dd>
                        </div>
                        <div v-if="vente.client?.email" class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.client.email }}</dd>
                        </div>
                        <div v-if="vente.client?.adresse" class="flex justify-between py-2.5">
                            <dt class="text-sm text-gray-500">Adresse</dt>
                            <dd class="text-sm font-medium text-gray-900 text-right max-w-[200px]">{{ vente.client.adresse }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Products table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-5 py-2.5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Produits vendus ({{ lignes.length }})
                    </h3>
                </div>
                <DataTable :value="lignes" stripedRows>
                    <Column field="label" header="Produit">
                        <template #body="{ data }">
                            <div>
                                <span class="font-medium text-gray-900">{{ data.label }}</span>
                                <div v-if="data.dci" class="text-xs text-gray-400">{{ data.dci }}</div>
                            </div>
                        </template>
                    </Column>
                    <Column field="qte" header="Qté" style="text-align: right; width: 70px">
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums text-gray-900">{{ data.qte }}</span>
                        </template>
                    </Column>
                    <Column field="prix" header="P. Vente" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-700">{{ fmt(data.prix) }}</span>
                        </template>
                    </Column>
                    <Column field="prix_achat" header="P. Achat" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-400">{{ fmt(data.prix_achat) }}</span>
                        </template>
                    </Column>
                    <Column v-if="Object.keys(tvaGroups).length > 0" field="tva" header="TVA"
                            style="text-align: right; width: 70px">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-400">{{ data.tva }}%</span>
                        </template>
                    </Column>
                    <Column field="totalHT" header="Total" style="text-align: right">
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums text-gray-900">{{ fmt(data.totalHT) }}</span>
                        </template>
                    </Column>
                    <Column field="benefice" header="Marge" style="text-align: right">
                        <template #body="{ data }">
                            <span class="font-medium tabular-nums"
                                  :class="data.benefice >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                {{ fmt(data.benefice) }}
                            </span>
                        </template>
                    </Column>
                </DataTable>

                <!-- Totals footer -->
                <div class="border-t border-gray-200">
                    <div class="max-w-sm ml-auto px-5 py-4 space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Sous-total</span>
                            <span class="font-medium tabular-nums text-gray-700">{{ fmt(vente.subtotal) }}</span>
                        </div>
                        <div v-if="parseFloat(vente.remise) > 0" class="flex justify-between text-sm">
                            <span class="text-gray-500">Remise ({{ vente.remise }}%)</span>
                            <span class="tabular-nums text-emerald-600">- {{ fmt(vente.remise_amount) }}</span>
                        </div>
                        <div v-for="(amount, rate) in tvaGroups" :key="rate" class="flex justify-between text-sm">
                            <span class="text-gray-500">TVA {{ rate }}%</span>
                            <span class="tabular-nums text-gray-500">{{ fmt(amount) }}</span>
                        </div>
                    </div>
                    <div class="bg-slate-800 px-5 py-4">
                        <div class="max-w-sm ml-auto flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-300">Total</span>
                            <span class="text-2xl font-bold tabular-nums text-white">{{ fmt(vente.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
