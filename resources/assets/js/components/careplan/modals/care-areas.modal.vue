<template>
    <modal name="care-areas" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true"
           class-name="modal-care-areas">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12" :class="{ 'problem-container': problems.length > 12 }">
                    <div class="btn-group" :class="{ 'problem-buttons': problems.length > 12 }" role="group"
                         aria-label="We are managing">
                        <div class="btn btn-secondary problem-button"
                             :class="{ selected: selectedProblem && (selectedProblem.id === problem.id) }"
                             v-for="(problem, index) in problemsForListing" :key="index" @click="select(problem)">
                            {{problem.name || `no name (${problem.id})`}}
                            <span class="delete" title="remove this cpm problem" @click="removeProblem">x</span>
                            <loader class="absolute"
                                    v-if="loaders.removeProblem && selectedProblem && (selectedProblem.id === problem.id)"></loader>
                        </div>
                        <input type="button" class="btn btn-secondary"
                               :class="{ selected: !selectedProblem || !selectedProblem.id }" value="+"
                               @click="select(null)"/>
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedProblem">
                    <add-condition :patient-id="patientId" :problems="problems"
                                   :should-select-is-monitored="true"></add-condition>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <div class="row top-20">
                        <div class="col-sm-12">
                            <form @submit="editCcdProblem">
                                <div class="row">
                                    <div class="col-sm-12 top-20">
                                        <div style="margin-bottom: 10px"
                                             v-if="selectedProblem.instruction.name !== selectedInstruction">
                                            <input type="button"
                                                   class="btn btn-secondary margin-0 instruction-add selected"
                                                   @click="resetInstructions"
                                                   value="Change Instructions to original"/>
                                        </div>
                                        <textarea class="form-control height-200"
                                                  v-model="selectedInstruction"
                                                  placeholder="Enter Instructions"></textarea>
                                        <loader class="absolute" v-if="loaders.addInstruction"></loader>
                                        <div class="font-14 color-blue" v-if="selectedProblem.original_name">
                                            Full Name: {{ selectedProblem.original_name }} {{ (selectedProblem.count() >
                                            1) ? ` (+${selectedProblem.count() - 1})` : '' }}
                                        </div>
                                    </div>
                                    <div class="col-sm-12 top-20 text-right font-14">
                                        <div class="row">
                                            <div class="col-sm-7">
                                                <label class="color-red" v-if="selectedProblem.is_monitored">Mapped
                                                    To:</label>
                                                <select class="form-control" v-model="selectedProblem.cpm_id"
                                                        @change="updateInstructions"
                                                        v-if="selectedProblem.is_monitored">
                                                    <option :value="null">Selected a Related Condition</option>
                                                    <option v-for="problem in cpmProblemsForSelect" :key="problem.value"
                                                            :value="problem.value">{{problem.label}}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-sm-3 text-right">
                                                <label class="top-30">
                                                    <input type="checkbox" :value="true"
                                                           v-model="selectedProblem.is_monitored"> We are managing
                                                </label>
                                            </div>
                                            <div class="col-sm-2">
                                                <br>
                                                <loader class="absolute" v-if="loaders.editProblem"></loader>
                                                <input type="submit"
                                                       class="btn btn-secondary margin-0 instruction-add selected"
                                                       value="Save"
                                                       title="Edit this problem"
                                                       :disabled="(selectedProblem.name || '').length === 0 || patientHasSelectedProblem"/>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-12">
                            <collapsible>
                                <template slot="title">
                                    <h4>
                                        Problem Codes
                                    </h4>
                                </template>
                                <template>
                                    <ul class="list-group font-16 border-bottom">
                                        <li class="row list-group-item" v-for="code in selectedProblem.codes"
                                            :key="code.id">
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
                                                <input type="button" class="btn btn-danger margin-0" value="-"
                                                       @click="removeCode(selectedProblem.id, code.id)"
                                                       :disabled="!code.id"/>
                                            </div>
                                        </li>
                                        <li class="row list-group-item" v-if="selectedProblem.codes.length === 0">
                                            <center>No Codes Yet</center>
                                        </li>
                                    </ul>
                                    <div class="row">
                                        <form @submit="addCode">
                                            <div class="col-sm-5">
                                                <v-select class="form-control"
                                                          v-model="selectedProblem.newCode.selectedCode"
                                                          :options="codesForSelect"
                                                          :class="{ error: codeHasBeenSelectedBefore }"
                                                          required></v-select>
                                            </div>
                                            <div class="col-sm-5">
                                                <input class="form-control" v-model="selectedProblem.newCode.code"
                                                       placeholder="Code" required/>
                                            </div>
                                            <div class="col-sm-2 text-right">
                                                <loader class="absolute" v-if="loaders.addCode"></loader>
                                                <input type="submit" class="btn btn-secondary selected margin-0"
                                                       value="Add"
                                                       :disabled="!selectedProblem.newCode.code || !(selectedProblem.newCode.selectedCode || {}).value || codeHasBeenSelectedBefore"/>
                                            </div>
                                        </form>
                                    </div>
                                </template>
                            </collapsible>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import {Event} from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'
    import VueSelect from 'vue-select'
    import VueComplete from 'v-complete'
    import Collapsible from '../../collapsible'
    import CareplanMixin from '../mixins/careplan.mixin'
    import AddConditionMixin from '../mixins/add-condition.mixin'
    import AddCondition from '../add-condition'

    export default {
        name: 'care-areas-modal',
        props: {
            'patient-id': String,
            problems: Array
        },
        mixins: [
            CareplanMixin,
            AddConditionMixin
        ],
        components: {
            'add-condition': AddCondition,
            'modal': Modal,
            'v-select': VueSelect,
            'v-complete': VueComplete,
            'collapsible': Collapsible
        },
        computed: {
            problemsForListing() {
                return this.problems.distinct((p) => p.name)
            },
            cpmProblemsForSelect() {
                return this.getAddConditionCpmProblems().map(p => ({
                    label: p.name,
                    value: p.id
                })).sort((a, b) => a.label < b.label ? -1 : 1)
            },
            codeHasBeenSelectedBefore() {
                return !!this.selectedProblem.codes.find(code => !!code.id && code.problem_code_system_id === (this.selectedProblem.newCode.selectedCode || {}).value)
            },
            codesForSelect() {
                return this.codes.map(p => ({label: p.name, value: p.id}))
            }
        },
        data() {
            return {
                selectedProblem: null,
                selectedInstruction: null,
                newCpmProblem: null,
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
            select(problem) {
                //when '+' is selected, problem === null.
                let instruction = problem ? problem.instruction.name : problem
                this.selectedProblem = problem
                this.selectedInstruction = instruction
            },
            updateInstructions(event) {
                let cpmProblem = this.getAddConditionCpmProblems().find(problem => {
                    return problem.id == event.target.value
                })

                if (cpmProblem.instruction) {
                    this.selectedInstruction = cpmProblem.instruction.name
                } else {
                    this.selectedInstruction = null
                }
            },
            resetInstructions() {
                this.selectedInstruction = this.selectedProblem.instruction.name
            },
            removeProblem() {
                if (this.selectedProblem && confirm('Are you sure you want to remove this problem?')) {
                    this.loaders.removeProblem = true
                    const url = `api/patients/${this.patientId}/problems/${this.selectedProblem.type}/${this.selectedProblem.id}`
                    return this.axios.delete(rootUrl(url)).then(response => {
                        this.loaders.removeProblem = false
                        Event.$emit(`care-areas:remove-${this.selectedProblem.type}-problem`, this.selectedProblem.id)
                        Event.$emit('problems:updated', {})
                        this.selectedProblem = null
                        setImmediate(() => this.checkPatientBehavioralStatus())
                    }).catch(err => {
                        console.error('care-areas:remove-problems', err)
                        this.loaders.removeProblem = false
                    })
                }
            },
            selectInstruction(index) {
                if (!this.loaders.removeInstruction) {
                    this.selectedInstruction = this.selectedProblem.instructions[index]
                }
            },
            editCcdProblem(e) {
                e.preventDefault()
                this.loaders.editProblem = true
                return this.axios.put(rootUrl(`api/patients/${this.patientId}/problems/ccd/${this.selectedProblem.id}`), {
                    cpm_problem_id: this.selectedProblem.is_monitored ? this.selectedProblem.cpm_id : null,
                    is_monitored: this.selectedProblem.is_monitored,
                    icd10: this.selectedProblem.icd10,
                    instruction: this.selectedInstruction
                }).then(response => {
                    this.loaders.editProblem = false
                    Event.$emit('problems:updated', {})
                    Event.$emit('full-conditions:edit', response.data)
                    setImmediate(() => this.checkPatientBehavioralStatus())
                }).catch(err => {
                    console.error('full-conditions:edit', err)
                    this.loaders.editProblem = false
                })
            },
            switchToFullConditionsModal() {
                Event.$emit('modal-care-areas:hide')
                Event.$emit('modal-full-conditions:show')
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
            },
            /**
             * is patient BHI, CCM or BOTH?
             */
            checkPatientBehavioralStatus() {
                const problems = this.problems || [];
                const cpmProblems = this.getAddConditionCpmProblems() || [];

                const ccmCount = problems.filter(problem => {
                    if (problem.is_monitored) {
                        const cpmProblem = cpmProblems.find(cpm => cpm.id == problem.cpm_id)
                        return cpmProblem ? !cpmProblem.is_behavioral : false
                    }
                    return false
                }).length
                const bhiCount = problems.filter(problem => {
                    const cpmProblem = cpmProblems.find(cpm => cpm.id == problem.cpm_id)
                    return cpmProblem ? cpmProblem.is_behavioral : false
                }).length
                console.log('ccm', ccmCount, 'bhi', bhiCount)
                Event.$emit('careplan:bhi', {
                    hasCcm: ccmCount > 0,
                    hasBehavioral: bhiCount > 0
                })
            }
        },
    }
</script>

<style>

    .v-complete > ul {
        overflow-y: scroll;
    }

    .modal-care-areas .modal-container {
        width: 900px;
    }

    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-care-areas .modal-container {
            width: 95%;
        }
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
        margin-top: 20px;
    }

    .top-30 {
        margin-top: 30px;
    }

    .color-red {
        color: red;
    }

    input[type='button'].right-0 {
        margin-right: 0px;
    }

    input.error, input.error:focus, select.error, select.error:focus {
        border: 1px solid red;
    }

    .list-group {
        font-size: 14px;
    }

    .problem-container {
        overflow-x: scroll;
    }

    .problem-buttons {
        width: 100%;
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

    .dropdown-menu {
        background-color: white;
    }

    .modal-care-areas input[type="radio"] {
        display: inline;
    }

    .v-complete.error {
        border: 1px solid red;
    }

    input.warning {
        border: 1px solid #fa0;
    }


</style>
