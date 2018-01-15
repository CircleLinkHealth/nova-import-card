<template>
    <modal name="full-conditions" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-full-conditions">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12" :class="{ 'problem-container': problems.length > 20 }">
                    <div class="btn-group" :class="{ 'problem-buttons': problems.length > 20 }" role="group" aria-label="Full Conditions">
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
                    <div class="row top-20">
                        <form @submit="addProblem">
                            <div class="col-sm-12">
                                <input class="form-control" v-model="newProblem.name" placeholder="Add New Problem" required />
                            </div>
                            <div class="col-sm-12 top-20">
                                <v-select class="form-control" v-model="newProblem.problem" :options="cpmProblemsForSelect"></v-select>
                            </div>
                            <div class="col-sm-12 text-right top-20">
                                <loader class="absolute" v-if="loaders.addProblem"></loader>
                                <input type="submit" class="btn btn-secondary margin-0 instruction-add selected" value="+" 
                                    title="add this problem" :disabled="newProblem.name.length === 0" />
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedProblem">
                    <div class="row top-20">
                        <form @submit="editProblem">
                            <div class="col-sm-12">
                                <input class="form-control" v-model="selectedProblem.name" placeholder="Problem Name" required />
                            </div>
                            <div class="col-sm-12 top-20">
                                <v-select class="form-control" v-model="selectedProblem.cpm_id" :options="cpmProblemsForSelect"></v-select>
                            </div>
                            <div class="col-sm-12 text-right top-20">
                                <loader class="absolute" v-if="loaders.editProblem"></loader>
                                <input type="submit" class="btn btn-secondary margin-0 instruction-add selected" value="Edit" 
                                    title="Edit this problem" :disabled="newProblem.name.length === 0" />
                            </div>
                        </form>
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
    import VueSelect from 'vue-select'


    export default {
        name: 'full-conditions-modal',
        props: {
            'patient-id': String,
            problems: Array,
            cpmProblems: Array
        },
        components: {
            'modal': Modal,
            'v-select': VueSelect
        },
        computed: {
            cpmProblemsForSelect() {
                return [{ label: 'Select a CPM Problem', value: null }].concat(this.cpmProblems.map(p => ({ label: p.name, value: p.id })))
            }
        },
        data() {
            return {
                newProblem: {
                    name: '',
                    problem: null,
                    problem_id: null
                },
                selectedProblem: null,
                loaders: {
                    addProblem: null,
                    removeProblem: null
                }
            }
        },
        methods: {
            select(index) {
                this.selectedProblem = (index >= 0) ? this.problems[index] : null
            },
            removeProblem() {
                if (this.selectedProblem && confirm('Are you sure you want to remove this condition?')) {
                    this.loaders.removeProblem = true
                    const ccdId = this.selectedProblem.id
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/problems/ccd/${this.selectedProblem.id}`)).then(response => {
                        console.log('full-conditions:remove', response.data)
                        this.loaders.removeProblem = false
                        this.selectedProblem = null
                        Event.$emit('full-conditions:remove', ccdId)
                    }).catch(err => {
                        console.error('full-conditions:remove', err)
                        this.loaders.removeProblem = false
                    })
                }
            },
            addProblem(e) {
                e.preventDefault();
                this.loaders.addProblem = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/ccd`), { name: this.newProblem.name, cpm_problem_id: this.newProblem.problem.value }).then(response => {
                    console.log('full-conditions:add', response.data)
                    this.loaders.addProblem = false
                    Event.$emit('full-conditions:add', response.data)
                }).catch(err => {
                    console.error('full-conditions:add', err)
                    this.loaders.addProblem = false
                })
            },
            editProblem(e) {
                e.preventDefault()
            }
        },
        mounted() {
            this.newProblem.problem = this.cpmProblemsForSelect[0]
        }
    }
</script>

<style>
    .modal-full-conditions .modal-container {
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
        padding-top: 10px;
    }

    .problem-buttons {
        width: 2000px;
    }

    .modal-full-conditions .instructions {
        overflow-y: scroll;
        max-height: 300px;
    }

    .modal-full-conditions .instruction-add {
        padding: 5 20 5 20;
        margin-top: 2px;
        margin-left: -25px;
    }

    .modal-full-conditions .problem-remove {
        margin: 0 -15 5 0;
        padding: 2 7 2 7;
    }

    .modal-full-conditions .dropdown-toggle.clearfix {
        border: none !important;
    }

    .modal-full-conditions .dropdown.v-select.form-control {
        padding: 0;
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

    input[type="submit"].margin-0 {
        margin: 0px;
    }
</style>