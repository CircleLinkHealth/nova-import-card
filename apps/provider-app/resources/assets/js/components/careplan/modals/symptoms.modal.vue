<template>
    <modal name="symptoms" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12 pad-top-10" :class="{ 'symptom-container': isExtendedView }">
                    <div class="btn-group" role="group" :class="{ 'symptom-buttons': isExtendedView }">
                        <div class="btn btn-secondary symptom-button" 
                                v-for="(symptom, index) in symptoms" :key="index" @click="select(symptom)" 
                                :class="{ selected: symptom.selected }">
                            {{symptom.name}}
                            <span class="delete" title="remove this cpm symptom" @click="removeSymptom(symptom)">x</span>
                            <loader class="absolute" v-if="symptom.loaders.removeSymptom || symptom.loaders.addSymptom"></loader>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/common/modal'

    export default {
        name: 'symptoms-modal',
        props: {
            'patient-id': String,
            symptoms: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            isExtendedView() {
                return this.symptoms.length > 12
            }
        },
        methods: {
            select(symptom) {
                if (!symptom.selected) {
                    Event.$emit('symptoms:select', symptom.id)
                }
            },
            removeSymptom(symptom) {
                if (symptom.selected) {
                    Event.$emit('symptoms:remove', symptom.id)
                }
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .symptom-container {
        overflow-x: scroll;
    }

    .symptom-buttons {
        width: 2000px;
    }

    .symptom-button span.delete {
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

    .symptom-button.selected span.delete {
        display: inline-block;
    }

    button.symptom-button div.loader.absolute {
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