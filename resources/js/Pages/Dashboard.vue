<script setup>
import {ref, computed, onMounted, nextTick, watch} from 'vue'
import {router} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Highcharts from 'highcharts'

const props = defineProps({
    total: Number,
    en_rupture: Number,
    perimes: Number,
    period: String,
    stats: Object,
    topProducts: Array,
    creancesClients: Array,
    dettesFournisseurs: Array,
    alertes: Array,
    activites: Array,
    salesChart: Object,
    categoryChart: Array,
    cashFlowChart: Object,
})

const periods = [
    {label: "Aujourd'hui", value: 'today'},
    {label: 'Cette semaine', value: 'week'},
    {label: 'Ce mois', value: 'month'},
    {label: 'Cette année', value: 'year'},
]

const selectedPeriod = ref(props.period || 'month')
const chartPeriod = ref('week')

const totalCreances = computed(() => (props.creancesClients ?? []).reduce((sum, c) => sum + c.montant, 0))
const totalDettes = computed(() => (props.dettesFournisseurs ?? []).reduce((sum, d) => sum + d.montant, 0))

const currentSalesData = computed(() => {
    if (!props.salesChart) return {categories: [], ca: [], benefice: []}
    if (chartPeriod.value === 'day') return props.salesChart.daily
    if (chartPeriod.value === 'week') return props.salesChart.weekly
    return props.salesChart.monthly
})

function loadDashboardData() {
    router.get(route('dashboard'), {period: selectedPeriod.value}, {preserveState: true, preserveScroll: true})
}

function fmt(amount) {
    if (amount == null) return '0,00 MAD'
    return new Intl.NumberFormat('fr-MA', {style: 'currency', currency: 'MAD'}).format(amount)
}

function fmtCompact(amount) {
    if (amount == null || amount === 0) return '0'
    if (Math.abs(amount) >= 1000000) return (amount / 1000000).toFixed(1) + 'M'
    if (Math.abs(amount) >= 1000) return (amount / 1000).toFixed(1) + 'K'
    return amount.toFixed(0)
}

// ─── Créances/Dettes helpers ─────────────────────────────

function getCreanceClass(client) {
    if (client.joursRetard > 0) return 'border-red-500 bg-red-50'
    if (client.joursRestants < 5) return 'border-orange-500 bg-orange-50'
    if (client.joursRestants < 15) return 'border-yellow-500 bg-yellow-50'
    return 'border-blue-500 bg-blue-50'
}

function getEcheanceText(client) {
    if (client.joursRetard > 0) return `Dépassée: ${client.joursRetard}j`
    return `Dans ${client.joursRestants}j`
}

function getDetteClass(joursRestants) {
    if (joursRestants < 0) return 'border-red-500 bg-red-50'
    if (joursRestants <= 3) return 'border-orange-500 bg-orange-50'
    if (joursRestants <= 10) return 'border-yellow-500 bg-yellow-50'
    return 'border-green-500 bg-green-50'
}

function getEcheanceTextFournisseur(f) {
    if (f.joursRestants < 0) return `Dépassée: ${Math.abs(f.joursRestants)}j`
    if (f.joursRestants === 0) return "Aujourd'hui"
    return `Dans ${f.joursRestants}j`
}

function getAlerteIcon(type) {
    return {rupture: 'text-red-600', critique: 'text-orange-600', perime: 'text-yellow-600', expire: 'text-blue-600'}[type] || 'text-gray-600'
}

function getAlerteBg(type) {
    return {rupture: 'bg-red-50', critique: 'bg-orange-50', perime: 'bg-yellow-50', expire: 'bg-blue-50'}[type] || 'bg-gray-50'
}

function getActivityDot(type) {
    return {vente: 'bg-green-500', reception: 'bg-blue-500', paiement: 'bg-yellow-500', alerte: 'bg-red-500'}[type] || 'bg-gray-500'
}

// ─── Charts ────────────────────────────────────────────────

let salesChartInstance = null

