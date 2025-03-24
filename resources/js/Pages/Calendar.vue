<template>
    <AppLayout>
        <div class="mx-auto max-w-7xl h-1/4 my-10 z-10 " >
            <ScheduleXCalendar :calendar-app="calendarApp" />
        </div>
        <DialogModal :show="show" :closeable="true" @close="show=false">
            <template #title>Information sur l'événement</template>
            <template #content>{{content}}</template>
            <template #footer>
                <DangerButton @click="show=false">Fermer</DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script setup>
import { ScheduleXCalendar } from '@schedule-x/vue'
import { createEventModalPlugin } from '@schedule-x/event-modal'

import {
    createCalendar,
    createViewDay,
    createViewMonthAgenda,
    createViewMonthGrid,
    createViewWeek,
    viewMonthGrid
} from '@schedule-x/calendar'
import '@schedule-x/theme-default/dist/index.css'
import AppLayout from "@/Layouts/AppLayout.vue";
import {onMounted, ref, watch} from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {createCalendarControlsPlugin} from "@schedule-x/calendar-controls";
const show = ref(false)
const content = ref(null)
onMounted(()=>{
    fetchCommandes(calendarControls.getRange().start,calendarControls.getRange().end)
})
// Do not use a ref here, as the calendar instance is not reactive, and doing so might cause issues
// For updating events, use the events service plugin
const eventModal = createEventModalPlugin()
const calendarControls = createCalendarControlsPlugin()
const calendarApp = createCalendar({
    calendars: {
        personal: {
            colorName: 'personal',
            lightColors: {
                main: '#f9d71c',
                container: '#fff5aa',
                onContainer: '#594800',
            },
            darkColors: {
                main: '#fff5c0',
                onContainer: '#fff5de',
                container: '#a29742',
            },
        },
        work: {
            colorName: 'work',
            lightColors: {
                main: '#f91c45',
                container: '#ffd2dc',
                onContainer: '#59000d',
            },
            darkColors: {
                main: '#ffc0cc',
                onContainer: '#ffdee6',
                container: '#a24258',
            },
        },
        leisure: {
            colorName: 'leisure',
            lightColors: {
                main: '#1cf9b0',
                container: '#dafff0',
                onContainer: '#004d3d',
            },
            darkColors: {
                main: '#c0fff5',
                onContainer: '#e6fff5',
                container: '#42a297',
            },
        },
        credit: {
            colorName: 'credit',
            lightColors: {
                main: '#f91c50',
                container: '#fecaca',
                onContainer: '#000',
            },
            darkColors: {
                main: '#c0dfff',
                onContainer: '#dee6ff',
                container: '#426aa2',
            },
        },
        school: {
            colorName: 'school',
            lightColors: {
                main: '#1c7df9',
                container: '#d2e7ff',
                onContainer: '#002859',
            },
            darkColors: {
                main: '#c0dfff',
                onContainer: '#dee6ff',
                container: '#426aa2',
            },
        },
    },
    defaultView: viewMonthGrid,
    locale: 'fr-FR',
   // selectedDate: '2023-12-19',
    views: [
        createViewMonthGrid(),
        createViewMonthAgenda(),
        createViewDay(),
        createViewWeek()
    ],
    plugins: [eventModal,calendarControls],
    events: [
    ],
    callbacks:{
        onRangeUpdate(range) {
            fetchCommandes(range.start,range.end)
        },
        onClickAgendaDate(date) {
           alert('Date clicked:', date);
        },
        onSelectedDateUpdate(date) {
           //axios to get event alert(date)
        },
        onClickDate(date) {
            /*calendarApp.events.add({
                id: 3,
                title: 'Event '+date,
                start: date,
                end: date
            })*/
        },
    }
})
const fetchCommandes = (start,end)=>{
    axios.post('/api/commandes',{start:start,end:end})
        .then(response=>{

            response.data.forEach(item=>{
                if(!calendarApp.events.get(item.id))
                    calendarApp.events.add({
                        id:item.id,
                        calendarId:'credit',
                        title:'Facture N°'+item.n_facture,
                        start:item.dateEcheance,
                        end:item.dateEcheance,
                        people:[item.fournisseur?.societe],
                        description:''

                    })
            })
        })
}

</script>

<style scoped>
.sx-vue-calendar-wrapper {
    width: 1200px;
    max-width: 100vw;
    height: 800px;
    max-height: 90vh;
}

</style>
