<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Follow These Instructions
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showCareAreasModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="col-xs-12 text-center" v-if="cpmProblemsWithInstructions.length === 0">
            No Instructions at this time
        </div>
        <div class="row gutter" v-if="cpmProblemsWithInstructions.length > 0">
            <div class="follow-these-instructions col-xs-12" v-for="(problem, index) in cpmProblemsWithInstructions" :key="index">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">For {{problem.name}}:</h3>
                <p v-for="(instruction, index) in (problem.instruction.name || '').split('\n')" :key="index" v-html="instruction || '<br>'"></p>
            </div>
        </div>
        <full-conditions-modal ref="fullConditionsModal" :patient-id="patientId" :cpm-problems="cpmProblems" :problems="ccdProblems"></full-conditions-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import FullConditionsModal from './modals/full-conditions.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'instructions',
        props: [
            'patient-id'
        ],
        components: {
            'full-conditions-modal': FullConditionsModal
        },
        mixins: [ CareplanMixin ],
        data() {
            return {
                 cpmProblems: [],
                 ccdProblems: [],
                 allCpmProblems: []
            }
        },
        computed: {
            cpmProblemsWithInstructions() {
                return this.ccdProblems.filter(problem => problem.instruction.name).distinct(problem => problem.name)
            }
        },
        methods: {
            setupCcdProblem(problem) {
                problem.newCode = {
                    code: null,
                    problem_code_system_id: null,
                    selectedCode: 'Select a Code'
                }
                problem.instruction = problem.instruction || (problem.should_show_default_instruction ? (this.allCpmProblems.find(cpm => (cpm.name == problem.name) || (cpm.id == problem.cpm_id)) || {}).instruction: {}) || {}
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
            getCcdProblems() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/problems/ccd`)).then(response => {
                    console.log('instructions:ccd', response.data)
                    this.ccdProblems = response.data.map(this.setupCcdProblem)
                    Event.$emit('care-areas:ccd-problems', this.ccdProblems)
                }).catch(err => console.error('instructions:ccd', err))
            },
            showFullConditionsModal() {
                Event.$emit('modal-full-conditions:show')
            },
            showCareAreasModal() {
                Event.$emit('modal-care-areas:show')
            }
        },
        mounted() {
            this.allCpmProblems = (this.careplan().allCpmProblems || [])
            this.ccdProblems = (this.careplan().ccdProblems || []).map(this.setupCcdProblem)

            Event.$emit('care-areas:ccd-problems', this.ccdProblems)

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems
            })

            Event.$on('care-areas:ccd-problems', (problems) => {
                this.ccdProblems = problems
            })

            Event.$on('full-conditions:add', (ccdProblem) => {
                if (ccdProblem) this.ccdProblems.push(this.setupCcdProblem(ccdProblem))
                App.$emit('patient-problems-updated', this.ccdProblems);
            })

            Event.$on('full-conditions:remove', (id) => {
                const index = this.ccdProblems.findIndex(problem => problem.id === id)
                this.ccdProblems.splice(index, 1)
            })

            Event.$on('full-conditions:edit', (ccdProblem) => {
                if (ccdProblem) {
                    const index = this.ccdProblems.findIndex(p => p.id == ccdProblem.id)
                    if (index >= 0) {
                        const problem = this.setupCcdProblem(ccdProblem)
                        this.ccdProblems[index].name = problem.name
                        this.ccdProblems[index].is_monitored = problem.is_monitored
                        this.ccdProblems[index].cpm_id = problem.cpm_id
                        this.ccdProblems[index].codes = problem.codes
                        this.ccdProblems[index].instruction = problem.instruction
                    }
                }
            })

            Event.$on('full-conditions:add-code', (code) => {
                const index = this.ccdProblems.findIndex(p => p.id === code.problem_id);
                if (index >= 0) {
                    if (this.ccdProblems[index].codes.find(c => c.problem_code_system_id === code.problem_code_system_id)) {
                        this.ccdProblems[index].codes = this.ccdProblems[index].codes.map(c => {
                            if (c.problem_code_system_id === code.problem_code_system_id) return code
                            return c
                        })
                    }
                    else {
                        this.ccdProblems[index].codes.push(code)
                    }
                    this.ccdProblems[index] = this.setupCcdProblem(this.ccdProblems[index])
                }
            })

            Event.$on('full-conditions:remove-code', (problem_id, id) => {
                const index = this.ccdProblems.findIndex(problem => problem.id === problem_id)
                if (index >= 0) {
                    const codeIndex = this.ccdProblems[index].codes.findIndex(c => c.id == id)
                    this.ccdProblems[index].codes.splice(codeIndex, 1)

                    this.ccdProblems[index] = this.setupCcdProblem(this.ccdProblems[index])
                }
                
            })

            Event.$emit('care-areas:request-problems')
        }
    }
</script>

<style>
    .follow-these-instructions {
        white-space: pre-wrap;
    }
</style>