<template>
    <div>
        <div class="row">
            <div class="col-sm-6">
                <a class="btn btn-primary btn-xs" @click="exportExcel">Export Records</a>
                <button class="btn btn-success btn-xs" @click="addCall">Add Call</button>
                <button class="btn btn-warning btn-xs" @click="showUnscheduledPatientsModal">Unscheduled Patients
                </button>
                <button class="btn btn-info btn-xs" @click="clearFilters">Clear Filters</button>
                <label class="btn btn-gray btn-xs">
                    <input type="checkbox" v-model="showOnlyUnassigned" @change="changeShowOnlyUnassigned"/>
                    Show Unassigned
                </label>
                <loader class="absolute" v-if="loaders.calls"></loader>
            </div>
            <div class="col-sm-6 text-right" v-if="itemsAreSelected">
                <button class="btn btn-primary btn-xs" @click="assignSelectedToNurse">Assign To Nurse</button>
                <button class="btn btn-success btn-xs" @click="assignTimesForSelected">Assign Call Times</button>
                <button class="btn btn-danger btn-xs" @click="deleteSelected">Delete</button>
                <button class="btn btn-info btn-xs" @click="clearSelected">Clear Selection</button>
            </div>
        </div>
        <div>
            <v-client-table ref="tblCalls" :data="tableData" :columns="columns" :options="options">
                <template slot="selected" slot-scope="props">
                    <input class="row-select" v-model="props.row.selected" @change="toggleSelect(props.row.id)"
                           type="checkbox"/>
                </template>
                <template slot="h__selected" slot-scope="props">
                    <input class="row-select" v-model="selected" @change="toggleAllSelect" type="checkbox"/>
                </template>
                <template slot="Manual" slot-scope="props">
                    <div class="container" style="width:60px;padding:0">
                        <div class="row" style="margin:auto">
                            <div v-if="props.row['Manual']" class="col-xs-12" style="margin:auto">
                                <font-awesome-icon icon="hand-point-up"/>
                                <font-awesome-icon icon="calendar-check"/>
                            </div>
                        </div>
                    </div>
                </template>
                <template slot="Patient ID" slot-scope="props">
                    <a :href="props.row.notesLink">{{ props.row['Patient ID'] }}</a>
                </template>
                <template slot="Nurse" slot-scope="props">
                    <select-editable v-model="props.row.NurseId" :display-text="props.row.Nurse"
                                     :values="props.row.nurses()" :class-name="'blue'"
                                     :on-change="props.row.onNurseUpdate.bind(props.row)"></select-editable>
                </template>
                <template slot="Next Call" slot-scope="props">
                    <div>
                        <date-editable v-model="props.row['Next Call']" :format="'YYYY-mm-DD'" :class-name="'blue'"
                                       :on-change="props.row.onNextCallUpdate.bind(props.row)"
                                       :show-confirm="props.row['Manual']"
                                       :confirm-message="getEditDateTimeConfirmMessage(props.row)"></date-editable>
                        <loader class="relative" v-if="props.row.loaders.nextCall"></loader>
                    </div>
                </template>
                <template slot="Call Time Start" slot-scope="props">
                    <div>
                        <time-editable :value="props.row['Call Time Start']" :format="'YYYY-mm-DD'" :class-name="'blue'"
                                       :on-change="props.row.onCallTimeStartUpdate.bind(props.row)"
                                       :show-confirm="props.row['Manual']"
                                       :confirm-message="getEditDateTimeConfirmMessage(props.row)"></time-editable>
                        <loader class="relative" v-if="props.row.loaders.callTimeStart"></loader>
                    </div>
                </template>
                <template slot="Call Time End" slot-scope="props">
                    <div>
                        <time-editable :value="props.row['Call Time End']" :format="'YYYY-mm-DD'" :class-name="'blue'"
                                       :on-change="props.row.onCallTimeEndUpdate.bind(props.row)"
                                       :show-confirm="props.row['Manual']"
                                       :confirm-message="getEditDateTimeConfirmMessage(props.row)"></time-editable>
                        <loader class="relative" v-if="props.row.loaders.callTimeEnd"></loader>
                    </div>
                </template>
                <template slot="CCM Time" slot-scope="props">
                    <div>
                        <span :class="!isCcmEligible(props.row.id) ? 'disabled' : ''">
                            {{props.row['CCM Time']}}
                        </span>
                    </div>
                </template>
                <template slot="BHI Time" slot-scope="props">
                    <div>
                        <span :class="!isBhiEligible(props.row.id) ? 'disabled' : ''">
                            {{props.row['BHI Time']}}
                        </span>
                    </div>
                </template>
            </v-client-table>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <loader class="absolute" v-if="loaders.calls"></loader>
            </div>
        </div>
        <select-nurse-modal ref="selectNurseModal" :selected-patients="selectedPatients"></select-nurse-modal>
        <select-times-modal ref="selectTimesModal" :selected-patients="selectedPatientsNew"></select-times-modal>
        <add-call-v2-modal ref="addCallV2Modal"></add-call-v2-modal>
        <unscheduled-patients-modal ref="unscheduledPatientsModal"></unscheduled-patients-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import {Event} from 'vue-tables-2'
    import {CancelToken} from 'axios'
    import TextEditable from './comps/text-editable'
    import DateEditable from './comps/date-editable'
    import SelectEditable from './comps/select-editable'
    import TimeEditable from './comps/time-editable'
    import Modal from '../common/modal'
    import AddCallV2Modal from './comps/modals/add-call-v2.modal'
    import SelectNurseModal from './comps/modals/select-nurse.modal'
    import SelectTimesModel from './comps/modals/select-times.modal'
    import UnscheduledPatientsModal from './comps/modals/unscheduled-patients.modal'
    import BindAppEvents from './app.events'
    import {DayOfWeek, ShortDayOfWeek} from '../helpers/day-of-week'
    import Loader from '../../components/loader'
    import VueCache from '../../util/vue-cache'
    import {today} from '../../util/today'
    import * as callUpdateFunctions from './utils/call-update.fn'
    import timeDisplay from '../../util/time-display'

    import {library} from '@fortawesome/fontawesome-svg-core'
    import {faHandPointUp, faCalendarCheck} from '@fortawesome/free-solid-svg-icons'
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'

    library.add(faHandPointUp);
    library.add(faCalendarCheck);

    const editCallDateTimeMessageForCall = "Warning: The selected call has been manually set by a Care Coach. Rescheduling may frustrate the patient expecting the call. Are you sure you want to reschedule this call?\nNote: Be sure to check with $CARE_COACH$ if you must reschedule this call.";
    const editCallDateTimeMessageForCalls = "Warning: The selected calls have at least one of them manually set by a Care Coach. Rescheduling may frustrate the patients expecting the calls. Are you sure you want to reschedule these calls?\nNote: Be sure to check with the Care Coaches if you must reschedule these calls.";

    const CALL_MUST_OVERRIDE_STATUS_CODE = 418;
    const CALL_MUST_OVERRIDE_WARNING = "The family members of this patient have a call scheduled at different time. Please confirm you still want to schedule this call.";

    export default {
        name: 'CallMgmtAppV2',
        mixins: [VueCache],
        components: {
            'text-editable': TextEditable,
            'date-editable': DateEditable,
            'select-editable': SelectEditable,
            'time-editable': TimeEditable,
            'modal': Modal,
            'add-call-v2-modal': AddCallV2Modal,
            'select-nurse-modal': SelectNurseModal,
            'select-times-modal': SelectTimesModel,
            'unscheduled-patients-modal': UnscheduledPatientsModal,
            'loader': Loader,
            'font-awesome-icon': FontAwesomeIcon
        },
        data() {
            return {
                pagination: null,
                selected: false,
                columns: ['selected', 'Manual', 'Nurse', 'Patient ID', 'Next Call', 'Last Call', 'CCM Time', 'BHI Time', 'Successful Calls', 'Practice', 'Call Time Start', 'Call Time End', 'Preferred Call Days', 'Scheduler'],
                tableData: [],
                nurses: [],
                loaders: {
                    nurses: false,
                    calls: false
                },
                currentDate: new Date(),
                tokens: {
                    calls: null
                },
                showOnlyUnassigned: false
            }
        },
        computed: {
            itemsAreSelected() {
                return !!this.tableData.find(row => !!row.selected)
            },
            selectedPatients() {
                return this.tableData.filter(row => row.selected && row.Patient).map(row => ({
                    id: row['Patient ID'],
                    callId: row.id,
                    name: row.Patient,
                    nurse: {
                        id: row.NurseId,
                        name: row.Nurse
                    },
                    nextCall: row['Next Call'],
                    callTimeStart: row['Call Time Start'],
                    callTimeEnd: row['Call Time End'],
                    loaders: row.loaders
                }))
            },
            selectedPatientsNew() {
                return this.tableData.filter(row => row.selected && row.Patient);
            },
            options() {
                return {
                    columnsClasses: {
                        'selected': 'blank'
                    },
                    sortable: ['Manual', 'Nurse', 'Patient ID', 'Next Call', 'Last Call', 'CCM Time', 'BHI Time', 'Practice', 'Scheduler'],
                    filterable: ['Nurse', 'Patient ID', 'Next Call', 'Last Call', 'Practice'],
                    filterByColumn: true,
                    texts: {
                        count: `Showing {from} to {to} of ${((this.pagination || {}).total || 0)} records|${((this.pagination || {}).total || 0)} records|One record`
                    },
                    perPage: 100,
                    perPageValues: [
                        10, 25, 50, 100, 150, 200
                    ]
                }
            },
            nursesForSelect() {
                return this.nurses.filter(n => !!n.display_name).map(nurse => ({
                    text: nurse.display_name,
                    value: nurse.id
                }))
            }
        },
        methods: {
            rootUrl,
            changeShowOnlyUnassigned(e) {
                return this.activateFilters()
            },
            columnMapping(name) {
                const columns = {
                    'Manual': 'is_manual',
                    'Nurse': 'nurse',
                    'Patient': 'patient',
                    'Patient ID': 'patient_id',
                    'Next Call': 'scheduled_date',
                    'Last Call': 'last_call',
                    'CCM Time': 'ccm_time',
                    'BHI Time': 'bhi_time',
                    'Successful Calls': 'no_of_successful_calls',
                    'Practice': 'practice',
                    'Call Time Start': 'call_time_start',
                    'Call Time End': 'call_time_end',
                    'Preferred Call Days': 'preferred_call_days',
                    'Patient Status': 'patient_status',
                    'Billing Provider': 'billing_provider',
                    'Scheduler': 'scheduler'
                };
                return columns[name] ?
                    columns[name] :
                    (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '');
            },
            clearFilters() {
                Object.keys(this.$refs.tblCalls.query).forEach((key) => {
                    const obj = {}
                    obj[key] = ''
                    this.$refs.tblCalls.setFilter(obj)
                })
                this.$refs.tblCalls.setOrder()
                this.activateFilters()
            },
            getFilters() {
                return this.$refs.tblCalls.query || {}
            },
            exportExcel() {
                const url = rootUrl(`admin/reports/call?excel${this.urlFilterSuffix()}`)
                console.log('calls:excel', url)
                document.location.href = url
            },
            today,
            urlFilterSuffix() {
                const $table = this.$refs.tblCalls
                if ($table && $table.$data) {
                    const query = $table.$data.query
                    const filters = Object.keys(query).map(key => ({
                        key,
                        value: query[key]
                    })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                    const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''
                    const unassigned = this.showOnlyUnassigned ? `&unassigned` : ''
                    console.log('sort:column', sortColumn)
                    return `${filters}${sortColumn}${unassigned}`
                }
                return ''
            },
            nextPageUrl() {
                const rowsFilterSuffix = this.$refs.tblCalls.limit ? `rows=${this.$refs.tblCalls.limit}` : ''
                if (this.pagination) {
                    return rootUrl(`api/admin/calls-v2?page=${this.$refs.tblCalls.page}&${rowsFilterSuffix}${this.urlFilterSuffix()}`)
                }
                else {
                    return rootUrl(`api/admin/calls-v2?${rowsFilterSuffix}${this.urlFilterSuffix()}`)
                }
            },
            activateFilters() {
                this.pagination = null
                this.tableData = [];
                (this.$refs.tblCalls.setPage || (() => ({})))(1)
                this.clearSelected()
                return this.next()
            },
            toggleAllSelect(e) {
                const $elem = this.$refs.tblCalls
                const filteredData = ($elem.filteredData || [])
                const fiteredDataIDs = filteredData.map(row => row.id)
                this.tableData = this.tableData.map(row => {
                    if (fiteredDataIDs.indexOf(row.id) >= 0) row.selected = this.selected;
                    return row;
                })
            },
            toggleSelect(id) {
                const row = this.tableData.find(row => row.id === id)
                if (row) {
                    row.selected = !row.selected
                }
            },
            deleteSelected(e, overrideConfirmation = false) {
                const count = this.tableData.filter(row => !!row.selected).length;
                if (count) {
                    if (overrideConfirmation || confirm(`Are you sure you want to delete the ${count} selected item${count > 1 ? 's' : ''}?`)) {
                        //perform delete action
                        return this.axios.delete(rootUrl(`api/admin/calls/${this.tableData.filter(row => !!row.selected).map(row => row.id).join(',')}`)).then(response => {
                            console.log('calls:delete', response.data)
                            response.data.forEach(id => {
                                this.tableData.splice(this.tableData.findIndex(row => row.id == id), 1)
                            })
                            this.activateFilters()
                            return response.data
                        }).catch(err => {
                            console.error('calls:delete', err)
                        })
                    }
                    else return Promise.reject('no confirmation')
                }
                else return Promise.reject('no selected items')
            },
            clearSelected() {
                this.selected = false
                this.toggleAllSelect()
            },
            assignSelectedToNurse() {
                Event.$emit('modal-select-nurse:show')
            },
            assignTimesForSelected() {
                const selectedCalls = this.tableData.filter(x => x.selected);
                const manualCalls = selectedCalls.filter(x => x['Manual']);
                let showModal = false;
                if (manualCalls.length === 0) {
                    showModal = true;
                }
                else if (selectedCalls.length === 1 && manualCalls.length === 1) {
                    if (confirm(this.getEditDateTimeConfirmMessage(manualCalls[0]))) {
                        showModal = true;
                    }
                }
                else if (confirm(editCallDateTimeMessageForCalls)) {
                    showModal = true;
                }

                if (showModal) {
                    Event.$emit('modal-select-times:show');
                }
            },
            addCall() {
                Event.$emit("modal-add-call-v2:show")
            },
            showUnscheduledPatientsModal() {
                Event.$emit('modal-unscheduled-patients:show')
            },
            getNurses() {
                this.loaders.nurses = true
                return this.axios.get(rootUrl('api/nurses?compressed')).then(response => {
                    const pagination = (response || {}).data
                    this.nurses = ((pagination || {}).data || []).filter(nurse => nurse.practices).map(nurse => {
                        return {
                            id: nurse.user_id,
                            nurseId: nurse.id,
                            display_name: ((nurse.user || {}).display_name || ''),
                            states: nurse.states,
                            practiceId: (nurse.user || {}).program_id,
                            practices: (nurse.practices || [])
                        }
                    })
                    //console.log('calls:nurses', pagination)
                    this.loaders.nurses = false
                    return this.nurses
                }).catch(err => {
                    console.error('calls:nurses', err)
                    this.loaders.nurses = false
                })
            },

            getEditDateTimeConfirmMessage(call) {
                if (call['Manual']) {
                    return editCallDateTimeMessageForCall.replace('$CARE_COACH$', call['Scheduler']);
                }
                else {
                    return undefined;
                }
            },

            isBhiEligible(id) {
                const row = this.tableData.find(row => row.id === id)
                return row && row.isBhiEligible;
            },

            isCcmEligible(id) {
                const row = this.tableData.find(row => row.id === id)
                return row && row.isCcmEligible;
            },

            showOverrideConfirmationIfNeeded: (err, successCallback) => {
                if (err && err.response
                    && err.response.status
                    && err.response.status === CALL_MUST_OVERRIDE_STATUS_CODE
                    && confirm(CALL_MUST_OVERRIDE_WARNING)) {

                    successCallback();
                }
            },

            setupCallNew(call) {
                const $vm = this;

                return ({
                    id: call.id,
                    selected: false,
                    isBhiEligible: call.is_bhi,
                    isCcmEligible: call.is_ccm,
                    Manual: call.is_manual,
                    Nurse: call.nurse,
                    NurseId: call.nurse_id,
                    Patient: call.patient,
                    Practice: call.practice,
                    Scheduler: call.scheduler,
                    'Last Call': call.last_call,
                    'CCM Time': timeDisplay(call.ccm_time),
                    'BHI Time': timeDisplay(call.bhi_time),
                    'Successful Calls': call.no_of_successful_calls,
                    'Preferred Call Days': call.preferred_call_days,
                    'Patient ID': call.patient_id,
                    notesLink: rootUrl(`manage-patients/${call.patient_id}/notes`),
                    'Next Call': call.scheduled_date,
                    'Call Time Start': call.call_time_start,
                    'Call Time End': call.call_time_end,
                    practiceId: call.practice_id,
                    nurses() {
                        return [...$vm.nurses.filter(Boolean)
                            .filter(nurse => nurse.practices.includes(call.practice_id))
                            .filter(n => !!n.display_name)
                            .map(nurse => ({text: nurse.display_name, value: nurse.id, nurse})), {
                            text: 'unassigned',
                            value: null
                        }]
                    },
                    loaders: {
                        nextCall: false,
                        nurse: false,
                        callTimeStart: false,
                        callTimeEnd: false
                    },
                    onNextCallUpdate: function (date, moment, old, revertCallback) {
                        callUpdateFunctions.onNextCallUpdate(this, date, false, old, revertCallback)
                            .catch(err =>
                                $vm.showOverrideConfirmationIfNeeded(
                                    err,
                                    () => callUpdateFunctions.onNextCallUpdate(this, date, true, old, revertCallback)
                                )
                            );
                    },
                    onNurseUpdate: function (nurseId, old, revertCallback) {
                        callUpdateFunctions.onNurseUpdate(this, nurseId, false, old, revertCallback)
                            .catch(err =>
                                $vm.showOverrideConfirmationIfNeeded(
                                    err,
                                    () => callUpdateFunctions.onNurseUpdate(this, nurseId, true, old, revertCallback)
                                )
                            );
                    },
                    onCallTimeStartUpdate: function (time, old, revertCallback) {
                        callUpdateFunctions.onCallTimeStartUpdate(this, time, false, old, revertCallback)
                            .catch(err =>
                                $vm.showOverrideConfirmationIfNeeded(
                                    err,
                                    () => callUpdateFunctions.onCallTimeStartUpdate(this, time, true, old, revertCallback)
                                )
                            );
                    },
                    onCallTimeEndUpdate: function (time, old, revertCallback) {
                        callUpdateFunctions.onCallTimeEndUpdate(this, time, false, old, revertCallback)
                            .catch(err =>
                                $vm.showOverrideConfirmationIfNeeded(
                                    err,
                                    () => callUpdateFunctions.onCallTimeEndUpdate(this, time, true, old, revertCallback)
                                )
                            );
                    },
                    updateMultiValues: function (obj, old, revertCallback) {
                        //need to return the promise, because its used in app.events.js: selectTimesChangeHandler
                        return callUpdateFunctions.updateMultiValues(this, obj, false, old, revertCallback)
                            .catch(err =>
                                $vm.showOverrideConfirmationIfNeeded(
                                    err,
                                    () => callUpdateFunctions.updateMultiValues(this, obj, true, old, revertCallback)
                                )
                            );
                    }
                });


            },
            next() {
                const $vm = this;
                $vm.loaders.calls = true;
                return this.axios
                    .get(this.nextPageUrl(), {
                        cancelToken: new CancelToken((c) => {
                            if ($vm.tokens.calls) {
                                $vm.tokens.calls()
                            }
                            $vm.tokens.calls = c
                        })
                    })
                    .then(result => {
                        //console.log('calls:response', this.nextPageUrl())
                        result = result.data;
                        $vm.pagination = {
                            current_page: result.meta.current_page,
                            from: result.meta.from,
                            last_page: result.meta.last_page,
                            last_page_url: result.links.last,
                            next_page_url: result.links.next,
                            path: result.meta.path,
                            per_page: result.meta.per_page,
                            to: result.meta.to,
                            total: result.meta.total
                        };

                        const calls = result.data || [];
                        const tableCalls = calls.map($vm.setupCallNew);
                        if (!$vm.tableData.length) {
                            const arr = $vm.tableData.concat(tableCalls)
                            const total = (($vm.pagination || {}).total || 0)
                            $vm.tableData = [...arr, ...'0'.repeat(total - arr.length).split('').map((item, index) => ({
                                id: arr.length + index + 1,
                                nurses() {
                                    return ([])
                                },
                                onNurseUpdate() {
                                },
                                onAttemptNoteUpdate() {
                                },
                                onGeneralCommentUpdate() {
                                },
                                onCallTimeStartUpdate() {
                                },
                                onCallTimeEndUpdate() {
                                },
                                onNextCallUpdate() {
                                },
                                loaders: {}
                            }))]
                        }
                        else {
                            const from = (($vm.pagination || {}).from || 0);
                            const to = (($vm.pagination || {}).to || 0);
                            for (let i = from - 1; i < to; i++) {
                                $vm.tableData[i] = tableCalls[i - from + 1]
                            }
                        }
                        setTimeout(() => {
                            $vm.$refs.tblCalls.count = $vm.pagination.total;
                            $vm.loaders.calls = false
                        }, 1000);
                        return tableCalls;
                    })
                    .catch((err) => {
                        console.error('calls:response', err)
                        $vm.loaders.calls = false
                    })
            }
        },
        mounted() {
            BindAppEvents(this, Event);

            return Promise.all([this.next(), this.getNurses()])
        }
    }
</script>

<style>
    .VueTables__child-row-toggler {
        display: block;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        cursor: pointer;
        text-align: center;
        background-color: #008cba;
        color: white;
    }

    .VueTables__child-row-toggler::before {
        content: "➡";
    }

    .VueTables__child-row-toggler.VueTables__child-row-toggler--open::before {
        content: "⬇";
    }

    .row-select {
        font-size: 20px;
    }

    .row-info ul {
        margin-left: -23px;
    }

    .row-info li {
        margin: 5px 0px;
    }

    .blue {
        color: #008cba;
    }

    tr.VueTables__filters-row input {
        font-size: 12px;
        height: 22px;
    }

    .big-text-edit button {
        font-size: 25px;
        float: left;
    }

    div.loader.relative {
        position: relative;
        left: 0px;
    }

    .table-bordered > tbody > tr > td {
        white-space: nowrap;
    }

    .disabled {
        color: #cacaca;
    }
</style>