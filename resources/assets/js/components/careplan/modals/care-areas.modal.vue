<template>
    <modal name="care-areas" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="btn-group" role="group" aria-label="We are managing">
                        <input type="button" class="btn btn-secondary" :class="{ selected: selectedProblem && (selectedProblem.id === problem.id) }" 
                                v-for="(problem, index) in problems" :key="index" :value="problem.name" @click="select(index)" />
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedProblem || !selectedProblem.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedProblem">
                    <select class="form-control" v-model="selectedCpmProblemId" :class="{ error: patientHasSelectedProblem }">
                        <option :value="null">Select a problem</option>
                        <option v-for="(problem, index) in cpmProblems" :key="index" :value="problem.id">{{problem.name}}</option>
                    </select>
                    <div class="text-right top-20">
                        <input type="button" class="btn btn-secondary right-0 selected" value="Add" @click="addProblem" :disabled="!selectedCpmProblemId || patientHasSelectedProblem" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <textarea class="form-control" v-model="newInstruction" placeholder="Add New Instruction"></textarea>
                    <div class="text-right top-20">
                        <loader v-if="loaders.addInstruction"></loader>
                        <input type="button" class="btn btn-secondary right-0 selected" value="Add" @click="addInstruction" :disabled="!newInstruction || newInstruction.length === 0" />
                    </div>
                    <ol class="list-group">
                        <li class="list-group-item" v-for="(instruction, index) in selectedProblem.instructions" :key="index">{{instruction}}</li>
                    </ol>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../app.config'
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
                cpmProblems: [],
                selectedCpmProblemId: null,
                loaders: {
                    addInstruction: null
                }
            }
        },
        methods: {
            select(index) {
                this.selectedProblem = (index >= 0) ? this.problems[index] : null
            },
            addInstruction() {
                if (this.newInstruction && this.newInstruction.length > 0) {
                    this.loaders.addInstruction = true
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/cpm/${this.selectedCpmProblemId}/instructions`), { name: this.newInstruction }).then(response => {
                        console.log('care-areas:add-instruction', response.data)
                        this.selectedProblem.instructions.push(response.data)
                        this.newInstruction = ''
                        this.loaders.addInstruction = false
                    }).catch(err => {
                        console.error('care-areas:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addProblem() {
                if (this.selectedCpmProblemId) {

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
        background: #ddd;
        padding: 10 20 10 20;
        margin-right: 15px; 
    }

    .btn.btn-secondary.selected {
        background: #47beab;
        color: white;
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

</style>