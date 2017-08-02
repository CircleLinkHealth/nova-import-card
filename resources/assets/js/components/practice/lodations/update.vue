<template>
    <div>
        <modal v-if="show">
            <template slot="header">
                <div class="row">
                    <div class="col s12">
                        <p class="modal-title">Edit Location</p>
                        <a class="close-button" @click="$emit('close-modal')">×</a>
                    </div>
                </div>
            </template>

            <template slot="body">
                <vue-form :state="formState" @submit.prevent="onSubmit">
                    <div class="row">
                        <validate auto-label :class="fieldClassName(formState.name)">
                            <div class="input-field col s6">
                                <input type="text" id="name" name="name" class="form-control input-md"
                                       placeholder="Name" required v-model="formData.name">

                                <label :class="fieldClassName(formState.name)" for="name" data-error="" data-success="">Name</label>

                                <field-messages name="name" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors text-right" slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.name)">
                            <div class="input-field col s6">
                                <material-select v-model="formData.timezone" class="input-field" name="timezone">
                                    <option v-for="option in timezoneOptions" :value="option.value"
                                            v-text="option.name"></option>
                                </material-select>

                                <field-messages name="timezone" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors text-right" slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>


                </vue-form>
            </template>

            <template slot="footer">

            </template>
        </modal>
    </div>
</template>

<script>
    import modal from '../../shared/materialize/modal.vue';
    import {mapGetters, mapActions} from 'vuex'
    import {clearOpenModal, addNotification} from '../../../store/actions'
    import MaterialSelect from '../../src/material-select.vue'

    export default {
        props: {
            show: {
                type: Boolean,
                default: false
            },
            location: Object
        },

        components: {
            modal,
            MaterialSelect
        },

        mounted() {
            this.formData = JSON.parse(JSON.stringify(this.location))
        },

        data() {
            return {
                formData: {
                    clinical_contact: {
                        email: '',
                        first_name: '',
                        last_name: '',
                        type: 'billing_provider'
                    },
                    timezone: 'America/New_York',
                    ehr_password: '',
                    city: '',
                    address_line_1: '',
                    address_line_2: '',
                    ehr_login: '',
                    errorCount: 0,
                    isComplete: false,
                    name: '',
                    phone: '',
                    fax: '',
                    emr_direct_address: '',
                    postal_code: '',
                    state: '',
                    validated: false
                },
                formState: {},
                timezoneOptions: [{
                    name: 'Eastern Time',
                    value: 'America/New_York'
                }, {
                    name: 'Central Time',
                    value: 'America/Chicago'
                }, {
                    name: 'Mountain Time',
                    value: 'America/Denver'
                }, {
                    name: 'Mountain Time (no DST)',
                    value: 'America/Phoenix'
                }, {
                    name: 'Pacific Time',
                    value: 'America/Los_Angeles'
                }, {
                    name: 'Alaska Time',
                    value: 'America/Anchorage'
                }, {
                    name: 'Hawaii-Aleutian',
                    value: 'America/Adak'
                }, {
                    name: 'Hawaii-Aleutian Time (no DST)',
                    value: 'Pacific/Honolulu'
                }]
            }
        },

        methods: Object.assign(
            mapActions(['clearOpenModal', 'addNotification']),
            {
                sendForm() {

                },

                fieldClassName(field) {
                    if (!field) {
                        return '';
                    }
                    if ((field.$touched || field.$submitted) && field.$valid) {
                        return 'has-success';
                    }
                    if ((field.$touched || field.$submitted) && field.$invalid) {
                        return 'has-danger';
                    }
                },
            }
        ),
    }
</script>