
<template>
  <v-list>
    <v-list-group :value="false" prepend-icon="https">
      <v-subheader class="primary--text" slot="activator">Administration</v-subheader>
    </v-list-group>
    <v-list-group :value="false" prepend-icon="settings">
      <v-subheader class="primary--text" slot="activator">Paramètre</v-subheader>
      <router-link tag="v-list-tile" to="/categories">
        <v-list-tile-action>
          <v-icon>monetization_on</v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title class="font-weight-thin">Catégories</v-list-tile-title>
        </v-list-tile-content>
      </router-link>
    </v-list-group>
    <v-list-group :value="false" prepend-icon="fas fa-truck">
      <v-subheader class="primary--text" slot="activator">Gestion fournisseurs</v-subheader>
      <v-list>
        <router-link tag="v-list-tile" to="/addFournisseur">
          <v-list-tile-action>
            <v-icon>fas fa-plus-circle</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Ajouter</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
      </v-list>
      <v-list>
        <router-link tag="v-list-tile" to="/fournisseurs">
          <v-list-tile-action>
            <v-icon>list</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Liste</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
      </v-list>
    </v-list-group>

    <!-- <v-list>
      <router-link tag="v-list-tile" to="/login">
        <v-list-tile-action>
          <v-icon>lock</v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title class="font-weight-thin">login</v-list-tile-title>
        </v-list-tile-content>
      </router-link>
    </v-list>-->
    <v-list-group :value="false" prepend-icon="local_hospital">
      <v-subheader class="primary--text" slot="activator">Gestion Produits</v-subheader>
      <v-list>
        <router-link tag="v-list-tile" to="/addProduit">
          <v-list-tile-action>
            <v-icon>fas fa-plus-circle</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Ajouter</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
      </v-list>
      <v-list>
        <router-link tag="v-list-tile" to="/peremption">
          <v-list-tile-action>
            <v-icon>event</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Calendrier de péremption</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
      </v-list>
    </v-list-group>
    <v-list-group :value="false" prepend-icon="import_export">
      <v-subheader class="primary--text" slot="activator">Gestion Stock</v-subheader>
      <v-list>
        <router-link tag="v-list-tile" to="/entrees">
          <v-list-tile-action>
            <v-icon>arrow_downward</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Stockage</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
        <router-link tag="v-list-tile" to="/sorties">
          <v-list-tile-action>
            <v-icon>arrow_upward</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Déstockage</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
                <router-link tag="v-list-tile" to="/produits">
          <v-list-tile-action>
            <v-icon>list</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Situation du Stock</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
        <router-link tag="v-list-tile" to="/rechercheCommande">
          <v-list-tile-action>
            <v-icon>search</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">Chercher</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
        <router-link tag="v-list-tile" to="/factures">
          <v-list-tile-action>
            <v-icon>fas fa-file-invoice-dollar</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title class="font-weight-thin">factures</v-list-tile-title>
          </v-list-tile-content>
        </router-link>
      </v-list>
    </v-list-group>
    <router-link tag="v-list-tile" to="/agenda">
      <v-list-tile-action>
        <v-icon>event</v-icon>
      </v-list-tile-action>
      <v-list-tile-content>
        <v-list-tile-title class="font-weight-thin">Agenda & Rappels</v-list-tile-title>
      </v-list-tile-content>
    </router-link>
  </v-list>
</template>
<script>
export default {
  data() {
    return {};
  },
  mounted() {}
};
</script>
<style scoped>
.v-list {
  padding: 0px !important;
}

.theme--dark .v-icon {
  color: white;
}
</style>
