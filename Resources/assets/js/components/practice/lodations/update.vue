<template>
    <div>
        <div class="row">
            <div class="col s12">
                <h5 class="left">
                    <div v-if="formData.id === 'new'">
                        Add Location
                    </div>
                    <div v-else>
                        Edit Location
                    </div>
                </h5>

                <div @click="submitForm()"
                     class="btn green waves-effect waves-light right">
                    Save & Close
                </div>

                <div @click="close()"
                     class="btn red waves-effect waves-light right"
                     style="margin-right: 2rem;">
                    Close
                </div>
            </div>
        </div>

        <vue-form :state="formState" @submit.prevent="onSubmit">
            <div class="row">
                <validate auto-label>
                    <div class="input-field col s6">

                        <v-input type="text" label="Name" v-model="formData.name" name="name" required autocomplete="organization"></v-input>

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
                        <v-input type="text" label="Address Line 1" v-model="formData.address_line_1"
                                 name="address_line_1" required autocomplete="address-line1"></v-input>

                        <field-messages name="address_line_1" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s6">
                        <v-input type="text" label="Address Line 2" v-model="formData.address_line_2"
                                 name="address_line_2" autocomplete="address-line2"></v-input>

                        <field-messages name="address_line_2" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="City" v-model="formData.city" name="city" required></v-input>

                        <field-messages name="city" show="$untouched || $touched || $submitted"></field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="State" v-model="formData.state" name="state" required></v-input>

                        <field-messages name="state" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="number" label="Postal Code" v-model="formData.postal_code"
                                 name="postal_code" required></v-input>

                        <field-messages name="postal_code" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="Phone" v-model="formData.phone" name="phone" required></v-input>

                        <field-messages name="phone" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="Fax" v-model="formData.fax" name="fax"></v-input>

                        <field-messages name="fax" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="EMR Direct Address" v-model="formData.emr_direct_address"
                                 name="emr_direct_address"></v-input>

                        <field-messages name="emr_direct_address" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="Clinical Escalation Phone Number" v-model="formData.clinical_escalation_phone" name="clinical_escalation_phone"></v-input>

                        <field-messages name="clinical_escalation_phone" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>
            </div>

            <div class="row">
                <h6 class="col s12">
                    Please provide login information for your EHR system.
                </h6>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="EHR Login" v-model="formData.ehr_login" name="ehr_login"></v-input>

                        <field-messages name="ehr_login" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <validate auto-label>
                    <div class="input-field col s4">
                        <v-input type="text" label="EHR Password" v-model="formData.ehr_password"
                                 name="ehr_password"></v-input>

                        <field-messages name="ehr_password" show="$untouched || $touched || $submitted">
                        </field-messages>
                    </div>
                </validate>

                <div class="input-field col s4">
                    <input type="checkbox" class="filled-in" id="sameEHRLogin-box"
                           v-model="formData.sameEHRLogin" :checked="formData.sameEHRLogin"/>
                    <label for="sameEHRLogin-box">Same for all locations?</label>
                </div>
            </div>

            <div class="row">
                <h6 class="col s12">
                    Who should be notified for patient clinical issues?
                </h6>

                <div class="input-field col s8">
                    <material-select v-model="formData.clinical_contact.type" class="input-field"
                                     name="clinical_contact">
                        <option v-for="option in clinicalContactOptions" :value="option.value"
                                v-text="option.name"></option>
                    </material-select>
                </div>

                <p class="input-field col s4">
                    <input type="checkbox" class="filled-in" id="sameClinicalIssuesContact-box"
                           v-model="formData.sameClinicalIssuesContact" :checked="formData.sameClinicalIssuesContact"/>
                    <label for="sameClinicalIssuesContact-box">Same for all locations?</label>
                </p>

                <div v-show="formData.clinical_contact.type !== 'billing_provider'">

                    <validate auto-label :class="isValid()">
                        <div class="input-field col s6">
                            <v-input type="text" label="First Name" v-model="formData.clinical_contact.first_name"
                                     name="clinical_contact.first_name"></v-input>

                            <field-messages name="clinical-contact-first-name"
                                            show="$untouched || $touched || $submitted">
                            </field-messages>
                        </div>
                    </validate>

                    <validate auto-label :class="isValid()">
                        <div class="input-field col s6">
                            <v-input type="text" label="Last Name" v-model="formData.clinical_contact.last_name"
                                     name="clinical_contact.last_name"></v-input>

                            <field-messages name="clinical_contact.last_name"
                                            show="$untouched || $touched || $submitted">
                            </field-messages>
                        </div>
                    </validate>

                    <validate auto-label>
                        <div class="input-field col s12">
                            <v-input type="text" label="Email" v-model="formData.clinical_contact.email"
                                     name="clinical_contact.email"></v-input>

                            <field-messages name="name" show="$untouched || $touched || $submitted">
                            </field-messages>
                        </div>
                    </validate>
                </div>
            </div>

            <div class="row">
                <div class="col s12">
                    <div @click="submitForm()"
                         class="btn green waves-effect waves-light right">
                        Save & Close
                    </div>

                    <div @click="close()"
                         class="btn red waves-effect waves-light right"
                         style="margin-right: 2rem;">
                        Close
                    </div>
                </div>
            </div>
        </vue-form>
    </div>
