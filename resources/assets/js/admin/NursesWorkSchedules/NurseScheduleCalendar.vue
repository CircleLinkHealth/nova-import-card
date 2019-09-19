<template>
    <div>
        <full-calendar :events="events"></full-calendar>
    </div>
</template>

<script>
    import {FullCalendar} from 'vue-full-calendar';
    import 'fullcalendar/dist/fullcalendar.css';

    export default {
        name: "NurseScheduleCalendar",

        props: [
            'calendarData'
        ],
        components: {
            'fullCalendar': FullCalendar,
        },

        data() {
            return {
                events: this.calendarData,
            }
        },

        computed: {
            sex() {

            }
        }


    }


</script>

<style scoped>

</style>