<template>
  <div>
    <div class="row">
      <div class="col-sm-6">
        <a class="btn btn-primary btn-xs" :href="rootUrl('admin/reports/call')">Export Records</a>
        <button class="btn btn-success btn-xs" @click="addCall">Add Call</button>
      </div>
      <div class="col-sm-6 text-right" v-if="itemsAreSelected">
        <button class="btn btn-primary btn-xs" @click="assignSelectedToNurse">Assign To Nurse</button>
        <button class="btn btn-danger btn-xs" @click="deleteSelected">Delete</button>
      </div>
    </div>
    <v-client-table ref="tblCalls" :data="tableData" :columns="columns" :options="options">
      <template slot="child_row" scope="props">
        <div class="row row-info">
          <div class="col-sm-12">
            <div class="row">
              <div class="col-lg-2">
                General Comment:
              </div>
              <div class="col-lg-10">
                <text-editable :value="props.row.Comment" :multi="true" :class-name="'blue big-text-edit'"></text-editable>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">Attempt Note:</div>
              <div class="col-lg-10">
                <text-editable :value="props.row.AttemptNote || 'Add Text'" :multi="true" :class-name="'blue big-text-edit'"></text-editable>
              </div>
            </div>
            <div class="row" v-if="props.row.Notes.length > 0">
              <div class="col-lg-2"><a :href="rootUrl('manage-patients/' + props.row['Patient ID'] + '/notes')" target="_blank">Last 3 Notes:</a></div>
              <div class="col-lg-10">
                <ul>
                  <li v-for="(note, index) in props.row.Notes.slice(0, 3)" :key="index">
                    Note {{note.created_at}}: 
                    <div class="label label-info" :class="{ inbound: note.type === 'in', outbound: note.type === 'out' }" style="margin:5px;">{{note.type === 'in' ? 'In' : 'Out'}} Call</div>
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
      <template slot="Nurse" scope="props">
        <select-editable :value="props.row.Nurse" :values="[
                                    'Nurse N RN', 
                                    'Kathryn Alchalabi RN', 
                                    'Patricia Koeppel RN', 
                                    'Dillenis Diaz RN', 
                                    'Liza Herrera RN', 
                                    'Monique Potter RN'
                                  ]" :class-name="'blue'"></select-editable>
      </template>
      <template slot="Next Call" scope="props">
        <date-editable :value="props.row['Next Call']" :format="'YYYY-mm-DD'" :class-name="'blue'"></date-editable>
      </template>
      <template slot="Call Time Start" scope="props">
        <time-editable :value="props.row['Call Time Start']" :format="'YYYY-mm-DD'" :class-name="'blue'"></time-editable>
      </template>
      <template slot="Call Time End" scope="props">
        <time-editable :value="props.row['Call Time End']" :format="'YYYY-mm-DD'" :class-name="'blue'"></time-editable>
      </template>
    </v-client-table>
    <text-editable :value="'Mykeels'"></text-editable>
    <date-editable :value="'01-20-2017'" :format="'mm-DD-YYYY'"></date-editable>
    <select-editable :values="['One', 'Two', 'Three']"></select-editable>
    <select-nurse-modal></select-nurse-modal>
    <add-call-modal></add-call-modal>
  </div>
</template>

