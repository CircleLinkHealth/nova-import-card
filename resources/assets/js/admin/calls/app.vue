<template>
  <div>
    <div class="row">
      <div class="col-sm-6">
        <a class="btn btn-primary btn-xs" @click="exportExcel">Export Records</a>
        <button class="btn btn-success btn-xs" @click="addCall">Add Call</button>
        <button class="btn btn-warning btn-xs" @click="showUnscheduledPatientsModal">Unscheduled Patients</button>
        <button class="btn btn-info btn-xs" @click="clearFilters">Clear Filters</button>
        <label class="btn btn-gray btn-xs">
          <input type="checkbox" v-model="showOnlyUnassigned" @change="changeShowOnlyUnassigned" />
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
        <template slot="child_row" scope="props">
          <div class="row row-info">
            <div class="col-sm-12">
              <div class="row">
                <div class="col-lg-2">
                  General Comment:
                </div>
                <div class="col-lg-10">
                  <text-editable :value="props.row.Comment" :multi="true" :class-name="'blue big-text-edit'" :on-change="props.row.onGeneralCommentUpdate.bind(props.row)"></text-editable>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-2">Attempt Note:</div>
                <div class="col-lg-10">
                  <text-editable :value="props.row.AttemptNote || 'Add Text'" :multi="true" :class-name="'blue big-text-edit'" :on-change="props.row.onAttemptNoteUpdate.bind(props.row)"></text-editable>
                </div>
              </div>
              <div class="row" v-if="props.row.Notes.length > 0">
                <div class="col-lg-2"><a :href="rootUrl('manage-patients/' + props.row['Patient ID'] + '/notes')" target="_blank">Last 3 Notes:</a></div>
                <div class="col-lg-10">
                  <ul>
                    <li v-for="(note, index) in props.row.Notes.slice(0, 3)" :key="index">
                      Note {{note.created_at}}: 
                      <div class="label label-info" :class="{ inbound: note.type === 'in', outbound: note.type === 'out' }" style="margin:5px;">{{note.type === 'in' ? 'In' : 'Out'}}bound Call</div>
                      <span style="font-weight:bold;">{{note.category}}</span> 
                      {{note.message}}
                      </li>
                  </ul>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-2">Call Windows:</div>
                <div class="col-sm-10">
                  <ul class="info-list">
                    <li v-for="(time_window, index) in props.row.CallWindows" :key="index">{{time_window.shortDayOfWeek}}: {{time_window.window_time_start}} - {{time_window.window_time_end}}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </template>
        <template slot="selected" scope="props">
          <input class="row-select" v-model="props.row.selected" @change="toggleSelect(props.row.id)" type="checkbox" />
        </template>
        <template slot="h__selected" scope="props">
          <input class="row-select" v-model="selected" @change="toggleAllSelect" type="checkbox" />
        </template>
        <template slot="Patient ID" scope="props">
          <a :href="props.row.notesLink">{{ props.row['Patient ID'] }}</a>
        </template>
        <template slot="Nurse" scope="props">
          <select-editable :value="props.row.NurseId" :display-text="props.row.Nurse" :values="props.row.nurses()" :class-name="'blue'" :on-change="props.row.onNurseUpdate.bind(props.row)"></select-editable>
        </template>
        <template slot="Next Call" scope="props">
          <div>
            <date-editable :value="props.row['Next Call']" :format="'YYYY-mm-DD'" :class-name="'blue'" :on-change="props.row.onNextCallUpdate.bind(props.row)"></date-editable>
            <loader class="relative" v-if="props.row.loaders.nextCall"></loader>
          </div>
        </template>
        <template slot="Call Time Start" scope="props">
          <div>
            <time-editable :value="props.row['Call Time Start']" :format="'YYYY-mm-DD'" :class-name="'blue'" :on-change="props.row.onCallTimeStartUpdate.bind(props.row)"></time-editable>
            <loader class="relative" v-if="props.row.loaders.callTimeStart"></loader>
          </div>
        </template>
        <template slot="Call Time End" scope="props">
          <div>
            <time-editable :value="props.row['Call Time End']" :format="'YYYY-mm-DD'" :class-name="'blue'" :on-change="props.row.onCallTimeEndUpdate.bind(props.row)"></time-editable>
            <loader class="relative" v-if="props.row.loaders.callTimeEnd"></loader>
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
    <select-times-modal ref="selectTimesModal" :selected-patients="selectedPatients"></select-times-modal>
    <add-call-modal ref="addCallModal"></add-call-modal>
    <unscheduled-patients-modal ref="unscheduledPatientsModal"></unscheduled-patients-modal>
  </div>
</template>

