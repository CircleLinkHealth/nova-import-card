<template>
    <modal name="select-nurse" :no-title="true" :no-footer="true" :info="selectNursesModalInfo"
           class-name="modal-select-nurse">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12 text-right">
                    <loader v-if="loaders.nurses"></loader>
                </div>
                <div class="col-sm-12" v-if="filterPatients.length">
                    <label>
                        <input type="checkbox" v-model="showOnlyPatientsWithNurses"> Show only patients with nurses
                    </label>
                </div>

                <div class="col-sm-12" style="margin-top: 5px">
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
                            <div class="row">
                                <div class="col-sm-9">
                                    <select class="form-control" name="nurse_id" v-if="patient.nurses"
                                            @change="props.info.onChange($event, patient)" required>
                                        <option :key="-1" :value="-1" :selected="-1 === patient.selectedNurseId">
                                            NA
                                        </option>
                                        <option v-for="nurse in patient.nurses" :key="nurse.id" :value="nurse.id"
                                                :selected="nurse.id === patient.selectedNurseId">
                                            {{nurse.name}}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                <span class="apply-to-all" @click="applySelectedToAllPatients(patient)"
                                      title="Apply nurse to all patients">
                                    ↓
                                </span>
                                </div>
                                <div class="col-sm-1">
                                      <span class="is-valid"
                                            :class="{ valid: patient.isValidSelection(), invalid: !patient.isValidSelection() }">
                                          <span></span>
                                      </span>
                                </div>
                            </div>

                            <loader v-if="!patient.nurses || patient.loaders.update"></loader>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 top-20">
                    <notifications name="select-nurse"></notifications>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import Modal from '../../../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal'
    import {Event} from 'vue-tables-2'
    import {rootUrl} from '../../../../app.config'
    import Notifications from '../../../../components/notifications'
    import Loader from '../../../../components/loader'
    import VueCache from '../../../../util/vue-cache'

    export default {
        name: 'select-nurse-modal',
        mixins: [
            VueCache
        ],
        props: {
            'selectedPatients': {
                type: Array,
                required: true
            }
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader
        },
        data() {
            const $vm = this;

            return {
                loaders: {
                    nurses: false
                },
                patients: [],
                showOnlyPatientsWithNurses: true,
                selectNursesModalInfo: {
                    onChange(e, patient) {
                        if (e && patient) {
                            const selectedNurseId = e.target.value
                            console.log("selected-nurse-id", selectedNurseId, patient)
                            patient.selectedNurseId = ((patient.nurses.find(nurse => nurse.id == selectedNurseId) || {}).id || -1)
                        }
                    },
                    okHandler(e) {
                        Event.$emit('notifications-select-nurse:dismissAll');
                        console.log('select-nurse:modal:ok', e)
                        const eligiblePatients = $vm.patients.filter(patient => patient.nurses.length)
                        if (eligiblePatients.every(patient => patient.isValidSelection())) {
                            Event.$emit('notifications-select-nurse:create', {
                                type: 'info',
                                text: 'Attempting to assign nurses'
                            })
                            return Promise.all(eligiblePatients.map(patient => {
                                patient.loaders.update = true;
                                return self.axios
                                    .post(rootUrl('callupdate'), {
                                        callId: patient.callId,
                                        columnName: 'outbound_cpm_id',
                                        value: patient.selectedNurseId,
                                    })
                                    .then(response => {
                                        console.log('select-nurse:update', response.data)
                                        patient.loaders.update = false

                                        /* emit the event so other components know that the nurseId has been updated */
                                        const data = {
                                            callId: patient.callId,
                                            nurseId: patient.selectedNurseId
                                        }
                                        Event.$emit('select-nurse:update', data);
                                        return data
                                    })
                                    .catch(err => {
                                        patient.loaders.update = false;
                                        throw err;
                                    })
                            }))
                                .then(responses => {
                                    console.log('select-nurse:update:all', responses);
                                    Event.$emit('modal-select-nurse:hide');
                                    return responses
                                })
                                .catch(err => {
                                    let reason = err.toString();
                                    if (err.response) {
                                        if (err.response && err.response.data && err.response.data.message) {
                                            reason = err.response.data.message;
                                        } else {
                                            reason = err.response.statusText;
                                        }
                                    }
                                    Event.$emit('notifications-select-nurse:create', {
                                        type: 'error',
                                        text: reason,
                                        noTimeout: true
                                    })
                                })
                        } else {
                            const reason = `Patients with names ${eligiblePatients.filter(patient => !patient.isValidSelection()).map(patient => patient.name).join(', ')} have not been assigned to available nurses`
                            Event.$emit('notifications-select-nurse:create', {
                                type: 'error',
                                text: reason,
                                noTimeout: true
                            })
                            //no need to reject, since the okHandler, used by modal.vue does not read the returned promise (might not be a promise at all times)
                            //return Promise.reject(reason);
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
                this.loaders.nurses = true
                return Promise
                    .all(this.selectedPatients.map(patient => patient.id).filter(Boolean).map(id => {
                        return this.cache()
                            .get(rootUrl('api/nurses?canCallPatient=' + id))
                            .then((response) => {
                                const nurses = (response.data || []).map(nurse => {
                                    nurse.user = nurse.user || {};
                                    const roles = nurse.user.roles.map(r => r.name);

                                    let displayName = nurse.user.display_name || '';
                                    if (roles.includes('care-center-external')) {
                                        displayName = displayName + ' (in-house)';
                                    }

                                    return {
                                        id: nurse.user_id,
                                        name: displayName,
                                        email: nurse.user.email,
                                        status: nurse.status
                                    }
                                });
                                const patient = this.patients.find(patient => patient.id === id)
                                console.log('select-nurse:find-patient', id, patient, nurses)
                                if (patient) {
                                    //patient.nurses = nurses.filter(nurse => nurse.id != patient.nurse.id)
                                    patient.nurses = nurses;
                                    return patient.nurses;
                                }
                                return [];
                            })
                            .catch((err) => {
                                console.error("error: get-patient-available-nurses", id, err);
                                return []; //not sure
                            });
                    }))
                    .then(nurses => {
                        this.loaders.nurses = false
                        return (nurses || []).reduce((a, b) => a.concat(b), [])
                    })
                    .catch((err) => {
                        this.loaders.nurses = false
                        console.error("error: get-available-nurses", err)
                    });
            },
            setPatients(patients = []) {
                this.patients = patients.filter(patient => patient.name).map(patient => ({
                    id: patient.id,
                    name: patient.name,
                    callId: patient.callId,
                    nurse: patient.nurse,
                    selectedNurseId: patient.nurse.id,
                    nurses: [],
                    loaders: {
                        update: false,
                        nurses: false
                    },
                    isValidSelection() {
                        return !!this.nurses.find(nurse => nurse.id == this.selectedNurseId)
                    }
                }))
                //console.log('select-nurse:patients', this.patients)
                this.getNurses()
                return this.patients
            },
            applySelectedToAllPatients(patient) {
                this.patients.forEach(p => {
                    this.selectNursesModalInfo.onChange({target: {value: patient.selectedNurseId}}, p);
                });
            }
        },
        watch: {
            selectedPatients(patients) {
                return this.setPatients(patients)
            }
        },
        mounted() {
            this.setPatients(this.selectedPatients)
        }
    }
</script>

<style>

    .modal-select-nurse .modal-container {
        width: 600px;
    }

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
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    span.is-valid, span.apply-to-all {
        cursor: pointer;
    }

    span.apply-to-all {
        position: absolute;
        top: 4px;
    }

    span.is-valid {
        position: absolute;
        top: 6px;
        /*right: 0px;*/
        background: none;
    }

    span.is-valid.valid {
        color: green;
    }

    span.is-valid.invalid {
        color: red;
    }

    span.is-valid.valid span::after {
        content: "✔";
    }

    span.is-valid.invalid span::after {
        content: "✕";
    }

    .top-20 {
        margin-top: 20px;
    }
</style>
