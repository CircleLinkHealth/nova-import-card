<template>
    <div>
        <div class="calendar-menu">
            <input id="holidaysCheckbox" type="checkbox" class="holidays-button" @click="holidaysChecked()">
            Upcoming Holidays
        </div>
        <div class="calendar">
            <full-calendar ref="calendar"
                           :events="events"
                           :config="config"
                           @day-click="handleDateCLick"
                           @event-selected="handleEventCLick"
                           @event-drop="handleEventDrop">
            </full-calendar>
            <!-- Modal --- sorry couldn't make a vue component act as modal here so i dumped this here-->
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
                            <div class="display-date">
                                <h4>{{workEventDate}}</h4>
                            </div>

                            <!--  Filter Options-->
                            <div v-if="!clickedToViewEvent" class="filter-options">
                                <h4>Add Work Window</h4>
                                <div>
                                    <vue-select :options="dataForDropdown"
                                                v-model="nurseData">
                                    </vue-select>
                                </div>

                                <div>
                                    <input v-model="hoursToWork"
                                           type="number"
                                           class="work-hours"
                                           min="1" max="12"
                                           style="max-width: 60px;">
                                </div>
                                <div class="minimum-padding">
                                    <input v-model="workRangeStarts"
                                           type="time"
                                           style="max-width: 120px;">
                                </div>
                                <div class="minimum-padding">
                                    <input v-model="workRangeEnds"
                                           type="time"
                                           style="max-width: 120px;">
                                </div>
                            </div>
                            <div v-if="clickedToViewEvent && eventToViewData[0].eventType === 'holiday'"
                                 class="view-event">
                                <div class="nurse-name">{{this.eventToViewData[0].name}} holiday on</div>
                                <div class="work-day">{{this.eventToViewData[0].day}}</div>
                                <div class="work-day">{{this.eventToViewData[0].date}}</div>
                            </div>
                            <div v-if="clickedToViewEvent && eventToViewData[0].eventType === 'workDay'"
                                 class="view-event">
                                <div class="nurse-name">{{this.eventToViewData[0].name}} on</div>
                                <div class="work-day">{{this.eventToViewData[0].day}} works for</div>
                                <div class="work-hours">{{this.eventToViewData[0].workHours}} hours between</div>
                                <div class="start-time">{{this.eventToViewData[0].start}} to</div>
                                <div class="start-end">{{this.eventToViewData[0].end}}</div>
                            </div>
                        </div>
                        <!-- Filters End-->

                        <div class="modal-footer">
                            <button v-if="clickedToViewEvent"
                                    type="button"
                                    class="btn btn-primary"
                                    @click="deleteEvent">Delete
                            </button>
                            <button v-if="!clickedToViewEvent" type="button"
                                    class="btn btn-primary"
                                    @click="addNewEvent">Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- modal end-->
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex';
    import {FullCalendar} from 'vue-full-calendar';
    import 'fullcalendar/dist/fullcalendar.css';
    import VueSelect from 'vue-select';
    import {addNotification} from '../../store/actions';
    import rrule from '@fullcalendar/rrule';
    // import Modal from '/admin/common/modal'

    const month = 'month';
    const defaultEventType = 'workDay';
    const holidayEventType = 'holiday';
    export default {
        name: "NurseScheduleCalendar",
        plugins: [rrule],
        props: [
            'calendarData',
            'dataForDropdown'
        ],

        components: {
            'fullCalendar': FullCalendar,
            'vue-select': VueSelect,
            'addNotification': addNotification,
            // 'modal': Modal,

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
                errors: [],
                clickedToViewEvent: false,
                eventToViewData: [],
                eventsAddedNow: [],

                config: {
                    defaultView: month,

                },

                // daysOfWeek: [
                //     {
                //         label: 'Monday',
                //         dayOfWeek: 0
                //     },
                //     {
                //         label: 'Tuesday',
                //         dayOfWeek: 1
                //     },
                //     {
                //         label: 'Wednesday',
                //         dayOfWeek: 2
                //     },
                //     {
                //         label: 'Thursday',
                //         dayOfWeek: 3
                //     },
                //     {
                //         label: 'Friday',
                //         dayOfWeek: 4
                //     },
                //     {
                //         label: 'Saturday',
                //         dayOfWeek: 5
                //     },
                //     {
                //         label: 'Sunday',
                //         dayOfWeek: 6
                //     },
                // ]
            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            deleteEvent() {

                const event = this.eventToViewData[0];
                const eventType = event.eventType;
                const isAddedNow = event.hasOwnProperty('isAddedNow');

                if (eventType !== holidayEventType) {
                    //delete workday
                    const windowId = this.eventToViewData[0].windowId;
                    axios.get(`/care-center/work-schedule/destroy/${windowId}`).then((response => {
                        $("#addWorkEvent").modal('toggle');

                        //Delete event from events() - dom

                        if (isAddedNow) {
                            const index = this.eventsAddedNow.findIndex(x => x.data.windowId === windowId);
                            this.eventsAddedNow.splice(index, 1);
                        }
                        if (!isAddedNow) {
                            const index = this.workHours.findIndex(x => x.data.windowId === windowId);
                            this.workHours.splice(index, 1);
                        }

                        //Show notification
                        this.addNotification({
                            title: "Success!",
                            text: response.data.message,
                            type: "success",
                            timeout: true
                        });


                        alert(response.data.message);

                    })).catch((error) => {
                        console.log(error);
                        if (error.response.status === 422) {
                            this.errors = error;
                            this.addNotification({
                                title: "Warning!",
                                text: this.errors.response.data.errors,
                                type: "danger",
                                timeout: true
                            });
                            alert(this.errors.response.data.errors);
                        }

                    });
                } else {
                    //delete holiday
                    const holidayId = event.holidayId;
                    axios.get(`/care-center/work-schedule/holidays/destroy/${holidayId}`).then((response => {
                        $("#addWorkEvent").modal('toggle');
                        console.log(response);

                        //Delete event from dom
                        if (isAddedNow) {
                            const index = this.eventsAddedNow.findIndex(x => x.data.windowId === windowId);
                            this.eventsAddedNow.splice(index, 1);
                        }

                        if (!isAddedNow) {
                            const index = this.holidays.findIndex(x => x.data.holidayId === holidayId);
                            this.holidays.splice(index, 1);
                        }

                        //Show notification
                        this.addNotification({
                            title: "Success!",
                            text: response.data.message,
                            type: "success",
                            timeout: true
                        });

                        alert(response.data.message);

                    })).catch((error) => {
                        this.errors = error;
                        this.addNotification({
                            title: "Warning!",
                            text: this.errors.response.data.errors,
                            type: "danger",
                            timeout: true
                        });
                        alert(this.errors.response.data.errors);
                    });
                    console.log('Post Holiday');
                }


            },

            addNewEvent() {
                const nurseId = this.clickedToViewEvent ? this.eventToViewData[0].nurseId : this.nurseData.nurseId;
                const defaultStartTime = '09:00';
                const defaultEndTime = '17:00';

                if (this.workRangeStarts === '') {
                    this.workRangeStarts = defaultStartTime; //@todo:assign placeholder to UI
                }
                if (this.workRangeEnds === '') {
                    this.workRangeEnds = defaultEndTime;
                }

                axios.post('/care-center/work-schedule', {
                    nurse_info_id: nurseId,
                    date: this.workEventDate,
                    day_of_week: this.dayOfWeek.dayOfWeek, //this is actually empty but is needed to pass validation. im creating this var in php
                    work_hours: this.hoursToWork,
                    window_time_start: this.workRangeStarts,
                    window_time_end: this.workRangeEnds,
                }).then((response => {
                        //@todo: Add loader
                        $("#addWorkEvent").modal('toggle'); //close modal
                        const newEvent = this.prepareLocalData(response.data); //to show in UI before page reload.
                        this.eventsAddedNow.push(newEvent);

                        this.addNotification({
                            title: "Success!",
                            text: "dfgdsfgggfhfhghhdfjhfdhghfghdfhdhjghjhjhjhggd.",
                            type: "success",
                            timeout: true
                        });
                    }
                ))
                    .catch((error) => {
                        if (error.response.status === 422) {
                            console.log(error.response.data.validator.window_time_start);
                            this.errors = error;
                            alert(this.errors.response.data.validator.window_time_start);
                        }
                    });
            },

            prepareLocalData(newEventData) {
                console.log(newEventData);

                return {
                    allDay: true,
                    data: {
                        date: this.workEventDate,
                        windowId: newEventData.window.id,
                        end: this.workRangeEnds,
                        start: this.workRangeStarts,
                        name: this.nurseData.label,
                        nurseId: this.nurseData.nurseId,
                        eventType: defaultEventType,
                        isAddedNow:true,
                    },
                    dow: [newEventData.window.dayOfWeek],
                    end: `${this.workEventDate}T${this.workRangeEnds}`,
                    start: `${this.workEventDate}T${this.workRangeStarts}`,
                    title: `${this.nurseData.label} - ${this.hoursToWork} Hrs`,
                }
            },

            handleDateCLick(date, jsEvent, view) {
                const eventDate = date.format();
                this.workEventDate = '';
                this.workEventDate = eventDate;
                $("#addWorkEvent").modal('toggle');
            },


            handleEventCLick(arg) {
                console.log('this', arg);
                this.clickedToViewEvent = true;
                // const data = this.prepareDataToPush(arg.data);
                this.eventToViewData.push(arg.data);
                this.workEventDate = '';
                this.workEventDate = this.eventToViewData[0].date;

                $("#addWorkEvent").modal('toggle');

            },

            // prepareDataToPush(argData) {
            //     const x = argData.hasOwnProperty('eventType');
            //     if (!x) {
            //        return this.prepareLocalData2(argData);
            //     }
            //     return argData;
            // },

            handleEventDrop(arg) {
                alert(arg);
            },

            holidaysChecked() {
                const checkBox = document.getElementById("holidaysCheckbox");
                const toggleData = checkBox.checked === true;
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

            resetModalValues() {
                this.clickedToViewEvent = false;
                this.eventToViewData = [];
                this.holidaysChecked = false;
            },
        }),

        computed: {
            events() {
                return this.showWorkHours ? this.workHours.concat(this.eventsAddedNow) : this.holidays;
            },

            hoursToWorkHasValue() {
                return this.hoursToWork.length !== 0;
            },
        },

        created() {
            const workHours = this.calendarData;
            this.workHours.push(...workHours);
        },

        mounted() {
            $('#addWorkEvent').on("hidden.bs.modal", this.resetModalValues)
        }
    }


</script>

<style scoped>

</style>