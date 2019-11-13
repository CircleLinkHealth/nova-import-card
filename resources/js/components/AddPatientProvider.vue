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

        <mdb-row v-if="!isCreatingNew && !(provider && provider.id)">
            <mdb-col>
                <mdb-input label="Provider" :value="searchValue" @input="onSearchValueChanged"></mdb-input>
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
            <p v-show="searchResults.length === 0 && searchValue.length > MIN_SEARCH_VALUE">
                Cannot find provider.
            </p>
            <p v-show="searchValue.length > MIN_SEARCH_VALUE">
                <mdb-btn size="sm" flat darkWaves @click.native="toggleCreateNew" :disabled="waiting">Click here to
                    create new.
                </mdb-btn>
            </p>
        </div>

        <div v-if="provider.id && !isCreatingNew">
            <mdb-row>
                <mdb-col>
                    <mdb-btn size="sm" flat @click.native="toggleSearchAgain">Search again</mdb-btn>
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
                <mdb-col>
                    <mdb-btn size="sm" flat @click.native="toggleCreateNew">Search again</mdb-btn>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col md="6">
                    <mdb-input label="First Name" v-model="provider.first_name" :required="true"></mdb-input>
                </mdb-col>
                <mdb-col md="6">
                    <mdb-input label="Last Name" v-model="provider.last_name" :required="true"></mdb-input>
                </mdb-col>
            </mdb-row>

            <mdb-row>
                <mdb-col>
                    <mdb-select v-model="specialties" placeholder="Select a specialty" :required="true"
                                @change="onSelectSpecialty"/>
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
                    <mdb-select v-model="practices" placeholder="Select a practice" :required="true"
                                @change="onSelectPractice"/>
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
                    <mdb-select v-model="suffixes" placeholder="Select clinical type" :required="true"
                                @change="onSelectClinicalType"/>
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
        mdbRow,
        mdbSelect
    } from 'mdbvue';

    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import specialties from './specialties-options';
    import suffixes from './suffix-options';

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
            mdbListGroupItem,
            mdbSelect
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
                specialties: specialties.map(s => {
                    return {text: s.text, value: s.id};
                }),
                suffixes: suffixes.map(s => {
                    return {text: s.text, value: s.id};
                }),
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

            /**
             * Called from parent component.
             *
             * @returns {provider|{zip, address, address2, city, last_name, suffix, phone_numbers, id, state, primary_practice, first_name, email, provider_info}}
             */
            getUser() {
                return this.provider;
            },

            getNewProvider() {
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
                this.provider = this.getNewProvider();
            },

            toggleCreateNew() {
                this.isCreatingNew = !this.isCreatingNew;

                if (this.isCreatingNew) {
                    this.getNewProvider();
                }

                this.searchValue = "";
            },

            onSearchValueChanged(val) {

                if (this.waiting) {
                    setTimeout(() => this.onSearchValueChanged(val), 500);
                    return;
                }

                this.searchValue = val;
                if (this.searchValue.length > this.MIN_SEARCH_VALUE) {
                    this.getProviders(this.searchValue);
                }
            },

            onSelectSpecialty(id) {
                this.provider.provider_info.specialty = id;
            },

            onSelectPractice(id) {
                this.provider.primary_practice.id = id;
            },

            onSelectClinicalType(id) {
                const suffix = suffixes.find(s => s.id === id);
                this.provider.provider_info.is_clinical = id !== "non-clinical";
                this.provider.suffix = suffix.text;
            },

            onSelectProvider(id) {
                const provider = this.searchResults.find(p => p.id === id);
                this.provider.id = id;
                this.provider.display_name = provider.display_name;
                this.isCreatingNew = false;
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

<style scoped>
    .providers-list {
        overflow-y: auto;
        max-height: 150px;
    }
</style>
