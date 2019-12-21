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


    export default {
        name: "add-condition",
        props: {
            'patient-id': String,
            'problems': Array,
            'shouldSelectIsMonitored':  Boolean
        },
        mixins: [CareplanMixin],
        components: {
            'modal': Modal,
            'v-select': VueSelect,
            'v-complete': VueComplete,
            'collapsible': Collapsible
        },
        data() {
            return {
                cpmProblems: [],
                newProblem: {
                    name: '',
                    problem: '',
                    is_monitored: true,
                    icd10: null,
                    cpm_problem_id: null
                },
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
            }
        },
        computed: {
            patientHasSelectedProblem() {
                //mixin territory
                if (!this.selectedProblem) return (this.newProblem.name !== '') && this.problems.findIndex(problem => (problem.name || '').toLowerCase() == (this.newProblem.name || '').toLowerCase()) >= 0
                else return (this.selectedProblem.name !== '') && this.problems.findIndex(problem => (problem != this.selectedProblem) && ((problem.name || '').toLowerCase() == (this.selectedProblem.name || '').toLowerCase())) >= 0
            },
            cpmProblemsForAutoComplete() {
                return this.cpmProblems.filter(p => p && p.name).reduce((pA, pB) => {
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
        },
        methods: {
            getSystemCodes() {
                let codes = this.careplan().allCpmProblemCodes || null

                if (codes !== null) {
                    this.codes = codes
                    return true
                }

                return this.axios.get(rootUrl(`api/problems/codes`)).then(response => {
                    // console.log('full-conditions:get-system-codes', response.data)
                    this.codes = response.data
                }).catch(err => {
                    console.error('full-conditions:get-system-codes', err)
                })
            },
            addCcdProblem(e) {
                e.preventDefault()
                this.loaders.addProblem = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/ccd`), {
                    name: this.newProblem.name,
                    cpm_problem_id: this.newProblem.cpm_problem_id,
                    is_monitored: this.newProblem.is_monitored,
                    icd10: this.newProblem.icd10
                }).then(response => {
                    console.log('full-conditions:add', response.data)
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
            setupCcdProblem(problem) {
                problem.newCode = {
                    code: null,
                    problem_code_system_id: null,
                    selectedCode: 'Select a Code'
                }
                problem.instruction = problem.instruction || (this.allCpmProblems.find(cpm => (cpm.name == problem.name) || (cpm.id == problem.cpm_id)) || {}).instruction || {}
                problem.type = 'ccd'
                problem.cpm = (this.cpmProblems.find(p => p.id == problem.cpm_id) || {}).name || 'Select a CPM Problem'
                problem.icd10 = ((problem.codes.find(c => c.code_system_name == 'ICD-10') || {}).code || null)
                problem.related = (function () {
                    return this.allCpmProblems.find(cpm => cpm.id === problem.cpm_id)
                }).bind(this)
                problem.title = () => `${(problem.icd10) || (problem.related() || {}).code || ''} ${problem.original_name}`
                problem.count = () => this.allCpmProblems.filter(p => p.name == problem.name).length
                if (!problem.icd10 && (problem.related() || {}).code) {
                    const icd10Code = {
                        code: (problem.related() || {}).code,
                        code_system_name: 'ICD-10',
                        problem_code_system_id: 2,
                        problem_id: problem.id
                    }
                    if (!problem.codes.find(p => p.problem_code_system_id == icd10Code.problem_code_system_id)) {
                        problem.codes.push(icd10Code)
                    }
                }
                return problem
            },
            /**
             * is patient BHI, CCM or BOTH?
             */
            checkPatientBehavioralStatus() {
                const ccmCount = this.problems.filter(problem => {
                    if (problem.is_monitored) {
                        const cpmProblem = this.cpmProblems.find(cpm => cpm.id == problem.cpm_id)
                        return cpmProblem ? !cpmProblem.is_behavioral : false
                    }
                    return false
                }).length
                const bhiCount = this.problems.filter(problem => {
                    const cpmProblem = this.cpmProblems.find(cpm => cpm.id == problem.cpm_id)
                    return cpmProblem ? cpmProblem.is_behavioral : false
                }).length
                console.log('ccm', ccmCount, 'bhi', bhiCount)
                Event.$emit('careplan:bhi', {
                    hasCcm: ccmCount > 0,
                    hasBehavioral: bhiCount > 0
                })
            }
        },
        mounted() {

                this.cpmProblems = this.careplan().allCpmProblems || []
                this.getSystemCodes()
        }
    }
</script>

<style>

</style>