<!-- DEPRECATED. In favor of add-call-v2.modal -->
<template>
    <modal name="add-call" :info="addCallModalInfo" :no-footer="true" class-name="modal-add-call">
        <template slot="title">
            <div class="row">
                <div class="col-sm-6">
                    Add New Call
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-warning btn-xs" @click="showUnscheduledPatients">Show Unscheduled Patients
                    </button>
                </div>
            </div>
        </template>
        <template slot-scope="props">
            <form action="/callcreate" @submit="submitForm">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Practice
                            </div>
                            <div class="col-sm-7">
                                <v-select class="form-control" v-model="selectedPracticeData"
                                          :options="practicesForSelect" :on-change="changePractice"></v-select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Patient <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <v-select class="form-control" name="inbound_cpm_id" v-model="selectedPatientData"
                                          :options="patientsForSelect" :on-change="changePatient" required></v-select>
                                <label>
                                    <input type="checkbox" v-model="filters.showUnscheduledPatients"
                                           @change="changeUnscheduledPatients">
                                    <small>Show Only Unscheduled Patients</small>
                                </label>
                                <loader v-if="loaders.patients"></loader>
                                <div class="alert alert-warning" v-if="selectedPatientIsInDraftMode">
                                    Call not allowed: Care plan is in draft mode. QA the care plan first.
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Nurse <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <v-select class="form-control" name="outbound_cpm_id" v-model="selectedNurseData"
                                          :options="nursesForSelect" :on-change="changeNurse" required>
                                </v-select>
                                <loader v-if="loaders.nurses"></loader>
                                <div class="alert alert-danger"
                                     v-if="formData.practiceId && (nursesForSelect.length == 0)">
                                    No available nurses for selected patient
                                </div>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Date <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <input class="form-control" type="date" name="scheduled_date" v-model="formData.date"
                                       required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Start Time <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <input class="form-control" type="time" name="window_start" v-model="formData.startTime"
                                       required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                End Time <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <input class="form-control" type="time" name="window_end" v-model="formData.endTime"
                                       required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-5">
                                Add Text <span class="required">*</span>
                            </div>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="attempt_note" v-model="formData.text"
                                          required></textarea>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <input type="checkbox" id="is_manual" name="is_manual" v-model="formData.isManual"/>
                                <label for="is_manual">Patient Requested Call Time</label>
                                <button class="submit hidden"></button>
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
    import {Event} from 'vue-tables-2'
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../components/loader'
    import {rootUrl} from '../../../../app.config'
    import {today} from '../../../../util/today'
    import notifications from '../../../../components/notifications'
    import VueSelect from 'vue-select'
    import VueCache from '../../../../util/vue-cache'

    const UNASSIGNED_VALUE = {label: 'Unassigned', value: null}

    export const defaultFormData = {
        practiceId: null,
        patientId: null,
        nurseId: null,
        date: today(),
        startTime: '09:00',
        endTime: '17:00',
        text: null,
        isManual: 0
    }

    export default {
        name: 'add-call-modal',
        mixins: [VueCache],
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            'v-select': VueSelect,
            notifications
        },
        data() {
            return {
                addCallModalInfo: {
                    okHandler() {
                        const form = this.$form()
                        this.errors().submit = null
                        console.log("form:add-call:submit", form)
                        form.querySelector('button.submit.hidden').click()
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
                formData: Object.assign({}, defaultFormData),
                selectedPatientData: UNASSIGNED_VALUE,
                selectedPracticeData: UNASSIGNED_VALUE,
                selectedNurseData: UNASSIGNED_VALUE,
                filters: {
                    showUnscheduledPatients: false
                },
                selectedPatientIsInDraftMode: false
            }
        },
        computed: {
            nursesForSelect() {
                return [
                    UNASSIGNED_VALUE,
                    ...this.nurses.map(nurse => ({
                        label: nurse.full_name,
                        value: nurse.id
                    }))]
            },
            practicesForSelect() {
                return [
                    UNASSIGNED_VALUE,
                    ...this.practices.map(practice => ({
                        label: practice.display_name,
                        value: practice.id
                    }))]
            },
            patientsForSelect() {
                return [
                    UNASSIGNED_VALUE,
                    ...this.patients.map(patient => ({
                        label: patient.name + ' (' + patient.id + ')',
                        value: patient.id
                    }))]
            }
        },
        methods: {
            selectedPatient() {
                return (this.patients.find(patient => patient.id === this.formData.patientId) || {})
            },
            setPractice(practiceId) {
                if (practiceId) {
                    this.formData.practiceId = practiceId
                    const practice = this.practices.find(practice => practice.id === this.formData.practiceId)
                    if (practice) {
                        if (!this.selectedPracticeData || this.selectedPracticeData.value !== practice.id) {
                            this.selectedPracticeData = {label: practice.display_name, value: practice.id}
                        }
                    }
                    else {
                        this.selectedPracticeData = UNASSIGNED_VALUE
                    }
                }
            },
            changePatient(patient) {
                if (patient) {
                    this.formData.patientId = patient.value
                    this.setPractice(this.selectedPatient().program_id)
                    this.selectedPatientIsInDraftMode = (this.selectedPatient().status == 'draft')
                }
            },
            changePractice(practice) {
                if (practice) {
                    if (this.formData.practiceId != practice.value) this.selectedPatientData = UNASSIGNED_VALUE
                    this.formData.practiceId = practice.value
                    this.selectedNurseData = UNASSIGNED_VALUE
                    return Promise.all([this.getPatients(), this.getNurses()])
                }
                return Promise.resolve([])
            },
            changeNurse(nurse) {
                if (nurse) {
                    this.formData.nurseId = nurse.value
                }
            },
            changeUnscheduledPatients(e) {
                if (e && e.target) {
                    return e.target.checked ? this.getUnscheduledPatients() : this.getPatients()
                }
                return Promise.resolve([])
            },
            getPractices() {
                this.loaders.practices = true
                return this.cache().get(rootUrl(`api/practices?admin-only=true`)).then(response => {
                    this.loaders.practices = false
                    console.log('add-call:practices', response)
                    return this.practices = (response || []).sort((a, b) => {
                        if (a.display_name < b.display_name) return -1;
                        else if (a.display_name > b.display_name) return 1
                        else return 0
                    }).distinct(patient => patient.id)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('add-call:practices', err)
                })
            },
            getPatients() {
                return !this.formData.practiceId ?
                    this.getAllPatients() :
                    (this.filters.showUnscheduledPatients ?
                            this.getUnscheduledPatients() :
                            this.getPracticePatients()
                    );
            },
            getUnscheduledPatients() {
                this.loaders.patients = true
                const practice_addendum = this.formData.practiceId ? `practices/${this.formData.practiceId}/` : '';
                return this.axios.get(rootUrl(`api/${practice_addendum}patients/without-scheduled-activities`)).then(response => {
                    this.loaders.patients = false
                    const pagination = response.data
                    console.log('add-call:patients:unscheduled', pagination)
                    return pagination
                }).then((pagination) => {
                    return this.patients = ((pagination || {}).data || []).map(patient => {
                        patient.name = patient.full_name
                        return patient;
                    }).sort((a, b) => a.name > b.name ? 1 : -1).distinct(patient => patient.id)
                }).catch(err => {
                    this.loaders.patients = false
                    this.errors.patients = err.message
                    console.error('add-call:patients:unscheduled', err)
                })
            },
            getPracticePatients() {
                if (this.formData.practiceId) {
                    this.loaders.patients = true
                    return this.axios.get(rootUrl(`api/practices/${this.formData.practiceId}/patients`)).then(response => {
                        this.loaders.patients = false
                        console.log('add-call:patients:practice', response.data)
                        return response.data
                    }).then((patients = []) => {
                        return this.patients = patients.map(patient => {
                            patient.name = patient.full_name;
                            return patient;
                        }).sort((a, b) => a.name > b.name ? 1 : -1).distinct(patient => patient.id)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-call:patients:practice', err)
                    })
                }
                return Promise.resolve([])
            },
            getAllPatients() {
                this.loaders.patients = true
                return this.cache().get(rootUrl(`api/patients?rows=all&autocomplete`)).then(response => {
                    this.loaders.patients = false
                    console.log('add-call:patients:all', response.data)
                    return response.data;
                })
                    .then((patients = []) => {
                        return this.patients = patients.sort((a, b) => {
                            return a.name > b.name ? 1 : -1;
                        }).distinct(patient => patient.id)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-call:patients:all', err)
                    })
            },
            getNurses() {
                if (this.formData.practiceId) {
                    this.loaders.nurses = true
                    return this.axios.get(rootUrl(`api/practices/${this.formData.practiceId}/nurses`)).then(response => {
                        this.loaders.nurses = false
                        this.nurses = (response.data || []).map(nurse => {
                            nurse.name = nurse.full_name
                            return nurse;
                        }).filter(nurse => nurse.name && nurse.name.trim() != '')
                        console.log('add-call-get-nurses', this.nurses)
                        return this.nurses
                    }).catch(err => {
                        console.error('add-call-get-nurses', err)
                        this.loaders.nurses = false
                        this.errors.nurses = err.message
                    })
                }
                return Promise.resolve([])
            },
            submitForm(e) {
                e.preventDefault();
                const formData = {
                    inbound_cpm_id: this.formData.patientId,
                    outbound_cpm_id: this.formData.nurseId,
                    scheduled_date: this.formData.date,
                    window_start: this.formData.startTime,
                    window_end: this.formData.endTime,
                    attempt_note: this.formData.text,
                    is_manual: this.formData.isManual
                };
                const patient = this.patients.find(patient => patient.id == this.formData.patientId)
                if (patient) {
                    if (patient.status === 'draft') {
                        Event.$emit('notifications-add-call-modal:create', {
                            text: `Call not allowed: This patient’s care plan is in draft mode. QA the care plan before scheduling a call`,
                            type: 'warning'
                        })
                    }
                    else {
                        this.loaders.submit = true
                        return this.axios.post(rootUrl('callcreate'), formData).then((response, status) => {
                            if (response) {
                                this.loaders.submit = false
                                this.formData = Object.assign({}, defaultFormData)
                                const call = response.data
                                Event.$emit("modal-add-call:hide")
                                Event.$emit('calls:add', call)
                                console.log('calls:add', call)
                                Event.$emit('notifications-add-call-modal:create', {text: 'Call created successfully'})
                                return call
                            }
                            else {
                                throw new Error('Could not create call. Patient already has a scheduled call')
                            }
                            return null
                        }).catch(err => {
                            this.errors.submit = err.message
                            this.loaders.submit = false
                            console.error('add-call', err)

                            let msg = err.message;
                            if (err.response && err.response.data && err.response.data.errors) {
                                // {is_manual: ['error message']}
                                const errors = err.response.data.errors;
                                if (Array.isArray(errors)) {
                                    msg += `: ${errors.join(', ')}`;
                                }
                                else {
                                    const errorsMessages = Object.values(errors).map(x=>x[0]).join(', ');
                                    msg += `: ${errorsMessages}`;
                                }
                            }

                            Event.$emit('notifications-add-call-modal:create', {text: msg, type: 'error'})
                        })
                    }
                }
                else {
                    Event.$emit('notifications-add-call-modal:create', {
                        text: `Patient not found`,
                        type: 'warning'
                    })
                }
                return Promise.resolve(null)
            },
            showUnscheduledPatients() {
                Event.$emit('modal-add-call:hide')
                Event.$emit('modal-unscheduled-patients:show')
            }
        },
        created() {
            return Promise.all([this.getPractices(), this.getPatients()])
        },
        mounted() {
            Event.$on('add-call-modals:set', (data) => {
                if (data) {
                    if (data.practiceId) {
                        // this.setPractice(data.practiceId)
                    }
                    if (data.patientId) {
                        this.formData.patientId = data.patientId
                        console.log(data)
                        this.selectedPatientData = {label: data.patientName, value: data.patientId}
                        this.selectedPatientData.label = data.patientName
                        this.selectedPatientData.value = data.patientId
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

    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    input#is_manual {
        width: 15px;
        height: 15px;
    }

</style>