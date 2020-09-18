<template>
    <modal name="lifestyles" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-care-areas">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12 pad-top-10" :class="{ 'lifestyle-container': isExtendedView }">
                    <div class="btn-group" role="group" :class="{ 'lifestyle-buttons': isExtendedView }">
                        <div class="btn btn-secondary lifestyle-button" 
                                v-for="(lifestyle, index) in lifestyles" :key="index" @click="select(lifestyle)" 
                                :class="{ selected: lifestyle.selected }">
                            {{lifestyle.name}}
                            <span class="delete" title="remove this cpm lifestyle" @click="removeLifestyle(lifestyle)">x</span>
                            <loader class="absolute" v-if="lifestyle.loaders.removeLifestyle || lifestyle.loaders.addLifestyle"></loader>
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
    import Modal from '../../../admin/common/modal'

    export default {
        name: 'lifestyles-modal',
        props: {
            'patient-id': String,
            lifestyles: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            isExtendedView() {
                return this.lifestyles.length > 12
            }
        },
        methods: {
            select(lifestyle) {
                if (!lifestyle.selected) {
                    Event.$emit('lifestyles:select', lifestyle.id)
                }
            },
            removeLifestyle(lifestyle) {
                if (lifestyle.selected) {
                    Event.$emit('lifestyles:remove', lifestyle.id)
                }
            }
        },
        mounted() {
            
        }
    }
</script>

<style>
    .lifestyle-container {
        overflow-x: scroll;
    }

    .lifestyle-buttons {
        width: 2000px;
    }

    .lifestyle-button span.delete {
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

    .lifestyle-button.selected span.delete {
        display: inline-block;
    }

    button.lifestyle-button div.loader.absolute {
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