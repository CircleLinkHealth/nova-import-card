<template>
    <modal name="misc" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-misc">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12" v-if="!selectedMisc">
                    <form @submit="addMisc">
                        <div class="form-group">
                            <div class="top-20">
                                <div class="font-14">
                                    <notifications></notifications>
                                </div>
                                <select class="form-control color-black" v-model="newMisc.id" :class="{ error: patientHasSelectedMisc }" required>
                                    <option :value="null">Select a Service</option>
                                    <option v-for="(misc, index) in miscs" :key="index" :value="misc.id">{{misc.name}}</option>
                                </select>
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
    import EventBus from '../../../admin/time-tracker/comps/event-bus'
    import NotificationsComponent from '../../notifications'

    export default {
        name: 'misc-modal',
        props: ['patient-id'],
        components: {
            'modal': Modal,
            'notifications': NotificationsComponent
        },
        data() {
            return {
                newMisc: {
                    id: null
                },
                newInstruction: '',
                selectedInstruction: null,
                selectedMiscs: [],
                selectedMiscName: null,
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
            },
            selectedMisc() {
                return this.selectedMiscs.find(misc => misc.name == this.selectedMiscName)
            }
        },
        methods: {
            select(name) {
                this.selectedMiscName = name
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
            getSelectedMisc() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/misc`)).then(response => {
                    console.log('misc:get-selected-misc', response.data)
                    this.selectedMiscs = response.data.map(this.setupMisc).filter(misc => misc.name != 'Allergies' && misc.name != 'Medication List' && misc.name != 'Full Conditions List' && misc.name != 'Appointments')
                }).catch(err => {
                    console.error('misc:get-selected-misc', err)
                })
            },
            getMisc() {
                return this.axios.get(rootUrl('api/misc')).then(response => {
                    console.log('misc:get-misc', response.data)
                    this.miscs = response.data.map(this.setupMisc).filter(misc => misc.name != 'Allergies' && misc.name != 'Medication List' && misc.name != 'Full Conditions List' && misc.name != 'Appointments')
                }).catch(err => {
                    console.error('misc:get-misc', err)
                })
            },
            addMisc(e, miscId) {
                if (e) e.preventDefault()
                this.loaders.addMisc = true
                miscId = miscId || this.newMisc.id
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/misc`), { miscId }).then(response => {
                    console.log('misc:add', response.data)
                    Event.$emit('misc:select', this.miscs.find(misc => misc.id == miscId))
                    this.newMisc.id = null
                    this.select(this.selectedMiscs.length - 1)
                    this.loaders.addMisc = false
                }).catch(err => {
                    console.error('misc:remove', err)
                    this.loaders.addMisc = false
                })
            },
            removeMisc(e) {
                if (this.selectedMisc && confirm('Are you sure you want to remove this misc?')) {
                    const miscId = this.selectedMisc.id
                    this.loaders.removeMisc = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/misc/${this.selectedMisc.id}`)).then(response => {
                        console.log('misc:remove', response.data)
                        this.loaders.removeMisc = false
                        this.selectedMiscName = null
                        this.selectedMiscs.splice(this.selectedMiscs.findIndex(misc => misc.id == miscId), 1)
                        Event.$emit('misc:remove', miscId)
                    }).catch(err => {
                        console.error('misc:remove', err)
                        this.loaders.removeMisc = false
                    })
                }
            },
            removeInstructionFromProblem(index) {
                if (this.selectedInstruction && this.selectedMisc && confirm('Are you sure you want to delete this instruction?')) {
                    this.loaders.removeInstruction = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/misc/${this.selectedMisc.id}/instructions/${this.selectedInstruction.id}`)).then((response) => {
                        console.log('misc:remove-instruction', response.data)
                        this.loaders.removeInstruction = false
                        this.selectedMisc.instructions.splice(index, 1)
                        this.selectedInstruction = null
                        Event.$emit('misc:change', this.selectedMisc)
                    }).catch(err => {
                        console.error('misc:remove-instruction', err)
                        this.loaders.removeInstruction = false
                    })
                }
            },
            addInstruction(e) {
                e.preventDefault()
                if (this.newInstruction && this.newInstruction.length > 0) {
                    this.loaders.addInstruction = true
                    return this.axios.post(rootUrl(`api/problems/instructions`), { name: this.newInstruction }).then(response => {
                        console.log('misc:add-instruction', response.data)
                        return this.addInstructionToMisc(response.data)
                    }).catch(err => {
                        console.error('misc:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
                }
            },
            addInstructionToMisc(instruction) {
               return this.axios.post(rootUrl(`api/patients/${this.patientId}/misc/${this.selectedMisc.id}/instructions`), { instructionId: instruction.id }).then(response => {
                        console.log('misc:add-instruction', response.data)
                        this.selectedMisc.instructions.unshift(instruction)
                        this.newInstruction = ''
                        this.loaders.addInstruction = false
                        Event.$emit('misc:change', this.selectedMisc)
                    }).catch(err => {
                        console.error('misc:add-instruction', err)
                        this.loaders.addInstruction = false
                    })
            }
        },
        mounted() {
            Promise.all([this.getMisc(), this.getSelectedMisc()]).then(() => {
                this.miscs.filter(m => !this.selectedMiscs.find(sm => sm.id == m.id)).forEach(misc => {
                    this.addMisc(null, misc.id)
                })
            })

            Event.$on('misc:select', (misc) => {
                if (misc && !this.selectedMiscs.find(m => m.id == misc.id)) {
                    this.selectedMiscs.push(misc)
                }
            })

            Event.$on('misc:page', (page) => {
                this.selectedMiscName = page
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

    .font-14 {
        font-size: 14px
    }
</style>