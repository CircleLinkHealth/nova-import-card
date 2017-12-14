<template>
    <modal name="add-call" :info="addCallModalInfo" :no-footer="true" class-name="modal-add-call">
      <template slot="title">
        <div class="row">
          <div class="col-sm-12">
            Add New Call
          </div>
        </div>
      </template>
      <template scope="props">
        <div class="row">
          <div class="col-sm-12">
            <div class="row form-group">
              <div class="col-sm-5">
                Practice:
              </div>
              <div class="col-sm-7">
                <select class="form-control" v-model="practiceId" @change="getPatients">
                  <option :value="null">Unassigned</option>
                  <option v-for="(practice, index) in practices" :key="practice.id" :value="practice.id">{{practice.display_name}}</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Patient:
              </div>
              <div class="col-sm-7">
                <select class="form-control" v-model="patientId">
                  <option :value="null">Unassigned</option>
                  <option v-for="(patient, index) in patients" :key="patient.id" :value="patient.id">{{patient.name}}</option>
                </select>
                <input type="checkbox" v-model="filters.showUnscheduledPatients" @change="getPatients"> Show Only Unscheduled Patients
                <loader v-if="loaders.patients"></loader>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Nurse:
              </div>
              <div class="col-sm-7">
                <select class="form-control" v-model="nurseId">
                  <option :value="null">Unassigned</option>
                  <option v-for="(nurse, index) in nurses" :key="nurse.id" :value="nurse.id">{{nurse.name}}</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Date:
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="date" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Start Time
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="time" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                End Time:
              </div>
              <div class="col-sm-7">
                <input class="form-control" type="time" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-5">
                Add Text:
              </div>
              <div class="col-sm-7">
                <textarea class="form-control"></textarea>
              </div>
            </div>
          </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../components/loader'
    import { rootUrl } from '../../../../app.config'

    export default {
        name: 'add-call-modal',
        components: {
            'modal': Modal,
            'loader': LoaderComponent
        },
        data() {
            return {
                addCallModalInfo: {
                    okHandler() {
                        console.log("okay clicked")
                        Event.$emit("modal-add-call:hide")
                    }
                },
                errors: {
                    practices: null,
                    patients: null
                },
                loaders: {
                    practices: false,
                    patients: false
                },
                practices: [],
                patients: [],
                nurses: [],
                practiceId: null,
                patientId: null,
                nurseId: null,
                filters: {
                  showUnscheduledPatients: false
                }
            }
        },
        methods: {
          getPractices() {
                this.loaders.practices = true
                this.axios.get(rootUrl(`api/practices`)).then(response => {
                    this.loaders.practices = false
                    this.practices = (response.data || []).sort((a, b) => {
                      if (a.display_name < b.display_name) return -1;
                      else if (a.display_name > b.display_name) return 1
                      else return 0
                    })
                    console.log('add-call-get-practices', response.data)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('add-call-get-practices', err)
                })
            },
            getUnscheduledPatients() {
                if (this.practiceId) {
                    this.loaders.patients = true
                    this.axios.get(rootUrl(`api/practices/${this.practiceId}/patients/without-scheduled-calls`)).then(response => {
                        this.loaders.patients = false
                        this.patients = (response.data || []).map(patient => {
                            patient.name = patient.full_name
                            return patient;
                        })
                        console.log('add-call-get-patients', response.data)
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
                if (this.practiceId) {
                    this.loaders.patients = true
                    this.axios.get(rootUrl(`api/practices/${this.practiceId}/patients`)).then(response => {
                        this.loaders.patients = false
                        this.patients = (response.data || []).map(patient => {
                            patient.name = patient.full_name
                            return patient;
                        })
                        console.log('add-call-get-patients', response.data)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-call-get-patients', err)
                    })
                }
            }
        },
        mounted() {
          this.getPractices()
        }
    }
</script>

<style>
    .modal-add-call .modal-container {
        width: 420px;
    }
</style>