<script setup>
import {computed, ref} from 'vue';

const props = defineProps({
    permissions: {type: Array, required: true},
    modelValue: {type: Array, required: true},
});
const emit = defineEmits(['update:modelValue']);

const search = ref('');

// Action metadata: colors, icons, display order
const ACTIONS = {
    'Lister':    {label: 'Lister',    order: 1, color: 'sky',    iconPath: 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'},
    'Ajouter':   {label: 'Ajouter',   order: 2, color: 'emerald', iconPath: 'M12 4.5v15m7.5-7.5h-15'},
    'Modifier':  {label: 'Modifier',  order: 3, color: 'amber',  iconPath: 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125'},
    'Supprimer': {label: 'Supprimer', order: 4, color: 'rose',   iconPath: 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'},
};

const COLOR_CLASSES = {
    sky:     {chip: 'bg-sky-50 text-sky-700 ring-sky-200',        check: 'checked:bg-sky-600 checked:border-sky-600'},
    emerald: {chip: 'bg-emerald-50 text-emerald-700 ring-emerald-200', check: 'checked:bg-emerald-600 checked:border-emerald-600'},
    amber:   {chip: 'bg-amber-50 text-amber-700 ring-amber-200',  check: 'checked:bg-amber-600 checked:border-amber-600'},
    rose:    {chip: 'bg-rose-50 text-rose-700 ring-rose-200',      check: 'checked:bg-rose-600 checked:border-rose-600'},
};

// Parse permission name into {action, resource}
function parse(name) {
    const [action, ...rest] = name.split(' ');
    return {action, resource: rest.join(' ')};
}

// Group permissions by resource
const groups = computed(() => {
    const byResource = {};
    for (const perm of props.permissions) {
        const {action, resource} = parse(perm.name);
        if (!byResource[resource]) byResource[resource] = {resource, actions: {}};
        byResource[resource].actions[action] = perm;
    }
    return Object.values(byResource)
        .filter(g => !search.value || g.resource.toLowerCase().includes(search.value.toLowerCase()))
        .sort((a, b) => a.resource.localeCompare(b.resource));
});

const selectedSet = computed(() => new Set(props.modelValue));

function isChecked(id) {
    return selectedSet.value.has(id);
}

function toggle(id) {
    const next = new Set(props.modelValue);
    next.has(id) ? next.delete(id) : next.add(id);
    emit('update:modelValue', [...next]);
}

function groupCount(group) {
    let count = 0;
    for (const a of Object.values(group.actions)) if (selectedSet.value.has(a.id)) count++;
    return count;
}

function toggleGroup(group) {
    const ids = Object.values(group.actions).map(a => a.id);
    const allSelected = ids.every(id => selectedSet.value.has(id));
    const next = new Set(props.modelValue);
    if (allSelected) ids.forEach(id => next.delete(id));
    else ids.forEach(id => next.add(id));
    emit('update:modelValue', [...next]);
}

function selectAll() {
    emit('update:modelValue', props.permissions.map(p => p.id));
}

function selectNone() {
    emit('update:modelValue', []);
}

const totalSelected = computed(() => props.modelValue.length);
const totalAvailable = computed(() => props.permissions.length);
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-100">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-900">Permissions</div>
                    <div class="text-xs text-gray-500 tabular-nums">
                        {{ totalSelected }} / {{ totalAvailable }} sélectionnées
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Rechercher..."
                           class="h-9 w-48 rounded-lg border-gray-300 bg-white pl-8 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"/>
                </div>
                <button type="button" @click="selectAll"
                        class="h-9 px-3 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 ring-1 ring-inset ring-indigo-200 transition-colors">
                    Tout cocher
                </button>
                <button type="button" @click="selectNone"
                        class="h-9 px-3 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 ring-1 ring-inset ring-gray-300 transition-colors">
                    Tout décocher
                </button>
            </div>
        </div>

        <!-- Grid of resource cards -->
        <div class="p-5">
            <div v-if="groups.length === 0" class="py-10 text-center text-sm text-gray-400">
                Aucune ressource ne correspond à "{{ search }}"
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="group in groups" :key="group.resource"
                     class="rounded-lg border border-gray-200 bg-white hover:border-indigo-300 hover:shadow-sm transition-all">
                    <!-- Group header -->
                    <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-gray-100 bg-gray-50/60 rounded-t-lg">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-white ring-1 ring-gray-200 text-[10px] font-bold text-indigo-700">
                                {{ group.resource.substring(0, 2).toUpperCase() }}
                            </span>
                            <span class="text-sm font-semibold text-gray-900 truncate">{{ group.resource }}</span>
                        </div>
                        <button type="button" @click="toggleGroup(group)"
                                class="flex-shrink-0 inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-semibold transition-colors"
                                :class="groupCount(group) === Object.keys(group.actions).length
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-700'
                                    : 'bg-white text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'">
                            {{ groupCount(group) }}/{{ Object.keys(group.actions).length }}
                        </button>
                    </div>
                    <!-- Actions list -->
                    <div class="p-2 space-y-1">
                        <template v-for="(meta, actionName) in ACTIONS" :key="actionName">
                            <label v-if="group.actions[actionName]"
                                   :for="'perm-' + group.actions[actionName].id"
                                   class="flex items-center gap-2.5 px-2 py-1.5 rounded-md cursor-pointer transition-colors"
                                   :class="isChecked(group.actions[actionName].id) ? COLOR_CLASSES[meta.color].chip + ' ring-1 ring-inset' : 'hover:bg-gray-50'">
                                <input :id="'perm-' + group.actions[actionName].id"
                                       type="checkbox"
                                       :checked="isChecked(group.actions[actionName].id)"
                                       @change="toggle(group.actions[actionName].id)"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                       :class="COLOR_CLASSES[meta.color].check"/>
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="meta.iconPath"/>
                                </svg>
                                <span class="text-xs font-medium">{{ meta.label }}</span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
