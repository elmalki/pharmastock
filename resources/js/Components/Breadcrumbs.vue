<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="flex items-center space-x-4">
            <li>
                <div>
                    <Link :href="route('dashboard')" class="text-gray-400 hover:text-gray-500">
                        <HomeIcon class="size-5 shrink-0" aria-hidden="true" />
                        <span class="sr-only">Home</span>
                    </Link>
                </div>
            </li>
            <li v-for="page in pages" :key="page.name">
                <div class="flex items-center">
                    <svg class="size-5 shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                    </svg>
                    <Link :href="page.href" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700" :class="{'text-indigo-500':page.current}">{{ page.name }}</Link>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script setup>
import { HomeIcon } from '@heroicons/vue/20/solid'
import {Link} from '@inertiajs/vue3'
const props = defineProps({pages:Array})
</script>
