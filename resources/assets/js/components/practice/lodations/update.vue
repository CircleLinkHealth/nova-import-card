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
                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="name" name="name" :class="fieldClassName(formData.name)"
                                       required
                                       v-model="formData.name">

                                <label :class="fieldClassName(formData.name)" for="name" :data-error="errors.get('name')"
                                       data-success=" ">Name</label>

                                <field-messages name="name" show="$untouched || $touched || $submitted">
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <material-select v-model="formData.timezone" name="timezone" :class="fieldClassName(formData.timezone)">
                                    <option v-for="option in timezoneOptions" :value="option.value"
                                            v-text="option.name"></option>
                                </material-select>

                                <field-messages name="timezone" show="$untouched || $touched || $submitted">
                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="address_line_1" name="address_line_1"
                                       :class="fieldClassName(formData.address_line_1)"
                                       v-model="formData.address_line_1">

                                <label :class="fieldClassName(formData.address_line_1)" for="address_line_1"
                                       :data-error="errors.get('address_line_1')" data-success="">Address Line 1</label>

                                <field-messages name="address_line_1" show="$untouched || $touched || $submitted">
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="address_line_2" name="address_line_2" :class="fieldClassName(formData.address_line_2)"
                                       v-model="formData.address_line_2">

                                <label :class="fieldClassName(formData.address_line_2)" for="address_line_2"
                                       data-error="" data-success="">Address Line 2</label>

                                <field-messages name="address_line_2" show="$untouched || $touched || $submitted">

                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="city" name="city" required :class="fieldClassName(formData.city)"
                                       v-model="formData.city">

                                <label :class="fieldClassName(formData.city)" for="city" data-error="" data-success="">City</label>

                                <field-messages name="city" show="$untouched || $touched || $submitted">

                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="state" name="state" :class="fieldClassName(formData.state)"
                                       v-model="formData.state">

                                <label :class="fieldClassName(formData.state)" for="state" data-error=""
                                       data-success="">State</label>

                                <field-messages name="state" show="$untouched || $touched || $submitted">

                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="postal_code" name="postal_code" :class="fieldClassName(formData.postal_code)"
                                       required v-model="formData.postal_code">

                                <label :class="fieldClassName(formData.postal_code)" for="postal_code" data-error=""
                                       data-success="">Postal Code</label>

                                <field-messages name="postal_code" show="$untouched || $touched || $submitted">


                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="phone" name="phone" :class="fieldClassName(formData.phone)"
                                       v-model="formData.phone">

                                <label :class="fieldClassName(formState.phone)" for="phone" data-error=""
                                       data-success="">Phone</label>

                                <field-messages name="phone" show="$untouched || $touched || $submitted">

                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row">
                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="fax" name="fax" required :class="fieldClassName(formData.fax)"
                                       v-model="formData.fax">

                                <label :class="fieldClassName(formData.fax)" for="fax" data-error="" data-success="">Fax</label>

                                <field-messages name="fax" show="$untouched || $touched || $submitted">


                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="emr_direct_address" name="emr_direct_address" :class="fieldClassName(formData.emr_direct_address)"
                                       v-model="formData.emr_direct_address">

                                <label :class="fieldClassName(formData.emr_direct_address)" for="emr_direct_address"
                                       data-error="" data-success="">EMR Direct Address</label>

                                <field-messages name="emr_direct_address" show="$untouched || $touched || $submitted">

                                </field-messages>
                            </div>
                        </validate>
                    </div>

                    <div class="row" v-if="!formData.sameEHRLogin">
                        <h6 class="col s12">
                            Please provide login information for your EHR system.
                        </h6>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="ehr_login" name="ehr_login" :class="fieldClassName(formData.ehr_login)"
                                       required
                                       v-model="formData.ehr_login">

                                <label :class="fieldClassName(formData.ehr_login)" for="ehr_login" data-error=""
                                       data-success="">EHR Login</label>

                                <field-messages name="ehr_login" show="$untouched || $touched || $submitted">

                                    <div class="validation-error has-errors " slot="required"></div>
                                </field-messages>
                            </div>
                        </validate>

                        <validate auto-label>
                            <div class="input-field col s6">
                                <input type="text" id="ehr_password" name="ehr_password" :class="fieldClassName(formData.ehr_password)"
                                       v-model="formData.ehr_password">

                                <label :class="fieldClassName(formData.ehr_password)" for="ehr_password"
                                       data-error="" data-success="">EHR Password</label>

                                <field-messages name="ehr_password" show="$untouched || $touched || $submitted">
                                </field-messages>
                            </div>
                        </validate>

                        <p class="right">
                            <input type="checkbox" class="filled-in" id="sameEHRLogin-box"
                                   v-model="formData.sameEHRLogin" checked="checked"/>
                            <label for="sameEHRLogin-box">Same for all locations?</label>
                        </p>
                    </div>

                    <div class="row" v-if="!formData.sameClinicalIssuesContact">
                        <h6 class="col s12">
                            Who should be notified for patient clinical issues?
                        </h6>

                        <div class="col s12">
                            <material-select v-model="formData.clinical_contact.type" class="input-field"
                                             name="ehr_login">
                                <option v-for="option in clinicalContactOptions" :value="option.value"
                                        v-text="option.name"></option>
                            </material-select>
                        </div>

                        <div v-show="formData.clinical_contact.type !== 'billing_provider'">

                                <validate auto-label :class="fieldClassName()">
                                    <div class="input-field col s6">
                                        <input type="text" id="clinical-contact-first-name"
                                               name="clinical-contact-first-name" required
                                               v-model="formData.clinical_contact.first_name">

                                        <label :class="fieldClassName()" for="clinical-contact-first-name" data-error=""
                                               data-success="">First Name</label>

                                        <field-messages name="clinical-contact-first-name"
                                                        show="$untouched || $touched || $submitted">
                                        </field-messages>
                                    </div>
                                </validate>

                                <validate auto-label :class="fieldClassName()">
                                    <div class="input-field col s6">
                                        <input type="text" id="clinical_contact.last_name"
                                               name="clinical_contact.last_name"

                                               v-model="formData.clinical_contact.last_name">

                                        <label :class="fieldClassName()" for="clinical_contact.last_name"
                                               data-error="" data-success="">Last Name</label>

                                        <field-messages name="clinical_contact.last_name"
                                                        show="$untouched || $touched || $submitted">
                                        </field-messages>
                                    </div>
                                </validate>

                            <validate auto-label>
                                <div class="input-field col s12">
                                    <input type="text" id="clinical_contact_email" name="clinical_contact.email"
                                           :class="{active: formData.clinical_contact.email, invalid: errors.get('clinical_contact.email')}"
                                           v-model="formData.clinical_contact.email">

                                    <label for="clinical_contact_email"
                                           :data-error="errors.get('clinical_contact.email')"
                                           data-success="">Email</label>

                                    <field-messages name="name" show="$untouched || $touched || $submitted">
                                    </field-messages>
                                </div>
                            </validate>
                        </div>

                        <p class="right">
                            <input type="checkbox" class="filled-in" id="sameClinicalIssuesContact-box"
                                   v-model="formData.sameClinicalIssuesContact" checked="checked"/>
                            <label for="sameClinicalIssuesContact-box">Same for all locations?</label>
                        </p>
                    </div>

                    <div @click="submitForm()"
                         class="btn blue waves-effect waves-light right">
                        Save
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
    import {clearOpenModal, addNotification, updatePracticeLocation} from '../../../store/actions'
    import {errors} from '../../../store/getters'
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

        created() {
            this.formData = JSON.parse(JSON.stringify(this.location))
        },

        computed: Object.assign(
            mapGetters({
                errors: 'errors'
            })
        ),

        methods: Object.assign(
            mapActions(['clearOpenModal', 'addNotification', 'updatePracticeLocation']),
            {
                submitForm() {
                    this.updatePracticeLocation(this.formData)
                },

                fieldClassName(field) {
                    return {
                        active: field !== '' && field !== null,
                        invalid: this.errors.get(field)
                    }
                },
            }
        ),

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
                    validated: false,
                    practice: {},
                    practice_id: '',
                    sameClinicalIssuesContact: false,
                    sameEHRLogin: false,
                },
                formState: {},
                clinicalContactOptions: [{
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
    }
</script>

<style>
    .valid {

    }

    .invalid {
        border-bottom: 1px solid #f44336;
        box-shadow: 0 1px 0 0 #f44336;
    }
</style>