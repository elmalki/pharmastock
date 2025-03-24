import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import PrimeVue from 'primevue/config';
import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: '{indigo.50}',
            100: '{indigo.100}',
            200: '{indigo.200}',
            300: '{indigo.300}',
            400: '{indigo.400}',
            500: '{indigo.500}',
            600: '{indigo.600}',
            700: '{indigo.700}',
            800: '{indigo.800}',
            900: '{indigo.900}',
            950: '{indigo.950}'
        }
    }
});
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const vuetify = createVuetify({
    components,
    directives,
    treeShake: true,
    locale: {
        locale: 'Fr-fr',
    },
})
const local_fr = {
    startsWith: 'Commence par',
    contains: 'Contient',
    notContains: 'Ne contient pas',
    endsWith: 'Se termine par',
    equals: 'Égal à',
    notEquals: 'Différent de',
    noFilter: 'Pas de filtre',
    lt: 'Moins que',
    lte: 'Moins que ou égal à',
    gt: 'Supérieur à',
    gte: 'Supérieur ou égal à',
    dateIs: 'La date est',
    dateIsNot: 'La date n’est pas',
    dateBefore: 'La date est avant',
    dateAfter: 'La date est après',
    clear: 'Effacer',
    apply: 'Appliquer',
    matchAll: 'Correspondre à tout',
    matchAny: 'Correspondre à un',
    addRule: 'Ajouter une règle',
    removeRule: 'Supprimer la règle',
    accept: 'Oui',
    reject: 'Non',
    choose: 'Choisir',
    upload: 'Télécharger',
    cancel: 'Annuler',
    completed: 'Terminé',
    pending: 'En attente',
    fileSizeTypes: ['B', 'Ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'],
    dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
    dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
    dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
    monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
    monthNamesShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui', 'Jui', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
    chooseYear: 'Choisir l\'année',
    chooseMonth: 'Choisir le mois',
    chooseDate: 'Choisir la date',
    prevDecade: 'Décennie précédente',
    nextDecade: 'Décennie suivante',
    prevYear: 'Année précédente',
    nextYear: 'Année suivante',
    prevMonth: 'Mois précédent',
    nextMonth: 'Mois suivant',
    prevHour: 'Heure précédente',
    nextHour: 'Heure suivante',
    prevMinute: 'Minute précédente',
    nextMinute: 'Minute suivante',
    prevSecond: 'Seconde précédente',
    nextSecond: 'Seconde suivante',
    am: 'matin',
    pm: 'après-midi',
    today: 'Aujourd\'hui',
    weekHeader: 'Sem',
    firstDayOfWeek: '0',
    showMonthAfterYear: 'faux',
    dateFormat: 'jj/mm/aa',
    weak: 'Faible',
    medium: 'Moyen',
    strong: 'Fort',
    passwordPrompt: 'Entrez un mot de passe',
    searchMessage: '{0} résultats disponibles',
    selectionMessage: '{0} éléments sélectionnés',
    emptySelectionMessage: 'Aucun élément sélectionné',
    emptySearchMessage: 'Aucun résultat trouvé',
    fileChosenMessage: '{0} fichiers',
    noFileChosenMessage: 'Aucun fichier choisi',
    emptyMessage: 'Aucune option disponible',
    aria: {
        trueLabel: 'Vrai',
        falseLabel: 'Faux',
        nullLabel: 'Non sélectionné',
        star: '1 étoile',
        stars: '{star} étoiles',
        selectAll: 'Tous les éléments sélectionnés',
        unselectAll: 'Aucun élément sélectionné',
        close: 'Fermer',
        previous: 'Précédent',
        next: 'Suivant',
        navigation: 'Navigation',
        scrollTop: 'Faire défiler en haut',
        moveTop: 'Déplacer en haut',
        moveUp: 'Monter',
        moveDown: 'Descendre',
        moveBottom: 'Déplacer en bas',
        moveToTarget: 'Déplacer vers la cible',
        moveToSource: 'Déplacer vers la source',
        moveAllToTarget: 'Déplacer tout vers la cible',
        moveAllToSource: 'Déplacer tout vers la source',
        pageLabel: 'Page {page}',
        firstPageLabel: 'Première page',
        lastPageLabel: 'Dernière page',
        nextPageLabel: 'Page suivante',
        prevPageLabel: 'Page précédente',
        rowsPerPageLabel: 'Lignes par page',
        jumpToPageDropdownLabel: 'Menu déroulant pour aller à la page',
        jumpToPageInputLabel: 'Saisie pour aller à la page',
        selectRow: 'Ligne sélectionnée',
        unselectRow: 'Ligne non sélectionnée',
        expandRow: 'Ligne dépliée',
        collapseRow: 'Ligne repliée',
        showFilterMenu: 'Afficher le menu de filtre',
        hideFilterMenu: 'Masquer le menu de filtre',
        filterOperator: 'Opérateur de filtre',
        filterConstraint: 'Contraintes de filtre',
        editRow: 'Éditer la ligne',
        saveEdit: 'Sauvegarder l\'édition',
        cancelEdit: 'Annuler l\'édition',
        listView: 'Vue liste',
        gridView: 'Vue grille',
        slide: 'Diapositive',
        slideNumber: '{numéroDeDiapositive}',
        zoomImage: 'Zoom sur l\'image',
        zoomIn: 'Zoom avant',
        zoomOut: 'Zoom arrière',
        rotateRight: 'Tourner à droite',
        rotateLeft: 'Tourner à gauche'
    }

}
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                locale: local_fr,
                theme: {
                    preset: MyPreset,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '.my-app-dark',
                        cssLayer: false
                    }
                }
            })
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
