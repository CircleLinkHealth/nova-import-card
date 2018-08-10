<style>
    .vue-modal label {
        font-size: 14px;
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
    <div>
        <modal>
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

                <vue-form :state="formstate" @submit.prevent="onSubmit">
                    <div class="row providerForm">

                        <div class="form-group">
                            <!--name-->
                            <label class="col-md-3 control-label">Provider Name</label>

                            <div class="col-md-9">
                                <div class="row">
                                    <div class="form-group required-field col-md-6">
                                        <validate auto-label :class="fieldClassName(formstate.first_name)">
                                            <div class="col-md-12">
                                                <input type="text"
                                                       id="first_name"
                                                       name="first_name"
                                                       class="form-control input-md"
                                                       placeholder="First"
                                                       disabled
                                                       required
                                                       v-model="formData.user.first_name">
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

                                    <div class="form-group required-field col-md-6">
                                        <validate auto-label :class="fieldClassName(formstate.last_name)">
                                            <div class="col-md-12">
                                                <input type="text"
                                                       id="last_name"
                                                       name="last_name"
                                                       class="form-control input-md"
                                                       placeholder="Last"
                                                       disabled
                                                       required
                                                       v-model="formData.user.last_name">
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
                                                <select2 :options="specialtiesOptions"
                                                         name="specialty"
                                                         v-model="formData.user.provider_info.specialty"
                                                         style="width: 100%;">
                                                    <option disabled value="0">Select one</option>
                                                </select2>
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
                                                       v-model="formData.user.address">
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
                                                       v-model="formData.user.address2">
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
                                                       v-model="formData.user.city">
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
                                                       v-model="formData.user.state">
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
                                                       v-model="formData.user.zip">
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
                                    <div class="form-group required-field col-md-12">
                                        <validate auto-label :class="fieldClassName(formstate.phone)">
                                            <div class="col-md-12">
                                                <input type="text"
                                                       id="phone"
                                                       name="phone"
                                                       class="form-control input-md"
                                                       placeholder="xxx-xxx-xxxx"
                                                       v-model="formData.user.phone_numbers[0].number">
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
                                    <div class="form-group required-field col-md-12">
                                        <validate auto-label :class="fieldClassName(formstate.practice)">
                                            <div class="col-md-12">
                                                <input type="text"
                                                       id="practice"
                                                       name="practice"
                                                       class="form-control input-md"
                                                       placeholder=""
                                                       v-model="formData.user.primary_practice.display_name">
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
                                    <div class="form-group required-field col-md-6">
                                        <validate auto-label :class="fieldClassName(formstate.email)">
                                            <div class="col-md-12">
                                                <input type="email"
                                                       id="email"
                                                       name="email"
                                                       class="form-control input-md"
                                                       v-model="formData.user.email">
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
                                    <div class="form-group col-md-6">

                                        <label class="col-md-3 control-label">Receives Alerts</label>

                                        <div class="col-md-9">
                                            <div class="row">
                                                <div class="form-group required-field col-md-12">
                                                    <validate auto-label :class="fieldClassName(formstate.send_alerts)">
                                                        <div class="col-md-12">

                                                            <input v-model="formData.alert"
                                                                   id="send_alerts"
                                                                   name="send_alerts"
                                                                   class="form-control input-md"
                                                                   type="checkbox"
                                                                   v-bind:disabled="!formData.user.email || formstate.email && !formstate.email.$valid"
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
                                                            <div v-if="!formData.user.email || formstate.email && !formstate.email.$valid"
                                                                 class="validation-error text-left"
                                                                 style="color: green;">
                                                                Email needs to be filled out and valid.
                                                            </div>
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
                                                <select v-model="formData.user.suffix"
                                                        id="suffix"
                                                        name="suffix"
                                                        class="form-control input-md"
                                                        required>
                                                    <option value="" disabled></option>
                                                    <option value="non-clinical"
                                                            :selected="formData.user.provider_info.is_clinical == 0">
                                                        Non-clinical
                                                    </option>
                                                    <option value="MD">MD</option>
                                                    <option value="DO">DO</option>
                                                    <option value="NP">NP</option>
                                                    <option value="PA">PA</option>
                                                    <option value="RN">RN</option>
                                                    <option value="LPN">LPN</option>
                                                    <option value="PN">PN</option>
                                                    <option value="CNA">CNA</option>
                                                    <option value="MA">MA</option>
                                                </select>
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
                                                <select2 :options="relationToPatientOptions"
                                                         name="specialty"
                                                         v-model="formData.typeForDropdown"
                                                         style="width: 100%;">
                                                </select2>
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
                            Save <i v-if="patientCareTeamIsUpdating" class="fa fa-spinner fa-pulse fa-fw"></i>
                        </button>
                    </div>

                </div>
            </template>
        </modal>
    </div>
</template>

<script>
    import modal from '../../shared/modal.vue';
    import {mapActions, mapGetters} from 'vuex'

    import {addNotification, clearOpenModal, getPatientCareTeam, updateCarePerson} from '../../../store/actions'
    import specialtiesOptions from '../../CareTeam/specialties-options';
    import store from '../../../store';
    import {
        RELATION_VALID_DROPDOWN_OPTIONS,
        BILLING_PROVIDER,
        REGULAR_DOCTOR,
        relationToPatientOptions,
        checkCareTeamRelations
    } from '../../CareTeam/care-team-relation-to-patient';

    export default {
        props: {
            carePerson: Object
        },

        components: {
            modal
        },

        computed: Object.assign(
            mapGetters({
                patientCareTeamIsUpdating: 'patientCareTeamIsUpdating',
                patientCareTeam: 'patientCareTeam'
            }),
            {
                validationErrors() {
                    return this.formstate && this.formstate.$invalid && this.formstate.$touched && this.submitClicked
                },
            },
            {
                name() {
                    return this.carePerson.user.first_name
                        + ' '
                        + this.carePerson.user.last_name
                }
            }
        ),

        methods: Object.assign(
            mapActions(['getPatientCareTeam', 'addNotification', 'updateCarePerson', 'clearOpenModal']),
            {
                sendForm() {

                    const {hasError, hasWarning, message} = checkCareTeamRelations(this.patientCareTeam, this.formData);
                    if (hasError) {
                        alert(message);
                        return;
                    }

                    if (hasWarning && !confirm(message)) {
                        return;
                    }

                    this.submitClicked = true;

                    if (this.validationErrors) {
                        return;
                    }

                    //pangratios - i don't like this
                    // change formatted_type only if type has been set to either
                    // billing_provider or regular_doctor
                    if (RELATION_VALID_DROPDOWN_OPTIONS.includes(this.formData.typeForDropdown)) {
                        this.formData.formatted_type = this.formData.typeForDropdown;
                    }
                    //

                    let id = this.formData.id ? this.formData.id : 'new'

                    this.updateCarePerson(this.formData, this.patientId)
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

        created() {
            this.formData = JSON.parse(JSON.stringify(this.carePerson));

            if (!RELATION_VALID_DROPDOWN_OPTIONS.includes(this.formData.type)) {
                this.formData.typeForDropdown = '';
            }
            else {
                this.formData.typeForDropdown = this.formData.type;
            }

            console.log('update-care-person:form-data', this.formData);

            store.watch(
                (state) => state.patientCareTeamIsUpdating,
                (val, oldVal) => {
                    if (oldVal === true && val === false) {
                        this.clearOpenModal();
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

        data() {
            return {
                submitClicked: false,
                updateRoute: $('meta[name="provider-update-route"]').attr('content'),
                patientId: $('meta[name="patient_id"]').attr('content'),
                formstate: {},
                relationToPatientOptions: relationToPatientOptions,
                specialtiesOptions: specialtiesOptions,
                formData: {
                    id: '',
                    formatted_type: 'External',
                    alert: false,
                    type: '',
                    typeForDropdown: '', //filtered to only include only RELATION_VALID_DROPDOWN_OPTIONS
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
                }
            }
        },
    }
</script>