function initCharts() {
    const sd = currentSalesData.value
    salesChartInstance = Highcharts.chart('salesChart', {
        chart: {type: 'area', height: 300, style: {fontFamily: 'inherit'}},
        title: {text: null},
        xAxis: {categories: sd.categories, labels: {style: {color: '#6b7280', fontSize: '11px'}}},
        yAxis: {title: {text: null}, labels: {style: {color: '#9ca3af'}}},
        tooltip: {shared: true, valueSuffix: ' MAD'},
        plotOptions: {area: {fillOpacity: 0.15, marker: {radius: 3}}},
        series: [
            {name: 'Ventes', data: [...sd.ca], color: '#6366f1'},
            {name: 'Bénéfice', data: [...sd.benefice], color: '#10b981'},
        ],
        credits: {enabled: false},
        legend: {itemStyle: {color: '#374151', fontSize: '12px'}},
    })

    if (props.categoryChart?.length > 0) {
        Highcharts.chart('categoryChart', {
            chart: {type: 'pie', height: 300, style: {fontFamily: 'inherit'}},
            title: {text: null},
            tooltip: {pointFormat: '<b>{point.percentage:.1f}%</b><br>{point.y:,.0f} MAD'},
            plotOptions: {
                pie: {
                    allowPointSelect: true, cursor: 'pointer',
                    dataLabels: {enabled: true, format: '<b>{point.name}</b>: {point.percentage:.1f}%', style: {fontSize: '11px'}},
                    showInLegend: false,
                },
            },
            series: [{
                name: 'Ventes', colorByPoint: true,
                data: props.categoryChart.map(c => ({name: c.name, y: c.y, color: c.color})),
            }],
            credits: {enabled: false},
        })
    }

    if (props.cashFlowChart) {
        Highcharts.chart('cashFlowChart', {
            chart: {type: 'column', height: 300, style: {fontFamily: 'inherit'}},
            title: {text: null},
            xAxis: {categories: props.cashFlowChart.categories, labels: {style: {color: '#6b7280', fontSize: '11px'}}},
            yAxis: {title: {text: null}, labels: {style: {color: '#9ca3af'}}},
            tooltip: {shared: true, valueSuffix: ' MAD'},
            plotOptions: {column: {borderRadius: 4}},
            series: [
                {name: 'Encaissements', data: [...props.cashFlowChart.encaissements], color: '#10b981'},
                {name: 'Décaissements', data: [...props.cashFlowChart.decaissements], color: '#ef4444'},
            ],
            credits: {enabled: false},
            legend: {itemStyle: {color: '#374151', fontSize: '12px'}},
        })
    }
}

function updateSalesChart() {
    if (!salesChartInstance) return
    const sd = currentSalesData.value
    salesChartInstance.xAxis[0].setCategories(sd.categories)
    salesChartInstance.series[0].setData([...sd.ca])
    salesChartInstance.series[1].setData([...sd.benefice])
}

