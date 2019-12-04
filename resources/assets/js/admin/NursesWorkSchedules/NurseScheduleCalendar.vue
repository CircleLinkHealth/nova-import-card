<template>
    <div>
        <div class="calendar-menu">
            <div v-if="authIsAdmin"
                 class="show-holidays">
                <input id="holidaysCheckbox"
                       type="checkbox"
                       class="holidays-button"
                       @click="refetchEvents()"
                       v-model="showHolidays">
                Holidays
            </div>
            <div v-if="authIsAdmin"
                 class="show-work">
                <input id="workCheckbox"
                       type="checkbox"
                       class="work-button"
                       @click="refetchEvents()"
                       v-model="showWorkEvents">
                Work Events
            </div>

            <div class="search-filter">
                <vue-select ref="searchFilter"
                            v-if="authIsAdmin"
                            multiple v-model="searchFilter"
                            @input="refetchEvents()"
                            :options="dataForSearchFilter()"
                            placeholder="Filter RN"
                            required>
                </vue-select>
            </div>

            <!-- Add new event - main button-->
            <div class="add-event-main">
                <button class="btn btn-primary" @click="openMainEventModal">Add new window</button>
            </div>

        </div>
        <div class="calendar">
            <full-calendar ref="calendar"
                           :event-sources="eventSources"
                           :config="config"
                           @day-click="handleDateCLick"
                           @event-selected="handleEventCLick"
                           @event-drop="handleEventDrop">
            </full-calendar>
            <!--LOADER-->
            <calendar-loader v-show="loader"></calendar-loader>
            <!-- Modal --- sorry couldn't make a vue component act as modal here so i dumped it here-->
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
                            <div v-if="!this.clickedToViewEvent || !this.addNewEventMainClicked" class="display-date">
                                <h4>{{this.dayInHumanLangForView}} {{workEventDate}}</h4>
                            </div>

                            <!--  Filter Options-->
                            <div v-if="!clickedToViewEvent" class="filter-options">
                                <div v-if="authIsAdmin">
                                    <vue-select v-model="nurseData"
                                                :options="dataForDropdown"
                                                placeholder="Choose RN"
                                                required>
                                    </vue-select>
                                </div>

                                <div class="modal-inputs col-md-12">
                                    <div class="work-hours">
                                        <h5>Work For:</h5>
                                        <input v-model="hoursToWork"
                                               type="number"
                                               :class="{disable: addHolidays}"
                                               :disabled="addHolidays"
                                               class="work-hours-input"
                                               placeholder="5"
                                               min="1" max="12"> <strong>Hours</strong>
                                    </div>
                                    <div class="start-end-time">
                                        <div class="start-time">
                                            <h5>Between (EDT):</h5>
                                            <input v-model="workRangeStarts"
                                                   type="time"
                                                   :class="{disable: addHolidays}"
                                                   :disabled="addHolidays"
                                                   class="time-input">
                                        </div>
                                        <div class="end-time">
                                            <h5>and (EDT):</h5>
                                            <input v-model="workRangeEnds"
                                                   type="time"
                                                   :class="{disable: addHolidays}"
                                                   :disabled="addHolidays"
                                                   class="time-input">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="choose-event-date">
                                <div v-if="addNewEventMainClicked">
                                    <h5>Date:</h5>
                                    <input type="date"
                                           name="event_date"
                                           :min="calculateMinDate()"
                                           v-model="selectedDate">
                                </div>
                            </div>

                            <div v-if="!clickedToViewEvent" style="margin-top: 8%;">
                                <div class="repeat-day-frequency">
                                    <vue-select :options="frequency"
                                                :class="{disable: addHolidays}"
                                                :disabled="addHolidays"
                                                v-model="eventFrequency"
                                                placeholder="Doesn't Repeat">
                                    </vue-select>
                                </div>

                                <div style="display: flex">
                                    <div class="repeat-until">
                                        <h5>Repeat Until</h5>
                                        <input type="date"
                                               :class="{disable: !repeatFrequencyHasSelected || addHolidays}"
                                               :disabled="!repeatFrequencyHasSelected || addHolidays"
                                               name="until"
                                               :min="calculateMinDate()"
                                               v-model="repeatUntil">
                                    </div>
                                    <!-- ADD HOLIDAYS-->
                                    <div v-if="! authIsAdmin"
                                         class="add-holidays">
                                        <input id="addHolidays"
                                               type="checkbox"
                                               class="add-holidays-button"
                                               v-model="addHolidays">
                                        Add holiday window
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
                                    @click="deleteEvent(false)">Delete selected
                            </button>

                            <button v-if="clickedToViewEvent && isRecurringEvent"
                                    type="button"
                                    class="btn btn-primary" style="background-color: crimson; border-color: crimson"
                                    @click="deleteEvent(true)">Delete all
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
    import {FullCalendar} from 'vue-full-calendar';
    import {RRule} from 'rrule';
    import 'fullcalendar/dist/fullcalendar.css';
    import VueSelect from 'vue-select';
    import {mapActions} from 'vuex';
    import {addNotification} from '../../../../../resources/assets/js/store/actions.js'; //@todo:doesnt work yet.
    import CalendarLoader from './CalendarLoader';
    import axios from "../../bootstrap-axios";

    let self;

    const viewDefault = 'agendaWeek';
    const defaultEventType = 'workDay';
    const holidayEventType = 'holiday';

    function removeDuplicatesFrom(events) {
        return Array.from(new Set(events.map(event => event.data.nurseId))).map(nurseId => {
            return {
                nurseId: nurseId,
                label: events.find(event => event.data.nurseId === nurseId).data.name
            }
        });
    }

    // async function sex() {
    //
    //        // return await axios.get('nurses/nurse-calendar-data').then(response => {
    //        //      return response.data;
    //        // }).catch(error => {
    //        //      console.log(error);
    //        //  });
    //     try {
    //         let res = await axios({
    //             url: 'nurses/nurse-calendar-data',
    //             method: 'get',
    //             timeout: 8000,
    //
    //         });
    //
    //         return res
    //     }
    //     catch (err) {
    //         console.error(err);
    //     }
    //
    //
    // }


    export default {
        name: "NurseScheduleCalendar",

        props: [
            'authData',
            'today'
        ],

        components: {
            'fullCalendar': FullCalendar,
            'vue-select': VueSelect,
            'addNotification': addNotification,
            CalendarLoader,
            RRule
        },

        data() {
            return {
                // calendarData:[],
                dataForDropdown: [],
                // today: '', //pushed from created()
                // startOfMonth: [],
                // endOfMonth: [],
                // endOfYear: [],
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
                showHolidays: false,
                showWorkEvents: false,
                dayInHumanLangForView: '',
                loader: false,
                searchFilter: [],
                eventFrequency: [],
                addNewEventMainClicked: false,
                selectedDate: [],
                repeatUntil: '',
                workEventsToConfirm: [],
                isRecurringEvent: false,
                addHolidays: false,
                authIsAdmin: false,
                authIsNurse: false,
                eventSources: [
                    { // has to be 'events()' else it doesnt work
                        // WorkEvents
                        events(start, end, timezone, callback) {
                            axios.get('care-center/work-schedule/get-calendar-data', {
                                params: {
                                    start: new Date(start),
                                    end: new Date(end),
                                }
                            })
                                .then((response => {
                                    const calendarData = response.data.calendarData;
                                    self.workHours = [];
                                    self.holidays = [];
                                    self.dataForDropdown = [];

                                    self.holidays.push(...calendarData.holidayEvents);
                                    self.workHours.push(...calendarData.workEvents);
                                    self.dataForDropdown.push(...calendarData.dataForDropdown);
                                    console.log(self.showHolidays);
                                    const x = self.eventsFiltered();
                                    // const c = calendarData.workEvents.concat(calendarData.holidayEvents);
                                    callback(x);

                                })).catch((error) => {
                                this.errors = error;
                                console.log(this.errors);
                            });
                        },
                    },
                    // {
                    //     // Holidays Events
                    //     events(start, end, timezone, callback) {
                    //         axios.get('nurses/holidays', {
                    //             params: {
                    //                 start: new Date(start),
                    //                 end: new Date(end),
                    //             }
                    //         })
                    //             .then((response => {
                    //                     const holidays = response.data.holidays;
                    //                     self.holidays = [];
                    //                     self.holidays.push(...holidays);
                    //                     console.log(self.showHolidays);
                    //                     callback(holidays);
                    //                 }
                    //             )).catch((error) => {
                    //              console.log(error);
                    //         });
                    //     }
                    // }
                ],

                config: {
                    defaultView: viewDefault,
                    editable: false,

                    header: {
                        left: 'prev, next, today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },

                },

                weekMap: [
                    {
                        label: 'Monday',
                        weekMapClhDayOfWeek: 1,
                        weekMapDayOfWeek: 0
                    },
                    {
                        label: 'Tuesday',
                        weekMapClhDayOfWeek: 2,
                        weekMapDayOfWeek: 1
                    },
                    {
                        label: 'Wednesday',
                        weekMapClhDayOfWeek: 3,
                        weekMapDayOfWeek: 2
                    },
                    {
                        label: 'Thursday',
                        weekMapClhDayOfWeek: 4,
                        weekMapDayOfWeek: 3
                    },
                    {
                        label: 'Friday',
                        weekMapClhDayOfWeek: 5,
                        weekMapDayOfWeek: 4
                    },
                    {
                        label: 'Saturday',
                        weekMapClhDayOfWeek: 6,
                        weekMapDayOfWeek: 5
                    },
                    {
                        label: 'Sunday',
                        weekMapClhDayOfWeek: 0,
                        weekMapDayOfWeek: 6
                    },
                ],

                frequency: [
                    {
                        label: 'Repeat Daily',
                        value: 'daily'
                    },
                    {
                        label: 'Repeat Weekly',
                        value: 'weekly'
                    },
                ],

            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            refetchEvents() {
                return this.$refs.calendar.$emit('refetch-events');
            },

            rerenderEvents() {
                return this.$refs.calendar.$emit('rerender-events');
            },

            openMainEventModal() {
                this.addNewEventMainClicked = true;
                this.toggleModal();
            },

            toggleModal() {
                $("#addWorkEvent").modal('toggle');
            },

            deleteEvent(shouldDeleteAll) {
                const event = this.eventToViewData[0];
                const eventType = event.eventType;


                if (eventType !== holidayEventType) {
                    this.deleteWorkDay(event, shouldDeleteAll);
                } else {
                    this.deleteHoliday(event);
                }
            },

            deleteHoliday(event) {
                this.loader = true;
                const holidayId = event.holidayId;
                axios.get(`/care-center/work-schedule/holidays/destroy/${holidayId}`).then((response => {
                    this.toggleModal();
                    console.log(response);

                    //Delete event from dom
                    // const index = this.holidays.findIndex(x => x.data.holidayId === holidayId);
                    // this.holidays.splice(index, 1);

                    this.loader = false;
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

            deleteWorkDay(event, shouldDeleteAll) {
                this.loader = true;
                const windowId = this.eventToViewData[0].windowId;
                axios.get(`/care-center/work-schedule/destroy/${windowId}`, {
                        params: {
                            deleteRecurringEvents: shouldDeleteAll
                        },
                    }
                ).then((response => {
                    this.loader = false;
                    this.toggleModal();
                    //Delete event from events() - dom
                    //
                    // const index = this.workHours.findIndex(x => x.data.windowId === windowId);
                    // this.workHours.splice(index, 1);


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

            formatDate(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;

                return [year, month, day].join('-');
            },

            getNurseId() {
                if (this.authIsAdmin) {
                    return this.clickedToViewEvent ? this.eventToViewData[0].nurseId : this.nurseData.nurseId;
                }

                if (this.authIsNurse) {
                    return this.authData.nurseInfoId;
                }
            },

            addNewEvent() {
                this.loader = true;
                const nurseId = this.getNurseId();
                const workDate = this.addNewEventMainClicked ? this.selectedDate : this.workEventDate;
                const repeatFreq = this.eventFrequency.length !== 0 ? this.eventFrequency.value : 'does_not_repeat';
                const validatedDefault = 'not_checked';
                const repeatUntil = this.repeatUntil !== ''
                && repeatFreq !== 'does_not_repeat'
                    ? this.repeatUntil
                    : null;

                if (this.addHolidays) {
                    axios.post('care-center/work-schedule/holidays', {
                        holiday: workDate
                    }).then((response => {
                            console.log(response);
                            this.refetchEvents();
                            this.loader = false;
                            this.toggleModal();
                            this.addNotification({
                                title: "Success!",
                                text: "Holiday has been saved.",
                                type: "success",
                                timeout: true
                            });
                        }
                    )).catch((error) => {
                        console.log(error);
                    });
                } else {
                    if (this.authIsAdmin) {
                        if (nurseId === null
                            || nurseId === undefined) {
                            this.loader = false;
                            this.addNotification({
                                title: "Warning!",
                                text: "Choose an RN field is required",
                                type: "danger",
                                timeout: true
                            });

                            alert("Choose an RN field is required");
                            return;

                        }
                    }

                    if (this.workRangeStarts === '') {
                        this.loader = false;
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
                        this.loader = false;
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
                        this.loader = false;
                        this.addNotification({
                            title: "Warning!",
                            text: "Hours to work for this day is required",
                            type: "danger",
                            timeout: true
                        });

                        alert("Hours to work for this day is required");
                        return;
                    }

                    if (repeatFreq !== 'does_not_repeat') {
                        const until = [];
                        const frequency = [];

                        if (repeatFreq === 'weekly') {
                            frequency.push(RRule.WEEKLY);
                            until.push(new Date(repeatUntil))
                        }
                        if (repeatFreq === 'daily') {
                            frequency.push(RRule.DAILY);
                            until.push(new Date(repeatUntil))
                        }


                        const recurringDatesToEvent = new RRule({                       //https://github.com/jakubroztocil/rrule
                            freq: frequency[0],
                            // byweekday: [q.data.clhDayOfWeek],
                            dtstart: new Date(workDate),
                            until: until[0],
                        });
                        const recurringDates = recurringDatesToEvent.all();
                        const events = this.workHours.concat(this.holidays);
                        // const events = sex().then(res => res.data.eventsForSelectedNurse);
                        // console.log(events);
                        // debugger;
                        const eventsToConfirmTemporary = [];
                        for (var i = 0; i < recurringDates.length; i++) {
                            const date = this.formatDate(recurringDates[i]);
                            const eventsToAskConfirmation = events.filter(event => event.data.date === date && event.data.nurseId === nurseId);
                            //I was expecting filter to return only arrays that satisfy the condition however i im getting also the empty arrays
                            // That's why i  use this conditional here
                            if (eventsToAskConfirmation.length !== 0) {
                                this.loader = false;
                                eventsToConfirmTemporary.push(...eventsToAskConfirmation);
                            }
                        }
                        this.workEventsToConfirm.push(...eventsToConfirmTemporary);

                        if (eventsToConfirmTemporary.length !== 0) {
                            if (confirm("There are some windows overlapping. Do you want to replace the existing windows with the new?")) {
                                this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault, true);
                            }
                        } else {
                            this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault);
                        }
                    } else {
                        this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault);
                    }
                }
            },

            updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault, updateCollisionWindow = null) {
                const updateCollidedWindows = updateCollisionWindow === null ? false : updateCollisionWindow;
                const nurseInfoId = !!nurseId ? nurseId : '';
                axios.post('/care-center/work-schedule', {
                    nurse_info_id: nurseInfoId,
                    date: workDate,
                    day_of_week: this.dayOfWeek.dayOfWeek, //this is actually empty but is needed to pass validation. im creating this var in php
                    work_hours: this.hoursToWork,
                    window_time_start: this.workRangeStarts,
                    window_time_end: this.workRangeEnds,
                    repeat_freq: repeatFreq,
                    until: repeatUntil,
                    validated: validatedDefault,
                    updateCollisions: updateCollidedWindows
                }).then((response => {
                        this.refetchEvents();
                        this.loader = false;
                        this.toggleModal();
                        //@todo: Will fix refetchEvents() for this. so i disabled it
                        // const newEvent = this.prepareLiveData(response.data); //to show in UI before page reload.
                        // this.eventsAddedNow.push(newEvent);
                        this.addNotification({
                            title: "Success!",
                            text: "Event has been created.",
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
            prepareLiveData(newEventData) {
                // return {
                //     allDay: true,
                //     until: newEventData.scheduledData.until,
                //
                //     data: {
                //         date: this.workEventDate,
                //         windowId: newEventData.window.id,
                //         end: this.workRangeEnds,
                //         start: this.workRangeStarts,
                //         name: this.nurseData.label,
                //         nurseId: this.nurseData.nurseId,
                //         eventType: defaultEventType,
                //
                //     },
                //     // dow: [newEventData.window.dayOfWeek],
                //     end: `${this.workEventDate}T${this.workRangeEnds}`,
                //     start: `${this.workEventDate}T${this.workRangeStarts}`,
                //     title: `${this.nurseData.label} (${this.hoursToWork}h)
                //     ${this.workRangeStarts}-${this.workRangeEnds}`,
                // }
            },

            handleDateCLick(date, jsEvent, view) {
                const clickedDate = date;
                const today = Date.parse(this.today);

                this.workEventDate = '';
                this.workEventDate = clickedDate.format();

                if (clickedDate >= today) {
                    this.toggleModal();
                    const clickedDayOfWeek = new Date(clickedDate.format()).getDay();
                    const weekMapDay = this.weekMap.filter(q => q.weekMapClhDayOfWeek === clickedDayOfWeek);
                    this.dayInHumanLangForView = weekMapDay[0].label;

                } else {
                    this.loader = false;
                    this.addNotification({
                        title: "Warning!",
                        text: 'You can only add/edit events for today or for a future date',
                        type: "danger",
                        timeout: true
                    });

                    alert('You can only add/edit events for today or for a future date');
                }

            },


            handleEventCLick(arg) {
                const today = Date.parse(this.today);
                const clickedDate = Date.parse(arg.data.date);
                // Dont allow delete of a past event
                if (clickedDate <= today) {
                    return;
                }
                this.loader = true;
                this.clickedToViewEvent = true;
                this.eventToViewData.push(arg.data);
                this.workEventDate = '';
                this.workEventDate = this.eventToViewData[0].date;


                if (arg.data.eventType !== 'holiday' && arg.repeat_frequency !== 'does_not_repeat') {
                    this.isRecurringEvent = true;
                }

                this.toggleModal();
                this.loader = false;
            },

            handleEventDrop(arg) {
                //@todo:do nothing for now.
            },

            resetModalValues() {
                this.clickedToViewEvent = false;
                this.eventToViewData = [];
                this.dayInHumanLangForView = '';
                this.hoursToWork = '5';
                this.nurseData = [];
                this.workRangeStarts = '09:00';
                this.workRangeEnds = '17:00';
                this.addNewEventMainClicked = false;
                this.eventFrequency = [];
                this.isRecurringEvent = false;
                this.workEventDate = '';
                this.addHolidays = false;
                this.selectedDate = '';
            },

            dataForSearchFilter() {
                // With Current way of rendedring events this array has only nurses from time range in display
                const workEvents = this.workHours;
                const workEventsWithHolidays = workEvents.concat(this.holidays);

                if (this.showWorkEvents && !this.showHolidays) {
                    return removeDuplicatesFrom(workEvents);
                } else if (!this.showWorkEvents && this.showHolidays) {
                    return removeDuplicatesFrom(this.holidays);
                } else {
                    return removeDuplicatesFrom(workEventsWithHolidays);
                }
            },
            eventsFiltered() {
                const workEvents = this.workHours;
                const workEventsWithHolidays = workEvents.concat(this.holidays);
                if (this.searchFilter === null || this.searchFilter.length === 0) {
                    if (this.showWorkEvents && !this.showHolidays) {
                        return workEvents;
                    } else if (this.showHolidays && !this.showWorkEvents) {
                        return this.holidays;
                    } else {
                        return workEventsWithHolidays;
                    }

                } else {
                    if (this.showWorkEvents && !this.showHolidays) {
                        return this.searchFilter.map(q => {
                            return workEvents.filter(event => event.data.nurseId === q.nurseId);
                        }).map(arr => arr).flat();
                    } else if (this.showHolidays && !this.showWorkEvents) {
                        return this.searchFilter.map(q => {
                            return this.holidays.filter(event => event.data.nurseId === q.nurseId);
                        }).map(arr => arr).flat();
                    } else {
                        return this.searchFilter.map(q => {
                            return workEventsWithHolidays.filter(event => event.data.nurseId === q.nurseId);
                        }).map(arr => arr).flat();
                    }
                }
            },

            calculateMinDate() {
                return this.workEventDate !== '' ? this.workEventDate : this.today;
            },

            // calculateMaxDate() {
            //
            // },
        }),
//@todo:implement a count for search bar results - for results found - and in which month are found. maybe a side bar
        computed: {
            repeatFrequencyHasSelected() {
                return this.eventFrequency !== null && this.eventFrequency.length !== 0;
            },

            modalTitle() {
                return this.clickedToViewEvent ? 'View / Delete Event' : 'Add new window';
            },

        },

        created() {
            self = this;
            if (this.authData.role === 'admin') {
                this.authIsAdmin = true;
            }

            if (this.authData.role === 'nurse') {
                this.authIsNurse = true;
            }

            // this.loader = true;
            // // All Work Events
            // axios.get('care-center/work-schedule/get-calendar-data')
            //     .then((response => {
            //         const calendarData = response.data.calendarData;
            //         // this.workHours.push(...calendarData.workEvents);
            //         // this.dataForDropdown.push(...calendarData.dataForDropdown);
            //         this.today = calendarData.today;
            //         // this.loader = false;
            //
            //     })).catch((error) => {
            //     this.errors = error;
            //     console.log(this.errors);
            // });
            // //    All Holiday Events
            // axios.get('nurses/holidays')
            //     .then((response => {
            //             //loader add
            //             this.showWorkHours = false;
            //             this.holidays.push(...response.data.holidays);
            //             this.loader = false;
            //         }
            //     )).catch((error) => {
            //     console.log(error);
            // });

        },

        mounted() {
            $('#addWorkEvent').on("hidden.bs.modal", this.resetModalValues);
        }
    }


</script>

<style>
    h5 {
        font-family: inherit;
        line-height: 0.2;
        font-size: 13px;
    }

    .add-event-main {
        margin-bottom: 30px;
    }

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

    .repeated-checkbox {
        margin-top: 19%;
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

    .choose-event-date {
        margin-top: 19%;
    }

    .modal-inputs {
        display: inline-flex;
    }

    .modal-footer {
        margin-top: 5%;
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

    .search-filter {
        width: 50%;
        margin-left: 23%;
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

    .add-holidays {
        font-size: 20px;
    }

    #addHolidays {
        display: inline-block;
        margin-left: 125px;
        margin-top: 43px;
    }

    .repeat-until {
        margin-top: 10px;
    }

    #addWorkEvent > div.modal-dialog > div > div.modal-header > button {
        visibility: hidden;
    }
</style>

