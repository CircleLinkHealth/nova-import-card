<template>
    <modal name="misc" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-right">
                    </div>
                </div>
                <div class="col-sm-12 pad-top-10">
                    <div class="btn-group" role="group">
                        <button class="btn btn-secondary misc-button" :class="{ selected: selectedMisc && (selectedMisc.id === misc.id) }" 
                                v-for="(misc, index) in miscs" :key="index" @click="select(index)">
                            {{misc.name}}
                            <span class="delete" title="remove this cpm misc" @click="removeMisc">x</span>
                            <loader class="absolute" v-if="loaders.removeMisc && selectedMisc && (selectedMisc.id === misc.id)"></loader>
                        </button>
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
        props: {
            'patient-id': String
        },
        components: {
            'modal': Modal
        },
        data() {
            return {
                selectedMisc: null,
                miscs: [],
                loaders: {
                    addMisc: null,
                    removeMisc: null
                }
            }
        },
        methods: {
            select(index) {
                this.selectedMisc = (index >= 0) ? Object.assign({}, this.miscs[index]) : null
            },
            reset() {
                this.newMisc.name = ''
            },
            addMisc(e) {
                e.preventDefault()
            },
            removeMisc(e) {
                if (this.selectedMisc && confirm('Are you sure you want to remove this misc?')) {
                    
                }
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .misc-container {
        overflow-x: scroll;
    }

    .misc-buttons {
        width: 2000px;
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