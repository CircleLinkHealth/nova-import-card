<template>
    <mdb-container>
        <add-patient-modal v-if="addPatientModalOptions.show" :options="addPatientModalOptions"></add-patient-modal>
        <send-link-modal v-if="sendLinkModalOptions.show" :options="sendLinkModalOptions"></send-link-modal>
        <mdb-row class="no-gutters">
            <mdb-col md="8" sm="12">
                <h1>AWV Patient List</h1>
            </mdb-col>
            <mdb-col md="4" sm="12">
                <p class="text-right">
                    <mdb-btn @click="addPatient" class="text-right">
                        Add AWV Patient
                    </mdb-btn>
                </p>
            </mdb-col>
        </mdb-row>
        <mdb-row>
            <v-server-table class="table" :url="getUrl()" :columns="columns" :options="options" ref="table">
                <template slot="patient_name" slot-scope="props">
                    <a v-if="hasWellnessDocsUrl()" :href="getWellnessDocsPage(props.row.patient_id)" target="_blank">
                        {{props.row.patient_name}}
                    </a>
                    <span v-else>{{props.row.patient_name}}</span>
                </template>
                <template slot="hra_status" slot-scope="props">
                    <a v-if="props.row.hra_status"
                       :href="getHraUrl(props.row)">{{getStatusTitle(props.row.hra_status)}}</a>
                    <span v-else>{{getStatusTitle(props.row.hra_status)}}</span>
                </template>
                <template slot="vitals_status" slot-scope="props">
                    <a v-if="props.row.vitals_status" :href="getVitalsUrl(props.row)">{{getStatusTitle(props.row.vitals_status)}}</a>
                    <span v-else>{{getStatusTitle(props.row.vitals_status)}}</span>
                </template>
                <template slot="eligibility" slot-scope="props">
                    <!-- todo -->
                    <span>Eligible</span>
                </template>
                <template slot="actions" slot-scope="props">
                    <mdb-dropdown class="actions">
                        <mdb-dropdown-toggle slot="toggle" class="actions-toggle">...</mdb-dropdown-toggle>
                        <mdb-dropdown-menu right :dropup="shouldDropUp(props)">
                            <mdb-dropdown-item @click="sendHraLink(props.row)">Send HRA link</mdb-dropdown-item>
                            <mdb-dropdown-item @click="sendVitalsLink(props.row)">Send Vitals link</mdb-dropdown-item>
                        </mdb-dropdown-menu>
                    </mdb-dropdown>
                </template>
            </v-server-table>
        </mdb-row>
    </mdb-container>
</template>

