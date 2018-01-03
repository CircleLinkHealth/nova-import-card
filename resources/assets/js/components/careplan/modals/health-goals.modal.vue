<template>
    <modal name="health-goals" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-health-goals">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="btn-group" role="group">
                        <input type="button" class="btn btn-secondary" :class="{ selected: selectedGoal && (selectedGoal.id === goal.id) }" 
                            v-for="(goal, index) in goals" :key="index" :value="goal.name" @click="select(index)" />
                        <input type="button" class="btn btn-secondary" value="+" 
                            :class="{ selected: !selectedGoal || !selectedGoal.id }" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedGoal">
                    <form @submit="addGoal">
                        <select class="form-control" v-model="selectedBiometricId" :class="{ error: patientHasSelectedBiometric }" required>
                            <option :value="null">Select a Goal</option>
                            <option v-for="(biometric, index) in biometrics" :key="index" :value="biometric.id">{{biometric.name}}</option>
                        </select>
                        <div class="text-right top-20">
                            <loader v-if="loaders.addGoal || loaders.getBiometrics"></loader>
                            <input type="submit" class="btn btn-secondary right-0 selected" value="Add" :disabled="!selectedBiometricId || patientHasSelectedBiometric" />
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
                this.selectedGoal = (index >= 0) ? this.goals[index] : null
            },
            getBiometrics() {
                this.loaders.getBiometrics = true
                return this.axios.get(rootUrl('api/biometrics')).then(response => {
                    console.log('health-goals:get-biometrics', response.data)
                    this.biometrics = response.data
                    this.loaders.getBiometrics = false
                }).catch(err => {
                    console.error('health-goals:get-biometrics', err)
                    this.loaders.getBiometrics = false
                })
            },
            addGoal(e) {
                e.preventDefault()
            }
        },
        mounted() {
            this.getBiometrics()
        }
    }
</script>

<style>
    .modal-health-goals .modal-container {
        width: 700px;
    }
</style>