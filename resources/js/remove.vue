<template>
    <div>
    <v-dialog v-model="dialog" width="400px">
      <v-card>
        <v-card-title class="title warning font-weight-light">
          <v-icon large color="grey darken-3" class="mr-3">warning</v-icon>{{title}}
        </v-card-title>
        <v-card-text class="body-2 warning font-weight-thin">Voulez-vous vraiment supprimer cet élément de la liste</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="light-green"  outline dark @click.native="$emit('confirmDelete')">Oui
            <v-icon right>check_circle</v-icon>
          </v-btn>
          <v-btn dark color="pink" outline @click.native="$emit('cancelDelete')">Non
            <v-icon right>block</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </div>
</template>
<script>
export default {
    props:['title'],
    data(){
        return{
          dialog:true
        }
    },
    methods:{

    }
}
</script>
