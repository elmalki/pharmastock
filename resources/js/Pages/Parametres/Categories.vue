<template>
  <AppLayout title="Dashboard">
      {{$page}}
      <div class="text-xl-h1" v-if="message">{{message}}</div>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <v-row>
          <v-col cols="12">
            <div>
              <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    color="primary"
                    dark
                    class="mb-2"
                    v-bind="attrs"
                    v-on="on"
                  >
                    Nouvelle Catégorie
                  </v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                  </v-card-title>
                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12">
                          <v-text-field
                            label="Libellé"
                            v-model="editedItem.label"
                            :error="errors.collect('libellé')"
                            required
                          ></v-text-field>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" variant="text" @click="close">
                      Annuler
                    </v-btn>
                    <v-btn color="blue darken-1" variant="text" @click="save">
                      Enregistrer
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

              <v-card>
                <v-card-title>
                  <v-spacer></v-spacer>
                  <v-text-field
                    append-icon="mdi-magnify"
                    label="Chercher"
                    single-line
                    hide-details
                    v-model="search"
                  ></v-text-field>
                </v-card-title>
                <v-data-table
                  :headers="headers"
                  :items="items"
                  :search="search"
                  :items-per-page="5"
                  no-results-text="Aucun enregistrements correspondants trouvés"
                >
                  <template v-slot:item.label="{ item }">
                    <span>{{ item.label }}</span>
                  </template>
                  <template v-slot:item.actions="{ item }">
                    <v-btn icon @click="addProduct(item)">
                      <v-icon color="blue">mdi-plus-circle</v-icon>
                    </v-btn>
                    <v-btn icon @click="editItem(item)">
                      <v-icon color="teal">mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon @click="deleteItem(item)">
                      <v-icon color="pink">mdi-delete</v-icon>
                    </v-btn>
                  </template>
                  <template v-slot:no-data>
                    <v-alert type="error" icon="mdi-alert">
                      Aucune donnée pour le moment
                    </v-alert>
                  </template>
                  <template
                    v-slot:footer.page-text="{
                      pageStart,
                      pageStop,
                      itemsLength,
                    }"
                  >
                    {{ pageStart }} - {{ pageStop }} sur {{ itemsLength }}
                  </template>
                </v-data-table>
              </v-card>
            </div>
          </v-col>

          <v-snackbar
            top
            :timeout="timeout"
            :color="snackbar_color"
            v-model="snackbar"
          >
            {{ snackbar_message }}
            <v-btn dark variant="text" @click="snackbar = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-snackbar>

          <remove
            v-if="dialog_delete"
            title="Supprimer la catégorie"
            @confirmDelete="confirmDelete"
            @cancelDelete="dialog_delete = false"
          ></remove>
        </v-row>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import {ref, useAttrs} from "vue";
const page = useAttrs()
import remove from "../remove.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
defineProps({ items: Array,message });
const dialog = ref(false);
const editedItem = ref({label:''});
const editedIndex = ref(-1);
function close() {
  this.dialog = false;
  setTimeout(() => {
    this.editedItem = Object.assign({}, this.defaultItem);
    this.editedIndex = -1;
  }, 300);
}
</script>
