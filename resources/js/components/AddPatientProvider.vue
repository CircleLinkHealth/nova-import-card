<template>
    <div>

        <mdb-row v-if="waiting">
            <mdb-col>
                <div class="spinner-overlay">
                    <div class="text-center">
                        <mdb-icon icon="spinner" :spin="true"/>
                    </div>
                </div>
            </mdb-col>
        </mdb-row>

        <mdb-row v-if="!isCreatingNew && !(provider && provider.id)">
            <mdb-col>
                <mdb-input class="mt-0 mb-3"
                           placeholder="Provider"
                           :value="searchValue"
                           @input="onSearchValueChanged"
                           ariaDescribedBy="button-addon2">
                    <mdb-btn color="primary" size="md"
                             icon="plus"
                             group slot="append"
                             v-show="searchValue.length > MIN_SEARCH_VALUE"
                             @click.native="toggleCreateNew"
                             :disabled="waiting"
                             id="button-addon2">
                        Create New
                    </mdb-btn>
                </mdb-input>
            </mdb-col>
        </mdb-row>

        <div v-if="!isCreatingNew && !(provider && provider.id)">
            <mdb-list-group class="providers-list">
                <mdb-list-group-item v-for="result in searchResults"
                                     :action="true"
                                     :key="result.id"
                                     @click.native="onSelectProvider(result.id)">
                    {{result.display_name}}
                </mdb-list-group-item>
            </mdb-list-group>
            <p v-show="searchFinished && searchResults.length === 0">
                Cannot find provider.
            </p>
        </div>

        <div v-if="provider.id && !isCreatingNew">
            <mdb-row>
                <mdb-col class="text-right">
                    <mdb-btn size="md" icon="search" outline="primary" @click.native="toggleSearchAgain">
                        Search again
                    </mdb-btn>
                </mdb-col>
            </mdb-row>
            <mdb-list-group>
                <mdb-list-group-item :active="true">
                    {{provider.display_name}}
                </mdb-list-group-item>
            </mdb-list-group>
        </div>

        <div v-if="isCreatingNew">

            <mdb-row>
                <mdb-col class="text-right">
                    <mdb-btn size="md" icon="search" outline="primary" @click.native="toggleSearchAgain">
                        Search again
                    </mdb-btn>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col md="6">
                    <mdb-input label="Provider First Name *" v-model="provider.firstName"
                               :customValidation="validation.firstName.validated"
                               :isValid="validation.firstName.valid"
                               @change="validate('firstName', $event)" invalidFeedback="Please set a first name."/>
                </mdb-col>
                <mdb-col md="6">
                    <mdb-input label="Provider Last Name *" v-model="provider.lastName"
                               :customValidation="validation.lastName.validated"
                               :isValid="validation.lastName.valid"
                               @change="validate('lastName', $event)" invalidFeedback="Please set a last name."/>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-select v-model="specialties" placeholder="Select a specialty"
                                label="Specialty *"
                                :customValidation="validation.specialty.validated"
                                :isValid="validation.specialty.valid"
                                @change="onSelectSpecialty"/>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-select v-model="practices" placeholder="Select a practice"
                                label="Practice *"
                                :customValidation="validation.primaryPracticeId.validated"
                                :isValid="validation.primaryPracticeId.valid"
                                @change="onSelectPractice"/>
                </mdb-col>
                <mdb-col>
                    <mdb-select v-model="suffixes" placeholder="Select clinical type"
                                label="Clinical Type *"
                                :customValidation="validation.suffix.validated"
                                :isValid="validation.suffix.valid"
                                @change="onSelectClinicalType"/>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-input label="Email *" v-model="provider.email"
                               type="email"
                               :customValidation="validation.email.validated"
                               :isValid="validation.email.valid"
                               @change="validate('email', $event)"
                               invalidFeedback="Please set a valid email."/>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-input label="EMR Direct Address" v-model="provider.emrDirect"
                               type="email"
                               :customValidation="validation.emrDirect.validated"
                               :isValid="validation.emrDirect.valid"
                               @change="validate('emrDirect', $event)"
                               invalidFeedback="Please set a valid EMR Direct Address."/>
                </mdb-col>
            </mdb-row>

            <mdb-row style="margin-top: 5px">
                <input type="hidden" v-model="isFormValid"/>
                <mdb-col>
                    <mdb-alert v-if="error" color="danger">
                        {{error}}
                    </mdb-alert>
                </mdb-col>
            </mdb-row>

        </div>

    </div>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbCol,
        mdbContainer,
        mdbIcon,
        mdbInput,
        mdbListGroup,
        mdbListGroupItem,
        mdbModal,
        mdbModalBody,
        mdbModalFooter,
        mdbModalHeader,
        mdbModalTitle,
        mdbRow,
        mdbSelect
    } from 'mdbvue';

    import specialties from './specialties-options';
    import suffixes from './suffix-options';

    import * as _ from "lodash";

    let self;

    export default {
        name: "AddPatientProvider",
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
            mdbListGroup,
            mdbListGroupItem,
            mdbSelect
        },
        props: [],
        data() {
            return {
                MIN_SEARCH_VALUE: 2,
                waiting: false,
                error: null,
                searchValue: '',
                searchResults: [],
                //needed because we are de-bouncing search, so the waiting flag is not enough
                searchFinished: false,
                isCreatingNew: false,
                practices: [],
                specialties: specialties.map(s => {
                    return {text: s.text, value: s.id};
                }),
                suffixes: suffixes.map(s => {
                    return {text: s.text, value: s.id};
                }),
                provider: this.getNewProvider(),
                validation: {
                    email: {
                        valid: false,
                        validated: false
                    },
                    firstName: {
                        valid: false,
                        validated: false
                    },
                    lastName: {
                        valid: false,
                        validated: false
                    },
                    suffix: {
                        valid: false,
                        validated: false
                    },
                    primaryPracticeId: {
                        valid: false,
                        validated: false
                    },
                    specialty: {
                        valid: false,
                        validated: false
                    },
                    emrDirect: {
                        //optional
                        valid: true,
                        validated: false
                    }
                }
            }
        },
        created() {
            self = this;
        },
        mounted() {
            this.getPractices();
        },
        computed: {
            isFormValid() {

                //in case user selected a provider,
                //no need to check further
                if (this.provider.id) {
                    return true;
                }

                for (let i in this.validation) {
                    if (!this.validation[i].valid) {
                        return false;
                    }
                }
                return true;
            }
        },
        methods: {


            /**
             * Called from parent component.
             *
             * @returns {provider|{zip, address, address2, city, last_name, suffix, phone_numbers, id, state, primary_practice, first_name, email, provider_info}}
             */
            getUser() {
                return this.provider;
            },

            validate(key, value) {
                const emailValidation = /\S+@\S+\.\S+/;
                switch (key) {
                    case "firstName":
                    case "lastName":
                        this.validation[key].valid = value.length > 0;
                        break;
                    case "email":
                        this.validation[key].valid = emailValidation.test(value);
                        break;
                    case "emrDirect":
                        if (!value || value.length === 0) {
                            this.validation[key].valid = true;
                        } else {
                            this.validation[key].valid = emailValidation.test(value);
                        }
                        break;
                    default:
                        this.validation[key].valid = true;
                }
                this.validation[key].validated = true;
            },

            getNewProvider() {
                return {
                    id: null,
                    email: null,
                    firstName: null,
                    lastName: null,
                    suffix: null,
                    primaryPracticeId: null,
                    specialty: null,
                    isClinical: null,
                    emrDirect: null
                };
            },

            getCreateUrl() {
                return 'manage-patients/providers/add';
            },

            getSearchUrl(searchVal) {
                return `manage-patients/providers/search?name=${searchVal}`;
            },

            getPracticesUrl() {
                return 'manage-patients/practices/search';
            },

            getPractices() {
                this.waiting = true;
                const url = this.getPracticesUrl();
                axios.get(url)
                    .then(resp => {
                        this.waiting = false;
                        this.practices = (resp.data || []).map(p => {
                            return {text: p.display_name, value: p.id}
                        });
                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    });
            },

            getProviders(searchVal) {
                this.waiting = true;
                axios.get(this.getSearchUrl(searchVal))
                    .then(resp => {
                        this.waiting = false;
                        this.searchResults = resp.data.results.map(p => {
                            if (!p.display_name || p.display_name.length === 0) {
                                p.display_name = `${p.first_name} ${p.last_name}`;
                            }
                            return p;
                        });
                        this.searchFinished = true;
                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    });
            },

            toggleSearchAgain() {
                this.isCreatingNew = false;
                this.searchValue = "";
                this.searchResults = [];
                this.searchFinished = false;
                this.provider = this.getNewProvider();
            },

            toggleCreateNew(event) {
                if (event && event.x === 0) {
                    // this is an Enter press, and Vue simulates a mouse event because we are inside an input
                    event.preventDefault();
                    return;
                }
                this.isCreatingNew = !this.isCreatingNew;

                if (this.isCreatingNew) {
                    this.getNewProvider();
                }

                this.searchValue = "";
            },

            onSearchValueChanged: _.debounce((val) => {
                self.searchFinished = false;
                self.searchValue = val;
                if (self.searchValue.length > self.MIN_SEARCH_VALUE) {
                    self.getProviders(self.searchValue);
                }
            }, 1000),

            onSelectSpecialty(id) {
                this.provider.specialty = id;
                this.validate('specialty', id);
            },

            onSelectPractice(id) {
                this.provider.primaryPracticeId = id;
                this.validate('primaryPracticeId', id);
            },

            onSelectClinicalType(id) {
                const suffix = suffixes.find(s => s.id === id);
                this.provider.isClinical = id !== "non-clinical";
                this.provider.suffix = suffix.text;
                this.validate('suffix', id);
            },

            onSelectProvider(id) {
                const provider = this.searchResults.find(p => p.id === id);
                this.provider.id = id;
                this.provider.display_name = provider.display_name;
                this.provider.primaryPracticeId = provider.primary_practice.id;
                this.isCreatingNew = false;
            },

            cancel() {
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
        }
    }
</script>

<style scoped>
    .providers-list {
        overflow-y: auto;
        max-height: 150px;
    }
</style>