</template>

<script>
    import modal from '../../shared/materialize/modal.vue';
    import {mapActions, mapGetters} from 'vuex'
    import {addNotification, clearErrors, clearOpenModal, updatePracticeLocation} from '../../../../../../../../resources/assets/js/store/actions'
    import {practiceLocations} from '../../../../../../../../resources/assets/js/store/getters'
    import MaterialSelect from '../../../../../../../../resources/assets/js/components/src/material-select.vue'

    export default {
        props: {
            location: {
                type: Object,
                default: () => {
                    return {}
                }
            }
        },

        components: {
            modal,
            MaterialSelect
        },

        created() {
            if (!_.isEmpty(this.location)) {
                this.formData = JSON.parse(JSON.stringify(this.location))
            }

            if (!_.isEmpty(this.locations)) {
                this.formData.sameClinicalIssuesContact = this.locations[0].sameClinicalIssuesContact
                this.formData.clinical_contact = this.locations[0].clinical_contact

                this.formData.sameEHRLogin = this.locations[0].sameEHRLogin
                this.formData.ehr_login = this.locations[0].ehr_login
                this.formData.ehr_password = this.locations[0].ehr_password

            }
        },

        computed: Object.assign(
            mapGetters({
                errors: 'errors',
                locations: 'practiceLocations'
            })
        ),

        methods: Object.assign(
            mapActions(['clearOpenModal', 'addNotification', 'updatePracticeLocation', 'clearErrors']),
            {
                submitForm() {
                    this.updatePracticeLocation(this.formData)
                        .then(response => {
                            Vue.nextTick(() => {
                                setTimeout(() => {
                                    if (!this.errors.any()) {
                                        Materialize.toast(this.formData.name + ' was successfully updated.', 3000)
                                        this.close()
                                    }
                                }, 500);
                            })
                        })
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

                close() {
                    this.clearErrors()
                    this.$emit('update-view', 'index-locations', {})
                }
            }
        ),

        data() {
            return {
                formData: {
                    id: 'new',
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
                    clinical_escalation_phone: '',
                    fax: '',
                    emr_direct_address: '',
                    postal_code: '',
                    state: '',
                    validated: false,
                    practice: {},
                    practice_id: $('meta[name=practice-id]').attr('content'),
                    sameClinicalIssuesContact: false,
                    sameEHRLogin: false,
                },
                formState: {},
                clinicalContactOptions: [{
                    name: 'Patient\'s Billing / Main provider',
                    value: 'billing_provider'
                }, {
                    name: 'Notify others instead of the billing provider',
                    value: 'instead_of_billing_provider'
                }, {
                    name: 'Notify others in addition to the billing provider',
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
    .invalid {
        border-bottom: 1px solid #f44336;
        box-shadow: 0 1px 0 0 #f44336;
    }
</style>