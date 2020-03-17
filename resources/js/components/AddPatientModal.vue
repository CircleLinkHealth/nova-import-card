<template>
    <mdb-modal v-on:close="cancel" size="lg">
        <form novalidate @submit="save">
            <mdb-modal-header>
                <mdb-modal-title>Add AWV Patient</mdb-modal-title>
            </mdb-modal-header>
            <mdb-modal-body>

                <mdb-container>

                    <mdb-tabs :active="0"
                              default
                              :links="[{text: 'Form'}, {text: 'CCD'}]"
                              :transition-duration="0.2"
                              transition-style="linear">

                        <template slot="Form">
                            <mdb-row>
                                <mdb-col>
                                    <div class="spinner-overlay" v-show="waiting">
                                        <div class="text-center">
                                            <mdb-icon icon="spinner" :spin="true"/>
                                        </div>
                                    </div>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row>
                                <mdb-col md="6">
                                    <mdb-input label="First Name *"
                                               v-model="patient.firstName"
                                               :customValidation="validation.firstName.validated"
                                               :isValid="validation.firstName.valid"
                                               @change="validate('firstName', $event)"
                                               invalidFeedback="Please set a first name."/>
                                </mdb-col>
                                <mdb-col md="6">
                                    <mdb-input label="Last Name *"
                                               v-model="patient.lastName"
                                               :customValidation="validation.lastName.validated"
                                               :isValid="validation.lastName.valid"
                                               @change="validate('lastName', $event)"
                                               invalidFeedback="Please set a last name."/>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row>
                                <mdb-col md="6">
                                    <mdb-input label="Phone number *" v-model="patient.phoneNumber"
                                               type="tel"
                                               :customValidation="validation.phoneNumber.validated"
                                               :isValid="validation.phoneNumber.valid"
                                               @change="validate('phoneNumber', $event)"
                                               invalidFeedback="Please set a valid phone number."/>
                                </mdb-col>
                                <mdb-col md="6">
                                    <mdb-input label="DOB *"
                                               v-model="patient.dob"
                                               :customValidation="validation.dob.validated"
                                               :isValid="validation.dob.valid"
                                               type="date"
                                               @change="validate('dob', $event)"/>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row>
                                <mdb-col>
                                    <mdb-input label="Email" v-model="patient.email"
                                               :customValidation="validation.email.validated"
                                               :isValid="validation.email.valid"
                                               @change="validate('email', $event)"
                                               invalidFeedback="Please set a valid email."
                                               type="email"/>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row>
                                <mdb-col md="6">
                                    <mdb-date-picker label="Appointment Date"
                                                     disable-input
                                                     icon="calendar"
                                                     ref="appointmentDatePicker"
                                                     invalidFeedback="Please select both date and time or none."
                                                     :customValidation="validation.appointment.validated"
                                                     :isValid="validation.appointment.valid"
                                                     @change="validate('appointmentDate', $event)"
                                                     v-model="patient.appointmentDate"/>
                                </mdb-col>
                                <mdb-col md="6">
                                    <mdb-time-picker label="Appointment Time"
                                                     disable-input
                                                     icon="clock"
                                                     ref="appointmentTimePicker"
                                                     v-model="patient.appointmentTime"/>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row class="provider-container">
                                <mdb-col>
                                    <add-patient-provider ref="providerComponent"></add-patient-provider>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row style="margin-top: 5px">
                                <mdb-col>
                                    <mdb-alert v-if="error" color="danger">
                                        <p v-html="error"></p>
                                    </mdb-alert>
                                </mdb-col>
                            </mdb-row>
                        </template>
                        <template slot="CCD">

                            <mdb-row>
                                <mdb-col>
                                    <p class="text-center">
                                        Click the button below to open a new page where you can import a patient using
                                        CCD.
                                    </p>
                                </mdb-col>
                            </mdb-row>

                            <mdb-row>
                                <mdb-col class="text-center">
                                    <a v-if="hasImportFromCcdUrl" :href="getImportFromCcdUrl()"
                                       class="btn btn-primary"
                                       target="_blank">
                                        Import from CCD
                                    </a>
                                </mdb-col>
                            </mdb-row>
                        </template>

                    </mdb-tabs>

                </mdb-container>

            </mdb-modal-body>
            <mdb-modal-footer>
                <mdb-btn color="warning" icon="ban" @click.native="cancel">Cancel</mdb-btn>
                <mdb-btn type="submit" color="primary" icon="save" :disabled="waiting || !isFormValid">Save</mdb-btn>
            </mdb-modal-footer>
        </form>
    </mdb-modal>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbCol,
        mdbContainer,
        mdbDatePicker,
        mdbIcon,
        mdbInput,
        mdbModalBody,
        mdbModalFooter,
        mdbModalHeader,
        mdbModalTitle,
        mdbRow,
        mdbTabs,
        mdbTimePicker
    } from 'mdbvue';
    import mdbModal from 'mdbvue/lib/components/mdbModal';

    import AddPatientProvider from "./AddPatientProvider";

    export default {
        name: "AddPatientModal",
        components: {
            mdbIcon,
            mdbModal,
            mdbModalBody,
            mdbModalFooter,
            mdbModalHeader,
            mdbModalTitle,
            mdbBtn,
            mdbAlert,
            mdbInput,
            mdbContainer,
            mdbCol,
            mdbRow,
            mdbTabs,
            mdbTimePicker,
            mdbDatePicker,
            'add-patient-provider': AddPatientProvider
        },
        props: ['options'],
        data() {
            return {
                patient: {
                    firstName: null,
                    lastName: null,
                    dob: null,
                    email: null,
                    phoneNumber: null,
                    appointmentDate: null,
                    appointmentTime: null,
                    appointment: null
                },
                validation: {
                    firstName: {
                        valid: false,
                        validated: false
                    },
                    lastName: {
                        valid: false,
                        validated: false
                    },
                    dob: {
                        valid: false,
                        validated: false
                    },
                    email: {
                        //optional
                        valid: true,
                        validated: false
                    },
                    phoneNumber: {
                        valid: false,
                        validated: false
                    },
                    provider: {
                        valid: false,
                        validated: false
                    },
                    appointment: {
                        //optional
                        valid: true,
                        validated: false
                    },
                },
                waiting: false,
                error: null,
            };
        },
        created() {

        },
        mounted() {
            this.resetForm();

            //custom validation does not work on time picker
            this.$watch(() => this.$refs.appointmentTimePicker.value, (value) => {
                this.validate('appointmentTime', value);
            });

            this.$watch(() => this.$refs.providerComponent.isFormValid, (value) => {
                this.validation['provider'].valid = value;
                this.validation['provider'].validated = true
            });
        },
        methods: {

            hasImportFromCcdUrl() {
                return !!this.options.ccdImporterUrl;
            },

            getImportFromCcdUrl() {
                return this.options.ccdImporterUrl ? `${this.options.ccdImporterUrl}?source=importer_awv` : '#';
            },

            validate(key, value) {
                switch (key) {
                    case "firstName":
                    case "lastName":
                        this.validation[key].valid = value.length > 0;
                        break;
                    case "phoneNumber":
                        const phoneRe = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
                        this.validation[key].valid = phoneRe.test(value);
                        break;
                    case "email":
                        if (!value || !value.length) {
                            //optional
                            this.validation[key].valid = true;
                        } else {
                            const re = /\S+@\S+\.\S+/;
                            this.validation[key].valid = re.test(value);
                        }
                        break;
                    case "appointmentDate":
                    case "appointmentTime":
                        const dtStr = this.getDateTimeFromPickers(value, key === "appointmentDate" ? 'date' : 'time');
                        if (!dtStr || !dtStr.length) {
                            //optional
                            this.validation["appointment"].valid = true;
                        } else {
                            const date = new Date(dtStr);
                            const isValid = date instanceof Date && !isNaN(date);
                            this.validation["appointment"].valid = isValid;
                            if (isValid) {
                                this.patient.appointment = dtStr;
                            }
                        }
                        break;
                    default:
                        this.validation[key].valid = true;
                        break;
                }

                if (key === "appointmentDate" || key === "appointmentTime") {
                    this.validation["appointment"].validated = true;
                }
                else {
                    this.validation[key].validated = true;
                }
            },

            getDateTimeFromPickers(value, picker) {
                let dateVal = this.$refs.appointmentDatePicker.value;
                let timeVal = this.$refs.appointmentTimePicker.value;
                if (picker === 'date') {
                    dateVal = value;
                } else {
                    timeVal = value;
                }

                const hasTimeVal = timeVal ? timeVal.length > 0 : false;

                if (!dateVal && !hasTimeVal) {
                    return null;
                }

                if ((dateVal && !hasTimeVal) || (!dateVal && hasTimeVal)) {
                    return 'invalid';
                }

                let hours = this.$refs.appointmentTimePicker.computedHours;
                if (this.$refs.appointmentTimePicker.dayTime === 'pm') {
                    hours = +hours; //transform to number
                    hours += 12;
                }

                if (hours === 24) {
                    hours = '00';
                }

                const minutes = this.$refs.appointmentTimePicker.computedMinutes;
                return `${dateVal} ${hours}:${minutes}`;
            },

            resetForm() {
                this.patient = {
                    firstName: null,
                    lastName: null,
                    dob: null,
                    email: null,
                    phoneNumber: null,
                };
            },

            save(e) {

                e.preventDefault();

                this.error = null;
                this.waiting = true;

                const url = `/manage-patients/store`;

                const req = {
                    patient: this.patient,
                    provider: this.$refs.providerComponent.getUser()
                };

                axios.post(url, req)
                    .then(resp => {
                        this.waiting = false;
                        this.options.onDone();
                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    })
            },

            cancel(e) {
                e.preventDefault();
                this.options.onDone();
            },

            handleError(error) {
                console.log(error);
                if (error.response && error.response.status === 504) {
                    this.error = `Server took too long to respond [${error.response.status}]. Please try again.`;
                } else if (error.response && error.response.status === 500) {
                    this.error = `There was an error with our servers [${error.response.status}]. Please contact CLH support.`;
                    console.error(error.response.data);
                } else if (error.response && error.response.status === 404) {
                    this.error = `Not Found [${error.response.status}]`;
                } else if (error.response && (error.response.status === 401 || error.response.status === 419)) {
                    this.error = `Not Authenticated [${error.response.status}]`;
                    //reload the page which will redirect to login
                    window.location.reload();
                } else if (error.response && error.response.data) {
                    const errors = [error.response.data.error];
                    Object.keys(error.response.data.errors || []).forEach(e => {
                        errors.push(error.response.data.errors[e]);
                    });
                    this.error = errors.join('<br/>');
                } else {
                    this.error = error.message;
                }
            }
        },
        computed: {
            isFormValid() {
                for (let i in this.validation) {
                    if (!this.validation[i].valid) {
                        return false;
                    }
                }
                return true;
            }
        }
    }
</script>

<style scoped>

    .container {
        position: relative;
    }

    .provider-container {
        padding-top: 10px;
        padding-bottom: 10px;
        border: 1px solid #dee2e6;
    }

</style>
