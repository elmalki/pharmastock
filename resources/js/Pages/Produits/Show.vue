<script setup>

import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumbs from "@/Components/Breadcrumbs.vue";
const props = defineProps({produit:Object})
</script>

<template>
    <AppLayout :title="produit.label">
        <div class="max-w-7xl mx-auto my-10">
            <Breadcrumbs :pages="[{name:'Produits',href:route('produits.index'),current:false},{name:produit.label,href:route('produits.show',{produit:produit.id}),current:true}]"></Breadcrumbs>
            <div class="overflow-hidden rounded-xl mt-3 border border-slate-500">
                <div class="flex justify-between items-center gap-x-4 border-b border-gray-900/5 bg-amber-300 p-6">
                    <div class="text-sm/6 font-medium text-center text-xl  w-full text-gray-900">{{produit.label}}</div>
                </div>
                <dl class="-my-3 divide-y divide-gray-300 px-6 py-4 text-sm/6">
                    <div class="flex justify-between gap-x-4 py-3">
                        <dt class="text-gray-700">Catégorie</dt>
                        <dd class="text-gray-700">{{produit.categorie?.label}}</dd>
                    </div>
                    <div class="flex justify-between gap-x-4 py-3">
                        <dt class="text-gray-700">Stock <span v-if="produit.qte<=produit.limit_command" class="rounded-md py-1 text-xs font-medium text-red-700 ">(En rupture)</span></dt>
                        <dd class="flex items-start gap-x-2">
                            <div class="font-medium text-gray-900">{{produit.qte}}</div>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-x-4 py-3">
                        <dt class="text-gray-700">Stock Critique</dt>
                        <dd class="flex items-start gap-x-2">
                            <div class="font-medium text-gray-900">{{produit.limit_command}}</div>
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-8 lg:grid-cols-2 xl:gap-x-8">
                <div class="overflow-hidden rounded-xl border border-slate-500" v-for="commande in produit.commandes">
                    <div class="flex justify-between items-center gap-x-4 border-b border-gray-900/5 bg-amber-200 p-6">
                        <div class="text-md font-medium text-gray-900">Facture: {{commande.n_facture}}</div>
                        <div class="text-md font-medium text-gray-900">Lot: {{commande.n_lot}}</div>
                    </div>
                    <dl class="-my-3 divide-y divide-gray-300 px-6 py-4 text-sm/6">
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Date Achat</dt>
                            <dd class="text-gray-900 font-medium"><time datetime="2023-01-22">{{commande.created_at.substring(0,10).split('-').reverse().join('/')}}</time></dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3" v-if="commande.pivot.expirationDate">
                            <dt class="text-gray-700">Date Péremption</dt>
                            <dd class="text-gray-900 font-medium"><time datetime="2023-01-22">{{commande.pivot.expirationDate}}</time></dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Quantité acheté</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{commande.pivot.qte_achete}}</div>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">En stock</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{commande.pivot.qte}}</div>
                            </dd>
                        </div>
                        <!--div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Qte Vendue</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{commande.pivot.qte_achete-commande.pivot.qte}}</div>
                            </dd>
                        </div-->
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Prix d'achat (HT)</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{commande.pivot.prix_achat}}</div>
                            </dd>
                        </div>
                        <!--div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Prix de Vente</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{commande.pivot.prix_vente}}</div>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">Revenu</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{(commande.pivot.qte_achete-commande.pivot.qte)*commande.pivot.prix_vente}} Dhs</div>
                            </dd>
                        </div-->
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-700">TVA({{commande.pivot.tva}}%)</dt>
                            <dd class="flex items-start gap-x-2">
                                <div class="font-medium text-gray-900">{{(commande.pivot.qte_achete)*commande.pivot.prix_achat*commande.pivot.tva/100}} Dhs</div>
                            </dd>
                        </div>
                    </dl>
                </div>

            </div>

        </div>
    </AppLayout>
</template>

<style scoped>

</style>
