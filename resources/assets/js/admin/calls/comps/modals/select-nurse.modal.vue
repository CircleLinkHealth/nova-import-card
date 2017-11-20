<template>
    <modal name="select-nurse" :no-title="true" :no-footer="true" :info="selectNursesModalInfo">
      <template scope="props">
        <div class="row">
            <div class="col-sm-12 text-right" v-if="filterPatients.length">
                <label>
                    <input type="checkbox" v-model="showOnlyPatientsWithNurses" > Show only patients with nurses
                </label>
            </div>
            <div class="col-sm-12">
                <div class="text-center" v-if="!filterPatients.length">
                    No available Nurses for select patients
                </div>
                <div class="row" v-for="patient in filterPatients" :key="patient.id">
                    <div class="col-sm-6">
                        <h5>
                            {{patient.name}} [id:{{patient.id}}] ({{patient.nurses ? patient.nurses.length : 0}})
                        </h5>
                    </div>
                    <div class="col-sm-6">
                        <select class="form-control" name="nurse_id" v-if="patient.nurses" @change="props.info.onChange($event, patient)" required>
                            <option :value="patient.nurse.id" :disabled="patient.nurse.disabled" selected>{{patient.nurse.name}}</option>
                            <option v-for="(nurse, index) in patient.nurses" :key="nurse.id" :value="nurse.id">{{nurse.name}}</option>
                        </select>
                        <span class="is-valid" :class="{ valid: patient.isValidSelection, invalid: !patient.isValidSelection }">{{patient.isValidSelection ? '&#x2714;' : '&#x2715;'}}</span>
                        <span v-if="!patient.nurses" class="loader"></span>
                    </div>
                </div>
            </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import { rootUrl } from '../../../../app.config'

    export default {
        name: 'select-nurse-modal',
        props: {
            'selectedPatients': {
                type: Array,
                required: true
            }
        },
        components: {
            'modal': Modal
        },
        data() {
            return {
                $nursePromise: false,
                patients: [],
                showOnlyPatientsWithNurses: true,
                selectNursesModalInfo: {
                    onChange(e, patient) {
                        if (e && patient) {
                            const selectedNurseId = e.target.value
                            patient.isValidSelection = !!patient.nurses.find(nurse => nurse.id === selectedNurseId)
                            console.log("selected-nurse-id", selectedNurseId, patient)

                        }
                    }
                }
            }
        },
        computed: {
            filterPatients() {
                return this.patients.filter(patient => {
                    return !this.showOnlyPatientsWithNurses || patient.nurses.length > 0
                })
            }
        },
        methods: {
            getNurses() {
                return this.$nursePromise = Promise.all(this.selectedPatients.map(patient => patient.id).map(id => {
                    return this.axios.get(rootUrl('api/nurses?canCallPatient=' + id)).then((response) => {
                        const nurses = ((response.data || {}).data || []).map(nurse => {
                            nurse.user = nurse.user || {}
                            return {
                                id: nurse.user_id,
                                name: nurse.user.display_name,
                                email: nurse.user.email,
                                status: nurse.status
                            }
                        })
                        const patient = this.patients.find(patient => patient.id === id)
                        return patient.nurses = nurses
                    }).catch((err) => {
                        console.error("error: get-available-nurses", id, err)
                    })
                })).then(results => {
                    this.$nursePromise = false
                })
            }
        },
        watch: {
            selectedPatients(patients, oldVal) {
                this.patients = patients.map(patient => ({
                    id: patient.id,
                    name: patient.name,
                    nurse: patient.nurse,
                    nurses: false
                }))
                this.getNurses().then((nurses) => {
                    this.nurses = nurses
                })
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .loader {
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        padding: 0px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    div.modal-container {
        width: 600px !important;
    }

    span.is-valid {
        position: absolute;
        top: 6px;
        right: 0px;
        background: none;
    }

    span.is-valid.valid {
        color: green;
    }

    span.is-valid.invalid {
        color: red;
    }
</style>