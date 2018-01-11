<template>
    <modal name="misc" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-misc">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12 pad-top-10">
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary misc-button" :class="{ selected: selectedMisc && selectedMisc.id == misc.id }"
                                v-for="(misc, index) in selectedMiscs" :key="index" @click="select(index)">
                            {{misc.name}}
                            <span class="delete" title="remove this cpm misc" @click="removeMisc">x</span>
                            <loader class="absolute" v-if="loaders.removeMisc && selectedMisc && (selectedMisc.id === misc.id)"></loader>
                        </button>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedMisc }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12" v-if="!selectedMisc">
                    <form @submit="addMisc">
                        <div class="form-group">
                            <div class="top-20">
                                <select class="form-control color-black" v-model="newMisc.id" :class="{ error: patientHasSelectedMisc }" required>
                                    <option :value="null">Select an item</option>
                                    <option v-for="(misc, index) in miscs" :key="index" :value="misc.id">{{misc.name}}</option>
                                </select>
                            </div>
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter an Instruction" v-model="newMisc.instruction" required />
                            </div>
                            <div class="top-20 text-right">
                                <loader v-if="loaders.addMisc"></loader>
                                <button class="btn btn-secondary selected" :disabled="cantCreateMisc">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12 top-20" v-if="selectedMisc">
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
                         <div v-for="(instruction, index) in selectedMisc.instructions" :key="index">
                            <ol class="list-group" v-for="(instructionChunk, chunkIndex) in instruction.name.split('\n')" 
                                @click="selectInstruction(index)" :key="chunkIndex">
                                <li class="list-group-item pointer" v-if="instructionChunk"
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
        name: 'misc-modal',
        props: ['patient-id'],
        components: {
            'modal': Modal
        },
        data() {
            return {
                newMisc: {
                    id: null,
                    instruction: ''
                },
                newInstruction: '',
                selectedInstruction: null,
                selectedMisc: null,
                selectedMiscs: [],
                miscs: [],
                loaders: {
                    addMisc: null,
                    removeMisc: null,
                    addInstruction: null,
                    removeInstruction: null
                }
            }
        },
        computed: {
            cantCreateMisc() {
                return !this.newMisc.id || this.patientHasSelectedMisc
            },
            patientHasSelectedMisc() {
                return !!this.selectedMiscs.find(misc => misc.id == this.newMisc.id)
            }
        },
        methods: {
            select(index) {
                this.selectedMisc = (index >= 0) ? Object.assign({}, this.selectedMiscs[index]) : null
            },
            selectInstruction(index) {
                if (!this.loaders.removeInstruction) {
                    this.selectedInstruction = this.selectedMisc.instructions[index]
                }
            },
            reset() {
                this.newMisc.name = ''
            },
            setupMisc(misc) {
                misc.instructions = []
                return misc
            },
            getMisc() {
                return this.axios.get(rootUrl('api/misc')).then(response => {
                    console.log('misc:get-misc', response.data)
                    this.miscs = response.data.map(this.setupMisc)
                }).catch(err => {
                    console.error('misc:get-misc', err)
                })
            },
            addMisc(e) {
                e.preventDefault()
            },
            removeMisc(e) {
                if (this.selectedMisc && confirm('Are you sure you want to remove this misc?')) {
                    
                }
            },
            removeInstructionFromProblem(index) {

            },
            addInstruction(e) {

            }
        },
        mounted() {
            this.getMisc()

            Event.$on('misc:select', (misc) => {
                if (misc && !this.selectedMiscs.find(m => m.id == misc.id)) {
                    this.selectedMiscs.push(misc)
                }
            })
        }
    }
</script>

<style>
    .modal-misc .modal-container {
        width: 700px;
    }

    .misc-button span.delete {
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

    .misc-button.selected span.delete {
        display: inline-block;
    }

    button.misc-button div.loader.absolute {
        right: -13px;
        top: 15px;
    }

    .pad-top-10 {
        padding-top: 10px;
    }

    input.color-black {
        color: black;
    }
</style>