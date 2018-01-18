<template>
    <modal name="care-areas" :no-title="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12" :class="{ 'problem-container': problems.length > 20 }">
                    <div class="btn-group" :class="{ 'problem-buttons': problems.length > 20 }" role="group" aria-label="We are managing">
                        <button class="btn btn-secondary problem-button" :class="{ selected: selectedProblem && (selectedProblem.id === problem.id) }" 
                                v-for="(problem, index) in problems" :key="index" @click="select(index)">
                            {{problem.name}}
                            <span class="delete" title="remove this cpm problem" @click="removeProblem">x</span>
                            <loader class="absolute" v-if="loaders.removeProblem && selectedProblem && (selectedProblem.id === problem.id)"></loader>
                        </button>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedProblem || !selectedProblem.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedProblem">
                    <select class="form-control" v-model="selectedCpmProblemId" :class="{ error: patientHasSelectedProblem }">
                        <option :value="null">Add condition for Care Management</option>
                        <option v-for="(problem, index) in cpmProblems" :key="index" :value="problem.id">{{problem.name}}</option>
                    </select>
                    <div class="text-right top-20">
                        <loader v-if="loaders.addProblem"></loader>
                        <input type="button" class="btn btn-secondary right-0 selected" value="Add" @click="addProblem" :disabled="!selectedCpmProblemId || patientHasSelectedProblem" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <div class="row instructions top-20">
                        <form @submit="addInstruction">
                            <div class="col-sm-12">
                                <textarea class="form-control free-note height-200" v-model="selectedProblem.instruction.name" placeholder="Enter Instructions"></textarea>
                            </div>
                            <div class="col-sm-12 text-right top-20">
                                <loader class="absolute" v-if="loaders.addInstruction"></loader>
                                <input type="submit" class="btn btn-secondary right-0 instruction-add selected" value="Save" 
                                    title="add this instruction for this cpm problem" 
                                    :disabled="!selectedProblem.instruction.name" />
                            </div>
                        </form>
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

    export default {
        name: 'care-areas-modal',
        props: {
            'patient-id': String,
            problems: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            patientHasSelectedProblem() {
                return this.problems.map(problem => problem.id).indexOf(this.selectedCpmProblemId) >= 0
            }
        },
        data() {
            return {
                selectedProblem: null,
                selectedInstruction: null,
                cpmProblems: [],
                selectedCpmProblemId: null,
                loaders: {
                    addInstruction: null,
                    addProblem: null,
                    removeProblem: null,
                    removeInstruction: null
                }
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
                        return this.addInstructionToProblem(response.data)
                    }).catch(err => {
                        console.error('care-areas:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addInstructionToProblem(instruction) {
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
            addProblem() {
                if (this.selectedCpmProblemId) {
                    this.loaders.addProblem = true
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems`), { cpmProblemId: this.selectedCpmProblemId }).then(response => {
                        console.log('care-areas:add-problem', response.data)
                        this.loaders.addProblem = false
                        Event.$emit('care-areas:problems', response.data)
                    }).catch(err => {
                        console.error('care-areas:add-problem', err)
                        this.loaders.addProblem = false
                    })
                }
            },
            removeProblem() {
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
            removeInstructionFromProblem(index) {
                if (this.selectedInstruction && this.selectedProblem && confirm('Are you sure you want to delete this instruction?')) {
                    this.loaders.removeInstruction = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/problems/cpm/${this.selectedProblem.id}/instructions/${this.selectedInstruction.id}`)).then((response) => {
                        console.log('care-areas:remove-instruction', response.data)
                        this.loaders.removeInstruction = false
                        this.selectedProblem.instructions.splice(index, 1)
                        this.selectedInstruction = null
                        Event.$emit('care-areas:problems', this.problems)
                    }).catch(err => {
                        console.error('care-areas:remove-instruction', err)
                        this.loaders.removeInstruction = false
                    })
                }
            },
            switchToFullConditionsModal() {
                Event.$emit('modal-care-areas:hide')
                Event.$emit('modal-full-conditions:show')
            }
        },
        mounted() {
            this.getCpmProblems();
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
</style>