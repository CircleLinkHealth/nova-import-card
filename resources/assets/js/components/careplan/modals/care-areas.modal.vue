<template>
    <modal name="care-areas" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
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
                        <option :value="null">Select a problem</option>
                        <option v-for="(problem, index) in cpmProblems" :key="index" :value="problem.id">{{problem.name}}</option>
                    </select>
                    <div class="text-right top-20">
                        <loader v-if="loaders.addProblem"></loader>
                        <input type="button" class="btn btn-secondary right-0 selected" value="Add" @click="addProblem" :disabled="!selectedCpmProblemId || patientHasSelectedProblem" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <div class="row top-20">
                        <form @submit="addInstruction">
                            <div class="col-sm-11">
                                <input class="form-control" v-model="newInstruction" placeholder="Add New Instruction" required />
                            </div>
                            <div class="col-sm-1">
                                <loader class="absolute" v-if="loaders.addInstruction"></loader>
                                <input type="submit" class="btn btn-secondary right-0 instruction-add selected" value="+" 
                                    title="add this instruction for this cpm problem" 
                                    :disabled="!newInstruction || newInstruction.length === 0" />
                            </div>
                        </form>
                    </div>
                    <div class="instructions top-20">
                         <div v-for="(instruction, index) in selectedProblem.instructions" :key="index">
                            <ol class="list-group" v-for="(instructionChunk, chunkIndex) in instruction.name.split('\n')" 
                                @click="selectInstruction(index)" :key="chunkIndex">
                                <li class="list-group-item" v-if="instructionChunk"
                                :class="{ selected: selectedInstruction && selectedInstruction.id === instruction.id, disabled: (selectedInstruction && selectedInstruction.id === instruction.id)  && loaders.removeInstruction }">
                                    {{instructionChunk}}
                                    <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeInstructionFromProblem(index)" v-if="chunkIndex === 0" />
                                </li>
                            </ol>
                        </div>
                    </div>
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
                newInstruction: '',
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
                if (this.newInstruction && this.newInstruction.length > 0) {
                    this.loaders.addInstruction = true
                    return this.axios.post(rootUrl(`api/problems/instructions`), { name: this.newInstruction }).then(response => {
                        console.log('care-areas:add-instruction', response.data)
                        return this.addInstructionToProblem(response.data)
                    }).catch(err => {
                        console.error('care-areas:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addInstructionToProblem(instruction) {
                if (this.newInstruction && this.newInstruction.length > 0) {
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/cpm/${this.selectedProblem.id}/instructions`), { instructionId: instruction.id }).then(response => {
                        console.log('care-areas:add-instruction-to-problem', response.data)
                        this.selectedProblem.instructions.unshift(instruction)
                        this.newInstruction = ''
                        this.loaders.addInstruction = false
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
                        console.error('care-areas:remove-problems', response.data)
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
                    }).catch(err => {
                        console.error('care-areas:remove-instruction', err)
                        this.loaders.removeInstruction = false
                    })
                }
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
</style>