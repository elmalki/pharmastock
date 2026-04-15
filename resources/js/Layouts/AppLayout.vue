<script setup>
import {ref} from 'vue';
import {Head, Link, router, usePage} from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({title: String});

const showingNavigationDropdown = ref(false);
const openDropdown = ref(null);
let closeTimer = null;

const page = usePage();

function can(permission) {
    return page.props.roles[0] === 'Administrateur' || page.props.permissions.some(el => el == permission);
}

function open(name) {
    clearTimeout(closeTimer);
    openDropdown.value = name;
}

function close() {
    closeTimer = setTimeout(() => openDropdown.value = null, 150);
}

function isActive(...patterns) {
    return patterns.some(p => route().current(p));
}

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {team_id: team.id}, {preserveState: false});
};

const logout = () => router.post(route('logout'));
</script>

<template>
    <div>
        <Head :title="title"/>
        <Banner/>
        <div class="min-h-screen ">
            <!-- ═══════════════════════════════════════════ -->
            <!-- TOP NAVIGATION BAR                         -->
            <!-- ═══════════════════════════════════════════ -->
            <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-200/60 shadow-sm">
                <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- ── Left: Logo + Nav ── -->
                        <div class="flex items-center gap-1">
                            <!-- Logo -->
                            <Link :href="route('dashboard')" class="shrink-0 flex items-center mr-4">
                                <ApplicationMark class="block h-9 w-auto"/>
                            </Link>

                            <!-- Desktop Navigation -->
                            <div class="hidden lg:flex lg:items-center lg:gap-0.5">
                                <!-- Dashboard -->
                                <Link :href="route('dashboard')"
                                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                      :class="isActive('dashboard')
                                          ? 'bg-indigo-50 text-indigo-700'
                                          : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                                    </svg>
                                    <span class="hidden xl:inline">Dashboard</span>
                                </Link>

                                <!-- Produits dropdown -->
                                <div class="relative" @mouseenter="open('produits')" @mouseleave="close()">
                                    <Link :href="route('produits.index')"
                                          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                          :class="isActive('produits.*')
                                              ? 'bg-indigo-50 text-indigo-700'
                                              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                        </svg>
                                        Produits
                                        <svg class="w-3.5 h-3.5 opacity-50" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                    </Link>
                                    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                        <div v-if="openDropdown === 'produits'" class="absolute left-0 top-full mt-1 w-64 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 p-2">
                                            <Link v-if="can('Lister Produit')" :href="route('produits.index')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 shrink-0">
                                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                                </div>
                                                Liste des produits
                                            </Link>
                                            <Link :href="route('produits.peremption')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-rose-100 shrink-0">
                                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                                </div>
                                                Calendrier péremptions
                                            </Link>
                                            <Link :href="route('produits.perimes')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 shrink-0">
                                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                                </div>
                                                Produits périmés
                                            </Link>
                                        </div>
                                    </transition>
                                </div>

                                <!-- Destockages dropdown -->
                                <div class="relative" @mouseenter="open('destockages')" @mouseleave="close()">
                                    <Link :href="route('destockages.index')"
                                          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                          :class="isActive('destockages.*')
                                              ? 'bg-indigo-50 text-indigo-700'
                                              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                                        </svg>
                                        Destockages
                                        <svg class="w-3.5 h-3.5 opacity-50" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                    </Link>
                                    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                        <div v-if="openDropdown === 'destockages'" class="absolute left-0 top-full mt-1 w-56 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 p-2">
                                            <Link :href="route('destockages.create')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 shrink-0">
                                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                </div>
                                                Destocker
                                            </Link>
                                            <Link :href="route('destockages.index')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 shrink-0">
                                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                                </div>
                                                Liste destockages
                                            </Link>
                                        </div>
                                    </transition>
                                </div>

                                <!-- Simple nav items -->
                                <Link :href="route('categories.index')"
                                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                      :class="isActive('categories.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                                    <span class="hidden xl:inline">Catégories</span>
                                </Link>

                                <Link :href="route('fournisseurs.index')"
                                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                      :class="isActive('fournisseurs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                    <span class="hidden xl:inline">Fournisseurs</span>
                                </Link>

                                <Link :href="route('clients.index')"
                                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                      :class="isActive('clients.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    <span class="hidden xl:inline">Clients</span>
                                </Link>

                                <Link :href="route('ventes.index')"
                                      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                      :class="isActive('ventes.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    <span class="hidden xl:inline">Ventes</span>
                                </Link>

                                <!-- Appro dropdown -->
                                <div class="relative" @mouseenter="open('appro')" @mouseleave="close()">
                                    <Link :href="route('commandes.index')"
                                          class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                                          :class="isActive('commandes.*', 'echeances')
                                              ? 'bg-indigo-50 text-indigo-700'
                                              : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                        Appro.
                                        <svg class="w-3.5 h-3.5 opacity-50" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                                    </Link>
                                    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0"
                                                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
                                        <div v-if="openDropdown === 'appro'" class="absolute right-0 top-full mt-1 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 p-2">
                                            <Link :href="route('commandes.create')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100 shrink-0">
                                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                </div>
                                                Nouvel approvisionnement
                                            </Link>
                                            <Link :href="route('commandes.index')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-sky-50 hover:text-sky-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100 shrink-0">
                                                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                                </div>
                                                Liste approvisionnements
                                            </Link>
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <Link :href="route('commandes.search')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-100 shrink-0">
                                                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                                                </div>
                                                Recherche multicritères
                                            </Link>
                                            <Link :href="route('echeances')"
                                                  class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100 shrink-0">
                                                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                </div>
                                                Échéances fournisseur
                                            </Link>
                                        </div>
                                    </transition>
                                </div>
                            </div>
                        </div>

                        <!-- ── Right: CTA + Bell + User + Hamburger ── -->
                        <div class="flex items-center gap-2">
                            <!-- Nouvelle Vente CTA -->
                            <Link :href="route('ventes.create')"
                                  class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 hover:from-emerald-500 hover:to-teal-500 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span class="hidden sm:inline">Nouvelle Vente</span>
                            </Link>

                            <!-- Notification Bell -->
                            <Link :href="route('notifications.index')"
                                  class="relative p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                                </svg>
                                <span v-if="$page.props.notifications.length > 0"
                                      class="absolute top-1 right-1 flex items-center justify-center w-4.5 h-4.5 text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white">
                                    {{ $page.props.notifications.length }}
                                </span>
                            </Link>

                            <!-- Desktop: User Dropdown -->
                            <div class="hidden lg:flex lg:items-center lg:gap-1 lg:ml-1">
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all">
                                            {{ $page.props.auth.user.current_team.name }}
                                            <svg class="w-3.5 h-3.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/></svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="w-60">
                                            <div class="block px-4 py-2 text-xs text-gray-400">Gérer l'équipe</div>
                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">Paramètres</DropdownLink>
                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">Créer une équipe</DropdownLink>
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200"/>
                                                <div class="block px-4 py-2 text-xs text-gray-400">Changer d'équipe</div>
                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>

                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-all cursor-pointer">
                                            <img v-if="$page.props.jetstream.managesProfilePhotos"
                                                 class="h-8 w-8 rounded-full object-cover ring-2 ring-gray-200"
                                                 :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                            <div v-else class="flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-xs font-bold">
                                                {{ $page.props.auth.user.name.substring(0, 2).toUpperCase() }}
                                            </div>
                                            <div class="hidden xl:block text-left">
                                                <p class="text-sm font-medium text-gray-700 leading-tight">{{ $page.props.auth.user.name }}</p>
                                                <p class="text-[11px] text-gray-400 leading-tight">{{ $page.props.auth.user.roles?.[0]?.name ?? '' }}</p>
                                            </div>
                                            <svg class="w-3.5 h-3.5 text-gray-400 hidden xl:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <div class="px-4 py-3 border-b border-gray-100">
                                            <p class="text-sm font-semibold text-gray-900">{{ $page.props.auth.user.name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                                Profil
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink v-if="$page.props.auth.user.roles[0].name === 'Administrateur'" :href="route('users.index')">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                                Utilisateurs
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink v-if="$page.props.auth.user.roles[0].name === 'Administrateur'" :href="route('roles.index')">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                                Rôles
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">Jetons API</DropdownLink>
                                        <div class="border-t border-gray-100"/>
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                <div class="flex items-center gap-2 text-red-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                                    Déconnexion
                                                </div>
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Hamburger (mobile/tablet) -->
                            <button class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition lg:hidden"
                                    @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown}"
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown}"
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ═══ Responsive Navigation Menu ═══ -->
                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                    <div v-if="showingNavigationDropdown" class="lg:hidden border-t border-gray-200 bg-white">
                        <!-- Quick CTA -->
                        <div class="px-4 pt-3 pb-2">
                            <Link :href="route('ventes.create')"
                                  class="flex items-center justify-center gap-2 w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:from-emerald-500 hover:to-teal-500 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Nouvelle Vente
                            </Link>
                        </div>

                        <div class="pt-2 pb-3 space-y-1">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">Tableau de bord</ResponsiveNavLink>

                            <div class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produits</span></div>
                            <ResponsiveNavLink v-if="can('Ajouter Produit')" :href="route('produits.create')">Ajouter un produit</ResponsiveNavLink>
                            <ResponsiveNavLink v-if="can('Lister Produit')" :href="route('produits.index')" :active="route().current('produits.index')">Liste des produits</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('produits.peremption')">Calendrier péremptions</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('produits.perimes')">Produits périmés</ResponsiveNavLink>

                            <div class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Destockages</span></div>
                            <ResponsiveNavLink :href="route('destockages.create')">Destocker</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('destockages.index')" :active="route().current('destockages.index')">Liste des destockages</ResponsiveNavLink>

                            <ResponsiveNavLink :href="route('categories.index')" :active="route().current('categories.*')">Catégories</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('fournisseurs.index')" :active="route().current('fournisseurs.*')">Fournisseurs</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('clients.index')" :active="route().current('clients.*')">Clients</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('ventes.index')" :active="route().current('ventes.*')">Ventes</ResponsiveNavLink>

                            <div class="px-4 pt-3 pb-1"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Approvisionnement</span></div>
                            <ResponsiveNavLink :href="route('commandes.create')">Nouvel approvisionnement</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('commandes.index')" :active="route().current('commandes.index')">Liste des approvisionnements</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('commandes.search')">Recherche multicritères</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('echeances')">Échéances fournisseur</ResponsiveNavLink>
                        </div>

                        <!-- Responsive User Section -->
                        <div class="pt-4 pb-3 border-t border-gray-200">
                            <div class="flex items-center px-4 gap-3">
                                <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0">
                                    <img class="h-10 w-10 rounded-full object-cover ring-2 ring-gray-200" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                </div>
                                <div v-else class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-sm font-bold shrink-0">
                                    {{ $page.props.auth.user.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">Profil</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="$page.props.auth.user.roles[0].name === 'Administrateur'" :href="route('users.index')">Utilisateurs</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="$page.props.auth.user.roles[0].name === 'Administrateur'" :href="route('roles.index')">Rôles</ResponsiveNavLink>
                                <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">Jetons API</ResponsiveNavLink>

                                <template v-if="$page.props.jetstream.hasTeamFeatures">
                                    <div class="border-t border-gray-200"/>
                                    <div class="block px-4 py-2 text-xs text-gray-400">Gérer l'équipe</div>
                                    <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">Paramètres</ResponsiveNavLink>
                                    <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">Créer une équipe</ResponsiveNavLink>
                                    <template v-if="$page.props.auth.user.all_teams.length > 1">
                                        <div class="border-t border-gray-200"/>
                                        <div class="block px-4 py-2 text-xs text-gray-400">Changer d'équipe</div>
                                        <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                            <form @submit.prevent="switchToTeam(team)">
                                                <ResponsiveNavLink as="button">
                                                    <div class="flex items-center">
                                                        <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                        <div>{{ team.name }}</div>
                                                    </div>
                                                </ResponsiveNavLink>
                                            </form>
                                        </template>
                                    </template>
                                </template>

                                <div class="border-t border-gray-200"/>
                                <form @submit.prevent="logout">
                                    <ResponsiveNavLink as="button">
                                        <span class="text-red-600">Déconnexion</span>
                                    </ResponsiveNavLink>
                                </form>
                            </div>
                        </div>
                    </div>
                </transition>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header"/>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot/>
            </main>
        </div>
    </div>
</template>
