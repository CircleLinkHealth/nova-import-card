<template>
    <modal name="add-call" :info="addCallModalInfo" :no-footer="true" class-name="modal-add-call">
      <template slot="title">
        <div class="row">
          <div class="col-sm-6">
            Add New Call
          </div>
          <div class="col-sm-6 text-right">
            <button class="btn btn-warning btn-xs" @click="showUnscheduledPatients">Show Unscheduled Patients</button>
          </div>
        </div>
      </template>
      <template scope="props">
        <form action="/callcreate" @submit="submitForm">
          <div class="row">
            <div class="col-sm-12">
              <div class="row form-group">
                <div class="col-sm-5">
                  Practice <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <select class="form-control" v-model="formData.practiceId" @change="changePractice" required>
                    <option :value="null">Unassigned</option>
                    <option v-for="(practice, index) in practices" :key="practice.id" :value="practice.id">{{practice.display_name}}</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  Patient <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <select class="form-control" name="inbound_cpm_id" v-model="formData.patientId" @change="checkIfSelectedPatientIsInDraftMode" required>
                    <option :value="null">Unassigned</option>
                    <option v-for="(patient, index) in patients" :key="patient.id" :value="patient.id">{{patient.name}} ({{patient.id}})</option>
                  </select>
                  <label>
                    <input type="checkbox" v-model="filters.showUnscheduledPatients" @change="getPatients"> <small>Show Only Unscheduled Patients</small>
                  </label>
                  <loader v-if="loaders.patients"></loader>
                  <div class="alert alert-warning" v-if="selectedPatientIsInDraftMode">Patient is in draft mode</div>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  Nurse <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <select class="form-control" name="outbound_cpm_id" v-model="formData.nurseId" required>
                    <option :value="null">Unassigned</option>
                    <option v-for="(nurse, index) in nursesForSelect" :key="nurse.id" :value="nurse.id">{{nurse.name}} ({{nurse.id}})</option>
                  </select>
                  <loader v-if="loaders.nurses"></loader>
                  <div class="alert alert-danger" v-if="formData.practiceId && (nursesForSelect.length == 0)">No available nurses for selected patient</div>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  Date <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <input class="form-control" type="date" name="scheduled_date" v-model="formData.date" required />
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  Start Time <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <input class="form-control" type="time" name="window_start" v-model="formData.startTime" required />
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  End Time <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <input class="form-control" type="time" name="window_end" v-model="formData.endTime" required />
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-5">
                  Add Text <span class="required">*</span>
                </div>
                <div class="col-sm-7">
                  <textarea class="form-control" name="attempt_note" v-model="formData.text" required></textarea>
                  <button class="hidden"></button>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-12">
                  <notifications ref="notificationsComponent" name="add-call-modal"></notifications>
                  <center>
                    <loader v-if="loaders.submit"></loader>
                  </center>
                </div>
              </div>
            </div>
          </div>
        </form>
      </template>
    </modal>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../components/loader'
    import { rootUrl } from '../../../../app.config'
    import moment from 'moment'
    import notifications from '../../../../components/notifications'

    const defaultFormData = {
                              practiceId: null,
                              patientId: null,
                              nurseId: null,
                              date: moment(new Date()).format('YYYY-MM-DD'),
                              startTime: '09:00',
                              endTime: '09:10',
                              text: null
                            }

    export default {
        name: 'add-call-modal',
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            notifications
        },
        data() {
            return {
                addCallModalInfo: {
                    okHandler() {
                        const form = this.$form()
                        form.querySelector('button').click()
                        this.errors().submit = null
                        console.log("okay clicked", form)
                    },
                    cancelHandler() {
                      this.errors().submit = null
                      Event.$emit("modal-add-call:hide")
                    },
                    $form: () => this.$el.querySelector('form'),
                    errors: () => this.errors
                },
                errors: {
                    practices: null,
                    patients: null,
                    submit: null
                },
                loaders: {
                    practices: false,
                    patients: false,
                    submit: false
                },
                practices: [],
                patients: [],
                nurses: [],
                formData: Object.create(defaultFormData),
                filters: {
                  showUnscheduledPatients: false
                },
                selectedPatientIsInDraftMode: false
            }
        },
        computed: {
          nursesForSelect () {
            const selectedPatient = this.selectedPatient()
            return this.nurses.filter(nurse => nurse.states.indexOf((selectedPatient).state) >= 0)
          }
        },
        methods: {
          selectedPatient () {
            return (this.patients.find(patient => patient.id === this.formData.patientId) || {})
          },
          checkIfSelectedPatientIsInDraftMode () {
            this.selectedPatientIsInDraftMode = (this.selectedPatient().status == 'draft')
          },
          getPractices() {
                this.loaders.practices = true
                this.axios.get(rootUrl(`api/practices`)).then(response => {
                    this.loaders.practices = false
                    this.practices = (response.data || []).sort((a, b) => {
                      if (a.display_name < b.display_name) return -1;
                      else if (a.display_name > b.display_name) return 1
                      else return 0
                    }).distinct(patient => patient.id)
                    console.log('add-call-get-practices', response.data)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('add-call-get-practices', err)
                })
            },
            getUnscheduledPatients() {
                if (this.formData.practiceId) {
                    this.loaders.patients = true
                    this.axios.get(rootUrl(`api/practices/${this.formData.practiceId}/patients/without-scheduled-calls`)).then(response => {
                        this.loaders.patients = false
                        const pagination = response.data
                        this.patients = ((pagination || {}).data || []).map(patient => {
                            patient.name = patient.full_name
                            return patient;
                        }).distinct(patient => patient.id)
                        console.log('add-call-get-patients', pagination)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-call-get-patients', err)
                    })
                }
            },
            getPatients() {
              return this.filters.showUnscheduledPatients ? 
                    this.getUnscheduledPatients() : this.getAllPatients();
            },
            getAllPatients() {
                if (this.formData.practiceId) {
                    this.loaders.patients = true
                    this.axios.get(rootUrl(`api/practices/${this.formData.practiceId}/patients`)).then(response => {
                        this.loaders.patients = false
                        this.patients = (response.data || []).map(patient => {
                            patient.name = patient.full_name
                            return patient;
                        }).distinct(patient => patient.id)
                        console.log('add-call-get-patients', response.data)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-call-get-patients', err)
                    })
                }
            },
            getNurses() {
                if (this.formData.practiceId) {
                    this.loaders.nurses = true
                    this.axios.get(rootUrl(`api/practices/${this.formData.practiceId}/nurses`)).then(response => {
                        this.loaders.nurses = false
                        this.nurses = (response.data || []).map(nurse => {
                            nurse.name = nurse.full_name
                            return nurse;
                        }).filter(nurse => nurse.name && nurse.name.trim() != '')
                        console.log('add-call-get-nurses', this.nurses)
                    }).catch(err => {
                        this.loaders.nurses = false
                        this.errors.nurses = err.message
                        console.error('add-call-get-nurses', err)
                    })
                }
            },
            changePractice() {
              this.getPatients()
              this.getNurses()
            },
            submitForm(e) {
              e.preventDefault();
              const formData = {
                inbound_cpm_id: this.formData.patientId,
                outbound_cpm_id: this.formData.nurseId,
                scheduled_date: this.formData.date,
                window_start: this.formData.startTime,
                window_end: this.formData.endTime,
                attempt_note: this.formData.text
              }
              const patient = this.patients.find(patient => patient.id == this.formData.patientId)
              if (patient) {
                if (patient.status === 'draft') {
                  Event.$emit('notifications-add-call-modal:create', { 
                    text: `Call not allowed: This patient’s care plan is in draft mode. QA the care plan before scheduling a call`, 
                    type: 'error'
                  })
                }
                else {
                  this.loaders.submit = true
                  this.axios.post(rootUrl('callcreate'), formData).then((response, status) => {
                    if (response) {
                      this.loaders.submit = false
                      this.formData = Object.create(defaultFormData)
                      const call = response.data
                      Event.$emit("modal-add-call:hide")
                      Event.$emit('calls:add', call)
                      console.log('calls:add', response.data)
                      Event.$emit('notifications-add-call-modal:create', { text: 'Call created successfully' })
                    }
                    else {
                      throw new Error('Could not create call. Patient already has a scheduled call')
                    }
                  }).catch(err => {
                    this.errors.submit = err.message
                    this.loaders.submit = false
                    console.error('add-call', err)
                    Event.$emit('notifications-add-call-modal:create', { text: err.message, type: 'error' })
                  })
                }
              }
              else {
                Event.$emit('notifications-add-call-modal:create', { 
                  text: `Patient not found`, 
                  type: 'warning'
                })
              }
            },
            showUnscheduledPatients () {
              Event.$emit('modal-add-call:hide')
              Event.$emit('modal-unscheduled-patients:show')
            }
        },
        mounted() {
          this.getPractices()

          Event.$on('add-call-modals:set', (data) => {
            if (data) {
              if (data.practiceId) {
                this.formData.practiceId = data.practiceId
                this.changePractice()
              }
              if (data.patientId) {
                this.formData.patientId = data.patientId
              }
            }
          })
        }
    }
</script>

<style>
    .modal-add-call .modal-container {
        width: 420px;
    }

    span.required {
        color: red;
        font-size: 29px;
        position: absolute;
        top: -7px;
        margin-left: 5px;
    }
</style>