<template>
    <div>
        <div class="calendar-menu">
            <div class="show-holidays">
                <input id="holidaysCheckbox"
                       :class="{disable: showWorkAndHolidaysIsChecked}"
                       :disabled="showWorkAndHolidaysIsChecked"
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
                       v-model="showWorkAndHolidaysIsChecked">
                Work Days and Holidays
            </div>

            <div class="search-filter">
                <vue-select :options="dataForSearchFilter"
                            v-model="searchFilter"
                            placeholder="Filter RN"
                            required>
                </vue-select>
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
            <loader v-show="loader"></loader>
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
                            <div v-if="!this.clickedToViewEvent" class="display-date">
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
                                <div class="work-day-read">{{this.eventToViewData[0].day}}</div>
                                <div class="work-day-read">{{this.eventToViewData[0].date}}</div>
                            </div>
                            <div v-if="clickedToViewEvent && eventToViewData[0].eventType === 'workDay'"
                                 class="view-event">
                                <div class="nurse-name">{{this.eventToViewData[0].name}}</div>
                                <div class="work-day-read">on {{this.eventToViewData[0].day}} works for</div>
                                <div class="work-hours-read">{{this.eventToViewData[0].workHours}} hours
                                    <div style="display: flex; margin-left: 22%; margin-top: 1%;">
                                        <div class="start-time-read">between {{this.eventToViewData[0].start}}</div>
                                        <div style="margin-left: 5%; margin-right: 5%;">to</div>
                                        <div class="end-time-read">{{this.eventToViewData[0].end}}</div>
                                    </div>
                                </div>
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
    import {addNotification} from '../../../../../resources/assets/js/store/actions.js';
    import LoaderComponent from '../../../../../resources/assets/js/components/loader.vue';

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
            'loader': LoaderComponent,
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
                showWorkAndHolidaysIsChecked: false,
                dayInHumanLangForView: '',
                loader: false,
                searchFilter: [],
                config: {
                    defaultView: viewDefault,
                    editable: false,
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
                        dayOfWeek: 0
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
                this.loader = true;
                const windowId = this.eventToViewData[0].windowId;
                axios.get(`/care-center/work-schedule/destroy/${windowId}`).then((response => {
                    this.loader = false;
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
                this.loader = true;
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
                        this.loader = false;
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
                    title: `${this.hoursToWork} Hrs - ${this.nurseData.label}
                    ${this.workRangeStarts}-${this.workRangeEnds}`,
                }
            },

            handleDateCLick(date, jsEvent, view) {
                this.loader = true;
                const clickedDate = date;

                const startOfThisWeek = Date.parse(this.startOfThisWeek);
                const endOfThisWeek = Date.parse(this.endOfThisWeek);

                this.workEventDate = '';
                this.workEventDate = clickedDate.format();

                if (clickedDate >= startOfThisWeek && clickedDate <= endOfThisWeek) {
                    this.loader = true;
                    $("#addWorkEvent").modal('toggle');//open  modal
                    const clickedDayOfWeek = new Date(this.workEventDate).getDay();
                    const weekMapDay = this.weekMap.filter(q => q.dayOfWeek === clickedDayOfWeek);
                    this.dayInHumanLangForView = weekMapDay[0].label;

                } else {
                    this.loader = false;
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
                this.loader = true;
                console.log('this', arg);
                this.clickedToViewEvent = true;
                this.eventToViewData.push(arg.data);
                this.workEventDate = '';
                this.workEventDate = this.eventToViewData[0].date;

                $("#addWorkEvent").modal('toggle');
                this.loader = false;
            },

            handleEventDrop(arg) {
                //do nothing for now boy
            },

            showHolidays() {
                this.config.header.right = 'month';
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
                this.hoursToWork = '5';
                this.nurseData = [];
                this.workRangeStarts = '09:00';
                this.workRangeEnds = '17:00';
            },
        }),

        computed: {
            modalTitle() {
                return this.clickedToViewEvent ? 'View / Delete Event' : 'Add new work window';
            },

            searchFilterSelection() {
                return this.workHours.filter(q => q.data.nurseId === this.searchFilter.nurseId);
            },

            events() {
                const events = this.workHours.concat(this.eventsAddedNow);

                if (this.searchFilter === null || this.searchFilter.length === 0) {
                    if (!this.showWorkAndHolidaysIsChecked) {
                        return this.showWorkHours ? events : this.holidays;
                    }

                    if (this.showWorkAndHolidaysIsChecked) {
                        return events.concat(this.holidays);
                    }
                } else {
                    if (!this.showWorkAndHolidaysIsChecked) {
                        return this.showWorkHours ? events.filter(q => q.data.nurseId === this.searchFilter.nurseId) : this.holidays.filter(q => q.data.nurseId === this.searchFilter.nurseId);
                    }

                    if (this.showWorkAndHolidaysIsChecked) {
                        return events.concat(this.holidays).filter(q => q.data.nurseId === this.searchFilter.nurseId);
                    }
                }
            },

            dataForSearchFilter() {
                //array distinct - filtering duplicates
                return Array.from(new Set(this.workHours.map(event => event.data.nurseId))).map(nurseId => {
                    return {
                        nurseId: nurseId,
                        label: this.workHours.find(event => event.data.nurseId === nurseId).data.name
                    }
                });
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

<style>
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

    .nurse-name {
        text-align: center;
        font-size: 20px;
        letter-spacing: 1px;
        font-weight: 500;
        margin-bottom: 1%;
        margin-top: 1%;
    }

    .work-hours-read {
        text-align: center;
        font-size: 17px;
        letter-spacing: 1px;
        font-weight: 500;
    }

    .work-day-read {
        text-align: center;
        font-size: 17px;
        letter-spacing: 1px;
        font-weight: 500;
        margin-right: 1%;
        margin-bottom: 1%;
    }

    .start-time-read {
        font-size: 17px;
        letter-spacing: 1px;
        font-weight: 500;
    }

    .end-time-read {
        font-size: 17px;
        letter-spacing: 1px;
        font-weight: 500;
        margin-left: 1%;

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

    .calendar-menu {
        margin-left: 10%;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-scroller.fc-time-grid-container {
        visibility: hidden !important;
        display: none !important;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > hr {
        visibility: hidden !important;
        display: none !important;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-unselectable > div > div.fc-content-skeleton {
        min-height: 100px;
        overflow-y: scroll;
        max-height: 670px;
    }

    #calendar > div.fc-view-container {
        width: 124%;
        margin-left: -12%;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr {
        text-align: left;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-bg > table > tbody > tr > td.fc-axis.fc-widget-content {
        visibility: hidden !important;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr > td > a {
        height: 35px;
        cursor: pointer;
    }

    #calendar > div.fc-toolbar.fc-header-toolbar > div.fc-right {
        margin-right: 6%;
    }

    #calendar > div.fc-toolbar.fc-header-toolbar > div.fc-left {
        margin-left: 10%;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr > td > a > div.fc-content > span {
        font-size: 112%;
        font-weight: 400;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr > td {
        padding-top: 8px;
    }
</style>