<script>
  import { rootUrl } from '../../app.config.js'
  import { Event } from 'vue-tables-2'
  import TextEditable from './comps/text-editable'
  import DateEditable from './comps/date-editable'
  import SelectEditable from './comps/select-editable'
  import TimeEditable from './comps/time-editable'
  import Modal from '../common/modal'
  import AddCallModal from './comps/modals/add-call.modal'
  import SelectNurseModal from './comps/modals/select-nurse.modal'
  import SelectTimesModel from './comps/modals/select-times.modal'
  import UnscheduledPatientsModal from './comps/modals/unscheduled-patients.modal'
  import BindAppEvents from './app.events'
  import { DayOfWeek, ShortDayOfWeek } from '../helpers/day-of-week'
  import Loader from '../../components/loader'
  import VueCache from '../../util/vue-cache'
  import { onNextCallUpdate, onNurseUpdate, onCallTimeStartUpdate, onCallTimeEndUpdate, onGeneralCommentUpdate, onAttemptNoteUpdate, updateMultiValues } from './utils/call-update.fn'
  import timeDisplay from '../../util/time-display'

  export default {
      name: 'CallMgmtApp',
      mixins: [ VueCache ],
      components: {
        'text-editable': TextEditable,
        'date-editable': DateEditable,
        'select-editable': SelectEditable,
        'time-editable': TimeEditable,
        'modal': Modal,
        'add-call-modal': AddCallModal,
        'select-nurse-modal': SelectNurseModal,
        'select-times-modal': SelectTimesModel,
        'unscheduled-patients-modal': UnscheduledPatientsModal,
        'loader': Loader
      },
      data() {
        return {
          pagination: null,
          selected: false,
          columns: ['selected', 'Nurse', 'Patient ID', 'Patient', 'Next Call', 'Last Call Status', 'Last Call', 'CCM Time', 'Successful Calls', 'Practice', 'Call Time Start', 'Call Time End', 'Time Zone', 'Preferred Call Days', 'Patient Status', 'Billing Provider', 'DOB', 'Scheduler'],
          tableData: [],
          nurses: [],
          loaders: {
            nurses: false,
            calls: false
          },
          currentDate: new Date(),
          $nextPromise: null,
          requests: {
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
        options () {
          return {
            columnsClasses: {
              'selected': 'blank'
            },
            sortable: ['Nurse','Patient ID', 'Patient','Next Call', 'Last Call', 'Last Call Status', 'CCM Time', 'Call Time Start', 'Call Time End', 'Preferred Call Days', 'Patient Status', 'Practice', 'Scheduler'],
            filterable: ['Nurse','Patient ID', 'Patient','Next Call', 'Last Call', 'Patient Status', 'Practice', 'Billing Provider', 'Scheduler'],
            filterByColumn: true,
            texts: {
                count: `Showing {from} to {to} of ${((this.pagination || {}).total || 0)} records|${((this.pagination || {}).total || 0)} records|One record`
            },
            perPage: 100,
            perPageValues: [
              10, 25, 50, 100, 150, 200
            ],
            customSorting: {
              Nurse: (ascending) => (a, b) => 0,
              'Patient ID': (ascending) => (a, b) => 0,
              Patient: (ascending) => (a, b) => 0,
              'Next Call': (ascending) => (a, b) => 0,
              'Last Call Status': (ascending) => (a, b) => 0,
              'Last Call': (ascending) => (a, b) => 0,
              'CCM Time': (ascending) => (a, b) => 0,
              'Call Time Start': (ascending) => (a, b) => 0,
              'Call Time End': (ascending) => (a, b) => 0,
              'Patient Status': (ascending) => (a, b) => 0,
              Practice: (ascending) => (a, b) => 0,
              'Billing Provider': (ascending) => (a, b) => 0,
              'Last Call Status': (ascending) => (a, b) => 0,
              'Preferred Call Days': (ascending) => (a, b) => 0,
              Scheduler: (ascending) => (a, b) => 0
            }
          }
        },
        nursesForSelect() {
          return this.nurses.filter(n => !!n.display_name).map(nurse => ({ text: nurse.display_name, value: nurse.id }))
        }
      },
      methods: {
        rootUrl,
        changeShowOnlyUnassigned (e) {
          return this.activateFilters()
        },
        columnMapping (name) {
          const columns = {
            'Patient ID': 'patientId',
            'Next Call': 'scheduledDate',
            'Last Call': 'lastCall',
            'CCM Time': 'ccmTime'
          }
          //to camel case
          return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
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
        exportExcel() {
          const url = rootUrl(`admin/reports/call?excel${this.urlFilterSuffix()}`)
          console.log('calls:excel', url)
          document.location.href = url
        },
        today() {
          const d = new Date()
          return `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`
        },
        urlFilterSuffix() {
            const $table = this.$refs.tblCalls
            const query = $table.$data.query
            const filters = Object.keys(query).map(key => ({ key, value: query[key] })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${item.value}`).join('')
            const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''
            const unassigned = this.showOnlyUnassigned ? `&unassigned` : ''
            console.log('sort:column', sortColumn)
            return `${filters}${sortColumn}${unassigned}`
        },
        nextPageUrl () {
            if (this.pagination) {
                return rootUrl(`api/admin/calls?scheduled&page=${this.$refs.tblCalls.page}&rows=${this.$refs.tblCalls.limit}${this.urlFilterSuffix()}&minScheduledDate=${this.today()}`)
            }
            else {
                return rootUrl(`api/admin/calls?scheduled&rows=${this.$refs.tblCalls.limit}${this.urlFilterSuffix()}&minScheduledDate=${this.today()}`)
            }
        },
        activateFilters () {
            this.pagination = null
            this.tableData = []
            this.$refs.tblCalls.setPage(1)
            return this.next()
        },
        toggleAllSelect(e) {
          const $elem = this.$refs.tblCalls
          const filteredData = $elem.filteredData
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
        deleteSelected() {
          if (window) {
            const count = this.tableData.filter(row => !!row.selected).length;
            if (count) {
              if (confirm(`Are you sure you want to delete the ${count} selected item${count > 1 ? 's' : ''}?`)) {
                //perform delete action
                this.axios.delete(rootUrl(`api/admin/calls/${this.tableData.filter(row => !!row.selected).map(row => row.id).join(',')}`)).then(response => {
                  console.log('calls:delete', response.data)
                  response.data.forEach(id => {
                    this.tableData.splice(this.tableData.findIndex(row => row.id == id), 1)
                  })
                  this.activateFilters()
                }).catch(err => {
                  console.error('calls:delete', err)
                })
              }
            }
          }
        },
        clearSelected() {
          this.selected = false
          this.toggleAllSelect()
        },
        assignSelectedToNurse() {
          Event.$emit('modal-select-nurse:show')
        },
        assignTimesForSelected() {
          Event.$emit('modal-select-times:show')
        },
        addCall() {
          Event.$emit("modal-add-call:show")
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
            console.log('calls:nurses', pagination)
            this.loaders.nurses = false
          }).catch(err => {
            console.error('calls:nurses', err)
            this.loaders.nurses = false
          })
        },
        setupCall (call) {
          const $vm = this
          if (call.inbound_user) call.inbound_user.id = call.inbound_cpm_id;
          if (call.outbound_user) call.outbound_user.id = call.outbound_cpm_id;                
          call.getNurse = () => ((call.inbound_user && call.inbound_user.nurse_info) ?
                                          call.inbound_user : 
                                    (call.outbound_user && call.outbound_user.nurse_info) ?
                                          call.outbound_user : 
                                          null)
          call.getPatient = () => ((call.inbound_user && call.inbound_user.patient_info) ?
                                          call.inbound_user : 
                                    (call.outbound_user && call.outbound_user.patient_info) ?
                                          call.outbound_user : 
                                          { getPractice: () => ({}), getInfo: () => ({}), getBillingProvider: () => ({ getUser: () => ({}) }) });
          
          const patient = call.getPatient();
          if (patient) {
            const emptyObject = {}
            patient.getBillingProvider = () => ((patient.billing_provider || [])[0] || { getUser: () => ({}) });
            patient.getPractice = () => (patient.primary_practice || {});
            patient.getInfo = () => (patient.patient_info || {});

            const billingProvider = patient.getBillingProvider();
            billingProvider.getUser = () => (billingProvider.user || {});

            (patient.getInfo().contact_windows || []).forEach(time_window => {
              time_window.dayOfWeek = DayOfWeek[time_window.day_of_week];
              time_window.shortDayOfWeek = ShortDayOfWeek(time_window.day_of_week);
            })
          }
          this.cache().get(rootUrl(`api/patients/${call['Patient ID']}/notes?sort_id=desc&rows=3`)).then(pagination => {
            call.Notes = ((pagination || {}).data || []).map(note => ({
                                created_at: note.created_at,
                                type: 'out',
                                category: note.type,
                                message: note.body
                              }))
          })
          return ({
                    id: call.id,
                    selected: false,
                    Nurse: (call.getNurse() || {}).full_name,
                    NurseId: (call.getNurse() || {}).id,
                    Patient: (call.getPatient() || {}).full_name,
                    Practice: (call.getPatient() || {}).getPractice().display_name,
                    Scheduler: call.scheduler,
                    CallWindows: call.getPatient().getInfo().contact_windows,
                    Comment: call.getPatient().getInfo().general_comment,
                    AttemptNote: call.attempt_note,
                    Notes: [],
                    'Last Call Status': call.getPatient().getInfo().last_call_status,
                    'Last Call': (call.getPatient().getInfo().last_contact_time || '').split(' ')[0],
                    'CCM Time': timeDisplay(call.getPatient().getInfo().cur_month_activity_time),
                    'Successful Calls': (((call.getPatient().patient_summaries || []).slice(-1)[0] || {}).no_of_successful_calls || 0),
                    'Time Zone': call.getPatient().timezone,
                    'Preferred Call Days': Object.values((call.getPatient().getInfo().contact_windows || [])
                                                                    .map(time_window => time_window.shortDayOfWeek)
                                                                    .reduce((obj, key) => {
                                                                      obj[key] = key;
                                                                      return obj;
                                                                    }, {})).join(','),
                    'Patient Status': call.getPatient().getInfo().ccm_status,
                    'DOB': call.getPatient().getInfo().birth_date,
                    'Billing Provider': call.getPatient().getBillingProvider().getUser().display_name,
                    'Patient ID': call.getPatient().id,
                    notesLink: rootUrl(`manage-patients/${call.getPatient().id}/notes`),
                    'Next Call': call.scheduled_date,
                    'Call Time Start': call.window_start,
                    'Call Time End': call.window_end,
                    state: call.getPatient().state,
                    practiceId: (call.getPatient() || {}).getPractice().id,
                    nurses () {
                      return [ ...$vm.nurses.filter(Boolean)
                                      .filter(nurse => nurse.practices.includes((call.getPatient() || {}).getPractice().id))
                                      .filter(n => !!n.display_name)
                                      .map(nurse => ({ text: nurse.display_name, value: nurse.id, nurse })), { text: 'unassigned', value: null } ]
                    },
                    loaders: {
                      nextCall: false,
                      nurse: false,
                      callTimeStart: false,
                      callTimeEnd: false
                    },
                    onNextCallUpdate, 
                    onNurseUpdate, 
                    onCallTimeStartUpdate, 
                    onCallTimeEndUpdate, 
                    onGeneralCommentUpdate, 
                    onAttemptNoteUpdate, 
                    updateMultiValues
                  });
        },
        next() {
          const $vm = this
          this.loaders.calls = true
            return this.$nextPromise = this.axios.get(this.nextPageUrl(), {
              before(request) {
                if ($vm.requests.calls) {
                  $vm.requests.calls.abort()
                }
                $vm.requests.calls = request
              }
            }).then((result) => result).then(result => {
              result = result.data;
              this.pagination = {
                            current_page: result.meta.current_page,
                            from: result.meta.from,
                            last_page: result.meta.last_page,
                            last_page_url: result.links.last,
                            next_page_url: result.links.next,
                            path: result.meta.path,
                            per_page: result.meta.per_page,
                            to: result.meta.to,
                            total: result.meta.total
                        }
              if (result) {
                const calls = result.data || [];
                if (calls && Array.isArray(calls)) {
                  const tableCalls = calls.map(this.setupCall)
                  if (!this.tableData.length) {
                      const arr = this.tableData.concat(tableCalls)
                      const total = ((this.pagination || {}).total || 0)
                      this.tableData = [ ...arr, ...'0'.repeat(total - arr.length).split('').map((item, index) => ({ 
                                                                                                                    id: arr.length + index + 1, 
                                                                                                                    nurses () { return ([]) },
                                                                                                                    onNurseUpdate() {},
                                                                                                                    onAttemptNoteUpdate() {},
                                                                                                                    onGeneralCommentUpdate() {},
                                                                                                                    onCallTimeStartUpdate() {},
                                                                                                                    onCallTimeEndUpdate() {},
                                                                                                                    onNextCallUpdate() {},
                                                                                                                    loaders: {}
                                                                                                                  })) ]
                  }
                  else {
                      const from = ((this.pagination || {}).from || 0)
                      const to = ((this.pagination || {}).to || 0)
                      for (let i = from - 1; i < to; i++) {
                          this.tableData[i] = tableCalls[i - from + 1]
                      }
                  }
                  setImmediate(() => {
                    this.$refs.tblCalls.count = this.pagination.total
                    delete this.$nextPromise;
                    this.loaders.calls = false
                  })
                  return tableCalls;
                }
              }
            }).catch(function (err) {
              console.error('calls:response', err)
              this.loaders.calls = false
            })
        }
      },
      mounted() {
        BindAppEvents(this, Event);
        this.next();
        this.getNurses();
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

  .row-info ul{
    margin-left: -23px;
  }

  .row-info li {
    margin:5px 0px;
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

  .table-bordered>tbody>tr>td {
    white-space: nowrap;
  }
</style>