onMounted(() => nextTick(() => initCharts()))
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1">Vue d'ensemble de votre activité</p>
                </div>
                <Select v-model="selectedPeriod" :options="periods" optionLabel="label" optionValue="value"
                        class="w-48" @change="loadDashboardData"/>
            </div>

            <!-- ═══ Inventory quick stats ═══ -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</p>
                        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ total }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">En rupture</p>
                        <p class="text-2xl font-bold tabular-nums" :class="en_rupture > 0 ? 'text-red-600' : 'text-gray-900'">{{ en_rupture }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Lots périmés</p>
                        <p class="text-2xl font-bold tabular-nums" :class="perimes > 0 ? 'text-yellow-600' : 'text-gray-900'">{{ perimes }}</p>
                    </div>
                </div>
            </div>

            <!-- ═══ Financial KPIs ═══ -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Ventes du jour -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                              :class="stats.ventesGrowth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <svg class="w-3 h-3" :class="stats.ventesGrowth >= 0 ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            {{ stats.ventesGrowth >= 0 ? '+' : '' }}{{ stats.ventesGrowth }}%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Ventes du jour</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ stats.ventesToday }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        <span :class="stats.ventesIncrease >= 0 ? 'text-green-600' : 'text-red-600'">{{ stats.ventesIncrease >= 0 ? '+' : '' }}{{ stats.ventesIncrease }}</span> vs hier
                    </p>
                </div>

                <!-- Chiffre d'affaires -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                              :class="stats.caGrowth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <svg class="w-3 h-3" :class="stats.caGrowth >= 0 ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            {{ stats.caGrowth >= 0 ? '+' : '' }}{{ stats.caGrowth }}%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Chiffre d'affaires</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtCompact(stats.ca) }} <span class="text-sm font-normal text-gray-400">MAD</span></p>
                    <p class="text-xs text-gray-400 mt-1">Ce mois: {{ fmt(stats.caMonth) }}</p>
                </div>

                <!-- Bénéfice -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-400 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full"
                              :class="stats.beneficeGrowth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <svg class="w-3 h-3" :class="stats.beneficeGrowth >= 0 ? '' : 'rotate-180'" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            {{ stats.beneficeGrowth >= 0 ? '+' : '' }}{{ stats.beneficeGrowth }}%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Bénéfice</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtCompact(stats.benefice) }} <span class="text-sm font-normal text-gray-400">MAD</span></p>
                    <p class="text-xs text-gray-400 mt-1">Marge: {{ stats.marge }}%</p>
                </div>

                <!-- Créances -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span v-if="stats.facturesEnAttente > 0" class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                            {{ stats.facturesEnAttente }} en attente
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Créances clients</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ fmtCompact(stats.creances) }} <span class="text-sm font-normal text-gray-400">MAD</span></p>
                    <p class="text-xs text-gray-400 mt-1">{{ stats.facturesEnAttente }} factures en attente</p>
                </div>
            </div>

            <!-- ═══ Charts row ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Sales evolution -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Évolution des ventes</h3>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <button v-for="p in [{l:'Jour',v:'day'},{l:'Semaine',v:'week'},{l:'Mois',v:'month'}]" :key="p.v"
                                    @click="chartPeriod = p.v; updateSalesChart()"
                                    :class="[chartPeriod === p.v ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50', 'px-3 py-1 text-xs font-medium transition-colors']">
                                {{ p.l }}
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div id="salesChart" style="height: 300px;"></div>
                    </div>
                </div>

                <!-- Category pie -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Ventes par catégorie</h3>
                    </div>
                    <div class="p-4">
                        <div id="categoryChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>

            <!-- ═══ Detail cards: Top products / Créances / Dettes ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Top products -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Top 5 Produits</h3>
                    </div>
                    <div v-if="topProducts?.length" class="divide-y divide-gray-100">
                        <div v-for="(p, i) in topProducts" :key="p.id" class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                      :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-200 text-gray-600' : 'bg-orange-100 text-orange-600'">
                                    {{ i + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ p.nom }}</p>
                                    <p class="text-xs text-gray-400">{{ p.quantite }} unités</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-emerald-600 tabular-nums flex-shrink-0 ml-3">{{ fmt(p.montant) }}</span>
                        </div>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-gray-400 text-sm">Aucune vente sur cette période</div>
                </div>

                <!-- Créances clients -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Créances Clients</h3>
                        <span v-if="totalCreances > 0" class="text-xs font-bold text-red-600 tabular-nums">{{ fmt(totalCreances) }}</span>
                    </div>
                    <div v-if="creancesClients?.length" class="divide-y divide-gray-100">
                        <div v-for="client in creancesClients" :key="client.id"
                             :class="['px-5 py-3 border-l-4', getCreanceClass(client)]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ client.nom }}</p>
                                <span class="text-sm font-bold tabular-nums" :class="client.joursRetard > 0 ? 'text-red-600' : 'text-gray-700'">{{ fmt(client.montant) }}</span>
                            </div>
                            <p class="text-xs mt-0.5" :class="client.joursRetard > 0 ? 'text-red-500' : 'text-gray-400'">{{ getEcheanceText(client) }}</p>
                        </div>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-gray-400 text-sm">Aucune créance</div>
                </div>

                <!-- Dettes fournisseurs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Dettes Fournisseurs</h3>
                        <span v-if="totalDettes > 0" class="text-xs font-bold text-orange-600 tabular-nums">{{ fmt(totalDettes) }}</span>
                    </div>
                    <div v-if="dettesFournisseurs?.length" class="divide-y divide-gray-100">
                        <div v-for="f in dettesFournisseurs" :key="f.id"
                             :class="['px-5 py-3 border-l-4', getDetteClass(f.joursRestants)]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">{{ f.nom }}</p>
                                <span class="text-sm font-bold tabular-nums" :class="f.joursRestants < 0 ? 'text-red-600' : 'text-gray-700'">{{ fmt(f.montant) }}</span>
                            </div>
                            <p class="text-xs mt-0.5" :class="f.joursRestants < 0 ? 'text-red-500' : 'text-gray-400'">{{ getEcheanceTextFournisseur(f) }}</p>
                        </div>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-gray-400 text-sm">Aucune dette</div>
                </div>
            </div>

            <!-- ═══ Alerts & Activity ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Alertes stock -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                            <h3 class="text-sm font-semibold text-gray-900">Alertes Stock</h3>
                        </div>
                        <span v-if="alertes?.length" class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ alertes.length }}</span>
                    </div>
                    <div v-if="alertes?.length" class="divide-y divide-gray-100">
                        <div v-for="alerte in alertes" :key="alerte.id"
                             :class="['flex items-start gap-3 px-5 py-3', getAlerteBg(alerte.type)]">
                            <svg :class="['w-5 h-5 mt-0.5 flex-shrink-0', getAlerteIcon(alerte.type)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ alerte.titre }}</p>
                                <p class="text-xs text-gray-500">{{ alerte.description }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-gray-400 text-sm">Aucune alerte</div>
                </div>

                <!-- Activité récente -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900">Activité Récente</h3>
                    </div>
                    <div v-if="activites?.length" class="divide-y divide-gray-100">
                        <div v-for="(act, i) in activites" :key="i" class="flex items-start gap-3 px-5 py-3">
                            <span :class="['w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0', getActivityDot(act.type)]"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ act.titre }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ act.description }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ act.date }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-gray-400 text-sm">Aucune activité récente</div>
                </div>
            </div>

            <!-- ═══ Cash flow ═══ -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Trésorerie</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">Solde:</span>
                        <span class="text-lg font-bold tabular-nums" :class="stats.solde >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ fmt(stats.solde) }}</span>
                    </div>
                </div>
                <div class="p-4">
                    <div id="cashFlowChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
