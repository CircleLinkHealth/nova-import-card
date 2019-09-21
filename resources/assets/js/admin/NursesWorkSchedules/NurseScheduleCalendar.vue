<template>
    <div>
        <div class="container">
            <div class="calendar-menu">
                <input id="holidaysCheckbox" type="checkbox" class="holidays-button" @click="isChecked()">
                Upcoming Holidays
            </div>
            <div class="calendar">
                <full-calendar :events="events"
                               :config="config"
                               @day-click="handleDateCLick"
                               @event-selected="handleEventCLick"
                               @event-drop="handleEventDrop">
                </full-calendar>
            </div>
        </div>
    </div>
</template>

<script>
    import {FullCalendar} from 'vue-full-calendar';
    import 'fullcalendar/dist/fullcalendar.css';


    const month = 'month';

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
                workHours: [],
                holidays: [],
                showWorkHours: true,

                config: {
                    defaultView: month,
                },
            }
        },

        methods: {
            handleDateCLick(arg) {
                console.log(arg);
                alert(arg);
            },

            handleEventCLick(arg) {
                console.log(arg);
                alert(arg);
            },

            handleEventDrop(arg){
                alert(arg);
            },

            isChecked() {
                const checkBox = document.getElementById("holidaysCheckbox");
                const toggleData = checkBox.checked === true;
                console.log(toggleData);
                this.showHolidays(toggleData);

            },

            showHolidays(toggleData) {
                if (toggleData === true && this.holidays.length === 0) {
                    axios.get('admin/nurses/holidays')
                        .then((response => {
                                //loader add
                                this.showWorkHours = false;
                                this.holidays.push(...response.data.holidays)
                            }
                        )).catch((error) => {

                    });
                } else this.showWorkHours = !(toggleData === true && this.holidays.length !== 0);
            },
        },

        computed: {
            events() {
                return this.showWorkHours ? this.workHours : this.holidays;
            }
        }
        ,

        created() {
            const workHours = this.calendarData.map(q => q.workHours);
            this.workHours.push(...workHours);

        }


    }


</script>

<style scoped>

</style>