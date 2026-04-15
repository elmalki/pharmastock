<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const dismissed = ref(false);

const errors = computed(() => page.props.errors || {});
const entries = computed(() => Object.entries(errors.value));
const visible = computed(() => entries.value.length > 0 && !dismissed.value);

watch(() => page.props.errors, () => {
    dismissed.value = false;
}, { deep: true });
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2">
        <div v-if="visible"
             class="fixed bottom-4 right-4 z-[100] max-w-md w-full rounded-xl border border-red-200 bg-red-50 shadow-xl">
            <div class="flex items-start gap-3 p-4">
                <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-red-800">
                        {{ entries.length }} erreur{{ entries.length > 1 ? 's' : '' }} de validation
                    </h3>
                    <ul class="mt-2 text-sm text-red-700 space-y-1 max-h-64 overflow-y-auto list-disc list-inside">
                        <li v-for="[field, message] in entries" :key="field" class="break-words">
                            {{ message }}
                        </li>
                    </ul>
                </div>
                <button type="button" @click="dismissed = true"
                        class="shrink-0 text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>
