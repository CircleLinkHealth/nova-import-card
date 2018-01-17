<template>
    <modal name="health-goals" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-health-goals">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="btn-group" role="group">
                        <input type="button" class="btn btn-secondary" :class="{ selected: selectedGoal && (selectedGoal.id === goal.id) }" 
                            v-for="(goal, index) in goals" :key="index" :value="goal.name" @click="select(index)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedGoal">
                    <form @submit="addGoal">
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <h4>Starting</h4>
                                <input type="text" class="form-control" placeholder="0.00" v-model="selectedGoal.info.starting" step="0.01" />
                            </div>
                            <div class="col-sm-6">
                                <h4>Target</h4>
                                <input type="text" class="form-control" placeholder="0.00" v-model="selectedGoal.info.target" step="0.01" />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 3"> <!--Blood Sugar-->
                            <div class="col-sm-6">
                                <h4>Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.low_alert" step="0.01"  />
                            </div>
                            <div class="col-sm-6">
                                <h4>High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.high_alert" step="0.01" />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 2"> <!--Blood Pressure-->
                            <div class="col-sm-6">
                                <h4>Systolic Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.systolic_low_alert" step="0.01" />
                            </div>
                            <div class="col-sm-6">
                                <h4>Systolic High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.systolic_high_alert" step="0.01"  />
                            </div>
                        </div>
                        <div class="row form-group" v-if="selectedGoal.id === 2">
                            <div class="col-sm-6">
                                <h4>Diastolic Low Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.diastolic_low_alert" step="0.01"  />
                            </div>
                            <div class="col-sm-6">
                                <h4>Diastolic High Alert</h4>
                                <input type="number" class="form-control" placeholder="0.00" v-model="selectedGoal.info.diastolic_high_alert" step="0.01"  />
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
                                <button class="btn btn-secondary selected btn-submit">{{selectedGoal.info.created_at ? 'Edit' : 'Add'}} {{selectedGoal.name}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'

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
                    addGoal: null
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
                if (!this.loaders.addGoal) this.selectedGoal = (index >= 0) ? this.goals[index] : null
            },
            addGoal(e) {
                e.preventDefault()
                this.loaders.addGoal = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/biometrics`), Object.assign({
                    biometric_id: this.selectedGoal.id,

                }, this.selectedGoal.info)).then(response => {
                    console.log('health-goals:add', response.data)
                    Event.$emit('health-goals:add', this.selectedGoal.id, response.data)
                    this.loaders.addGoal = false
                }).catch(err => {
                    console.error('health-goals:add', err)
                    this.loaders.addGoal = false
                })
            },
            editGoal(e) {
                e.preventDefault()
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

    .btn.btn-submit {
        margin-top: 35px;
    }

    input[type='checkbox'] {
        display: inline !important;
    }
</style>