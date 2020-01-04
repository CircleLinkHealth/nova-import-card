<template>
    <div class="row">
        <form @submit="addCcdProblem">
            <div class="col-sm-12">
                <label class="label label-danger font-14" v-if="patientHasSelectedProblem">
                    Condition is already in care plan. Please add a new condition.
                </label>
            </div>
            <div class="col-sm-12 top-10">
                <v-complete placeholder="Enter a Condition" :required="true" v-model="newProblem.name"
                            :value="newProblem.name" :limit="15"
                            :suggestions="cpmProblemsForAutoComplete"
                            :class="{ error: patientHasSelectedProblem }" :threshold="0.8"
                            @input="resolveIcd10Code">
                </v-complete>
            </div>
            <div v-if="shouldSelectIsMonitored">
                <div class="col-sm-6 font-14 top-20">
                    <label><input type="radio" :value="true" v-model="newProblem.is_monitored"/> For Care
                        Management</label>
                </div>
                <div class="col-sm-6 font-14 top-20">
                    <label><input type="radio" :value="false" v-model="newProblem.is_monitored"/> Other
                        Condition</label>
                </div>
            </div>
            <div class="col-sm-12 top-20" v-if="newProblem.is_monitored">
                <input type="text" class="form-control" v-model="newProblem.icd10"
                       placeholder="ICD10 Code"/>
            </div>
            <div class="col-sm-12 text-right top-20">
                <loader v-if="loaders.addProblem"></loader>
                <input type="submit" class="btn btn-secondary right-0 selected" value="Add Condition"
                       :disabled="patientHasSelectedProblem"/>
            </div>
        </form>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config'
    import {Event} from 'vue-tables-2'
    import Modal from '../../admin/common/modal'
    import VueSelect from 'vue-select'
    import VueComplete from 'v-complete'
    import Collapsible from '../collapsible'
    import CareplanMixin from './mixins/careplan.mixin'
    import AddConditionMixin from './mixins/add-condition.mixin'


    export default {
        name: "add-condition",
        props: {
            'patient-id': String,
            'problems': Array,
            'shouldSelectIsMonitored':  Boolean,
            'cpmProblems': Array
        },
        mixins: [
            CareplanMixin,
            AddConditionMixin
        ],
        components: {
            'modal': Modal,
            'v-select': VueSelect,
            'v-complete': VueComplete,
            'collapsible': Collapsible
        },
        data() {
            return {
                loaders: {
                    addInstruction: null,
                    addProblem: null,
                    editProblem: null,
                    removeProblem: null,
                    removeInstruction: null,
                    addCode: null,
                    removeCode: null,
                    editCode: null
                },
                patient_id: null
            }
        },
        computed: {
            cpmProblemsForAutoComplete() {
                return this.cpmProbs.filter(p => p && p.name).reduce((pA, pB) => {
                    return pA.concat([{
                        name: pB.name,
                        id: pB.id,
                        code: pB.code,
                        is_snomed: false,
                    }, ...(pB.is_behavioral ? pB.snomeds.map(snomed => ({
                        name: snomed.icd_10_name,
                        id: pB.id,
                        code: snomed.icd_10_code,
                        is_snomed: true,
                    })) : [])])
                }, []).distinct(p => p.name)
                    .sort((a, b) => (+b.is_snomed) - (+a.is_snomed) || b.name.localeCompare(a.name));
            },
            pId(){
                return this.patient_id ? this.patient_id : this.patientId;
            }
        },
        methods: {
            addCcdProblem(e) {
                e.preventDefault()
                this.loaders.addProblem = true
                return this.axios.post(rootUrl(`api/patients/${this.pId}/problems/ccd`), {
                    name: this.newProblem.name,
                    cpm_problem_id: this.newProblem.cpm_problem_id,
                    is_monitored: this.newProblem.is_monitored,
                    icd10: this.newProblem.icd10
                }).then(response => {
                    this.loaders.addProblem = false
                    Event.$emit('problems:updated', {})
                    Event.$emit('full-conditions:add', response.data)
                    this.reset()
                    this.selectedProblem = response.data
                    setImmediate(() => this.checkPatientBehavioralStatus())
                }).catch(err => {
                    console.error('full-conditions:add', err)
                    this.loaders.addProblem = false
                })
            },
            resolveIcd10Code() {
                const autoCompleteProblem = this.cpmProblemsForAutoComplete.find(p => p.name == this.newProblem.name)
                this.newProblem.icd10 = (autoCompleteProblem || {}).code || (this.problems.find(p => p.name == this.newProblem.name) || {}).code
                this.newProblem.cpm_problem_id = (autoCompleteProblem || {}).id
            },
            reset() {
                this.newProblem.name = ''
                this.newProblem.problem = ''
                this.newProblem.is_monitored = true
                this.newProblem.icd10 = null
            },
        },
        mounted() {
            Event.$on('modal-attest-call-conditions:show', (patient) => {
                this.patient_id = String(patient.id)
            })
        }
    }
</script>

<style>

</style>