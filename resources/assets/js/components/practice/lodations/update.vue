<template>
    <div>
        <div class="row">
            <div class="col s12">
                <p class="modal-title">Edit Location</p>
                <a class="close-button" @click="closeModal()">×</a>
            </div>
        </div>

        <vue-form :state="formState" @submit.prevent="onSubmit">
            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">

                        <v-input type="text" label="Name" v-model="formData.name" name="name"></v-input>

                        <field-messages name="name" show="$untouched || $touched || $submitted"></field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <material-select v-model="formData.timezone" name="timezone" id="timezone"
                                         :class="isValid(formData.timezone)">
                            <option v-for="option in timezoneOptions" :value="option.value"
                                    v-text="option.name"></option>
                        </material-select>

                        <label for="timezone">Timezone</label>

                        <field-messages name="timezone" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="Address Line 1" v-model="formData.address_line_1" name="address_line_1"></v-input>

                        <field-messages name="address_line_1" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="Address Line 2" v-model="formData.address_line_2" name="address_line_2"></v-input>

                        <field-messages name="address_line_2" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="City" v-model="formData.city" name="city"></v-input>

                        <field-messages name="city" show="$untouched || $touched || $submitted"></field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="State" v-model="formData.state" name="state"></v-input>

                        <field-messages name="state" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="number" label="Postal Code" v-model="formData.postal_code" name="postal_code"></v-input>

                        <field-messages name="postal_code" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="Phone" v-model="formData.phone" name="phone"></v-input>

                        <field-messages name="phone" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="Fax" v-model="formData.fax" name="fax"></v-input>

                        <field-messages name="fax" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="EMR Direct Address" v-model="formData.emr_direct_address" name="emr_direct_address"></v-input>

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
                        <v-input type="text" label="EHR Login" v-model="formData.ehr_login" name="ehr_login"></v-input>

                        <field-messages name="ehr_login" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="EHR Password" v-model="formData.ehr_password" name="ehr_password"></v-input>

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
                                     name="clinical_contact">
                        <option v-for="option in clinicalContactOptions" :value="option.value"
                                v-text="option.name"></option>
                    </material-select>
                </div>

                <div v-show="formData.clinical_contact.type !== 'billing_provider'">

                    <validate auto-label :class="isValid()">
                        <div class="input-field col s6">
                            <v-input type="text" label="First Name" v-model="formData.clinical_contact.first_name" name="clinical_contact.first_name"></v-input>

                            <field-messages name="clinical-contact-first-name" show="$untouched || $touched || $submitted">
                            </field-messages>
                        </div>
                    </validate>

                    <validate auto-label :class="isValid()">
                        <div class="input-field col s6">
                            <v-input type="text" label="Last Name" v-model="formData.clinical_contact.last_name" name="clinical_contact.last_name"></v-input>

                            <field-messages name="clinical_contact.last_name" show="$untouched || $touched || $submitted">
                            </field-messages>
                        </div>
                    </validate>

                    <validate auto-label>
                        <div class="input-field col s12">
                            <v-input type="text" label="Email" v-model="formData.clinical_contact.email" name="clinical_contact.email"></v-input>

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

                isValid(field) {
                    return {
                        invalid: this.errors.get(field)
                    }
                },

                isActive(field) {
                    return {
                        active: this.formData[field],
                    }
                },

                closeModal() {
                    this.$emit('update-view', 'index-locations', {})
                }
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