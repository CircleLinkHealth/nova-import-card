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
                <vue-select multiple v-model="searchFilter"
                            :options="dataForSearchFilter"
                            placeholder="Filter RN"
                            required>
                </vue-select>
            </div>

            <!-- Add new event - main button-->
            <div class="add-event-main">
                <button class="btn btn-primary" @click="openMainEventModal">Add New</button>
            </div>

            <!--            Previous - Next custom Buttons -->
            <!--            <div class="prev-next-buttons">-->
            <!--                <button type="button"-->
            <!--                        class="prev-button"-->
            <!--                        aria-label="prev" @click="sex('prev')">-->
            <!--                    <span class="fc-icon fc-icon-left-single-arrow"></span>-->
            <!--                </button>-->

            <!--                <button type="button"-->
            <!--                        class="next-button"-->
            <!--                        aria-label="next" @click="sex('next')">-->
            <!--                    <span class="fc-icon fc-icon-right-single-arrow"></span>-->
            <!--                </button>-->

            <!--            </div>-->
        </div>
        <div class="calendar">
            <full-calendar ref="fullCalendar"
                           id="calendar"
                           :events="events"
                           :config="config"
                           @day-click="handleDateCLick"
                           @event-selected="handleEventCLick"
                           @event-drop="handleEventDrop">
            </full-calendar>
            <!--LOADER-->
            <calendar-loader v-show="loader"></calendar-loader>
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
                            <div v-if="!this.clickedToViewEvent || !this.addNewEventMainClicked" class="display-date">
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

                                <div class="repeat-until">
                                    <input type="date" name="until"
                                           v-model="repeatUntil">
                                </div>
                            </div>

                            <div v-if="!clickedToViewEvent" style="margin-top: 20%;">
                                <div class="repeat-day-frequency">
                                    <vue-select :options="frequency"
                                                v-model="eventFrequency"
                                                placeholder="Doesn't Repeat">
                                    </vue-select>
                                </div>

                                <div v-if="addNewEventMainClicked">
                                    <div class="choose-event-date">
                                        <input type="date" name="event_date"
                                               v-model="selectedDate">
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
    import {FullCalendar} from 'vue-full-calendar';
    import RRule from 'rrule';
    import 'fullcalendar/dist/fullcalendar.css';
    import VueSelect from 'vue-select';
    import {mapActions} from 'vuex';
    import {addNotification} from '../../../../../resources/assets/js/store/actions.js'; //@todo:doesnt work yet.
    import CalendarLoader from './CalendarLoader';

    const viewDefault = 'agendaWeek';
    const defaultEventType = 'workDay';
    const holidayEventType = 'holiday';

    export default {
        name: "NurseScheduleCalendar",

        props: [
            'calendarData',
            'dataForDropdown',
            'today',
            'startOfMonth',
            'endOfMonth',
            'endOfYear'
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
                eventFrequency: [],
                addNewEventMainClicked: false,
                selectedDate: [],
                selectedMonthInView: this.startOfMonth,
                repeatUntil: '',


                config: {
                    defaultView: viewDefault,
                    editable: false,

                    header: {
                        left: 'prev, next, today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },

                    // validRange: {
                    //     end: this.endOfThisWeek,
                    //     start: this.startOfThisWeek,
                    // }


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
                    {
                        label: 'Repeat Monthly on same date',
                        value: 'monthly'
                    },
                ],

                // monthOfYearDates: [
                //     {
                //         month: 'September',
                //         monthAbr: 'Sep',
                //         date: new Date(this.year, 8, 1)
                //
                //     },
                //     {
                //         month: 'October',
                //         monthAbr: 'Oct',
                //         date: new Date('2019-10-01')
                //     },
                //     {
                //         month: 'November',
                //         monthAbr: 'Nov',
                //         date: new Date('2019-11-01')
                //     },
                //     {
                //         month: 'December',
                //         monthAbr: 'Dec',
                //
                //         date: new Date('2019-12-01')
                //     },
                //     {
                //         month: 'January',
                //         monthAbr: 'Jan',
                //         date: new Date('2019-01-01')
                //     },
                //     {
                //         month: 'February',
                //         monthAbr: 'Feb',
                //         date: new Date('2019-02-01')
                //     },
                //     {
                //         month: 'March',
                //         monthAbr: 'Mar',
                //         date: new Date('2019-03-01')
                //     },
                //     {
                //         month: 'April',
                //         monthAbr: 'Apr',
                //         date: new Date('2019-04-01')
                //     },
                //     {
                //         month: 'May',
                //         monthAbr: 'May',
                //         date: new Date('2019-05-01')
                //     },
                //     {
                //         month: 'June',
                //         monthAbr: 'Jun',
                //         date: new Date('2019-06-01')
                //     },
                //     {
                //         month: 'July',
                //         monthAbr: 'Jul',
                //         date: new Date('2019-07-01')
                //     },
                //     {
                //         month: 'August',
                //         monthAbr: 'Aug',
                //         date: new Date('2019-08-01')
                //     }
                // ],
            }
        },

        methods: Object.assign(mapActions(['addNotification']), {
            openMainEventModal() {
                this.addNewEventMainClicked = true;
                this.toggleModal();
            },

            toggleModal() {
                $("#addWorkEvent").modal('toggle');
            },

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
                this.loader = true;
                const holidayId = event.holidayId;
                axios.get(`/care-center/work-schedule/holidays/destroy/${holidayId}`).then((response => {
                    this.toggleModal();
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

            deleteWorkDay(event, isAddedNow) {
                this.loader = true;
                const windowId = this.eventToViewData[0].windowId;
                axios.get(`/care-center/work-schedule/destroy/${windowId}`).then((response => {
                    this.loader = false;
                    this.toggleModal();
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
                const workDate = this.addNewEventMainClicked ? this.selectedDate : this.workEventDate;
                const repeatFreq = this.eventFrequency.length !== 0 ? this.eventFrequency.value : 'does_not_repeat';
                const repeatUntil = this.repeatUntil !== '' ? this.repeatUntil : this.endOfYear; //null - repeat forever

                if (nurseId === null || nurseId === undefined) {
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

                axios.post('/care-center/work-schedule', {
                    nurse_info_id: nurseId,
                    date: workDate,
                    day_of_week: this.dayOfWeek.dayOfWeek, //this is actually empty but is needed to pass validation. im creating this var in php
                    work_hours: this.hoursToWork,
                    window_time_start: this.workRangeStarts,
                    window_time_end: this.workRangeEnds,
                    repeat_freq: repeatFreq,
                    repeat_until: repeatUntil
                }).then((response => {
                        this.loader = false;
                        this.toggleModal();
                        const newEvent = this.prepareLiveData(response.data); //to show in UI before page reload.
                        this.eventsAddedNow.push(newEvent);

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
                    // dow: [newEventData.window.dayOfWeek],
                    end: `${this.workEventDate}T${this.workRangeEnds}`,
                    start: `${this.workEventDate}T${this.workRangeStarts}`,
                    title: `${this.nurseData.label} (${this.hoursToWork}h)
                    ${this.workRangeStarts}-${this.workRangeEnds}`,
                }
            },

            handleDateCLick(date, jsEvent, view) {
                const clickedDate = date;
                const today = Date.parse(this.today);

                this.workEventDate = '';
                this.workEventDate = clickedDate.format();

                if (clickedDate >= today) {
                    this.toggleModal();
                    const clickedDayOfWeek = new Date(this.workEventDate).getDay();
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
                this.loader = true;
                this.clickedToViewEvent = true;
                this.eventToViewData.push(arg.data);
                this.workEventDate = '';
                this.workEventDate = this.eventToViewData[0].date;

                this.toggleModal();
                this.loader = false;
            },

            handleEventDrop(arg) {
                //@todo:do nothing for now.
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
                this.addNewEventMainClicked = false;
                this.eventFrequency = [];
            },

            // sex(direction) {
            //
            //     const calendarDateTitle = this.$refs.fullCalendar.fireMethod('getView');
            //     const monthInView = calendarDateTitle.title.split(" ", 1);
            //
            //     const x = this.monthOfYearDates.filter(q => q.month === monthInView[0] || q.monthAbr === monthInView[0]);
            //     const date = x[0].date;
            //
            //     this.selectedMonthInView = '';
            //     this.selectedMonthInView = date;
            //
            //
            //     if (direction === 'prev') {
            //         this.$refs.fullCalendar.fireMethod('prev');
            //     } else {
            //         this.$refs.fullCalendar.fireMethod('next');
            //     }
            //
            // },
        }),

        computed: {
            modalTitle() {
                return this.clickedToViewEvent ? 'View / Delete Event' : 'Add new work window';
            },

            events() {
                debugger;
                const data = this.workHours.concat(this.eventsAddedNow);
                //@todo: Future impl. if event is not set to repeated the DONT add the rule.
                const events = data.map(q => {
                    const repeatFrequency = q.repeat_frequency;
                    const repeatUntil  = q.until !== null ? q.until : this.endOfYear;

                    if (repeatFrequency !== 'does_not_repeat') {
                        const frequency = [];
                        //current data have null frequency. These events will repeat WEEKLY by default,
                        // to keep current functionality working
                        if (repeatFrequency === null || repeatFrequency === 'weekly') {
                            frequency.push(RRule.WEEKLY);
                        }

                        if (repeatFrequency === 'monthly') {
                            frequency.push(RRule.MONTHLY);
                        }

                        if (repeatFrequency === 'daily') {
                            frequency.push(RRule.DAILY);
                        }
                        const rule = new RRule({                       //https://github.com/jakubroztocil/rrule
                            freq: frequency[0],
                            // byweekday: [q.data.clhDayOfWeek],
                            dtstart: new Date(q.start),
                            until: new Date(repeatUntil),
                        });
                        // const rrule = rule.between(new Date(this.selectedMonthInView), new Date(this.endOfMonth));

                        const rrule = rule.all();
                        const eventWithRules = [];
                        for (var i = 0; i < rrule.length; i++) {
                            eventWithRules.push({
                                title: q.title,
                                start: rrule[i],
                                allDay: true,
                                color: q.color,
                                textColor: q.textColor,
                                repeat_frequency: q.repeat_frequency,
                                until:q.until,
                                data: q.data,
                            })
                        }
                        return eventWithRules;
                    } else {
                        return data;
                    }
                }).map(event => event).flat();

                //@todo:implement a count - results found and in which month are found

                const workEventsWithHolidays = events.concat(this.holidays);
                if (this.searchFilter === null || this.searchFilter.length === 0) {
                    if (!this.showWorkAndHolidaysIsChecked) {
                        return this.showWorkHours ? events : this.holidays;
                    }

                    if (this.showWorkAndHolidaysIsChecked) {
                        return workEventsWithHolidays;
                    }
                } else {
                    if (!this.showWorkAndHolidaysIsChecked) {
                        return this.searchFilter.map(q => {
                            return this.showWorkHours ? events.filter(event => event.data.nurseId === q.nurseId)
                                : this.holidays.filter(event => event.data.nurseId === q.nurseId);
                        }).map(arr => arr).flat();
                    }
                    if (this.showWorkAndHolidaysIsChecked) {
                        return this.searchFilter.map(q => {
                            return workEventsWithHolidays.filter(event => event.data.nurseId === q.nurseId);
                        }).map(arr => arr).flat();

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
            $('#addWorkEvent').on("hidden.bs.modal", this.resetModalValues);
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
        margin-top: 4%;
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
</style>

