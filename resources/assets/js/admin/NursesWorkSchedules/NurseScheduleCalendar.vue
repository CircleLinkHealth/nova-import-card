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
                <!-- Modal -->
                <div class="modal fade" id="addWorkEvent" tabindex="-1" role="dialog"
                     aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <vue-select :options="namesForDropdown">

                                </vue-select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- modal end-->
            </div>
        </div>
    </div>
</template>

<script>
    import {FullCalendar} from 'vue-full-calendar';
    import 'fullcalendar/dist/fullcalendar.css';
    import VueSelect from 'vue-select';


    const month = 'month';

    export default {
        name: "NurseScheduleCalendar",

        props: [
            'calendarData',
            'namesForDropdown'
        ],

        components: {
            'fullCalendar': FullCalendar,
            'vue-select': VueSelect,

        },

        data() {
            return {
                workHours: [],
                holidays: [],
                showWorkHours: true,
                nurses: [],

                config: {
                    defaultView: month,
                },
            }
        },

        methods: {
            handleDateCLick(date, jsEvent, view) {
                const calendarDay = date.format();
                $("#addWorkEvent").modal('toggle');
            },

            handleEventCLick(arg) {
                console.log(arg);
            },

            handleEventDrop(arg) {
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