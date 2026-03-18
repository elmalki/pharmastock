<script setup>
import { ScheduleXCalendar } from '@schedule-x/vue'
import { createEventModalPlugin } from '@schedule-x/event-modal'
import {
    createCalendar,
    createViewMonthGrid,
    viewMonthGrid
} from '@schedule-x/calendar'
import '@schedule-x/theme-default/dist/index.css'
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, computed} from "vue";
import {createCalendarControlsPlugin} from "@schedule-x/calendar-controls";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
import {Link} from "@inertiajs/vue3";

const show = ref(false)
const content = ref(null)
const eventsLoaded = ref(0)
const expiredCount = ref(0)
const soonCount = ref(0)
const okCount = ref(0)

function categorizeEvent(date) {
    if (!date) return;
    const d = new Date(date);
    const now = new Date();
    const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
    if (diff < 0) expiredCount.value++;
    else if (diff <= 30) soonCount.value++;
    else okCount.value++;
}

onMounted(() => {
    fetchCommandes(calendarControls.getRange().start, calendarControls.getRange().end)
})

const eventModal = createEventModalPlugin()
const calendarControls = createCalendarControlsPlugin()
const calendarApp = createCalendar({
    calendars: {
        expired: {
            colorName: 'expired',
            lightColors: {
                main: '#ef4444',
                container: '#fecaca',
                onContainer: '#7f1d1d',
            },
            darkColors: {
                main: '#fca5a5',
                onContainer: '#fecaca',
                container: '#991b1b',
            },
        },
        soon: {
            colorName: 'soon',
            lightColors: {
                main: '#f59e0b',
                container: '#fef3c7',
                onContainer: '#78350f',
            },
            darkColors: {
                main: '#fcd34d',
                onContainer: '#fef3c7',
                container: '#92400e',
            },
        },
        ok: {
            colorName: 'ok',
            lightColors: {
                main: '#10b981',
                container: '#d1fae5',
                onContainer: '#064e3b',
            },
            darkColors: {
                main: '#6ee7b7',
                onContainer: '#d1fae5',
                container: '#065f46',
            },
        },
    },
    defaultView: viewMonthGrid,
    locale: 'fr-FR',
    views: [createViewMonthGrid()],
    plugins: [eventModal, calendarControls],
    events: [],
    callbacks: {
        onRangeUpdate(range) {
            fetchCommandes(range.start, range.end)
        },
    }
})

const fetchCommandes = (start, end) => {
    axios.post('/api/peremption', {start, end})
        .then(response => {
            response.data.forEach(item => {
                if (!calendarApp.events.get(item.id)) {
                    const d = new Date(item.expirationDate);
                    const now = new Date();
                    const diff = Math.ceil((d - now) / (1000 * 60 * 60 * 24));
                    let calId = 'ok';
                    if (diff < 0) calId = 'expired';
                    else if (diff <= 30) calId = 'soon';

                    calendarApp.events.add({
                        id: item.id,
                        calendarId: calId,
                        title: item.produit.label,
                        start: item.expirationDate,
                        end: item.expirationDate,
                        description: `Quantité restante: ${item.qte} | Lot: ${item.n_lot || '—'}`
                    })
                    eventsLoaded.value++
                    categorizeEvent(item.expirationDate)
                }
            })
        })
}
</script>

<template>
    <AppLayout title="Calendrier de péremptions">
        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <Breadcrumbs :pages="[
                {name: 'Produits', href: route('produits.index'), current: false},
                {name: 'Calendrier Péremptions', href: route('produits.peremption'), current: true}
            ]"/>

            <!-- Hero Header -->
            <div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-rose-600 via-red-500 to-orange-500 p-6 sm:p-8 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-0 right-20 -mb-6 w-24 h-24 bg-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/5 rounded-full"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Calendrier de Péremptions</h1>
                            <p class="text-rose-200 text-sm mt-0.5">Visualisez les dates d'expiration de vos produits</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link :href="route('produits.perimes')"
                              class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 shadow-sm hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            Voir périmés
                        </Link>
                        <Link :href="route('produits.index')"
                              class="inline-flex items-center gap-2 rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                            </svg>
                            Retour
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Total events -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-100">
                                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Total lots</p>
                        <p class="mt-1 text-3xl font-bold text-violet-700">{{ eventsLoaded }}</p>
                        <p class="mt-1 text-xs text-gray-400">Lots avec date péremption</p>
                    </div>
                </div>

                <!-- Expired -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-red-100">
                                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <span v-if="expiredCount > 0" class="flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Périmés</p>
                        <p class="mt-1 text-3xl font-bold" :class="expiredCount > 0 ? 'text-red-600' : 'text-gray-400'">{{ expiredCount }}</p>
                        <p class="mt-1 text-xs" :class="expiredCount > 0 ? 'text-red-400' : 'text-gray-400'">{{ expiredCount > 0 ? 'Action requise' : 'Aucun périmé' }}</p>
                    </div>
                </div>

                <!-- Expiring soon -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Expire bientôt</p>
                        <p class="mt-1 text-3xl font-bold" :class="soonCount > 0 ? 'text-amber-600' : 'text-gray-400'">{{ soonCount }}</p>
                        <p class="mt-1 text-xs text-gray-400">Moins de 30 jours</p>
                    </div>
                </div>

                <!-- OK -->
                <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-60"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm font-medium text-gray-500">En règle</p>
                        <p class="mt-1 text-3xl font-bold text-emerald-700">{{ okCount }}</p>
                        <p class="mt-1 text-xs text-gray-400">Plus de 30 jours</p>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Légende</span>
                    </div>
                    <div class="flex items-center gap-5 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-xs font-medium text-gray-600">Périmé</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-medium text-gray-600">Expire dans 30j</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-medium text-gray-600">En règle</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-rose-100">
                                <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z"/>
                                </svg>
                            </div>
                            <h2 class="text-base font-semibold text-gray-900">Vue calendrier</h2>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                            {{ eventsLoaded }} événement{{ eventsLoaded > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <ScheduleXCalendar :calendar-app="calendarApp" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.sx-vue-calendar-wrapper {
    width: 100%;
    max-width: 100%;
    height: 700px;
    max-height: 85vh;
}
</style>
