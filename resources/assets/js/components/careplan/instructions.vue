<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Follow these instructions
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showCareAreasModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <slot v-if="cpmProblems.length === 0">
            <div class="col-xs-12 text-center">
                No Instructions at this time
            </div>
        </slot>
        <div class="row gutter" v-if="cpmProblems.length > 0">
            <div class="col-xs-12" v-for="(problem, index) in cpmProblemsWithInstructions" :key="index">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">For {{problem.name}}:</h3>
                <ul>
                    <li v-for="(instruction, index) in problem.instructions" :key="index" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index">{{chunk}}</p>
                    </li>
                </ul>
            </div>
        </div>
        <!-- <div class="row gutter" v-if="ccdProblems">
            <div class="col-xs-12">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">Full Conditions List:
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showFullConditionsModal" aria-hidden="true"></span>
                </h3>
                <p v-if="ccdProblems.length === 0">
                    No instructions at this time
                </p>
                <ul>
                    <li v-for="(problem, index) in ccdProblems" :key="index">
                        <p>{{problem.name}}</p>
                    </li>
                </ul>
            </div>
        </div> -->
        <full-conditions-modal ref="fullConditionsModal" :patient-id="patientId" :cpm-problems="cpmProblems" :problems="ccdProblems"></full-conditions-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import FullConditionsModal from './modals/full-conditions.modal'

    export default {
        name: 'instructions',
        props: [
            'patient-id'
        ],
        components: {
            'full-conditions-modal': FullConditionsModal
        },
        data() {
            return {
                 cpmProblems: [],
                 ccdProblems: []
            }
        },
        computed: {
            cpmProblemsWithInstructions() {
                return this.cpmProblems.filter(problem => problem.instructions && problem.instructions.length > 0)
            }
        },
        methods: {
            setupCcdProblem(problem) {
                problem.newCode = {
                    code: null,
                    problem_code_system_id: null,
                    selectedCode: 'Select a Code'
                }
                problem.instruction = {}
                problem.type = 'ccd'
                problem.cpm = (this.cpmProblems.find(p => p.id == problem.cpm_id) || {}).name || 'Select a CPM Problem'
                problem.icd10 = ((problem.codes.find(c => c.code_system_name == 'ICD-10') || {}).code || null)
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
            this.getCcdProblems()

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems
            })

            Event.$on('full-conditions:add', (ccdProblem) => {
                if (ccdProblem) this.ccdProblems.push(this.setupCcdProblem(ccdProblem))
            })

            Event.$on('full-conditions:remove', (id) => {
                const index = this.ccdProblems.findIndex(problem => problem.id === id)
                this.ccdProblems.splice(index, 1)
            })

            Event.$on('full-conditions:edit', (ccdProblem) => {
                if (ccdProblem) {
                    const index = this.ccdProblems.findIndex(p => p.id == ccdProblem.id)
                    if (index >= 0) {
                        this.ccdProblems[index] = this.setupCcdProblem(ccdProblem)
                    }
                }
            })

            Event.$on('full-conditions:add-code', (code) => {
                const index = this.ccdProblems.findIndex(p => p.id === code.problem_id);
                if (index >= 0) {
                    this.ccdProblems[index].codes.push(code)
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
        }
    }
</script>

<style>
    
</style>