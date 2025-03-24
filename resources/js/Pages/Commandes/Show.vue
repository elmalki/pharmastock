<template>
    <AppLayout>
        <button @click="exportToPDF()">download</button>
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" id="element-to-convert">
            <div class="mx-auto max-w-2xl items-start gap-x-8 gap-y-8 lg:mx-0 lg:max-w-none ">
                <div class="-mx-4 px-4 py-8 shadow-sm ring-1 ring-teal-400 sm:mx-0 sm:rounded-lg sm:px-8 sm:pb-14 lg:col-span-2 lg:row-span-2 lg:row-end-2 xl:px-16 xl:pb-20 xl:pt-16">
                    <h2 class="text-base font-semibold text-gray-900">Commande</h2>
                    <dl class="mt-6 grid grid-cols-1 text-sm/6 sm:grid-cols-2">
                        <div class="sm:pr-4">
                            <dt class="inline text-gray-700">Date </dt>
                            {{ ' ' }}
                            <dd class="inline text-gray-900"><time datetime="2023-23-01">{{commande.created_at.substring(0,10)}}</time></dd>
                        </div>
                        <div class="sm:mt-0 sm:pl-4">
                            <dt class="inline text-gray-700">Date Échéance</dt>
                            {{ ' ' }}
                            <dd class="inline text-gray-900"><time datetime="2023-31-01">{{commande.dateEcheance}}</time></dd>
                        </div>
                        <div class="mt-6 sm:mt-6 sm:border-t sm:border-gray-200 sm:pl-4 sm:pt-6">
                            <dd class="mt-2 text-gray-500">
                                <span class="font-medium text-gray-900">N° Facture: {{commande.n_facture}}</span><br />
                                <span class="font-medium text-gray-900">N° Bon: {{commande.n_bon}}</span>
                            </dd>
                        </div>
                        <div class="mt-6 border-t border-gray-900 pt-6 sm:pr-4">
                            <dt class="font-semibold text-gray-900">Founisseur</dt>
                            <dd class="mt-2 text-gray-900">
                                <span class="font-medium text-gray-900">{{commande.fournisseur?.societe}}</span><br />
                                {{commande.fournisseur?.adresse}}<br />{{commande.fournisseur?.email}}<br />{{commande.fournisseur?.phone}}</dd>
                        </div>
                    </dl>
                    <table class="mt-16 w-full whitespace-nowrap text-left text-sm/6">
                        <colgroup>
                            <col class="w-full" />
                            <col />
                            <col />
                            <col />
                        </colgroup>
                        <thead class="border-b border-gray-200 text-gray-900">
                        <tr>
                            <th scope="col" class="px-0 py-3 font-semibold">N° Lot</th>
                            <th scope="col" class="px-0 py-3 font-semibold">Libellé</th>
                            <th scope="col" class="hidden py-3 pl-8 pr-0 text-right font-semibold sm:table-cell">Prix HT</th>
                            <th scope="col" class="hidden py-3 pl-8 pr-0 text-right font-semibold sm:table-cell">Quantité</th>
                            <th scope="col" class="hidden py-3 pl-8 pr-0 text-right font-semibold sm:table-cell">TVA</th>
                            <th scope="col" class="py-3 pl-8 pr-0 text-right font-semibold">Prix TTC</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in commande.produits" :key="item.id" class="border-b border-gray-100">
                            <td class="max-w-0 px-0 py-5 align-top">
                                <div class="font-medium text-gray-900">{{ item.pivot.n_lot }}</div>
                            </td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.label }}</td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.pivot.prix_achat }}</td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.pivot.qte }}</td>
                            <td class="hidden py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700 sm:table-cell">{{ item.pivot.tva }}%</td>
                            <td class="py-5 pl-8 pr-0 text-right align-top tabular-nums text-gray-700">{{ item.pivot.prix_achat*item.pivot.qte }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th scope="row" class="px-0 pb-0 pt-6 font-normal text-gray-700 sm:hidden">Sous Total</th>
                            <th scope="row" colspan="5" class="hidden px-0 pb-0 pt-6 text-right font-normal text-gray-700 sm:table-cell">Sous Total</th>
                            <td class="pb-0 pl-8 pr-0 pt-6 text-right tabular-nums text-gray-900">{{ soustotal }}</td>
                        </tr>
                        <tr v-for="(value,key) in tva" v-show="value">
                            <th scope="row" class="pt-4 font-normal text-gray-700 sm:hidden">Tax</th>
                            <th scope="row" colspan="5" class="hidden pt-4 text-right font-normal text-gray-700 sm:table-cell">TVA {{key}}%</th>
                            <td class="pb-0 pl-8 pr-0 pt-4 text-right tabular-nums text-gray-900">{{value}}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="pt-4 font-semibold text-gray-900 sm:hidden">Total</th>
                            <th scope="row" colspan="5" class="hidden pt-4 text-right font-semibold text-gray-900 sm:table-cell">Total</th>
                            <td class="pb-0 pl-8 pr-0 pt-4 text-right font-semibold tabular-nums text-gray-900">{{ tax+soustotal }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div hidden="hidden">TEST</div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import html2pdf from "html2pdf.js";
const props = defineProps({commande:Object})
const tva = {}
let soustotal = 0
let tax = 0
props.commande.produits.forEach(el=>{
    soustotal += el.pivot.qte*el.pivot.prix_achat
    tax += el.pivot.qte*el.pivot.prix_achat*el.pivot.tva*0.01
   if(tva.hasOwnProperty(el.pivot.tva)){
      tva[el.pivot.tva] += el.pivot.qte*el.pivot.prix_achat*el.pivot.tva*0.01
   }else{
       tva[el.pivot.tva] = el.pivot.qte*el.pivot.prix_achat*el.pivot.tva*0.01
   }
})
function  exportToPDF() {
    html2pdf(document.getElementById("element-to-convert"), {
        margin: 1,
        filename: "generated-pdf.pdf",
    }).then(function (pdf) {
        var totalPages = pdf.internal.getNumberOfPages();

        for (let i = 1; i <= totalPages; i++) {
            pdf.setPage(i);
            pdf.setFontSize(10);
            pdf.setTextColor(150);
            //Add you content in place of example here
            pdf.text('example', pdf.internal.pageSize.getWidth() - 100, pdf.internal.pageSize.getHeight() - 10);
        }
    });
}
</script>
