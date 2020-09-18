<style>
    .vue-modal label {
        font-size: 14px;
    }

    .suffix-element .dropdown-menu {
        max-height: 160px !important;
    }

    .relation-element .dropdown-menu {
        max-height: 80px !important;
    }


    .care-team-dropdown.searchable .dropdown-toggle {
        cursor: text;
    }
    .care-team-dropdown .vs__selected-options input {
        margin-top: 0 !important;
    }

    .care-team-dropdown .dropdown-toggle {
        height: 35px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(60, 60, 60, .26) !important;
    }

    .care-team-dropdown .vs__actions {
        padding-top: 4px !important;
    }

    .care-team-dropdown .selected-tag {
        padding-top: 3px !important;
    }

    .care-team-dropdown .vs__open-indicator {
        padding-top: 2px;
    }


    .providerForm {
        padding: 10px;
    }

    .validation-error {
        padding: 3px;
        margin-bottom: 10px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .has-danger .form-control {
        border-color: #ff0000;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    }

    .has-errors {
        color: #a94442;
    }
</style>

<template>
    <modal v-show="show">
        <template slot="header">
            <button type="button" class="close" @click="clearOpenModal">×</button>
            <h4 class="modal-title">Provider Details</h4>
        </template>

        <template slot="body">
            <div v-if="validationErrors" class="row providerForm">
                <div class="error-list">
                    <h5 class="has-errors">
                        <u>There were some problems with your input. Please review the form.</u>
                    </h5>
                </div>
            </div>

            <search-providers v-if="!newCarePerson.user.id"
                              :first_name="newCarePerson.user.first_name"
                              :last_name="newCarePerson.user.last_name"
                              @existing-user-selected="attachExistingUser"
            ></search-providers>


            <vue-form :state="formstate" @submit.prevent="onSubmit">
                <div class="row providerForm">

                    <div class="form-group">
                        <!--name-->
                        <label class="col-md-3 control-label">Provider Name</label>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="required-field col-md-6">
                                    <validate auto-label :class="fieldClassName(formstate.first_name)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="first_name"
                                                   name="first_name"
                                                   class="form-control input-md"
                                                   placeholder="First"
                                                   required
                                                   v-model="newCarePerson.user.first_name">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="first_name"
                                                            show="$untouched || $touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>

                                <div class="required-field col-md-6">
                                    <validate auto-label :class="fieldClassName(formstate.last_name)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="last_name"
                                                   name="last_name"
                                                   class="form-control input-md"
                                                   placeholder="Last"
                                                   required
                                                   v-model="newCarePerson.user.last_name">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="last_name"
                                                            show="$untouched || $touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--specialty-->
                <div class="row providerForm">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Specialty</label>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <validate auto-label :class="fieldClassName(formstate.specialty)">
                                        <div class="col-md-12">
                                            <v-select class="care-team-dropdown"
                                                      placeholder="Select one"
                                                    label="text"
                                                      :options="specialtiesOptions"
                                                      name="specialty"
                                                      v-model="newCarePerson.user.provider_info.specialty"
                                                      index="id"
                                            >
                                            </v-select>
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="specialty" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--address-->
                <div class="row providerForm">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Address</label>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-8">
                                    <validate auto-label :class="fieldClassName(formstate.address)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="address"
                                                   name="address"
                                                   class="form-control input-md"
                                                   placeholder="Line 1"
                                                   v-model="newCarePerson.user.address">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="address" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>

                                <div class="col-md-4">
                                    <validate auto-label :class="fieldClassName(formstate.address_2)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="address_2"
                                                   name="address_2"
                                                   class="form-control input-md"
                                                   placeholder="Line 2"
                                                   v-model="newCarePerson.user.address2">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="address_2" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right"
                                                     slot="required"></div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <validate auto-label :class="fieldClassName(formstate.city)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="city"
                                                   name="city"
                                                   class="form-control input-md"
                                                   placeholder="City"
                                                   v-model="newCarePerson.user.city">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="city" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>

                                <div class="col-md-4">
                                    <validate auto-label :class="fieldClassName(formstate.state)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="state"
                                                   name="state"
                                                   class="form-control input-md"
                                                   placeholder="State"
                                                   v-model="newCarePerson.user.state">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="state" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>

                                <div class="col-md-4">
                                    <validate auto-label :class="fieldClassName(formstate.zip)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="zip"
                                                   name="zip"
                                                   class="form-control input-md"
                                                   placeholder="Zip"
                                                   v-model="newCarePerson.user.zip">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="zip" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--phone-->
                <div class="row providerForm">
                    <div class="form-group">

                        <label class="col-md-3 control-label">Phone Number</label>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="required-field col-md-12">
                                    <validate auto-label :class="fieldClassName(formstate.phone)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="phone"
                                                   name="phone"
                                                   class="form-control input-md"
                                                   placeholder="xxx-xxx-xxxx"
                                                   v-model="newCarePerson.user.phone_numbers[0].number">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="phone" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--practice-->
                <div class="row providerForm">
                    <div class="form-group">

                        <label class="col-md-3 control-label">Practice Name</label>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="required-field col-md-12">
                                    <validate auto-label :class="fieldClassName(formstate.practice)">
                                        <div class="col-md-12">
                                            <input type="text"
                                                   id="practice"
                                                   name="practice"
                                                   class="form-control input-md"
                                                   placeholder=""
                                                   v-model="newCarePerson.user.primary_practice.display_name">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="practice" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!--email-->
                <div class="row providerForm">
                    <div class="form-group">

                        <label class="col-md-3 control-label">Email</label>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="required-field col-md-6">
                                    <validate auto-label :class="fieldClassName(formstate.email)">
                                        <div class="col-md-12">
                                            <input type="email"
                                                   id="email"
                                                   name="email"
                                                   class="form-control input-md"
                                                   v-model="newCarePerson.user.email">
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="email" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                                <div class="validation-error has-errors text-right" slot="email">
                                                    Please enter a valid email
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>

                                <!--send alerts-->
                                <div class="col-md-6">

                                    <label class="col-md-3 control-label">Send Alerts</label>

                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="form-group required-field col-md-12">
                                                <validate auto-label :class="fieldClassName(formstate.send_alerts)">
                                                    <div class="col-md-12">

                                                        <input v-model="newCarePerson.alert"
                                                               id="send_alerts"
                                                               name="send_alerts"
                                                               class="form-control input-md"
                                                               type="checkbox"
                                                               v-bind:disabled="!newCarePerson.user.email || formstate.email && !formstate.email.$valid"
                                                               style="display: inline;">
                                                    </div>

                                                    <div class="col-md-12">
                                                        <field-messages name="send_alerts"
                                                                        show="$touched || $submitted">
                                                            <div></div>
                                                            <div class="validation-error has-errors text-right"
                                                                 slot="required">
                                                                *required
                                                            </div>
                                                        </field-messages>
                                                        <span v-if="!newCarePerson.user.email || formstate.email && !formstate.email.$valid"
                                                              class="validation-error text-left"
                                                              style="color: green;">
                                                            A valid email is required.
                                                        </span>
                                                    </div>
                                                </validate>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row providerForm">
                    <div class="form-group">

                        <label class="col-md-3 control-label">Clinical Type</label>

                        <div class="col-md-9">
                            <div class="row">
                                <!--clinical type-->
                                <div class="form-group required-field col-md-6">
                                    <validate auto-label :class="fieldClassName(formstate.suffix)">
                                        <div class="col-md-12">
                                            <v-select
                                                    class="care-team-dropdown suffix-element"
                                                    :options="suffixOptions"
                                                    label="text"
                                                    index="id"
                                                    :value="selectedSuffix"
                                                    @input="setSelectedSuffix"
                                                    id="suffix"
                                                    name="suffix"
                                                    required>
                                            </v-select>
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="suffix"
                                                            show="$untouched || $touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--billing vs regular dr-->
                <div class="row providerForm">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Relation to patient</label>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-12">
                                    <validate auto-label :class="fieldClassName(formstate.typeForDropdown)">
                                        <div class="col-md-12">
                                            <v-select class="care-team-dropdown relation-element"
                                                      label="text"
                                                      index="id"
                                                      :options="relationToPatientOptions.data"
                                                      name="relation-to-patient"
                                                      v-model="newCarePerson.typeForDropdown"
                                            >
                                            </v-select>
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="specialty" show="$touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>

                                    </validate>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </vue-form>
        </template>

        <template slot="footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-default" @click="clearOpenModal">Close</button>

                    <button :disabled="false" @click="sendForm" class=" btn btn-info">
                        Save <i v-if="false" class="fa fa-spinner fa-pulse fa-fw"></i>
                    </button>
                </div>

            </div>
        </template>
    </modal>
</template>

<script>
    import modal from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/modal.vue';
    import SearchProviders from './search-providers.vue'
    import store from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store';
    import {mapActions, mapGetters} from 'vuex'
    import {addNotification, clearOpenModal, getPatientCareTeam, updateCarePerson} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/actions'
    import specialtiesOptions from './specialties-options';
    import suffixOptions from './suffix-options';

    import VueSelect from 'vue-select';
    import {
        checkCareTeamRelations, RELATION_VALID_DROPDOWN_OPTIONS,
        relationToPatientOptions
    } from './care-team-relation-to-patient';

    export default {
        props: {
            show: {
                type: Boolean,
                default: false
            },
        },

        components: {
            modal,
            SearchProviders,
            'v-select': VueSelect,
        },

        computed: Object.assign(
            mapGetters({
                patientCareTeamIsUpdating: 'patientCareTeamIsUpdating',
                patientCareTeam: 'patientCareTeam'
            }),
            {
                validationErrors() {
                    return this.formstate && this.formstate.$invalid && this.submitClicked
                }
            },
            {
                name() {
                    return this.newCarePerson.user.first_name
                        + ' '
                        + this.newCarePerson.user.last_name
                }
            },
            {
                selectedSuffix() {
                    return this.newCarePerson.user.provider_info.is_clinical == 0
                    || this.newCarePerson.user.suffix == 'non-clinical' ?
                        suffixOptions['non-clinical'] :
                        suffixOptions[this.newCarePerson.user.suffix];

                }
            }
        ),

        created() {
            store.watch(
                (state) => state.patientCareTeamIsUpdating,
                (val, oldVal) => {
                    if (oldVal === true && val === false) {
                        Object.assign(this.$data, this.$options.data.apply(this))

                        this.clearOpenModal();
                        this.clearNotifications();
                        this.addNotification({
                            title: "Successfully saved Care Person",
                            text: "",
                            type: "success",
                            timeout: true
                        });

                        //refresh the page
                        let url = window.location.href;
                        if (url.includes('view-careplan')) {
                            window.location.replace(url)
                        }
                    }
                }
            );
        },

        methods: Object.assign({},
            mapActions(['getPatientCareTeam', 'clearOpenModal', 'addNotification', 'clearNotifications', 'updateCarePerson']),
            {
                attachExistingUser(user) {
                    this.newCarePerson.user = user
                    this.newCarePerson.user.phone_numbers = user.phone_numbers
                    this.newCarePerson.user.primary_practice = user.primary_practice
                    this.newCarePerson.user.provider_info = user.provider_info
                },
                setSelectedSuffix(input) {
                    this.newCarePerson.user.suffix = input
                },

                sendForm() {

                    const {hasError, hasWarning, message} = checkCareTeamRelations(this.patientCareTeam, this.newCarePerson);
                    if (hasError) {
                        alert(message);
                        return;
                    }

                    if (hasWarning && !confirm(message)) {
                        return;
                    }

                    this.submitClicked = true

                    if (this.validationErrors) {
                        return
                    }

                    //pangratios - i don't like this
                    // change formatted_type only if type has been set to either
                    // billing_provider or regular_doctor
                    if (RELATION_VALID_DROPDOWN_OPTIONS.includes(this.newCarePerson.typeForDropdown)) {
                        this.newCarePerson.formatted_type = this.newCarePerson.typeForDropdown;
                    }
                    //

                    let id = this.newCarePerson.id ? this.newCarePerson.id : 'new'

                    this.updateCarePerson(this.newCarePerson);
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
                }
                ,
            }
        ),

        data() {
            return {
                submitClicked: false,
                updateRoute: $('meta[name="provider-update-route"]').attr('content'),
                patientId: $('meta[name="patient_id"]').attr('content'),
                formstate: {},
                relationToPatientOptions: relationToPatientOptions,
                newCarePerson: {
                    id: 'new',
                    formatted_type: 'External',
                    alert: false,
                    typeForDropdown: '',
                    user_id: $('meta[name="patient_id"]').attr('content'),
                    user: {
                        id: '',
                        email: '',
                        first_name: '',
                        last_name: '',
                        suffix: '',
                        address: '',
                        address2: '',
                        city: '',
                        state: '',
                        zip: '',
                        phone_numbers: {
                            0: {
                                id: '',
                                number: '',
                            }
                        },
                        primary_practice: {
                            id: '',
                            display_name: ''
                        },
                        provider_info: {
                            id: '',
                            is_clinical: '',
                            specialty: '',
                        }
                    }
                },
                specialtiesOptions: specialtiesOptions,
                suffixOptions: suffixOptions
            }
        }
    }
</script>