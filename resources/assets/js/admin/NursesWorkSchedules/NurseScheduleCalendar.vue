<template>
    <div>
        <div class="calendar-menu">
            <div v-if="authIsAdmin"
                 class="col-lg-2">
                <div class="show-holidays">
                    <input id="holidaysCheckbox"
                           type="checkbox"
                           class="holidays-button"
                           @click="refetchEvents()"
                           v-model="showHolidays">
                    Holidays
                </div>
                <div class="show-work">
                    <input id="workCheckbox"
                           type="checkbox"
                           class="work-button"
                           @click="refetchEvents()"
                           v-model="showWorkEvents">
                    Work Events
                </div>
            </div>

            <div class="search-filter">
                <vue-select ref="searchFilter"
                            v-if="authIsAdmin"
                            multiple v-model="searchFilter"
                            @input="refetchEvents()"
                            :options="nursesForSearchFilter()"
                            placeholder="Filter RN"
                            required>
                </vue-select>
            </div>
        </div>
        <div class="calendar">
            <!-- Add new event - main button-->
            <div class="add-event-main col-md-2">
                <button class="btn btn-primary" @click="openMainEventModal">Add new window</button>
            </div>
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
                                    <h5>Event Date:</h5>
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
    import {addNotification} from '../../../../../resources/assets/js/store/actions.js';
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
                dataForDropdown: [],
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
                isRecurringEvent: false,
                addHolidays: false,
                authIsAdmin: false,
                authIsNurse: false,
                eventSources: [
                    { // has to be 'events()' else it doesnt work
                        events(start, end, timezone, callback) {
                            self.loader = true;
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
                                    const eventsFiltered = self.eventsFiltered();
                                    self.loader = false;
                                    callback(eventsFiltered);
                                })).catch((error) => {
                                this.errors = error;
                                console.log(this.errors);
                            });
                        },
                    },
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
                // This should never happen
                if (eventType === 'companyHoliday') {
                    alert('You cant delete company holiday');
                }
                if (!confirm('Are you sure you want to delete this window?')) {
                    return;
                }

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
                    this.loader = false;
                    this.toggleModal();
                    this.refetchEvents();
                    this.throwSuccessNotification(response.data.message);
                })).catch((error) => {
                    this.errors = error;
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
                    this.refetchEvents();
                    this.throwSuccessNotification(response.data.message);
                })).catch((error) => {
                    console.log(error);
                    if (error.response.status === 422) {
                        this.errors = error;
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

            addHolidayEvents(workDate) {
                axios.post('care-center/work-schedule/holidays', {
                    holiday: workDate
                }).then((response => {
                        console.log(response);
                        this.refetchEvents();
                        this.loader = false;
                        this.toggleModal();
                        this.throwSuccessNotification("Holiday has been saved.");
                    }
                )).catch((error) => {
                    console.log(error.response.data);
                    if (error.response.status === 422) {
                        this.manipulateError(error);
                    }
                });
            },

            manipulateError(error) {
                const validatorError = error.response.data.validator;
                this.refetchEvents();
                this.loader = false;
                // this.toggleModal();
                Object.keys(validatorError).forEach(key => {
                    this.throwWarningNotification(validatorError[key][0]);
                });
            },

            throwWarningNotification(text) {
                this.addNotification({
                    title: "Warning!",
                    text: text,
                    type: "danger",
                    timeout: true
                });
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
                    this.addHolidayEvents(workDate);
                } else {
                    if (this.authIsAdmin) {
                        if (nurseId === null
                            || nurseId === undefined) {
                            this.loader = false;
                            this.throwWarningNotification("Choose an RN field is required");
                            return;
                        }
                    }

                    this.getExistingEventsForSelectedNurse(nurseId, workDate).then(events => {
                        const eventsToConfirmTemporary = this.getEventsToConfirmTemporary(events, repeatFreq, repeatUntil, workDate);
                        if (eventsToConfirmTemporary.length !== 0) {
                            if (eventsToConfirmTemporary.filter(event => event.data.eventType === 'holiday' || event.data.eventType === 'companyHoliday').length !== 0) {
                                if (confirm("There are windows overlapping some of your days-off. We will not replace your days-off.")) {
                                    this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault, true);
                                }
                            } else if (confirm("There are overlapping windows. Do you want to replace the existing windows with new?")) {
                                this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault, true);
                            }
                        } else {
                            this.updateOrSaveEventsInDb(nurseId, workDate, repeatFreq, repeatUntil, validatedDefault);
                        }
                    }).catch(error => {
                        console.log(error);
                        alert(error);
                    });
                }
            },

            getEventsToConfirmTemporary(events, repeatFreq, repeatUntil, workDate) {
                // Create temporary recurring dates to evaluate and prompt user to act in front-end
                const eventsToConfirmTemporary = [];
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

                    const recurringDatesToEvent = new RRule({                  //https://github.com/jakubroztocil/rrule
                        freq: frequency[0],
                        dtstart: new Date(workDate),
                        until: until[0],
                    });

                    const recurringDates = recurringDatesToEvent.all();
                    for (var i = 0; i < recurringDates.length; i++) {
                        const date = this.formatDate(recurringDates[i]);
                        const eventsToAskConfirmation = this.getConflicts(events, date);
                        if (eventsToAskConfirmation.length !== 0) {
                            this.loader = false;
                            eventsToConfirmTemporary.push(...eventsToAskConfirmation);
                        }
                    }
                }
                return eventsToConfirmTemporary;
            },
            getConflicts(events, date) {
                return events.filter(event => event.data.date === date);
            },

            async getExistingEventsForSelectedNurse(nurseId, workDate) {
                let res = await axios.post('nurses/nurse-calendar-data', {
                    nurseInfoId: nurseId,
                    startDate: workDate
                });
                return res.data.eventsForSelectedNurse;

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
                        this.throwSuccessNotification("Event has been created.");
                    }
                ))
                    .catch((error) => {
                        console.log(error.response.data);
                        if (error.response.status === 422) {
                        this.manipulateError(error);
                        }
                    });
            },

            throwSuccessNotification(text) {
                this.addNotification({
                    title: "Success!",
                    text: text,
                    type: "success",
                    timeout: true
                });
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
                    this.throwWarningNotification('You can only add/edit events for today or for a future date');
                }

            },


            handleEventCLick(arg) {
                const today = Date.parse(this.today);
                if (arg.data.eventType === 'companyHoliday') {
                    alert('This is a Company Holiday, you cannot edit or delete this window');
                    return;
                }

                if (this.authIsAdmin && arg.data.eventType === 'holiday') {
                    alert("You cannot edit a nurse's holiday");
                    return;
                }
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

            nursesForSearchFilter() {
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
        margin-right: -10%;
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
        margin-bottom: 40px;
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

    #calendar > div.fc-toolbar.fc-header-toolbar > div.fc-center {
        margin-left: -2%;
    }

    #calendar > div.fc-toolbar.fc-header-toolbar > div.fc-right {
        margin-right: 15%;
    }
</style>

