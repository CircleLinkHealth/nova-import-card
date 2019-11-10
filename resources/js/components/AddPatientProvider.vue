<template>
    <div>

        <mdb-row v-if="waiting">
            <mdb-col>
                <div class="spinner-overlay">
                    <div class="text-center">
                        <font-awesome-icon icon="spinner" :spin="true"/>
                    </div>
                </div>
            </mdb-col>
        </mdb-row>

        <mdb-row v-if="!isCreatingNew">
            <mdb-col>
                <mdb-input label="Provider" :value="searchValue" @input="onSearchValueChanged"></mdb-input>
            </mdb-col>
        </mdb-row>

        <div v-if="!waiting && !isCreatingNew">
            <mdb-list-group>
                <mdb-list-group-item v-for="result in searchResults"
                                     :action="true"
                                     :key="result.it"
                                     @click.native="onSelectProvider">
                    {{result.display_name}}
                </mdb-list-group-item>
            </mdb-list-group>
            <p v-show="searchResults.length === 0 && searchValue.length > MIN_SEARCH_VALUE">
                Cannot find provider.
                <mdb-btn flat darkWaves @click.native="toggleCreateNew">Click here to create new.</mdb-btn>

            </p>
        </div>

        <div v-if="isCreatingNew">

            <mdb-row>
                <mdb-col>
                    <mdb-btn flat @click.native="toggleCreateNew">Search again</mdb-btn>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col md="6">
                    <mdb-input label="First Name" :model="provider.first_name" :required="true"></mdb-input>
                </mdb-col>
                <mdb-col md="6">
                    <mdb-input label="Last Name" :model="provider.last_name" :required="true"></mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <select class="browser-default custom-select" required @change="onSelectSpecialty">
                        <option selected>Select a specialty</option>
                        <option v-for="specialty in specialties"
                                :value="specialty.id"
                                :key="specialty.id">
                            {{specialty.text}}
                        </option>
                    </select>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col md="8">
                    <mdb-input label="Line 1" v-model="provider.address"></mdb-input>
                </mdb-col>
                <mdb-col md="4">
                    <mdb-input label="Line 2" v-model="provider.address2"></mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col md="4">
                    <mdb-input label="City" v-model="provider.city"></mdb-input>
                </mdb-col>
                <mdb-col md="4">
                    <mdb-input label="State" v-model="provider.state"></mdb-input>
                </mdb-col>
                <mdb-col md="4">
                    <mdb-input label="Zip" v-model="provider.zip"></mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-input label="Phone number" v-model="provider.phone_numbers[0]"
                               type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                               :required="true">
                    </mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <select class="browser-default custom-select" required @change="onSelectPractice">
                        <option selected>Select a practice</option>
                        <option v-for="practice in practices"
                                :value="practice.id"
                                :key="practice.id">
                            {{practice.display_name}}
                        </option>
                    </select>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-input label="Email" v-model="provider.email"
                               type="email">
                    </mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
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
        mdbInput,
        mdbListGroup,
        mdbListGroupItem,
        mdbModal,
        mdbModalBody,
        mdbModalFooter,
        mdbModalHeader,
        mdbModalTitle,
        mdbRow
    } from 'mdbvue';

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
    import specialties from './specialties-options';
    import {debounce} from "lodash";

    library.add(faSpinner);

    export default {
        name: "AddPatientProvider",
        components: {
            FontAwesomeIcon,
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
            mdbListGroupItem
        },
        props: [],
        data() {
            return {
                MIN_SEARCH_VALUE: 2,
                waiting: false,
                typing: false, //used to debounce the search
                error: null,
                searchValue: '',
                searchResults: [],
                isCreatingNew: false,
                practices: [],
                specialties,
                provider: {
                    id: null,
                    email: null,
                    first_name: null,
                    last_name: null,
                    suffix: null,
                    address: null,
                    address2: null,
                    city: null,
                    state: null,
                    zip: null,
                    phone_numbers: [],
                    primary_practice: {
                        id: null,
                        display_name: null,
                    },
                    provider_info: {
                        id: null,
                        is_clinical: null,
                        specialty: null,
                    }
                }
            }
        },
        created() {
        },
        mounted() {
            this.getPractices();
        },
        methods: {

            resetForm() {
                return {
                    id: null,
                    email: null,
                    first_name: null,
                    last_name: null,
                    suffix: null,
                    address: null,
                    address2: null,
                    city: null,
                    state: null,
                    zip: null,
                    phone_numbers: [],
                    primary_practice: {
                        id: null,
                        display_name: null,
                    },
                    provider_info: {
                        id: null,
                        is_clinical: null,
                        specialty: null,
                    }
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
                        this.practices = resp.data;
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
                        this.searchResults = resp.data.results;
                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    });
            },

            toggleCreateNew() {
                this.isCreatingNew = !this.isCreatingNew;

                if (this.isCreatingNew) {
                    this.resetForm();
                    this.provider.first_name = this.searchValue;
                }

                this.searchValue = "";
            },

            onSearchValueChanged(val) {
                this.searchValue = val;
                debounce(() => {
                    if (this.searchValue.length > this.MIN_SEARCH_VALUE) {
                        this.getProviders(this.searchValue);
                    }
                }, 1000)();
            },

            onSelectSpecialty(id) {

            },

            onSelectPractice(id) {

            },

            onSelectProvider(id) {

            },

            cancel() {
                this.options.onDone();
            },

            save() {
                this.waiting = true;
            },

            handleError(error) {
                console.log(error);
                if (error.response && error.response.status === 504) {
                    this.error = "Server took too long to respond. Please try again.";
                } else if (error.response && error.response.status === 500) {
                    this.error = "There was an error with our servers. Please contact CLH support.";
                    console.error(error.response.data);
                } else if (error.response && error.response.status === 404) {
                    this.error = "Not Found [404]";
                } else if (error.response && error.response.status === 419) {
                    this.error = "Not Authenticated [419]";
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
