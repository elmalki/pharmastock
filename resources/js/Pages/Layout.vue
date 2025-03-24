<template>
    <v-app id="inspire">
        <v-app-bar
            color="primary"
            prominent
        >
            <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>

            <v-toolbar-title>My files</v-toolbar-title>

            <v-spacer></v-spacer>

            <template v-if="$vuetify.display.mdAndUp">
                <v-btn icon="mdi-magnify" variant="text"></v-btn>

                <v-btn icon="mdi-filter" variant="text"></v-btn>
            </template>

            <v-btn icon="mdi-dots-vertical" variant="text"></v-btn>
        </v-app-bar>
        <v-navigation-drawer v-model="drawer">
            <v-sheet
                class="pa-4"
                color="grey-lighten-4"
            >
                <v-avatar
                    class="mb-4"
                    color="grey-darken-1"
                    size="64"
                ></v-avatar>

                <div>john@google.com</div>
            </v-sheet>

            <v-divider></v-divider>

            <v-list v-model:opened="open"  color="primary--text">
                <v-list-item
                    v-for="[icon, text] in links"
                    :key="icon"
                    :prepend-icon="icon"
                    :title="text"
                    link
                ></v-list-item>
                <Link href="/dashboard">
                <v-list-item
                    key="mdi-inbox-arrow-down"
                    prepend-icon="mdi-inbox-arrow-down"
                    title="Dashboard"
                    link
                ></v-list-item>
                </Link>
                <v-list-item prepend-icon="mdi-home" title="Home"></v-list-item>

                <v-list-group value="Users">
                    <template v-slot:activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-account-circle"
                            title="Users"
                        ></v-list-item>
                    </template>

                    <v-list-group value="Admin">
                        <template v-slot:activator="{ props }">
                            <v-list-item
                                v-bind="props"
                                title="Admin"
                            ></v-list-item>
                        </template>

                        <v-list-item
                            v-for="([title, icon], i) in admins"
                            :key="i"
                            :prepend-icon="icon"
                            :title="title"
                            :value="title"
                        ></v-list-item>
                    </v-list-group>

                    <v-list-group value="Actions">
                        <template v-slot:activator="{ props }">
                            <v-list-item
                                v-bind="props"
                                title="Actions"
                            ></v-list-item>
                        </template>

                        <v-list-item
                            v-for="([title, icon], i) in cruds"
                            :key="i"
                            :prepend-icon="icon"
                            :title="title"
                            :value="title"
                        ></v-list-item>
                    </v-list-group>
                </v-list-group>
            </v-list>
        </v-navigation-drawer>
        <v-main>
            <v-container
                class="py-8 px-6"
                fluid
            >
                <v-row>
                    <v-col
                        v-for="card in cards"
                        :key="card"
                        cols="12"
                    >
                        <v-card>
                            <v-list lines="two">
                                <v-list-subheader :title="card"></v-list-subheader>

                                <template v-for="n in 6" :key="n">
                                    <v-list-item>
                                        <template v-slot:prepend>
                                            <v-avatar color="grey-darken-1"></v-avatar>
                                        </template>

                                        <v-list-item-title :title="`Message ${n}`"></v-list-item-title>

                                        <v-list-item-subtitle title="Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil repellendus distinctio similique"></v-list-item-subtitle>
                                    </v-list-item>

                                    <v-divider
                                        v-if="n !== 6"
                                        :key="`divider-${n}`"
                                        inset
                                    ></v-divider>
                                </template>
                            </v-list>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const cards = ['Today', 'Yesterday']
const links = [
    ['mdi-inbox-arrow-down', 'Inbox'],
    ['mdi-send', 'Send'],
    ['mdi-delete', 'Trash'],
    ['mdi-alert-octagon', 'Spam'],
]
const admins= [
    ['Management', 'mdi-account-multiple-outline'],
    ['Settings', 'mdi-cog-outline'],
]
  const  cruds =[
    ['Create', 'mdi-plus-outline'],
    ['Read', 'mdi-file-outline'],
    ['Update', 'mdi-update'],
    ['Delete', 'mdi-delete'],
]

const drawer = ref(null)
const open = ref(null)
</script>

<script>
export default {
    data: () => ({
        cards: ['Today', 'Yesterday'],
        drawer: null,
        links: [
            ['mdi-inbox-arrow-down', 'Inbox'],
            ['mdi-send', 'Send'],
            ['mdi-delete', 'Trash'],
            ['mdi-alert-octagon', 'Spam'],
        ],
    }),
}
</script>
