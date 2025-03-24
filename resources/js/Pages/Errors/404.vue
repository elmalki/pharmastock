<script setup>
import { computed } from 'vue'

const props = defineProps({ status: Number })

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    }[props.status]
})

const description = computed(() => {
    return {
        503: 'Désolé, nous effectuons quelques travaux de maintenance. Veuillez revenir bientôt.',
        500: 'Oups, quelque chose s\'est mal passé sur nos serveurs..',
        404: 'Désolé, la page que vous recherchez est introuvable.',
        403: 'Sorry, you are forbidden from accessing this page.',
    }[props.status]
})
</script>

<template>
    <title>TEST</title>
    <div class="flex w-full text-center items-center justify-center h-screen text-red-500 m-auto">
        <section class="bg-white dark:bg-gray-900">
            <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
                <div class="mx-auto max-w-screen text-center">
                    <h1 class="mb-4 text-8xl  tracking-tight font-bold lg:text-7xl text-indigo-600 dark:text-indigo-500">
                        {{ props.status }}</h1>
                    <p class="mb-4 text-3xl tracking-tight font-bold text-gray-900 md:text-4xl dark:text-white">
                        Il manque quelque chose!</p>
                    <p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">{{description}} </p>
                    <a href="#" class="inline-flex text-white bg-primary-600 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-primary-900 my-4">Back to Homepage</a>
                </div>
            </div>
        </section>
    </div>
</template>
