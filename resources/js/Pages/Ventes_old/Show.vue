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

const tvaGroups = computed(() => {
    const groups = {};
    lignes.value.forEach(l => {
        if (l.tva > 0) {
            const amount = l.totalHT * l.tva / 100;
            groups[l.tva] = (groups[l.tva] || 0) + amount;
        }
    });
    return groups;
});

const totalTVA = computed(() => Object.values(tvaGroups.value).reduce((s, v) => s + v, 0));

function formatDate(date) {
    if (!date) return '—';
    return date.substring(0, 10).split('-').reverse().join('/');
}

function formatPrice(val) {
    if (val == null) return '—';
    return parseFloat(val).toFixed(2) + ' Dhs';
}

function statutVal(s) {
    if (!s) return '';
    return typeof s === 'object' ? s?.value ?? s : s;
}

function statutSeverity(s) {
    const val = statutVal(s);
    if (val === 'payé') return 'success';
    if (val === 'partiel') return 'warn';
    if (val === 'annulé') return 'danger';
    return 'secondary';
}

function statutLabel(s) {
    const val = statutVal(s);
    return {'payé': 'Payé', 'partiel': 'Partiel', 'annulé': 'Annulé'}[val] ?? val;
}

function paiementVal(p) {
    if (!p) return '';
    return typeof p === 'object' ? p?.value ?? p : p;
}

function paiementSeverity(p) {
    const val = paiementVal(p);
    const map = {
        'Éspèce': 'success', 'TPE': 'success', 'Chèque': 'info', 'Effet': 'info',
        'Virement': 'info', 'En compte': 'warn', 'Crédit': 'warn', 'Multi Réglement': 'secondary',
    };
    return map[val] ?? 'secondary';
}
</script>

<template>
    <AppLayout title="Détail Vente">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Ventes', href: route('ventes.index'), current: false},
                {name: vente.n_facture, href: route('ventes.show', {vente: vente.id}), current: true}
            ]"/>

            <!-- Header -->
            <div class="flex items-center justify-between mt-4 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Vente {{ vente.n_facture }}</h1>
                <div class="flex items-center gap-2">
                    <Link :href="route('ventes.edit', {vente: vente.id})"
                          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Modifier
                    </Link>
                    <Link :href="route('ventes.index')"
                          class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Retour
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ formatPrice(vente.total) }}</div>
                    <div v-if="parseFloat(vente.remise) > 0" class="mt-0.5 text-xs text-gray-400">
                        <span class="line-through">{{ formatPrice(vente.subtotal) }}</span>
                        <span class="ml-1 text-emerald-500">-{{ vente.remise }}%</span>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Payé</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600">{{ formatPrice(vente.montantPaye) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Reste</div>
                    <div class="mt-1 text-2xl font-bold" :class="parseFloat(vente.reste) > 0 ? 'text-amber-600' : 'text-gray-900'">
                        {{ formatPrice(vente.reste) }}
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm text-gray-500">Bénéfice</div>
                    <div class="mt-1 text-2xl font-bold text-indigo-600">{{ formatPrice(vente.benefice) }}</div>
                </div>
            </div>

            <!-- Info cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Vente info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Vente</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-6">
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">N° Facture</dt>
                            <dd class="text-sm font-medium font-mono text-gray-900">{{ vente.n_facture }}</dd>
                        </div>
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Date</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ formatDate(vente.date) }}</dd>
                        </div>
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Articles</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ lignes.length }} produit{{ lignes.length > 1 ? 's' : '' }} ({{ totalArticles }} unités)</dd>
                        </div>
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Vendeur</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.user?.name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Paiement info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Paiement</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-6">
                        <div class="flex justify-between items-center py-3">
                            <dt class="text-sm text-gray-500">Mode</dt>
                            <dd><Tag :severity="paiementSeverity(vente.paiement)" :value="paiementVal(vente.paiement) || '—'" class="text-xs"/></dd>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <dt class="text-sm text-gray-500">Statut</dt>
                            <dd><Tag :severity="statutSeverity(vente.statut)" :value="statutLabel(vente.statut)" class="text-xs"/></dd>
                        </div>
                        <div v-if="vente.dateEcheance" class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Échéance</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ formatDate(vente.dateEcheance) }}</dd>
                        </div>
                        <div v-if="parseFloat(vente.remise) > 0" class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Remise</dt>
                            <dd class="text-sm font-medium text-emerald-600">{{ vente.remise }}% ({{ formatPrice(vente.remise_amount) }})</dd>
                        </div>
                    </dl>
                </div>

                <!-- Client info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Client</h3>
                    </div>
                    <dl class="divide-y divide-gray-100 px-6">
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Nom</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.client?.nom ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between py-3">
                            <dt class="text-sm text-gray-500">Téléphone</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ vente.client?.tel || '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Products table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                    <h3 class="text-sm font-semibold text-gray-900">Produits vendus ({{ lignes.length }})</h3>
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
                    <Column field="qte" header="Qté" style="text-align: right">
                        <template #body="{ data }">
                            <span class="font-medium tabular-nums text-gray-900">{{ data.qte }}</span>
                        </template>
                    </Column>
                    <Column field="prix" header="Prix unitaire" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-700">{{ formatPrice(data.prix) }}</span>
                        </template>
                    </Column>
                    <Column field="prix_achat" header="P. Achat" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-500">{{ formatPrice(data.prix_achat) }}</span>
                        </template>
                    </Column>
                    <Column field="tva" header="TVA" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums text-gray-500">{{ data.tva }}%</span>
                        </template>
                    </Column>
                    <Column field="totalHT" header="Total HT" style="text-align: right">
                        <template #body="{ data }">
                            <span class="font-semibold tabular-nums text-gray-900">{{ formatPrice(data.totalHT) }}</span>
                        </template>
                    </Column>
                    <Column field="benefice" header="Bénéfice" style="text-align: right">
                        <template #body="{ data }">
                            <span class="tabular-nums" :class="data.benefice >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatPrice(data.benefice) }}</span>
                        </template>
                    </Column>
                </DataTable>

                <!-- Totals -->
                <div class="border-t border-gray-200 bg-gray-50">
                    <div class="max-w-xs ml-auto px-6 py-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Sous-total</span>
                            <span class="font-medium tabular-nums text-gray-900">{{ formatPrice(vente.subtotal) }}</span>
                        </div>
                        <div v-if="parseFloat(vente.remise) > 0" class="flex justify-between text-sm">
                            <span class="text-gray-500">Remise ({{ vente.remise }}%)</span>
                            <span class="tabular-nums text-emerald-600">-{{ formatPrice(vente.remise_amount) }}</span>
                        </div>
                        <div v-for="(amount, rate) in tvaGroups" :key="rate" class="flex justify-between text-sm">
                            <span class="text-gray-500">TVA {{ rate }}%</span>
                            <span class="tabular-nums text-gray-700">{{ formatPrice(amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                            <span class="font-semibold text-gray-900">Total</span>
                            <span class="font-bold tabular-nums text-indigo-600 text-base">{{ formatPrice(vente.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
