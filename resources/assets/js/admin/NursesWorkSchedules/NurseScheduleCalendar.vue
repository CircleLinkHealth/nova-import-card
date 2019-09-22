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
                                <vue-select :options="dataForDropdown"
                                            v-model="nurseData">
                                </vue-select>
                                <div>
                                    <vue-select :options="daysOfWeek"
                                                v-model="dayOfWeek">
                                    </vue-select>
                                </div>
                                <div>
                                    <input v-model="hoursToWork"
                                           type="number"
                                           class="work-hours"
                                           min="1" max="12"
                                           style="max-width: 60px;"
                                           required>
                                </div>
                                <div class="minimum-padding">
                                    <input v-model="workRangeStarts"
                                           type="time"
                                           required
                                           style="max-width: 120px;">
                                </div>
                                <div class="minimum-padding">
                                    <input v-model="workRangeEnds"
                                           type="time"
                                           required
                                           style="max-width: 120px;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" @click="submitWorkEvent">Save changes
                                </button>
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
            'dataForDropdown'
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
                workEventDate: '',
                nurseData: [],
                dayOfWeek: '',
                hoursToWork: '',
                workRangeStarts: '',
                workRangeEnds: '',

                config: {
                    defaultView: month,
                },

                daysOfWeek: [
                    {
                        label: 'Monday',
                        dayOfWeek: 1
                    },
                    {
                        label: 'Tuesday',
                        dayOfWeek: 2
                    },
                    {
                        label: 'Wednesday',
                        dayOfWeek: 3
                    },
                    {
                        label: 'Thursday',
                        dayOfWeek: 4
                    },
                    {
                        label: 'Friday',
                        dayOfWeek: 5
                    },
                    {
                        label: 'Saturday',
                        dayOfWeek: 6
                    },
                    {
                        label: 'Sunday',
                        dayOfWeek: 7
                    },
                ]
            }
        },

        methods: {
            submitWorkEvent() {
                axios.post('/care-center/work-schedule', {
                    nurse_info_id: this.nurseData.nurseId,
                    date:this.workEventDate,
                    day_of_week: this.dayOfWeek.dayOfWeek,
                    work_hours: this.hoursToWork,
                    window_time_start: this.workRangeStarts,
                    window_time_end: this.workRangeEnds,
                }).then((response => {
                        //loader add

                    }
                )).catch((error) => {

                });
            },

            handleDateCLick(date, jsEvent, view) {
                const calendarDay = date.format();
                this.workEventDate = '';
                this.workEventDate = calendarDay;
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