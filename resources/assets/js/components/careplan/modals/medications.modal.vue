<template>
    <modal name="medications" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary medication-button" :class="{ selected: selectedMedication && (selectedMedication.id === medication.id) }" 
                                v-for="(medication, index) in medications" :key="index" @click="select(index)">
                            {{medication.name}}
                            <span class="delete" title="remove this cpm medication" @click="removeMedication">x</span>
                            <loader class="absolute" v-if="loaders.removeMedication && selectedMedication && (selectedMedication.id === medication.id)"></loader>
                        </button>
                        <input type="button" class="btn btn-secondary" :class="{ selected: !selectedMedication || !selectedMedication.id }" value="+" @click="select(-1)" />
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
            medications: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
        },
        data() {
            return {
                selectedMedication: null,
                loaders: {

                }
            }
        },
        methods: {
            select(index) {
                this.selectedMedication = (index >= 0) ? this.medications[index] : null
            },
            removeMedication() {

            }
        },
        mounted() {
            
        }
    }
</script>

<style>

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
</style>