<script>

    import {
        mdbBtn,
        mdbCol,
        mdbContainer,
        mdbDropdown,
        mdbDropdownItem,
        mdbDropdownMenu,
        mdbDropdownToggle,
        mdbRow
    } from 'mdbvue';

    import SendLinkModal from './SendLinkModal';
    import AddPatientModal from './AddPatientModal';

    let self;

    export default {
        name: "PatientList",
        components: {
            AddPatientModal,
            SendLinkModal,
            mdbDropdown,
            mdbDropdownItem,
            mdbDropdownMenu,
            mdbDropdownToggle,
            mdbBtn,
            mdbContainer,
            mdbRow,
            mdbCol
        },
        props: ['debug', 'wellnessDocsUrl', 'ccdImporterUrl'],
        data() {
            return {
                addPatientModalOptions: {
                    ccdImporterUrl: this.ccdImporterUrl,
                    debug: this.debug,
                    show: false,
                    onDone: null
                },
                sendLinkModalOptions: {
                    debug: this.debug,
                    show: false,
                    survey: null,
                    patientId: null,
                    onDone: null,
                },
                columns: ['mrn', 'patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob', 'appointment', 'actions'],
                options: {
                    debounce: 1500,
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            // data.query.hideStatus = self.hideStatus;
                            // data.query.hideAssigned = self.hideAssigned;
                        }
                        return data;
                    },
                    columnsClasses: {
                        // 'selected': 'blank',
                        'actions': 'text-center'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    dateColumns: ['appointment'],
                    datepickerOptions: {
                        opens: 'left',
                    },
                    listColumns: {
                        hra_status: [
                            {id: 'null', text: this.getStatusTitle('null')},
                            {id: 'pending', text: this.getStatusTitle('pending')},
                            {id: 'in_progress', text: this.getStatusTitle('in_progress')},
                            {id: 'completed', text: this.getStatusTitle('completed')},
                        ],
                        vitals_status: [
                            {id: 'null', text: this.getStatusTitle('null')},
                            {id: 'pending', text: this.getStatusTitle('pending')},
                            {id: 'in_progress', text: this.getStatusTitle('in_progress')},
                            {id: 'completed', text: this.getStatusTitle('completed')},
                        ]
                    },
                    //todo: eligibility should have a dropdown for filters
                    filterable: ['mrn', 'patient_name', 'provider_name', 'hra_status', 'vitals_status', 'appointment', 'dob'],
                    initFilters: this.getInitialFiltersFromUrl(),
                    sortable: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'appointment', 'dob'],
                    sortIcon: {
                        base: 'fa',
                        is: 'fa-sort',
                        up: 'fa-sort-asc',
                        down: 'fa-sort-desc'
                    },
                    headings: {
                        'mrn': 'MRN',
                        'patient_name': 'Patient Name',
                        'provider_name': 'Provider Name',
                        'hra_status': 'HRA Status',
                        'vitals_status': 'Vitals Status',
                        'eligibility': 'Eligibility',
                        'dob': 'DOB',
                        'appointment': 'AWV Date',
                        'actions': 'Actions'
                    }
                },
            };
        },
        methods: {
            getUrl() {
                return `/manage-patients/list`
            },

            getHraUrl(patient) {
                return `survey/hra/${patient.patient_id}`;
            },

            getVitalsUrl(patient) {
                return `survey/vitals/${patient.patient_id}`;
            },

            hasWellnessDocsUrl() {
                return !!this.wellnessDocsUrl;
            },

            getWellnessDocsPage(patientId) {
                return this.wellnessDocsUrl ? this.wellnessDocsUrl.replace("$PATIENT_ID$", patientId) : '#';
            },

            getInitialFiltersFromUrl() {
                const search = new URLSearchParams(window.location.search);
                const val = search.get('appointment');
                if (!val) {
                    return null;
                }
                const parsed = JSON.parse(val);
                return {
                    'appointment': {
                        'start': moment(parsed['start']),
                        'end': moment(parsed['end']),
                    }
                };
            },

            getStatusTitle(id) {
                switch (id) {
                    case "pending":
                        return "Not Started";
                    case "in_progress":
                        return "In Progress";
                    case "completed":
                        return "Completed";
                    default:
                        return 'N/A';
                }
            },

            shouldDropUp(props) {
                const table = this.$refs.table;
                const count = table.data.length;
                const diff = count - props.index;
                return diff < 2;
            },

            sendHraLink(patient) {
                this.sendLinkModalOptions.patientId = patient.patient_id;
                this.sendLinkModalOptions.survey = "hra";
                this.sendLinkModalOptions.show = true;
                this.sendLinkModalOptions.onDone = () => {
                    this.sendLinkModalOptions.show = false;
                }
            },

            sendVitalsLink(patient) {
                this.sendLinkModalOptions.patientId = patient.patient_id;
                this.sendLinkModalOptions.survey = "vitals";
                this.sendLinkModalOptions.show = true;
                this.sendLinkModalOptions.onDone = () => {
                    this.sendLinkModalOptions.show = false;
                }
            },

            addPatient() {
                this.addPatientModalOptions.show = true;
                this.addPatientModalOptions.onDone = () => {
                    this.addPatientModalOptions.show = false;
                    this.refresh();
                }
            },

            refresh() {
                this.$refs.table.refresh();
            }
        },
        created() {
            self = this;
        }
    }


</script>

<style scoped>

    .dropdown.actions {
        border: none;
    }

    .actions-toggle {
        padding: 0;
        font-weight: 600;
        font-size: 20px;
        line-height: 1;
        letter-spacing: 3px;
        box-shadow: none;
        border: none;
        margin-top: -10px;
    }

    .actions-toggle:after {
        display: none;
    }

</style>
