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
                                <div class="form-group required-field col-md-6">
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

                                <div class="form-group required-field col-md-6">
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
                                            <select2 :options="specialtiesOptions"
                                                     name="specialty"
                                                     v-model="newCarePerson.user.provider_info.specialty"
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
                                <div class="form-group required-field col-md-12">
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
                                <div class="form-group required-field col-md-12">
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
                                <div class="form-group required-field col-md-6">
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
                                <div class="form-group col-md-6">

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
                                                        <div v-if="!newCarePerson.user.email || formstate.email && !formstate.email.$valid"
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
                                    <validate auto-label :class="fieldClassName(formstate.qualification)">
                                        <div class="col-md-12">

                                            <select v-model="newCarePerson.user.provider_info.qualification"
                                                    id="qualification"
                                                    name="qualification"
                                                    class="form-control input-md"
                                                    required>
                                                <option value="" disabled></option>
                                                <option value="clinical">Clinical (MD, RN or other)</option>
                                                <option value="non-clinical">Non-clinical</option>
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <field-messages name="qualification"
                                                            show="$untouched || $touched || $submitted">
                                                <div></div>
                                                <div class="validation-error has-errors text-right" slot="required">
                                                    *required
                                                </div>
                                            </field-messages>
                                        </div>
                                    </validate>
                                </div>

                                <!--is ccm billing provider-->
                                <div class="form-group col-md-6">

                                    <label class="col-md-3 control-label">CCM Billing Provider</label>

                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="form-group required-field col-md-12">
                                                <validate auto-label
                                                          :class="fieldClassName(formstate.is_billing_provider)">
                                                    <div class="col-md-12">

                                                        <input v-model="newCarePerson.is_billing_provider"
                                                               id="is_billing_provider"
                                                               name="is_billing_provider"
                                                               class="form-control input-md"
                                                               type="checkbox"
                                                               style="display: inline;">
                                                    </div>

                                                    <div class="col-md-12">
                                                        <field-messages name="is_billing_provider"
                                                                        show="$touched || $submitted">
                                                            <div></div>
                                                            <div class="validation-error has-errors text-right"
                                                                 slot="required">
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
    import modal from '../shared/modal.vue';
    import SearchProviders from './search-providers.vue'
    import {mapGetters, mapActions} from 'vuex'
    import {getPatientCareTeam, clearOpenModal, addNotification, updateCarePerson} from '../../store/actions'

    export default {
        props: {
            show: {
                type: Boolean,
                default: false
            },
        },

        components: {
            modal,
            SearchProviders
        },

        computed: Object.assign({},
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
            }
        ),

        methods: Object.assign({},
            mapActions(['getPatientCareTeam', 'clearOpenModal', 'addNotification', 'updateCarePerson']),
            {
                attachExistingUser(user) {
                    this.newCarePerson.user = user
                    this.newCarePerson.user.phone_numbers = user.phone_numbers
                    this.newCarePerson.user.primary_practice = user.primary_practice
                    this.newCarePerson.user.provider_info = user.provider_info
                },

                sendForm() {
                    this.submitClicked = true

                    if (this.validationErrors) {
                        return
                    }

                    if (this.newCarePerson.is_billing_provider) {
                        this.newCarePerson.formatted_type = 'Billing Provider';
                    }

                    let id = this.newCarePerson.id ? this.newCarePerson.id : 'new'

                    this.updateCarePerson(this.newCarePerson)

                    this.getPatientCareTeam(this.patientId)

                    Object.assign(this.$data, this.$options.data.apply(this))
                    this.clearOpenModal();

                    this.addNotification({
                        title: "Successfully saved Care Person",
                        text: "",
                        type: "success",
                        timeout: true
                    })

                    let url = window.location.href

                    if (url.includes('view-careplan')) {
                        window.location.replace(url + '/#care-team')
                    }
                }
                ,

                fieldClassName(field)
                {
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

        data()
        {
            return {
                submitClicked: false,
                updateRoute: $('meta[name="provider-update-route"]').attr('content'),
                patientId: $('meta[name="patient_id"]').attr('content'),
                formstate: {},

                newCarePerson: {
                    id: 'new',
                    formatted_type: 'External',
                    alert: false,
                    is_billing_provider: false,
                    user_id: $('meta[name="patient_id"]').attr('content'),
                    user: {
                        id: '',
                        email: '',
                        first_name: '',
                        last_name: '',
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
                            qualification: '',
                            specialty: '',
                        }
                    }
                },
                specialtiesOptions: [
                    {id: "Abdominal Radiology", text: "Abdominal Radiology"},
                    {id: "Addiction Psychiatry", text: "Addiction Psychiatry"},
                    {id: "Adolescent Medicine", text: "Adolescent Medicine"},
                    {id: "Adult Reconstructive Orthopaedics", text: "Adult Reconstructive Orthopaedics"},
                    {
                        id: "Advanced Heart Failure & Transplant Cardiology",
                        text: "Advanced Heart Failure & Transplant Cardiology"
                    },
                    {id: "Allergy & Immunology", text: "Allergy & Immunology"},
                    {id: "Anesthesiology", text: "Anesthesiology"},
                    {id: "Biochemical Genetics", text: "Biochemical Genetics"},
                    {id: "Blood Banking - Transfusion Medicine", text: "Blood Banking -T ransfusion Medicine"},
                    {id: "Cardiology", text: "Cardiology"},
                    {id: "Cardiothoracic Radiology", text: "Cardiothoracic Radiology"},
                    {id: "Cardiovascular Disease", text: "Cardiovascular Disease"},
                    {id: "Chemical Pathology", text: "Chemical Pathology"},
                    {id: "Child & Adolescent Psychiatry", text: "Child & Adolescent Psychiatry"},
                    {id: "Child Abuse Pediatrics", text: "Child Abuse Pediatrics"},
                    {id: "Child Neurology", text: "Child Neurology"},
                    {id: "Clinical & Laboratory Immunology", text: "Clinical & Laboratory Immunology"},
                    {id: "Clinical Cardiac Electrophysiology", text: "Clinical Cardiac Electrophysiology"},
                    {id: "Clinical Neurophysiology", text: "Clinical Neurophysiology"},
                    {id: "Colon & Rectal Surgery", text: "Colon & Rectal Surgery"},
                    {id: "Congenital Cardiac Surgery", text: "Congenital Cardiac Surgery"},
                    {id: "Craniofacial Surgery", text: "Craniofacial Surgery"},
                    {id: "Critical Care Medicine", text: "Critical Care Medicine"},
                    {id: "Critical Care Medicine", text: "Critical Care Medicine"},
                    {id: "Cytopathology", text: "Cytopathology"},
                    {id: "Dermatology", text: "Dermatology"},
                    {id: "Dermatopathology", text: "Dermatopathology"},
                    {id: "Developmental-Behavioral Pediatrics", text: "Developmental-Behavioral Pediatrics"},
                    {id: "Ears, Nose, Throat (ENT)", text: "Ears, Nose, Throat (ENT)"},
                    {id: "Emergency Medicine", text: "Emergency Medicine"},
                    {id: "Endocrinology, Diabetes & Metabolism", text: "Endocrinology, Diabetes & Metabolism"},
                    {id: "Endovascular Surgical Neuroradiology", text: "Endovascular Surgical Neuroradiology"},
                    {id: "Family Medicine", text: "Family Medicine"},
                    {id: "Family Practice", text: "Family Practice"},
                    {
                        id: "Female Pelvic Medicine & Reconstructive Surgery",
                        text: "Female Pelvic Medicine & Reconstructive Surgery"
                    },
                    {id: "Foot & Ankle Orthopaedics", text: "Foot & Ankle Orthopaedics"},
                    {id: "Forensic Pathology", text: "Forensic Pathology"},
                    {id: "Forensic Psychiatry", text: "Forensic Psychiatry"},
                    {id: "Gastroenterology", text: "Gastroenterology"},
                    {id: "Geriatric Medicine", text: "Geriatric Medicine"},
                    {id: "Geriatric Psychiatry", text: "Geriatric Psychiatry"},
                    {id: "Hand Surgery", text: "Hand Surgery"},
                    {id: "Hematology", text: "Hematology"},
                    {id: "Hematology & Oncology", text: "Hematology & Oncology"},
                    {id: "Homecare Nurse", text: "Homecare Nurse"},
                    {id: "Infectious Disease", text: "Infectious Disease"},
                    {id: "Internal Medicine", text: "Internal Medicine"},
                    {id: "Internal Medicine-Pediatrics", text: "Internal Medicine-Pediatrics"},
                    {id: "Interventional Cardiology", text: "Interventional Cardiology"},
                    {id: "MD", text: "MD"},
                    {id: "Medical Genetics", text: "Medical Genetics"},
                    {id: "Medical Microbiology", text: "Medical Microbiology"},
                    {id: "Medical Toxicology", text: "Medical Toxicology"},
                    {id: "Molecular Genetic Pathology", text: "Molecular Genetic Pathology"},
                    {id: "Muscoskeletal Radiology", text: "Muscoskeletal Radiology"},
                    {id: "Musculoskeletal Oncology", text: "Musculoskeletal Oncology"},
                    {id: "Neonatal-Perinatal Medicine", text: "Neonatal-Perinatal Medicine"},
                    {id: "Nephrology", text: "Nephrology"},
                    {id: "Neurological Surgery", text: "Neurological Surgery"},
                    {id: "Neurology", text: "Neurology"},
                    {id: "Neuromuscular Medicine", text: "Neuromuscular Medicine"},
                    {id: "Neuroradiology", text: "Neuroradiology"},
                    {id: "Nuclear Medicine", text: "Nuclear Medicine"},
                    {id: "Nuclear Radiology", text: "Nuclear Radiology"},
                    {id: "Obstetric Anesthesiology", text: "Obstetric Anesthesiology"},
                    {id: "Obstetrics & Gynecology", text: "Obstetrics & Gynecology"},
                    {id: "Oncology", text: "Oncology"},
                    {
                        id: "Ophthalmic Plastic & Reconstructive Surgery",
                        text: "Ophthalmic Plastic & Reconstructive Surgery"
                    },
                    {id: "Ophthalmology", text: "Ophthalmology"},
                    {id: "Orthopaedic Sports Medicine", text: "Orthopaedic Sports Medicine"},
                    {id: "Orthopaedic Surgery", text: "Orthopaedic Surgery"},
                    {id: "Orthopaedic Surgery of the Spine", text: "Orthopaedic Surgery of the Spine"},
                    {id: "Orthopaedic Trauma", text: "Orthopaedic Trauma"},
                    {id: "Otolaryngology", text: "Otolaryngology"},
                    {id: "Otology - Neurotology", text: "Otology - Neurotology"},
                    {id: "Pain Medicine", text: "Pain Medicine"},
                    {id: "Pathology-Anatomic & Clinical", text: "Pathology-Anatomic & Clinical"},
                    {id: "Pediatric Anesthesiology", text: "Pediatric Anesthesiology"},
                    {id: "Pediatric Cardiology", text: "Pediatric Cardiology"},
                    {id: "Pediatric Critical Care Medicine", text: "Pediatric Critical Care Medicine"},
                    {id: "Pediatric Emergency Medicine", text: "Pediatric Emergency Medicine"},
                    {id: "Pediatric Endocrinology", text: "Pediatric Endocrinology"},
                    {id: "Pediatric Gastroenterology", text: "Pediatric Gastroenterology"},
                    {id: "Pediatric Hematology-Oncology", text: "Pediatric Hematology-Oncology"},
                    {id: "Pediatric Infectious Diseases", text: "Pediatric Infectious Diseases"},
                    {id: "Pediatric Nephrology", text: "Pediatric Nephrology"},
                    {id: "Pediatric Orthopaedics", text: "Pediatric Orthopaedics"},
                    {id: "Pediatric Otolaryngology", text: "Pediatric Otolaryngology"},
                    {id: "Pediatric Pathology", text: "Pediatric Pathology"},
                    {id: "Pediatric Pulmonology", text: "Pediatric Pulmonology"},
                    {id: "Pediatric Radiology", text: "Pediatric Radiology"},
                    {id: "Pediatric Rheumatology", text: "Pediatric Rheumatology"},
                    {id: "Pediatric Sports Medicine", text: "Pediatric Sports Medicine"},
                    {id: "Pediatric Surgery", text: "Pediatric Surgery"},
                    {id: "Pediatric Transplant Hepatology", text: "Pediatric Transplant Hepatology"},
                    {id: "Pediatric Urology", text: "Pediatric Urology"},
                    {id: "Pediatrics", text: "Pediatrics"},
                    {id: "Physical Medicine & Rehabilitation", text: "Physical Medicine & Rehabilitation"},
                    {id: "Physical Therapy", text: "Physical Therapy"},
                    {id: "Plastic Surgery", text: "Plastic Surgery"},
                    {id: "Preventive Medicine", text: "Preventive Medicine"},
                    {id: "Procedural Dermatology", text: "Procedural Dermatology"},
                    {id: "Psychiatry", text: "Psychiatry"},
                    {id: "Pulmonary Disease", text: "Pulmonary Disease"},
                    {
                        id: "Pulmonary Disease & Critical Care Medicine",
                        text: "Pulmonary Disease & Critical Care Medicine"
                    },
                    {id: "Radiation Oncology", text: "Radiation Oncology"},
                    {id: "Radiology-Diagnostic", text: "Radiology-Diagnostic"},
                    {id: "Rheumatology", text: "Rheumatology"},
                    {id: "Sleep Medicine", text: "Sleep Medicine"},
                    {id: "Social Worker", text: "Social Worker"},
                    {id: "Spinal Cord Injury Medicine", text: "Spinal Cord Injury Medicine"},
                    {id: "Sports Medicine", text: "Sports Medicine"},
                    {id: "Surgery-General", text: "Surgery-General"},
                    {id: "Surgical Critical Care", text: "Surgical Critical Care"},
                    {id: "Therapist", text: "Therapist"},
                    {id: "Thoracic Surgery", text: "Thoracic Surgery"},
                    {id: "Thoracic Surgery-Integrated", text: "Thoracic Surgery-Integrated"},
                    {id: "Transplant Hepatology", text: "Transplant Hepatology"},
                    {id: "Urology", text: "Urology"},
                    {id: "Vascular & Interventional Radiology", text: "Vascular & Interventional Radiology"},
                    {id: "Vascular Surgery", text: "Vascular Surgery"},
                ]
            }
        }
    }
</script>