<script>
  import { rootUrl } from '../../app.config.js'
  import { Event } from 'vue-tables-2'
  import TextEditable from './comps/text-editable'
  import DateEditable from './comps/date-editable'
  import SelectEditable from './comps/select-editable'
  import TimeEditable from './comps/time-editable'
  import Modal from './comps/modal'
  import AddCallModal from './comps/modals/add-call.modal'
  import SelectNurseModal from './comps/modals/select-nurse.modal'
  import BindAppEvents from './app.events'
  import { DayOfWeek, ShortDayOfWeek } from './helpers/day-of-week'

  export default {
      name: 'CallMgmtApp',
      components: {
        'text-editable': TextEditable,
        'date-editable': DateEditable,
        'select-editable': SelectEditable,
        'time-editable': TimeEditable,
        'modal': Modal,
        'add-call-modal': AddCallModal,
        'select-nurse-modal': SelectNurseModal
      },
      data() {
        return {
          page: 1,
          selected: false,
          columns: ['selected', 'Nurse','Patient ID', 'Patient','Next Call', 'Last Call Status', 'Last Call', 'CCM Time', 'Successful Calls', 'Time Zone', 'Call Time Start', 'Call Time End', 'Preferred Call Days', 'Patient Status', 'Practice', 'Billing Provider', 'DOB', 'Scheduler'],
          tableData: [],
          options: {
          // see the options API
            columnsClasses: {
              'selected': 'blank'
            },
            sortable: ['Nurse','Patient ID', 'Patient','Next Call', 'Last Call Status', 'Last Call', 'CCM Time', 'Successful Calls', 'Time Zone', 'Call Time Start', 'Call Time End', 'Preferred Call Days', 'Patient Status', 'Practice', 'Billing Provider', 'DOB', 'Scheduler'],
            filterable: ['Nurse','Patient ID', 'Patient','Next Call', 'Last Call Status', 'Last Call', 'CCM Time', 'Successful Calls', 'Time Zone', 'Call Time Start', 'Call Time End', 'Preferred Call Days', 'Patient Status', 'Practice', 'Billing Provider', 'DOB', 'Scheduler'],
            filterByColumn: true,
            footerHeadings: true
          },
          currentDate: new Date()
        }
      },
      computed: {
        itemsAreSelected() {
          return !!this.tableData.find(row => !!row.selected)
        }
      },
      methods: {
        rootUrl,
        toggleAllSelect(e) {
          this.tableData = this.tableData.map(row => {
            row.selected = this.selected;
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
              }
            }
          }
        },
        assignSelectedToNurse() {
          Event.$emit('modal-select-nurse:show')
        },
        addCall() {
          Event.$emit("modal-add-call:show")
        },
        next() {
          if (!this.$nextPromise) {
            return this.$nextPromise = this.$http.get(rootUrl('api/admin/calls?page=' + this.page)).then((result) => result.data).then(result => {
              if (result) {
                const calls = result.data;
                if (calls && Array.isArray(calls)) {
                  calls.forEach(call => {
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
                                                    null);
                    
                    const patient = call.getPatient();
                    if (patient) {
                      const emptyObject = {}
                      patient.getBillingProvider = () => ((patient.billing_provider || [])[0] || emptyObject);
                      patient.getPractice = () => (patient.primary_practice || {});
                      patient.getInfo = () => (patient.patient_info || {});

                      const billingProvider = patient.getBillingProvider();
                      billingProvider.getUser = () => (billingProvider.user || {});

                      patient.getInfo().contact_windows.forEach(time_window => {
                        time_window.dayOfWeek = DayOfWeek[time_window.day_of_week];
                        time_window.shortDayOfWeek = ShortDayOfWeek(time_window.day_of_week);
                      })
                    }
                  })
                  const tableCalls = calls.map(call => ({
                                        id: call.id,
                                        selected: false,
                                        Nurse: (call.getNurse() || {}).full_name,
                                        Patient: (call.getPatient() || {}).full_name,
                                        Practice: (call.getPatient() || {}).getPractice().display_name,
                                        Scheduler: call.scheduler,
                                        CallWindows: call.getPatient().getInfo().contact_windows,
                                        Comment: call.getPatient().getInfo().general_comment,
                                        AttemptNote: call.attempt_note,
                                        Notes: [{
                                          created_at: (new Date()).toDateString(),
                                          type: 'in',
                                          category: 'Morning Checkup',
                                          message: 'Demo: The Patient is responding to treatment'
                                        }],
                                        'Last Call Status': call.getPatient().getInfo().last_call_status,
                                        'Last Call': new Date(call.getPatient().getInfo().last_contact_time).toDateString(),
                                        'CCM Time': call.getPatient().getInfo().cur_month_activity_time,
                                        'Successful Calls': (call.getPatient().getInfo().monthly_summaries.slice(-1).no_of_successful_calls || 0),
                                        'Time Zone': call.getPatient().timezone,
                                        'Preferred Call Days': Object.values(call.getPatient().getInfo().contact_windows
                                                                                        .map(time_window => time_window.shortDayOfWeek)
                                                                                        .reduce((obj, key) => {
                                                                                          obj[key] = key;
                                                                                          return obj;
                                                                                        }, {})).join(','),
                                        'Patient Status': call.getPatient().getInfo().ccm_status,
                                        'DOB': call.getPatient().getInfo().birth_date,
                                        'Billing Provider': call.getPatient().getBillingProvider().getUser().display_name,
                                        'Patient ID': call.getPatient().id,
                                        'Next Call': call.scheduled_date,
                                        'Call Time Start': call.window_start,
                                        'Call Time End': call.window_end
                                      }))
                  this.tableData = this.tableData.concat(tableCalls)
                  this.page++;
                  delete this.$nextPromise;
                  console.log(calls);
                  console.log(this.$refs);
                  return tableCalls;
                }
              }
            })
          }
        }
      },
      mounted() {
        BindAppEvents(this, Event);
        this.next();
      }
  }
</script>

<style>
  .VueTables__child-row-toggler {
    display: block;
    width: 20px;
    height: 20px;
    border: 1px solid #AAA;
    border-radius: 50%;
    cursor: pointer;
  }

  .VueTables__child-row-toggler.VueTables__child-row-toggler--open {
    border-color: #0AF;
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
</style>