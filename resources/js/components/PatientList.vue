<template>
    <div>
        <v-server-table class="table" :url="getUrl()" :columns="columns" :options="options" ref="table">
            <template slot="hra_status" slot-scope="props">
                <a v-if="props.row.hra_status" :href="getHraUrl(props.row)">{{getStatusTitle(props.row.hra_status)}}</a>
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
                    <mdb-dropdown-menu right :dropup="isLastRow(props)">
                        <mdb-dropdown-item @click="sendHraLink(props.row)">Send HRA link</mdb-dropdown-item>
                        <mdb-dropdown-item @click="sendVitalsLink(props.row)">Send Vitals link</mdb-dropdown-item>
                    </mdb-dropdown-menu>
                </mdb-dropdown>
            </template>
        </v-server-table>
        <send-link-modal v-if="sendLinkModalOptions.show" :options="sendLinkModalOptions"></send-link-modal>
    </div>
</template>

<script>

    import {mdbDropdown, mdbDropdownItem, mdbDropdownMenu, mdbDropdownToggle} from 'mdbvue';
    import SendLinkModal from './SendLinkModal';

    let self;

    export default {
        name: "PatientList",
        components: {SendLinkModal, mdbDropdown, mdbDropdownItem, mdbDropdownMenu, mdbDropdownToggle},
        props: ['debug'],
        data() {
            return {
                sendLinkModalOptions: {
                    debug: this.debug,
                    show: false,
                    survey: null,
                    patientId: null,
                    onDone: null,
                },
                columns: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob', 'actions'],
                options: {
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
                    filterable: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                    sortable: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                    sortIcon: {
                        base: 'fa',
                        is: 'fa-sort',
                        up: 'fa-sort-asc',
                        down: 'fa-sort-desc'
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

            isLastRow(props) {
                return this.$refs.table.count === props.index;
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
