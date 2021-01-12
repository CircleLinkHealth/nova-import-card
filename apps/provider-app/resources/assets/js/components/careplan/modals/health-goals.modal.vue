<template>
    <modal name="health-goals" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-health-goals">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary goal-button" :class="{ selected: selectedGoal && (selectedGoal.id === goal.id), disabled: !goal.enabled }" 
                            v-for="(goal, index) in goals" :key="index" @click="select(index)">
                            {{goal.name}}
                            <label class="label label-danger" v-if="!goal.enabled">disabled</label>
                        </button>
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedGoal">
                    <div class="row form-group">
                        <div class="col-sm-12">
                            <label class="form-control">
                                <input type="checkbox" v-model="selectedGoal.enabled" @change="toggleEnable" :disabled="loaders.addGoal || loaders.removeGoal" /> Enable
                                <loader v-if="loaders.removeGoal"></loader>
                            </label>
                        </div>
                    </div>
                    <form @submit="addGoal">
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <h4>Starting<small v-if="selectedGoal.id == 4"> (# per day)</small></h4>
                                <input type="text" class="form-control" v-model="selectedGoal.info.starting" step="0.01" pattern="(\d+)(\/\d+)?" />
                            </div>
                            <div class="col-sm-6">
                                <h4>Target<small v-if="selectedGoal.id == 4"> (# per day)</small></h4>
                                <input type="text" class="form-control" placeholder="0.00" v-model="selectedGoal.info.target" step="0.01" pattern="(\d+)(\/\d+)?" :required="selectedGoal.name != 'Weight'" />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 3"> <!--Blood Sugar-->
                            <div class="col-sm-6">
                                <h4>Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.low_alert" step="0.01" max="999"  />
                            </div>
                            <div class="col-sm-6">
                                <h4>High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.high_alert" step="0.01" max="999" />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 2"> <!--Blood Pressure-->
                            <div class="col-sm-6">
                                <h4>Systolic Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.systolic_low_alert" step="0.01" max="999" />
                            </div>
                            <div class="col-sm-6">
                                <h4>Systolic High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.systolic_high_alert" step="0.01" max="999"  />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 2">
                            <div class="col-sm-6">
                                <h4>Diastolic Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.diastolic_low_alert" step="0.01" max="999"  />
                            </div>
                            <div class="col-sm-6">
                                <h4>Diastolic High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.diastolic_high_alert" step="0.01" max="999"  />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-6" v-if="selectedGoal.id === 3">
                                <h4>Starting A1C</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.starting_a1c" step="0.01"  />
                            </div>
                            <div class="col-sm-6" v-if="selectedGoal.id === 1"> <!--Weight-->
                                <h4>Monitor Changes for CHF <input type="checkbox" v-model="selectedGoal.info.monitor_changes_for_chf" /></h4>
                            </div>
                            <div class="col-sm-6 text-right" :class="{ 'col-sm-12': selectedGoal.id % 2 === 0 }">
                                <loader v-if="loaders.addGoal"></loader>
                                <button class="btn btn-secondary selected btn-submit" :class="{ 'warning': selectedGoal.isModified() }" title="Don't forget to save" :disabled="!selectedGoal.enabled">
                                    {{selectedGoal.info.created_at ? 'Save' : 'Add'}} {{selectedGoal.name}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/common/modal'

    export default {
        name: 'health-goals-modal',
        props: {
            'patient-id': String,
            goals: Array
        },
        components: {
            'modal': Modal
        },
        data() {
            return {
                biometrics: [],
                selectedGoal: null,
                selectedBiometricId: null,
                loaders: {
                    getBiometrics: null,
                    addGoal: null,
                    removeGoal: null
                }
            }
        },
        computed: {
            patientHasSelectedBiometric() {
                return this.goals.map(goal => goal.id).indexOf(this.selectedBiometricId) >= 0
            }
        },
        methods: {
            select(index) {
                if (!this.loaders.addGoal && !this.loaders.removeGoal) this.selectedGoal = (index >= 0) ? this.goals[index] : null
            },
            addGoal(e) {
                e.preventDefault()
                this.loaders.addGoal = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/biometrics`), Object.assign({
                    biometric_id: this.selectedGoal.id
                }, this.selectedGoal.info)).then(response => {
                    Event.$emit('health-goals:add', this.selectedGoal.id, response.data)
                    this.loaders.addGoal = false
                }).catch(err => {
                    console.error('health-goals:add', err)
                    this.loaders.addGoal = false
                })
            },
            removeGoal(e) {
                e.preventDefault()
                if (this.selectedGoal && confirm('Are you sure you want to disable this goal?')) {
                    this.loaders.removeGoal = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/biometrics/${this.selectedGoal.id}`)).then(response => {
                        Event.$emit('health-goals:remove', this.selectedGoal.id)
                        this.loaders.removeGoal = false
                    }).catch(err => {
                        console.error('health-goals:add', err)
                        this.loaders.removeGoal = false
                    })
                }
                else {
                    this.selectedGoal.enabled = true
                }
                return false
            },
            toggleEnable(e) {
                if (this.selectedGoal) {
                    if (e.target.checked) {
                        const promise = this.addGoal(e)
                        if (promise) promise.then(() => {
                            this.selectedGoal.enabled = true
                        })
                        else {
                            this.selectedGoal.enabled = false
                        }
                    }
                    else {
                        const promise = this.removeGoal(e)
                        if (promise) promise.then(() => {
                            this.selectedGoal.enabled = false
                        })
                        else {
                            this.selectedGoal.enabled = true
                        }
                    }
                }
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .modal-health-goals .modal-container {
        width: 700px;
    }
    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-health-goals .modal-container {
            width: 95%;
        }
    }

    .btn.btn-submit {
        margin-top: 35px;
    }

    .btn.btn-secondary.goal-button.disabled {
        /*background-color: #fa0;*/
        pointer-events: all;
    }

    .btn.btn-secondary.selected.disabled {
        background: #47beab;
        color: white;
        pointer-events: none;
    }

    .btn.btn.btn-secondary.goal-button.disabled label {
        position: absolute;
        top: -10px;
        right: -1px;
    }

    .btn.btn.btn-secondary.goal-button.selected label {
        color: white;
    }

    input[type='checkbox'] {
        display: inline !important;
    }

    .goal-button span.delete {
        width: 20px;
        height: 20px;
        font-size: 12px;
        background-color: #FA0;
        color: white;
        padding: 1px 5px;
        border-radius: 50%;
        position: absolute;
        top: -8px;
        right: -10px;
        cursor: pointer;
        display: none;
    }

    .goal-button.selected span.delete {
        display: inline-block;
    }

    button.goal-button div.loader.absolute {
        right: -13px;
        top: 15px;
    }

    .label.label-primary {
        background-color: #ddd;
        color: black;
    }

    button.warning {
        background-color: #fa0 !important;
    }
</style>