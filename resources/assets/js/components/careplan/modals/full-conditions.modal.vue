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
                            <div class="col-sm-11">
                                <input class="form-control" v-model="newProblem.name" placeholder="Add New Problem" required />
                            </div>
                            <div class="col-sm-1">
                                <loader class="absolute" v-if="loaders.addProblem"></loader>
                                <input type="submit" class="btn btn-secondary right-0 instruction-add selected" value="+" 
                                    title="add this problem" :disabled="newProblem.name.length === 0" />
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

    export default {
        name: 'full-conditions-modal',
        props: {
            'patient-id': String,
            problems: Array
        },
        components: {
            'modal': Modal
        },
        data() {
            return {
                newProblem: {
                    name: ''
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
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/problems/ccd`), { name: this.newProblem.name }).then(response => {
                    console.log('full-conditions:add', response.data)
                    this.loaders.addProblem = false
                    Event.$emit('full-conditions:add', response.data)
                }).catch(err => {
                    console.error('full-conditions:add', err)
                    this.loaders.addProblem = false
                })
            }
        },
        mounted() {

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