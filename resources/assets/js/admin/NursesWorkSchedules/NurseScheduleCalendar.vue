<template>
    <div>
        <div class="calendar-menu">
            <div class="show-holidays">
                <input id="holidaysCheckbox"
                       :class="{disable: workHolidaysChecked}"
                       :disabled="workHolidaysChecked"
                       type="checkbox"
                       class="holidays-button"
                       v-model="holidaysChecked"
                       @change="showHolidays()">
                Filter - Holidays
            </div>
            <div v-show="holidaysChecked" class="show-work-holidays">
                <input id="workHolidaysCheckbox"
                       type="checkbox"
                       class="work-holidays-button"
                       v-model="workHolidaysChecked">
                Work Days and Holidays
            </div>
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
                            <h3 class="modal-title" id="exampleModalLabel">{{modalTitle}}</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="display-date">
                                <h4>{{this.dayInHumanLangForView}} {{workEventDate}}</h4>
                            </div>

                            <!--  Filter Options-->
                            <div v-if="!clickedToViewEvent" class="filter-options">
                                <h5>Add Work Window:</h5>
                                <div>
                                    <vue-select :options="dataForDropdown"
                                                v-model="nurseData"
                                                placeholder="Choose RN"
                                                required>
                                    </vue-select>
                                </div>

                                <div class="modal-inputs col-md-12">
                                    <div class="work-hours">
                                        <h5>Work For:</h5>
                                        <input v-model="hoursToWork"
                                               type="number"
                                               class="work-hours-input"
                                               placeholder="5"
                                               min="1" max="12"> <strong>Hours</strong>
                                    </div>
                                    <div class="start-end-time">
                                        <div class="start-time">
                                            <h5>Between (EDT):</h5>
                                            <input v-model="workRangeStarts"
                                                   type="time"
                                                   class="time-input">
                                        </div>
                                        <div class="end-time">
                                            <h5>and (EDT):</h5>
                                            <input v-model="workRangeEnds"
                                                   type="time"
                                                   class="time-input">
                                        </div>
                                    </div>
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

    const viewDefault = 'agendaWeek';
    const defaultEventType = 'workDay';
    const holidayEventType = 'holiday';

    export default {
        name: "NurseScheduleCalendar",

        props: [
            'calendarData',
            'dataForDropdown',
            'startOfThisYear',
            'startOfThisWeek',
            'endOfThisWeek'
        ],

        components: {
            'fullCalendar': FullCalendar,
            'vue-select': VueSelect,
            'addNotification': addNotification,
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
                hoursToWork: '5',
                workRangeStarts: '09:00',
                workRangeEnds: '17:00',
                errors: [],
                clickedToViewEvent: false,
                eventToViewData: [],
                eventsAddedNow: [],
                holidaysChecked: false,
                workHolidaysChecked: false,
                dayInHumanLangForView: '',
                config: {
                    defaultView: viewDefault,
                    editable: true,
                    // validRange: {
                    //     end: this.endOfThisWeek,
                    //     start: this.startOfThisWeek,
                    // }


                },

                weekMap: [
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

        methods: Object.assign(mapActions(['addNotification']), {
            deleteEvent() {
                const event = this.eventToViewData[0];
                const eventType = event.eventType;
                const isAddedNow = event.hasOwnProperty('isAddedNow');

                if (eventType !== holidayEventType) {
                    this.deleteWorkDay(event, isAddedNow);
                } else {
                    this.deleteHoliday(event, isAddedNow);
                }


            },

            deleteHoliday(event, isAddedNow) {
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
            },

            deleteWorkDay(event, isAddedNow) {
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
            },

            addNewEvent() {
                const nurseId = this.clickedToViewEvent ? this.eventToViewData[0].nurseId : this.nurseData.nurseId;

                if (nurseId === null || nurseId === undefined) {
                    this.addNotification({
                        title: "Warning!",
                        text: "Choose an RN field is required",
                        type: "danger",
                        timeout: true
                    });

                    alert("Choose an RN field is required");
                    return;

                }

                if (this.workRangeStarts === '') {
                    this.addNotification({
                        title: "Warning!",
                        text: "Work start time is required",
                        type: "danger",
                        timeout: true
                    });

                    alert("Work start time is required");
                    return;
                }
                if (this.workRangeEnds === '') {
                    this.addNotification({
                        title: "Warning!",
                        text: "Work end time is required",
                        type: "danger",
                        timeout: true
                    });

                    alert("Work end time is required");
                    return;
                }
                if (this.hoursToWork === '') {
                    this.addNotification({
                        title: "Warning!",
                        text: "Hours to work for this day is required",
                        type: "danger",
                        timeout: true
                    });

                    alert("Hours to work for this day is required");
                    return;
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
                        isAddedNow: true,
                    },
                    dow: [newEventData.window.dayOfWeek],
                    end: `${this.workEventDate}T${this.workRangeEnds}`,
                    start: `${this.workEventDate}T${this.workRangeStarts}`,
                    title: `${this.hoursToWork} Hrs - ${this.nurseData.label}`,
                }
            },

            handleDateCLick(date, jsEvent, view) {
                const clickedDate = date;

                const startOfThisWeek = Date.parse(this.startOfThisWeek);
                const endOfThisWeek = Date.parse(this.endOfThisWeek);

                this.workEventDate = '';
                this.workEventDate = clickedDate.format();

                if (clickedDate >= startOfThisWeek && clickedDate <= endOfThisWeek) {
                    $("#addWorkEvent").modal('toggle');//open  modal
                    const clickedDayOfWeek = new Date(this.workEventDate).getDay();
                    const weekMapDay = this.weekMap.filter(q => q.dayOfWeek === clickedDayOfWeek);
                    this.dayInHumanLangForView = weekMapDay[0].label;

                } else {

                    this.addNotification({
                        title: "Warning!",
                        text: 'You can only add/edit events within current week range',
                        type: "danger",
                        timeout: true
                    });

                    alert('You can only add/edit events within current week range');
                }

            },


            handleEventCLick(arg) {
                console.log('this', arg);
                this.clickedToViewEvent = true;
                this.eventToViewData.push(arg.data);
                this.workEventDate = '';
                this.workEventDate = this.eventToViewData[0].date;

                $("#addWorkEvent").modal('toggle');

            },

            handleEventDrop(arg) {
                alert(arg);
            },

            showHolidays() {
                const toggleData = this.holidaysChecked;
                if (toggleData && this.holidays.length === 0) {
                    this.getHolidays();
                } else this.showWorkHours = !(toggleData && this.holidays.length !== 0);
            },

            getHolidays() {
                axios.get('admin/nurses/holidays')
                    .then((response => {
                            //loader add
                            this.showWorkHours = false;
                            this.holidays.push(...response.data.holidays)
                        }
                    )).catch((error) => {
                    console.log(error);
                });
            },
            resetModalValues() {
                this.clickedToViewEvent = false;
                this.eventToViewData = [];
                this.dayInHumanLangForView = '';
                this.hoursToWork = '';
                this.nurseData = [];
            },
        }),

        computed: {
            modalTitle() {
                return this.clickedToViewEvent ? 'View / Delete Event' : 'Add new work window';
            },

            events() {
                const events = this.workHours.concat(this.eventsAddedNow);

                if (!this.workHolidaysChecked) {
                    return this.showWorkHours ? events : this.holidays;
                }

                if (this.workHolidaysChecked) {
                    return events.concat(this.holidays);
                }
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
    .disable {
        background-color: #f4f6f6;
        color: #d5dbdb;
        cursor: default;
        opacity: 0.7;
    }

    .modal-header {
        padding: 10px;
    }

    .modal-title {
        text-align: center;
    }

    .display-date {
        text-align: center;
    }

    .work-hours {
        margin-top: 4%;
    }

    .work-hours-input {
        width: 100px;
        height: 54%;
        font-size: 14px;
        color: #555;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    }

    .modal-inputs {
        display: inline-flex;
    }

    .modal-footer {
        margin-top: 18%;
    }

    .start-end-time {
        padding-top: 4%;
        display: inline-flex;
    }

    .start-time {
        margin-left: 34%;

    }

    .end-time {
        margin-left: 55%;
    }

    .time-input {
        width: 100px;
        height: 54%;
        font-size: 14px;
        color: #555;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    }
</style>