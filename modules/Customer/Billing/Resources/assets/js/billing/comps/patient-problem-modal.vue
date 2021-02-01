<template>
    <modal name="patient-problem" :info="patientProblemModalInfo" :no-footer="true" class-name="modal-patient-problem">
        <template slot="title">
            <div>Select Eligible Problem for {{patientProblemModalInfo.Patient}}</div>
        </template>
        <template>
            <div class="row">
                <div class="col-sm-12">
                    <div class="row form-group">
                        <div class="col-sm-12">
                            Eligible Problems
                        </div>
                        <div class="col-sm-12">
                            <select class="form-control" v-model="patientProblemModalInfo.id"
                                    @change="patientProblemModalInfo.changeProblemName">
                                <option value="New">New</option>
                                <option v-for="(problem, index) in patientProblemModalInfo.problemsForSelect()"
                                        :key="index" :value="problem.id">{{problem.name}}
                                </option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group" v-show="patientProblemModalInfo.id === 'Other'">
                        <div class="col-sm-12">
                            Choose Problem
                        </div>
                        <div class="col-sm-12">
                            <select class="form-control" v-model="patientProblemModalInfo.cpm_id"
                                    @change="patientProblemModalInfo.changeCpmProblemName">
                                <option v-for="(problem, index) in patientProblemModalInfo.cpmProblemsForSelect()" :key="index"
                                        :value="problem.id">{{problem.name}}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-12">
                            Problem Name
                        </div>
                        <div class="col-sm-12">
                            <input class="form-control" type="text" v-model="patientProblemModalInfo.name" placeholder="Name"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-12">
                            ICD-10 Code
                        </div>
                        <div class="col-sm-12">
                            <input class="form-control" type="text" v-model="patientProblemModalInfo.code" placeholder="Code"/>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import {Event} from 'vue-tables-2'
    import Modal from '../../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal'

    const PROBLEM_TYPES = {
        PROBLEM_1: 1,
        PROBLEM_2: 2,
        BHI: 3
    }

    export default {
        name: 'patient-problem-modal',
        props: {
            cpmProblems: Array
        },
        components: {
            'modal': Modal
        },
        computed: {},
        data() {
            const self = this
            return {
                patientProblemModalInfo: {
                    okHandler() {
                        console.log("okay clicked")
                        Event.$emit("modal-patient-problem:hide")
                        if (this.done && typeof(this.done) === 'function') {
                            console.log('ok-handler', this)
                            this.done(this)
                        }
                    },
                    changeProblemName(e) {
                        const problem = (this.problems.find(problem => problem.id === this.id) || {})
                        Object.assign(this, problem)
                        this.cpm_id = (this.cpmProblems.find(problem => problem.name == this.name) || {}).id || 0
                        if (this.id === 'New' || this.id === 'Other') {
                            this.code = ''
                            this.name = ''
                        }
                        self.$forceUpdate()
                    },
                    changeCpmProblemName(e) {
                        const cpmProblem = (this.cpmProblems.find(problem => problem.id == e.target.value) || {})
                        this.name = cpmProblem.name
                        this.code = cpmProblem.code
                    },
                    cpmProblems: this.cpmProblems,
                    cpm_id: null,
                    problems: [],
                    type: 1,
                    cpmProblemsForSelect() {
                        if (this.type == PROBLEM_TYPES.BHI) {
                            return this.cpmProblems.filter(problem => problem.name && problem.is_behavioral)
                        }
                        return this.cpmProblems.filter(problem => problem.name && !problem.is_behavioral)
                    },
                    problemsForSelect() {
                        if (this.type == PROBLEM_TYPES.BHI) {
                            return this.problems.filter(problem => problem.name && problem.is_behavioral)
                        }
                        return this.problems.filter(problem => problem.name && !problem.is_behavioral)
                    }
                }
            }
        },
        methods: {},
        mounted() {
            Event.$on('modal-patient-problem:show', (patientProblem, type, done) => {
                this.patientProblemModalInfo.Patient = patientProblem.Patient;
                this.patientProblemModalInfo.problems = patientProblem.problems
                this.patientProblemModalInfo.type = type
                this.patientProblemModalInfo.name = (type === 1) ? patientProblem['Problem 1'] : (type === 2 ? patientProblem['Problem 2'] : patientProblem['BHI Problem'])
                this.patientProblemModalInfo.code = (type === 1) ? patientProblem['Problem 1 Code'] : (type === 2 ? patientProblem['Problem 2 Code'] : patientProblem['BHI Problem Code'])
                this.patientProblemModalInfo.id = (patientProblem.problems.find(problem => problem.code === this.patientProblemModalInfo.code) || {}).id
                console.log(this.patientProblemModalInfo)
                if (done && typeof(done) == 'function') this.patientProblemModalInfo.done = done.bind(this.patientProblemModalInfo)
                this.patientProblemModalInfo.changeProblemName = this.patientProblemModalInfo.changeProblemName.bind(this.patientProblemModalInfo)
                this.patientProblemModalInfo.changeCpmProblemName = this.patientProblemModalInfo.changeCpmProblemName.bind(this.patientProblemModalInfo)
            })
        }
    }
</script>

<style>
    .modal-patient-problem .modal-container {
        width: 420px;
    }
</style>