<template>
    <modal name="medications" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true"
           class-name="modal-medications">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12 pad-top-10" :class="{ 'medication-container': isExtendedView }">
                    <div class="btn-group" role="group" :class="{ 'medication-buttons': isExtendedView }">
                        <div class="btn btn-secondary medication-button"
                             :class="{ selected: selectedMedication && (selectedMedication.id === medication.id) }"
                             v-for="(medication, index) in medications" :key="index" @click="select(index)">
                            {{medication.title()}}
                            <span class="delete" title="remove this cpm medication" @click="removeMedication">x</span>
                            <loader class="absolute"
                                    v-if="loaders.removeMedication && selectedMedication && (selectedMedication.id === medication.id)"></loader>
                        </div>
                        <input type="button" class="btn btn-secondary"
                               :class="{ selected: !selectedMedication || !selectedMedication.id }" value="+"
                               @click="select(-1)"/>
                    </div>
                </div>
                <div class="col-sm-12" v-if="selectedMedication">
                    <form @submit="editMedication">
                        <div class="form-group">
                            <div class="top-20">
                                <bootstrap-toggle v-model="selectedMedication.activeBool"
                                                  :options="{ on: 'Active', off: 'Inactive' }" :disabled="false"/>
                            </div>
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter a title"
                                       v-model="selectedMedication.name" required/>
                            </div>
                            <div class="top-20">
                                <v-select class="form-control" v-model="selectedMedication.groupName"
                                          :value="selectedMedication.medication_group_id"
                                          :options="groupsForSelect"></v-select>
                            </div>
                            <div class="top-20">
                                <textarea class="form-control" placeholder="Enter a description"
                                          v-model="selectedMedication.sig"></textarea>
                            </div>
                            <div class="top-20 text-right">
                                <loader v-if="loaders.editMedication"></loader>
                                <button class="btn btn-secondary selected">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12" v-if="!selectedMedication">
                    <form @submit="addMedication">
                        <div class="form-group">
                            <div class="top-20">
                                <bootstrap-toggle v-model="newMedication.activeBool"
                                                  :options="{ on: 'Active', off: 'Inactive' }" :disabled="false"/>
                            </div>
                            <div class="top-20">
                                <input type="text" class="form-control color-black" placeholder="Enter a title"
                                       v-model="newMedication.name" required/>
                            </div>
                            <div class="top-20">
                                <v-select class="form-control" v-model="newMedication.group"
                                          :options="groupsForSelect"></v-select>
                            </div>
                            <div class="top-20">
                                <textarea class="form-control" placeholder="Enter a description"
                                          v-model="newMedication.sig"></textarea>
                            </div>
                            <div class="top-20 text-right">
                                <loader v-if="loaders.addMedication"></loader>
                                <button :disabled="actionDisabled" class="btn btn-secondary selected">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import {Event} from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/admin/common/modal'
    import VueSelect from 'vue-select'
    import BootstrapToggle from 'vue-bootstrap-toggle';

    export default {
        name: 'medications-modal',
        props: {
            'patient-id': String,
            medications: Array,
            groups: Array
        },
        components: {
            'modal': Modal,
            'v-select': VueSelect,
            BootstrapToggle
        },
        computed: {
            isExtendedView() {
                return this.medications.length > 12
            },
            groupsForSelect() {
                return this.groups.map(group => ({label: group.name, value: group.id}))
            },
            actionDisabled() {
                return this.loaders.addMedication || this.loaders.removeMedication || this.loaders.editMedication;
            }
        },
        data() {
            return {
                newMedication: {
                    activeBool: true,
                    name: null,
                    sig: null,
                    group: 'Select a Medication Type',
                    medication_group_id: null
                },
                selectedMedication: null,
                loaders: {
                    addMedication: false,
                    removeMedication: false,
                    editMedication: false
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
                this.loaders.editMedication = true
                return this.axios.put(rootUrl(`api/patients/${this.patientId}/medication/${this.selectedMedication.id}`), {
                    active: this.selectedMedication.activeBool,
                    name: this.selectedMedication.name,
                    sig: this.selectedMedication.sig,
                    medication_group_id: (this.selectedMedication.groupName || {}).value || this.selectedMedication.medication_group_id
                }).then(response => {
                    console.log('medication:edit', response.data)
                    this.loaders.editMedication = false
                    Event.$emit('medication:edit', response.data)
                }).catch(err => {
                    console.error('medication:edit', err)
                    this.loaders.editMedication = false
                })
            },
            addMedication(e) {

                e.preventDefault();

                if (this.actionDisabled) {
                    return;
                }

                this.loaders.addMedication = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/medication`), {
                    active: this.newMedication.activeBool,
                    name: this.newMedication.name,
                    sig: this.newMedication.sig,
                    medication_group_id: (this.newMedication.group || {}).value
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
    .modal-medications .modal-container {
        width: 900px;
        max-height: 100vh;
        overflow-y: auto;
    }

    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-medications .modal-container {
            width: 95%;
        }
    }

    .medication-container {
        overflow-x: scroll;
    }

    .medication-buttons {
        width: auto;
    }

    .btn.btn-secondary.medication-button {
        max-width: 250px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
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
        top: 0px;
        right: 0px;
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

    .modal-medications .dropdown-toggle.clearfix {
        border: none !important;
    }

    .modal-medications .dropdown.v-select.form-control {
        padding: 0;
    }

    .v-select .dropdown-toggle{
        border: none;
    }

    .v-select .selected-tag{
        display: block;
    }

    .v-select .vs__actions{
        padding-bottom: 1%;
    }
</style>