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
            <div class="add-event-main col-md-3">
                <button class="btn btn-primary" @click="openMainEventModal">{{this.addMainEventButtonName}}</button>
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
            <!-- Daily Report Modal -->

            <calendar-daily-report
                    :report-data="reportData"
                    :report-date="reportDate"
                    :report-flags="reportFlags"
                    :pop-up-now="false">
            </calendar-daily-report>
            <!-- Daily Report Modal End-->

            <!-- Modal -->
            <div class="modal fade" id="addWorkEvent" tabindex="-1" role="dialog"
                 aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">

                            <div class="modal-title" id="exampleModalLabel">
                                <div class="row">
                                    <div class="col-md-12" style="text-align: center">
                                        <h3>{{this.modalTitle}}</h3>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body-custom col-md-12">
                            <!--  Filter Options-->
                            <div class="row">
                                <div v-if="authIsAdmin && !clickedToViewEvent" class="col-md-6">
                                    <vue-select v-model="nurseData"
                                                :options="dataForDropdown"
                                                placeholder="Choose RN"
                                                required>
                                    </vue-select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="holiday-on-off">
                                    <div v-if="! authIsAdmin && (clickedOnDay || addNewEventMainClicked)">

                                        <div class="event-toggle">
                                            <button id="workDayToggle"
                                                    type="button"
                                                    class="btn btn-primary"
                                                    :class="{disableToggleButtons : disableAddWorkDayToggle}"
                                                    style="background-color: white; font-size: 20px; color: #5b5858;"
                                                    @click="toggleEventSwitch(true)">Work Day
                                            </button>

                                            <div class="toggle-switch">
                                                <label class="switch">
                                                    <input id="toggleSwitch"
                                                           type="checkbox"
                                                           v-model="addHolidays"
                                                           checked>
                                                    <span class="slider round"></span>
                                                </label>

                                                <button type="button"
                                                        class="btn btn-primary"
                                                        :class="{disableToggleButtons : disableAddDayOffToggle}"
                                                        style="background-color: white; font-size: 20px; color: #5b5858;"
                                                        @click="toggleEventSwitch(false)">Day Off
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div v-if="!clickedToViewEvent" class="filter-options col-md-3">
                                <div class="row">
                                    <div class="choose-event-date">
                                        <div v-if="addNewEventMainClicked">
                                            <span class="modal-inputs-labels">{{this.addFromMainButtonDateLabel}}</span>
                                            <input type="date"
                                                   id="eventDate"
                                                   class="event-date-field"
                                                   name="event_date"
                                                   :min="calculateMinDate()"
                                                   v-model="selectedDate">
                                        </div>
                                    </div>


                                    <div class="work-hours">
                                        <span class="modal-inputs-labels">For:</span>
                                        <input v-model="hoursToWork"
                                               type="number"
                                               :class="{disable: addHolidays}"
                                               :disabled="addHolidays"
                                               class="work-hours-input"
                                               placeholder="5"
                                               min="1" max="12">
                                        <span class="modal-inputs-labels" style="padding-left:1%">hours</span>
                                    </div>


                                    <div class="start-time">
                                        <span class="modal-inputs-labels">Start Time:</span>
                                        <input v-model="workRangeStarts"
                                               type="time"
                                               :class="{disable: addHolidays}"
                                               style="height: 30px;width: 105px;"
                                               :disabled="addHolidays"
                                               class="time-input">
                                    </div>

                                    <div class="end-time">
                                        <span class="modal-inputs-labels">End Time:</span>
                                        <input v-model="workRangeEnds"
                                               type="time"
                                               :class="{disable: addHolidays}"
                                               style="height: 30px;width: 105px;"
                                               :disabled="addHolidays"
                                               class="time-input"></div>
                                </div>

                                <div v-if="!clickedToViewEvent" class="repeat-day-frequency">
                                    <span class="modal-inputs-labels">Repeat Frequency:</span>
                                    <vue-select :options="frequency"
                                                :class="{disable: addHolidays}"
                                                :disabled="addHolidays"
                                                style="width: 188px;"
                                                v-model="eventFrequency"
                                                placeholder="Doesn't Repeat">
                                    </vue-select>
                                </div>


                                <div v-if="repeatFrequencyHasSelected">
                                    <div class="repeat-until">
                                        <span class="modal-inputs-labels">Keep repeating until:</span>
                                        <input type="date"
                                               :class="{disable: !repeatFrequencyHasSelected || addHolidays}"
                                               :disabled="!repeatFrequencyHasSelected || addHolidays"
                                               class="repeat-until-input"
                                               name="until"
                                               :min="calculateMinDate()"
                                               :max="calculateMaxDate()"
                                               v-model="repeatUntil">
                                    </div>

                                    <div class="exclude-weekends">
                                        <input id="excludeWeekends"
                                               type="checkbox"
                                               class="exclude-weekends"
                                               v-model="excludeWeekends">
                                        Exclude Weekends
                                    </div>
                                </div>
                            </div>


                            <div v-if="clickedToViewEvent && eventToViewData[0].eventType === 'holiday'"
                                 class="view-event">
                                <div v-if="authIsAdmin" class="nurse-name">{{this.eventToViewData[0].name}}</div>
                            </div>
                            <div v-if="clickedToViewEvent && eventToViewData[0].eventType === 'workDay'"
                                 class="view-event">
                                <div v-if="authIsAdmin" class="nurse-name">{{this.eventToViewData[0].name}}</div>
                                <div class="work-hours-read">Work for {{this.eventToViewData[0].workHours}} hours
                                    between {{this.eventToViewData[0].start}} and {{this.eventToViewData[0].end}}
                                </div>
                            </div>
                        </div>
                        <!-- Filters End-->
                        <div class="modal-footer">
                            <button v-if="clickedToViewEvent"
                                    type="button"
                                    class="btn btn-primary"
                                    style="float: left; background-color: rgba(255, 133, 28, 0.94);"
                                    @click="deleteEvent(false)">Delete this event
                            </button>

                            <button v-if="clickedToViewEvent && isRecurringEvent"
                                    type="button"
                                    class="btn btn-primary"
                                    style="background-color: crimson; border-color: crimson; float: left;"
                                    @click="deleteEvent(true)">Delete all repeating events
                            </button>

                            <button v-if="!clickedToViewEvent" type="button"
                                    class="btn btn-primary"
                                    @click="addNewEvent">{{this.modalSubmitButton}}
                            </button>
                            <button type="button"
                                    class="btn btn-primary"
                                    style="float: right; background-color:#d9534f;"
                                    @click="cancelModalAction">Cancel
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
    import CalendarLoader from './FullScreenLoader';
    import axios from "../../bootstrap-axios";
    import CalendarDailyReport from "./CalendarDailyReport";
    // import VModal from 'vue-js-modal';
    //
    // Vue.use(VModal);

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
            RRule,
            'calendar-daily-report': CalendarDailyReport
        },

        data() {
            return {
                excludeWeekends: true,
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
                clickedOnDay: false,
                dailyReports: [],
                reportData: [],
                reportDate: '',
                reportFlags: [],
                eventSources: [
                    { // has to be 'events()' else it doesnt work
                        events(start, end, timezone, callback) {
                            self.loader = true;
                            axios.get('care-center/work-schedule/get-calendar-data', {
                                params: {
                                    start: new Date(start),
                                    end: new Date(end),
                                }
                            }).then((response => {
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
                                if (error.response.status === 422) {
                                    const e = error.response.data;
                                    if (e.hasOwnProperty('message')) {
                                        alert(e.message);
                                    } else {
                                        alert(e.validator);
                                    }
                                    self.loader = false;
                                }
                                console.log(error);
                            });
                        },
                    },
                    {
                        events(start, end, timezone, callback) {
                            self.loader = true;
                            // Dont call this on view change from "week" to "month"
                            if (self.dailyReports.length === 0 && self.authIsNurse) {
                                axios.get('care-center/work-schedule/get-daily-report')
                                    .then((response => {
                                        const dailyReports = response.data.dailyReports;
                                        self.dailyReports.push(...dailyReports);
                                        self.loader = false;
                                        callback(dailyReports);
                                    })).catch((error) => {
                                    if (error.response.status === 422) {
                                        const e = error.response.data;
                                        if (e.hasOwnProperty('message')) {
                                            alert(e.message);
                                        } else {
                                            alert(e.validator);
                                        }
                                        self.loader = false;
                                    }
                                    console.log(error);
                                });
                            } else {
                                callback(self.dailyReports);
                                self.loader = false;
                            }
                        }
                    }
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
                        label: 'Repeat Every Day',
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
            toggleEventSwitch(status) {
                //If day off is clicked then toggle checkbox to "checked" else to "unchecked"
                if (status) {
                    this.addHolidays = false;
                    document.getElementById("toggleSwitch").checked = false;
                } else {
                    this.addHolidays = true;
                    document.getElementById("toggleSwitch").checked = true;
                }
            },

            // showModal() {
            //     this.$modal.show('hello-world');
            // },
            eventIsTomorrow() {
                const todayDate = new Date(this.today);
                const eventDate = new Date(this.workEventDate);
                const tomorrowDate = new Date(todayDate.setDate(todayDate.getDate() + 1));
                return eventDate.getDate() === tomorrowDate.getDate();

            },

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

            toggleModalDailyReport() {
                $("#dailyReport").modal('toggle');
            },

            userIsNurseAndDeletesTomorrowEvent(eventType) {
                return this.eventIsTomorrow() && this.authIsNurse && eventType === 'workDay';
            },

            deleteEvent(shouldDeleteAll) {
                const event = this.eventToViewData[0];
                const eventType = event.eventType;

                if (this.userIsNurseAndDeletesTomorrowEvent(eventType)) {
                    this.throwWarningNotification('Workdays cannot be removed the day before. Please reach out to your manager for assistance.');
                    return;
                }
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

            formatDateForPlaceHolder(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;

                return [month, day, year].join('/');
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
                    if (workDate.length === 0) {
                        this.loader = false;
                        this.throwWarningNotification("Day-off date is required");
                        return;
                    }
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
                const excludeWeekends = repeatFreq !== 'does_not_repeat' && this.excludeWeekends;
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
                    updateCollisions: updateCollidedWindows,
                    excludeWkds: excludeWeekends
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
                this.clickedOnDay = true;

                if (clickedDate >= today) {
                    this.toggleModal();
                    const clickedDayOfWeek = new Date(clickedDate).getUTCDay();
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

                if (this.authIsNurse && arg.data.eventType === 'dailyReport') {
                    this.reportData = [];
                    this.reportDate = '';
                    this.reportFlags = [];
                    this.reportData = arg.data.reportData;
                    this.reportDate = arg.data.date;
                    this.reportFlags = arg.data.reportFlags;
                    this.toggleModalDailyReport();
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

            cancelModalAction() {
                this.resetModalValues();
                this.toggleModal();
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
                this.clickedOnDay = false;
                this.excludeWeekends = true;
                this.repeatUntil = '';
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

            calculateMaxDate() {
                //Sets limit from starting selected date + 1 month.
                const date = new Date(this.workEventDate);
                const maxRepeatDate = date.setMonth(date.getMonth() + 1);
                return this.formatDate(maxRepeatDate);
            },
        }),
//@todo:implement a count for search bar results - for results found - and in which month are found. maybe a side bar
        computed: {
            disableAddWorkDayToggle() {
                return this.addHolidays;
            },

            disableAddDayOffToggle() {
                return !this.addHolidays;
            },
            repeatFrequencyHasSelected() {
                return this.eventFrequency !== null && this.eventFrequency.length !== 0;
            },

            addMainEventButtonName() {
                if (this.authIsAdmin) {
                    return 'Add Workday';
                }
                if (this.authIsNurse) {
                    return 'Add Workday/Holiday';
                }
                return 'Add Workday';
            },

            modalSubmitButton() {
                return this.addHolidays ? 'Save Day Off' : 'Save Work Day';
            },

            modalTitle() {
                if (this.addNewEventMainClicked) {
                    return 'Add Event';
                }
                if (this.clickedToViewEvent && this.eventToViewData[0].eventType === 'workDay') {
                    return `Workday: ${this.eventToViewData[0].day} (${this.eventToViewData[0].date})`
                }

                if (this.clickedToViewEvent && this.eventToViewData[0].eventType === 'holiday') {
                    return `Day off on ${this.eventToViewData[0].day} (${this.eventToViewData[0].date})`
                }
                if (this.clickedOnDay) {
                    return `Add Event for ${this.dayInHumanLangForView} (${this.workEventDate})`
                }
            },

            addFromMainButtonDateLabel() {
                return this.addNewEventMainClicked && this.addHolidays ? 'Day-off on:' : 'Working on:';
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
        text-align: left;
    }

    .display-date {
        text-align: left;
    }

    .nurse-name {
        text-align: left;
        font-size: 18px;
        letter-spacing: 1px;
        font-weight: 500;
        margin-bottom: 1%;
        margin-top: 1%;
    }

    .work-hours-read {
        text-align: left;
        font-size: 17px;
        letter-spacing: 1px;
        font-weight: 500;
    }

    .work-day-read {
        text-align: left;
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
        padding-top: 15px;
    }

    .work-hours-input {
        height: 25px;
        font-size: 14px;
        color: #555;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
    }


    .modal-footer {
        margin-top: 10%;
        border-top: unset;
    }

    .start-time {
        padding-top: 15px;
    }

    .end-time {
        padding-top: 15px;
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
        /*min-height: 100px;*/
        /*overflow-y: scroll;*/
        /*max-height: 670px;*/
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
        margin-left: 6%;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr > td > a > div.fc-content > span {
        font-size: 112%;
        font-weight: 400;
    }

    #calendar > div.fc-view-container > div > table > tbody > tr > td > div.fc-day-grid.fc-unselectable > div > div.fc-content-skeleton > table > tbody > tr > td {
        padding-top: 8px;
    }

    #addHolidays {
        display: inline-block;
        margin-bottom: 10px;
        font-size: larger;
    }

    .repeat-until {
        margin-left: -15px;
        padding-top: 15px;
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

    .event-date-field {
        height: 31px;
        width: 139px;
        border-radius: 5px;
    }

    .modal-inputs-labels {
        color: #5b5858;
        font-weight: bolder;
        font-size: 15px;
    }

    .repeat-until-input {
        height: 32px;
        border-radius: 5px;
        width: 165px;
    }

    .repeat-day-frequency {
        margin-left: -15px;
        padding-top: 15px;
    }

    .holiday-on-off {
        padding-left: 1em;
        padding-bottom: 1em;
    }

    #excludeWeekends {
        display: inline-block;
        font-size: 20px;
    }

    .exclude-weekends {
        font-weight: bolder;
        font-size: 18px;
        padding-top: 20px;
    }

    .modal-backdrop.fade {
        opacity: 0.1;
    }

    #addWorkEvent > div.modal-dialog > div > div.modal-header {
        border-bottom: unset;
    }


    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #47bdab;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #40a0b1;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #40a0b1;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .toggle-switch-name {
        font-size: 20px;
    }

    .event-toggle {
        margin-left: 11em;
        display: inline-flex;
    }

    .toggle-switch {
        display: inline-flex;
    }

    .disableToggleButtons {
        opacity: 0.3;
    }
</style>

