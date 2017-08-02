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
                                <input type="text" id="name" name="name" class="form-control input-md" required
                                       v-model="formData.name">

                                <label :class="{active: formData.name}" for="name" data-error=""
                                       data-success="">Name</label>

                                <field-messages name="name" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.timezone)">
                            <div class="input-field col s6">
                                <material-select v-model="formData.timezone" class="input-field" name="timezone">
                                    <option v-for="option in timezoneOptions" :value="option.value"
                                            v-text="option.name"></option>
                                </material-select>

                                <field-messages name="timezone" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label :class="fieldClassName(formState.address_line_1)">
                            <div class="input-field col s6">
                                <input type="text" id="address_line_1" name="address_line_1"
                                       class="form-control input-md" required v-model="formData.address_line_1">

                                <label :class="fieldClassName(formState.address_line_1)" for="address_line_1"
                                       data-error="" data-success="">Address Line 1</label>

                                <field-messages name="address_line_1" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.address_line_2)">
                            <div class="input-field col s6">
                                <input type="text" id="address_line_2" name="address_line_2"
                                       class="form-control input-md" v-model="formData.address_line_2">

                                <label :class="fieldClassName(formState.address_line_2)" for="address_line_2"
                                       data-error="" data-success="">Address Line 2</label>

                                <field-messages name="address_line_2" show="$untouched || $touched || $submitted">
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label :class="fieldClassName(formState.city)">
                            <div class="input-field col s6">
                                <input type="text" id="city" name="city" class="form-control input-md" required
                                       v-model="formData.city">

                                <label :class="fieldClassName(formState.city)" for="city" data-error="" data-success="">City</label>

                                <field-messages name="city" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.state)">
                            <div class="input-field col s6">
                                <input type="text" id="state" name="state" class="form-control input-md"
                                       v-model="formData.state">

                                <label :class="fieldClassName(formState.state)" for="state" data-error=""
                                       data-success="">State</label>

                                <field-messages name="state" show="$untouched || $touched || $submitted">
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label :class="fieldClassName(formState.postal_code)">
                            <div class="input-field col s6">
                                <input type="text" id="postal_code" name="postal_code" class="form-control input-md"
                                       required v-model="formData.postal_code">

                                <label :class="fieldClassName(formState.postal_code)" for="postal_code" data-error=""
                                       data-success="">Postal Code</label>

                                <field-messages name="postal_code" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.phone)">
                            <div class="input-field col s6">
                                <input type="text" id="phone" name="phone" class="form-control input-md"
                                       v-model="formData.phone">

                                <label :class="fieldClassName(formState.phone)" for="phone" data-error=""
                                       data-success="">Phone</label>

                                <field-messages name="phone" show="$untouched || $touched || $submitted">
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label :class="fieldClassName(formState.fax)">
                            <div class="input-field col s6">
                                <input type="text" id="fax" name="fax" class="form-control input-md" required
                                       v-model="formData.fax">

                                <label :class="fieldClassName(formState.fax)" for="fax" data-error="" data-success="">Fax</label>

                                <field-messages name="fax" show="$untouched || $touched || $submitted">
                                    <div></div>
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label :class="fieldClassName(formState.emr_direct_address)">
                            <div class="input-field col s6">
                                <input type="text" id="emr_direct_address" name="emr_direct_address"
                                       class="form-control input-md" v-model="formData.emr_direct_address">

                                <label :class="fieldClassName(formState.emr_direct_address)" for="emr_direct_address"
                                       data-error="" data-success="">EMR Direct Address</label>

                                <field-messages name="emr_direct_address" show="$untouched || $touched || $submitted">
                                    <div class="validation-error has-errors " slot="required">*required</div>
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row" v-if="!sameEHRLogin">
                        <h6>
                            Please provide login information for your EHR system.
                        </h6>

                        <div class="col s6">
                            <material-select v-model="formData.ehr_login" class="input-field" name="ehr_login">
                                <option v-for="option in ehrLoginOptions" :value="option.value"
                                        v-text="option.name"></option>
                            </material-select>

                            <field-messages name="ehr_login" show="$untouched || $touched || $submitted">
                                <div></div>
                                <div class="validation-error has-errors " slot="required">*required</div>
                            </field-messages>
                        </div>
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
                sameEHRLogin: false,
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
                ehrLoginOptions: [{
                    name: 'Patient\'s Billing / Main provider',
                    value: 'billing_provider'
                }, {
                    name: 'Someone else instead of the billing provider',
                    value: 'instead_of_billing_provider'
                }, {
                    name: 'Someone else in addition to the billing provider',
                    value: 'in_addition_to_billing_provider'
                }],
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