<template>
    <nav v-if="last_page>1" id="paginator" class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0 py-6">
        <div class="-mt-px flex w-0 flex-1">
            <button  v-show="current_page!=1" @click="router.get(url,{...route().params,page:current_page-1})"
               class="inline-flex items-center pr-1 pt-4 text-sm font-medium text-gray-500 hover:text-indigo-700">
                <ArrowLongLeftIcon class="mr-3 h-5 w-5 " aria-hidden="true"/>
                Précédent
            </button>
        </div>
        <div class="hidden md:-mt-px md:flex" v-for="(link,i) in links.filter((el,index)=>index!=0 && index!=links.length-1)" v-key="i">

            <button v-if="link.label!='...'"
               @click="router.get(url,{...route().params,page:link.label})"
               :class="current_page==link.label?'inline-flex items-center border-t-2 border-solid border-indigo-500 px-4 pt-4 text-md font-medium text-indigo-600':'inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700'">
                {{link.label}}</button>
            <!-- Current: "border-indigo-500 text-indigo-600", Default: "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" -->
            <span v-else class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-700" >{{ link.label }}</span>


        </div>
        <div class="-mt-px flex w-0 flex-1 justify-end">
            <button  @click="router.get(url,{...route().params,page:current_page+1})" v-show="current_page!=last_page"
                     class="inline-flex items-center pr-1 pt-4 text-sm font-medium text-gray-500 hover:text-indigo-700">
                Suivant
                <ArrowLongRightIcon class="ml-3 h-5 w-5" aria-hidden="true"/>
            </button>
        </div>
    </nav>
</template>

<script setup>
import { ArrowLongLeftIcon, ArrowLongRightIcon } from '@heroicons/vue/20/solid'
import {router} from "@inertiajs/vue3";
defineProps({
    url:String,
    first_page_url:String,
    last_page_url:String,
    next_page_url:String,
    prev_page_url:String,
    last_page:Number,
    total:Number,
    current_page:Number,
    per_page:Number,
    from:Number,
    to:Number,
    links:Array
})
</script>

