<template>
    <modal name="medications" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12 pad-top-10" :class="{ 'medication-container': isExtendedView }">
                    <div class="btn-group" role="group" :class="{ 'medication-buttons': isExtendedView }">
                        <button class="btn btn-secondary medication-button" :class="{ selected: selectedMedication && (selectedMedication.id === medication.id) }" 
                                v-for="(medication, index) in medications" :key="index" @click="select(index)">
                            {{medication.title()}}
                            <span class="delete" title="remove this cpm medication" @click="removeMedication">x</span>
                            <loader class="absolute" v-if="loaders.removeMedication && selectedMedication && (selectedMedication.id === medication.id)"></loader>
                        </button>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedMedication || !selectedMedication.id }" value="+" @click="select(-1)" />
                    </div>
                </div>
                <div class="col-sm-12" v-if="selectedMedication">
                    <form @submit="editMedication">
                        <div class="form-group">
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter a title" v-model="selectedMedication.name" required />
                            </div>
                            <div class="top-20">
                                <textarea class="form-control" placeholder="Enter a description" v-model="selectedMedication.sig" required></textarea>
                            </div>
                            <div class="top-20 text-right">
                                <button class="btn btn-secondary selected">Edit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12" v-if="!selectedMedication">
                    <form @submit="addMedication">
                        <div class="form-group">
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter a title" v-model="newMedication.name" required />
                            </div>
                            <div class="top-20">
                                <textarea class="form-control" placeholder="Enter a description" v-model="newMedication.sig" required></textarea>
                            </div>
                            <div class="top-20 text-right">
                                <loader v-if="loaders.addMedication"></loader>
                                <button class="btn btn-secondary selected">Create</button>
                            </div>
                        </div>
                    </form>
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
            medications: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            isExtendedView() {
                return this.medications.length > 12
            }
        },
        data() {
            return {
                newMedication: {
                    name: null,
                    sig: null
                },
                selectedMedication: null,
                loaders: {
                    addMedication: null,
                    removeMedication: null
                }
            }
        },
        methods: {
            select(index) {
                this.selectedMedication = (index >= 0) ? this.medications[index] : null
            },
            reset() {
                this.newMedication.name = ''
                this.newMedication.sig = ''
            },
            removeMedication() {
                if (this.selectedMedication && confirm('Are you sure you want to remove this medication?')) {
                    const medicationId = this.selectedMedication.id
                    this.loaders.removeMedication = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/medication/${this.selectedMedication.id}`)).then(response => {
                        console.log('medication:remove-medication', response.data)
                        this.loaders.removeMedication = false
                        this.selectedMedication = null
                        Event.$emit('medication:remove', medicationId)
                    }).catch(err => {
                        console.error('care-areas:remove-medication', err)
                        this.loaders.removeMedication = false
                    })
                }
            },
            editMedication(e) {
                e.preventDefault()

            },
            addMedication(e) {
                e.preventDefault()
                this.loaders.addMedication = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/medication`), { 
                            name: this.newMedication.name, 
                            sig: this.newMedication.sig 
                    }).then(response => {
                        console.log('medication:add', response.data)
                        this.loaders.addMedication = false
                        Event.$emit('medication:add', response.data)
                        this.reset()
                    }).catch(err => {
                        console.error('medication:add', err)
                        this.loaders.addMedication = false
                    })
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .medication-container {
        overflow-x: scroll;
    }

    .medication-buttons {
        width: 2000px;
    }

    .medication-button span.delete {
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

    .medication-button.selected span.delete {
        display: inline-block;
    }

    button.medication-button div.loader.absolute {
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