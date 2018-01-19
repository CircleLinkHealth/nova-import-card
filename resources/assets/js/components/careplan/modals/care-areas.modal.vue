<template>
    <modal name="care-areas" :no-title="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12" :class="{ 'problem-container': problems.length > 12 }">
                    <div class="btn-group" :class="{ 'problem-buttons': problems.length > 12 }" role="group" aria-label="We are managing">
                        <button class="btn btn-secondary problem-button" :class="{ selected: selectedProblem && (selectedProblem.id === problem.id) }" 
                                v-for="(problem, index) in problems" :key="index" @click="select(index)">
                            {{problem.name}}
                            <span class="delete" title="remove this cpm problem" @click="removeCpmProblem">x</span>
                            <loader class="absolute" v-if="loaders.removeProblem && selectedProblem && (selectedProblem.id === problem.id)"></loader>
                        </button>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedProblem || !selectedProblem.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedProblem">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="btn btn-secondary full-width" :class="{ selected: newProblem.is_monitored }">
                                <input type="radio" :value="true" v-model="newProblem.is_monitored" /> For Care Management
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <label class="btn btn-secondary full-width" :class="{ selected: !newProblem.is_monitored }">
                                <input type="radio" :value="false" v-model="newProblem.is_monitored" /> Other Condition
                            </label>
                        </div>
                        <div class="col-sm-12">
                            <v-complete placeholder="Enter a Condition" v-model="newProblem.name" :value="newProblem.name" :suggestions="cpmProblemsForAutoComplete"  :class="{ error: patientHasSelectedProblem }">
                            </v-complete>
                            <div class="text-right top-20">
                                <loader v-if="loaders.addProblem"></loader>
                                <input type="button" class="btn btn-secondary right-0 selected" value="Add" @click="addCpmProblem" :disabled="!patientHasSelectedProblem" />
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <div class="row instructions top-20" v-if="selectedProblem.type == 'cpm'">
                        <form @submit="addInstruction">
                            <div class="col-sm-12">
                                <textarea class="form-control free-note height-200" v-model="selectedProblem.instruction.name" placeholder="Enter Instructions"></textarea>
                            </div>
                            <div class="col-sm-12 text-right top-20">
                                <loader v-if="loaders.addInstruction"></loader>
                                <input type="submit" class="btn btn-secondary right-0 instruction-add selected" value="Save" 
                                    title="add this instruction for this cpm problem" 
                                    :disabled="!selectedProblem.instruction.name" />
                            </div>
                        </form>
                    </div>
                     <div class="row top-20" v-if="selectedProblem.type == 'ccd'">
                        <div class="col-sm-12">
                            <form @submit="editCcdProblem">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <input class="form-control" v-model="selectedProblem.name" placeholder="Problem Name" required />
                                    </div>
                                    <div class="col-sm-6">
                                        <v-select class="form-control" v-model="selectedProblem.cpm" :value="selectedProblem.cpm_id" 
                                            :options="cpmProblemsForSelect"></v-select>
                                    </div>
                                    <div class="col-sm-6 top-20 font-14">
                                        <label>
                                            <input type="checkbox" v-model="selectedProblem.is_monitored" /> Monitor Problem
                                        </label>
                                    </div>
                                    <div class="col-sm-6 top-20 text-right">
                                        <loader class="absolute" v-if="loaders.editProblem"></loader>
                                        <input type="submit" class="btn btn-secondary margin-0 instruction-add selected" value="Save" 
                                            title="Edit this problem" :disabled="selectedProblem.name.length === 0 || !(selectedProblem.cpm || {}).value" />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-12">
                            <h4>
                                Problem Codes
                            </h4>
                        </div>
                        <div class="col-sm-12 top-20">
                            <ul class="list-group font-16 border-bottom">
                                <li class="row list-group-item" v-for="code in selectedProblem.codes" :key="code.id">
                                    <div class="col-sm-5">
                                        <p>
                                            {{code.code_system_name}}
                                        </p>
                                    </div>
                                    <div class="col-sm-5">
                                        <p>{{code.code}}</p>
                                    </div>
                                    <div class="col-sm-2 text-right">
                                        <loader class="absolute" v-if="loaders.removeCode"></loader>
                                        <input type="button" class="btn btn-danger margin-0" value="-" @click="removeCode(selectedProblem.id, code.id)" />
                                    </div>
                                </li>
                                <li class="row list-group-item" v-if="selectedProblem.codes.length === 0">
                                    <center>No Codes Yet</center>
                                </li>
                            </ul>
                            <div class="row">
                                <form @submit="addCode">
                                    <div class="col-sm-5">
                                        <v-select class="form-control" v-model="selectedProblem.newCode.selectedCode" 
                                            :options="codesForSelect" :class="{ error: codeHasBeenSelectedBefore }" required></v-select>
                                    </div>
                                    <div class="col-sm-5">
                                        <input class="form-control" v-model="selectedProblem.newCode.code" placeholder="Code" required />
                                    </div>
                                    <div class="col-sm-2 text-right">
                                        <loader class="absolute" v-if="loaders.addCode"></loader>
                                        <input type="submit" class="btn btn-secondary selected margin-0" value="Add" 
                                            :disabled="!selectedProblem.newCode.code || !(selectedProblem.newCode.selectedCode || {}).value || codeHasBeenSelectedBefore" />
                                    </div>
                                </form>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template slot="footer">
            <label class="label label-success font-14 pointer bg-gray" @click="switchToFullConditionsModal">Switch to Non-Care Management Conditions</label>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'
    import VueSelect from 'vue-select'
    import VueComplete from 'v-complete'

    export default {
        name: 'care-areas-modal',
        props: {
            'patient-id': String,
            problems: Array
        },
        components: {
            'modal': Modal,
            'v-select': VueSelect,
            'v-complete': VueComplete
        },
        computed: {
            patientHasSelectedProblem() {
                return this.problems.map(problem => problem.id).indexOf(this.selectedCpmProblemId) >= 0
            },
            cpmProblemsForSelect() {
                return this.cpmProblems.map(p => ({ label: p.name, value: p.id }))
            },
            cpmProblemsForAutoComplete() {
                return this.cpmProblems.map(p => ({ name: p.name, id: p.id }))
            },
            codeHasBeenSelectedBefore() {
                return !!this.selectedProblem.codes.find(code => code.problem_code_system_id === (this.selectedProblem.newCode.selectedCode || {}).value)
            },
            codesForSelect() {
                return this.codes.map(p => ({ label: p.name, value: p.id }))
            }
        },
        data() {
            return {
                selectedProblem: null,
                selectedInstruction: null,
                cpmProblems: [],
                newCpmProblem: null,
                newProblem: {
                    name: '',
                    problem: '',
                    problem_id: null,
                    is_monitored: 0
                },
                selectedCpmProblemId: null,
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
                codes: []
            }
        },
        methods: {
            select(index) {
                this.selectedProblem = (index >= 0) ? this.problems[index] : null
            },
            addInstruction(e) {
                e.preventDefault()
                if (((this.selectedProblem || {}).instruction || {}).name) {
                    this.loaders.addInstruction = true
                    return this.axios.post(rootUrl(`api/problems/instructions`), { name: ((this.selectedProblem || {}).instruction || {}).name }).then(response => {
                        console.log('care-areas:add-instruction', response.data)
                        return this.addInstructionToCpmProblem(response.data)
                    }).catch(err => {
                        console.error('care-areas:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addInstructionToCpmProblem(instruction) {
                if (((this.selectedProblem || {}).instruction || {}).name) {
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/cpm/${this.selectedProblem.id}/instructions`), { instructionId: instruction.id }).then(response => {
                        console.log('care-areas:add-instruction-to-problem', response.data)
                        this.selectedProblem.instruction = instruction
                        this.loaders.addInstruction = false
                        Event.$emit('care-areas:problems', this.problems)
                    }).catch(err => {
                        console.error('care-areas:add-instruction-to-problem', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addCpmProblem() {
                if (this.newCpmProblem && this.newCpmProblem.value) {
                    this.loaders.addProblem = true
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems`), { cpmProblemId: this.newCpmProblem.value }).then(response => {
                        console.log('care-areas:add-problem', response.data)
                        this.loaders.addProblem = false
                        Event.$emit('care-areas:problems', response.data)
                    }).catch(err => {
                        console.error('care-areas:add-problem', err)
                        this.loaders.addProblem = false
                    })
                }
            },
            removeCpmProblem() {
                if (this.selectedProblem && confirm('Are you sure you want to remove this problem?')) {
                    this.loaders.removeProblem = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/problems/cpm/${this.selectedProblem.id}`)).then(response => {
                        console.log('care-areas:remove-problems', response.data)
                        this.loaders.removeProblem = false
                        this.selectedProblem = null
                        Event.$emit('care-areas:problems', response.data)
                    }).catch(err => {
                        console.error('care-areas:remove-problems', err)
                        this.loaders.removeProblem = false
                    })
                }
            },
            getCpmProblems(page = 1) {
                if (page === 1) {
                    this.cpmProblems = []
                }
                return this.axios.get(rootUrl(`api/problems/cpm?page=${page}`)).then(response => {
                    console.log('care-areas:get-cpm-problems', response.data)
                    const pageInfo = response.data
                    if (pageInfo.next_page_url) {
                        return this.getCpmProblems(page + 1)
                    }
                    else {
                        this.cpmProblems = this.cpmProblems.concat(pageInfo.data)
                    }
                }).catch(err => {
                    console.error('care-areas:get-cpm-problems', err)
                })
            },
            selectInstruction(index) {
                if (!this.loaders.removeInstruction) {
                    this.selectedInstruction = this.selectedProblem.instructions[index]
                }
            },
            editCcdProblem(e) {
                e.preventDefault()
                this.loaders.editProblem = true
                return this.axios.put(rootUrl(`api/patients/${this.patientId}/problems/ccd/${this.selectedProblem.id}`), { name: this.selectedProblem.name, cpm_problem_id: this.selectedProblem.cpm.value }).then(response => {
                    console.log('full-conditions:edit', response.data)
                    this.loaders.editProblem = false
                    Event.$emit('full-conditions:edit', response.data)
                }).catch(err => {
                    console.error('full-conditions:edit', err)
                    this.loaders.editProblem = false
                })
            },
            switchToFullConditionsModal() {
                Event.$emit('modal-care-areas:hide')
                Event.$emit('modal-full-conditions:show')
            },
            getSystemCodes() {
                return this.axios.get(rootUrl(`api/problems/codes`)).then(response => {
                    console.log('full-conditions:get-system-codes', response.data)
                    this.codes = response.data
                }).catch(err => {
                    console.error('full-conditions:get-system-codes', err)
                })
            },
            addCode(e) {
                e.preventDefault()
                this.loaders.addCode = true
                return this.axios.post(rootUrl(`api/problems/codes`), { 
                                problem_id: this.selectedProblem.id, 
                                problem_code_system_id: this.selectedProblem.newCode.selectedCode.value,
                                code: this.selectedProblem.newCode.code 
                            }).then(response => {
                    console.log('full-conditions:add-code', response.data)
                    this.loaders.addCode = false
                    Event.$emit('full-conditions:add-code', response.data)
                }).catch(err => {
                    console.error('full-conditions:add-code', err)
                    this.loaders.addCode = false
                })
            },
            removeCode(problem_id, id) {
                this.loaders.removeCode = true
                return this.axios.delete(rootUrl(`api/problems/codes/${id}`)).then(response => {
                    console.log('full-conditions:remove-code', response.data)
                    this.loaders.removeCode = false
                    Event.$emit('full-conditions:remove-code', problem_id, id)
                }).catch(err => {
                    console.error('full-conditions:remove-code', err)
                    this.loaders.removeCode = false
                })
            },
            getProblemAutoCompleteTemplate(item) {
                return (item || {}).name
            }
        },
        mounted() {
            this.getCpmProblems()
            this.getSystemCodes()
        }
    }
</script>

<style>
    .modal-care-areas .modal-container {
        width: 700px;
    }

    .modal-care-areas .modal-footer {
        padding: 0px;
    }

    .btn.btn-secondary {
        background-color: #ddd;
        padding: 10 20 10 20;
        margin-right: 15px; 
        margin-bottom: 5px;
    }

    .btn.btn-danger {
        background-color: #d9534f;
    }

    .btn.btn-secondary.selected, .list-group-item.selected {
        background: #47beab;
        color: white;
    }

    .list-group-item.disabled {
        background: #ddd;
    }

    .top-20 {
        margin-top: 20px
    }

    input[type='button'].right-0 {
        margin-right: 0px;
    }

    select.error, select.error:focus {
        border: 1px solid red;
    }

    .list-group {
        font-size: 14px;
    }

    .problem-container {
        overflow-x: scroll;
    }

    .problem-buttons {
        width: 2000px;
    }

    .modal-care-areas .instructions {
        overflow-y: scroll;
        max-height: 300px;
    }

    .modal-care-areas .instruction-add {
        padding: 5 20 5 20;
        margin-top: 2px;
        margin-left: -25px;
        font-size: 11px;
        line-height: 1;
    }

    .modal-care-areas .problem-remove {
        margin: 0 -15 5 0;
        padding: 2 7 2 7;
    }

    .absolute {
        position: absolute;
    }

    .loader.absolute {
        z-index: 1;
        right: -20px;
        height: 25px;
        width: 25px;
        top: 5px;
    }

    .list-group-item .delete {
        right: 4px;
        top: 9px;
        border-radius: 25px;
        background: white;
        color: #47beab;
        border-color: #47beab;
        padding: 2px 7px;
        font-size: 12px;
        display: none;
    }

    .list-group-item.selected:first-of-type .delete {
        display: inline-block;
    }

    .problem-button span.delete {
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

    .problem-button.selected span.delete {
        display: inline-block;
    }

    button.problem-button div.loader.absolute {
        right: -13px;
        top: 15px;
    }

    .bg-gray {
        background-color: #ddd;
        color: black;
    }

    .height-200 {
        height: 200px !important;
        width: 100% !important;
    }

    .modal-care-areas .dropdown-toggle.clearfix {
        border: none !important;
    }

    .modal-care-areas .dropdown.v-select.form-control {
        padding: 0;
    }

    .margin-0 {
        margin: 0px !important;
    }

    .padding-0 {
        padding: 0px !important;
    }
</style>