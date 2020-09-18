<template>
    <modal name="full-conditions" :no-title="true" :no-cancel="true" :no-buttons="true" class-name="modal-full-conditions">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12" :class="{ 'problem-container': problems.length > 20 }">
                    <div class="btn-group" :class="{ 'problem-buttons': problems.length > 20 }" role="group" aria-label="Full Conditions">
                        <div class="btn btn-secondary problem-button" :class="{ selected: selectedProblem && (selectedProblem.id === problem.id) }"
                                v-for="(problem, index) in problems" :key="index" @click="select(index)">
                            {{problem.name}}
                            <span class="delete" title="remove this cpm problem" @click="removeProblem">x</span>
                            <loader class="absolute" v-if="loaders.removeProblem && selectedProblem && (selectedProblem.id === problem.id)"></loader>
                        </div>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedProblem || !selectedProblem.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="!selectedProblem">
                    <div class="row top-20">
                        <form @submit="addProblem">
                            <div class="col-sm-12">
                                <input class="form-control" v-model="newProblem.name" placeholder="Add New CCD Problem" required />
                            </div>
                            <div class="col-sm-12 top-20">
                                <v-select class="form-control" v-model="newProblem.problem" placeholder="Select a CPM Problem" :options="cpmProblemsForSelect"></v-select>
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
                            <div class="col-sm-12">
                                <form @submit="editProblem">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <input class="form-control" v-model="selectedProblem.name" placeholder="Problem Name" required />
                                        </div>
                                        <div class="col-sm-5">
                                            <v-select class="form-control" v-model="selectedProblem.cpm" :value="selectedProblem.cpm_id"
                                                :options="cpmProblemsForSelect" required></v-select>
                                        </div>
                                        <div class="col-sm-2 text-right">
                                            <loader class="absolute" v-if="loaders.editProblem"></loader>
                                            <input type="submit" class="btn btn-secondary margin-0 instruction-add selected" value="Edit"
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
            <label class="label label-success font-14 pointer bg-gray" @click="switchToCareAreasModal">Switch to Care Management Conditions</label>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/common/modal'
    import VueSelect from 'vue-select'
    import CareplanMixin from '../mixins/careplan.mixin'

    export default {
        name: 'full-conditions-modal',
        mixins: [CareplanMixin],
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
                return this.cpmProblems.map(p => ({ label: p.name, value: p.id }))
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
                newProblem: {
                    name: '',
                    problem: 'Select a CPM Problem',
                    problem_id: null
                },
                selectedProblem: null,
                loaders: {
                    addProblem: null,
                    removeProblem: null,
                    editProblem: null,
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
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/ccd`), { name: this.newProblem.name, cpm_problem_id: (this.newProblem.problem || {}).value }).then(response => {
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
            switchToCareAreasModal() {
                Event.$emit('modal-full-conditions:hide')
                Event.$emit('modal-care-areas:show')
            }
        },
        mounted() {
            this.newProblem.problem = this.cpmProblemsForSelect[0]
            this.getSystemCodes()
        }
    }
</script>

<style>
    .modal-full-conditions .modal-container {
        width: 700px;
    }

    .modal-full-conditions .modal-footer {
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
        padding-top: 10px;
    }

    .problem-buttons {
        width: 100%;
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

    input[type].margin-0 {
        margin: 0px;
    }

    div.v-select.error {
        border: 1px solid red;
    }

    ul.font-16 {
        font-size: 16px;
    }

    .bg-gray {
        background-color: #ddd;
        color: black;
    }
</style>