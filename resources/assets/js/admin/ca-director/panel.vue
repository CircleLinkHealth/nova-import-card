<template>
    <div class="container-fluid">
        <div class="row">
            <div class="row">
                <div class="col-sm-12">
                    <notifications ref="notificationsComponent" name="ca-panel"></notifications>
                </div>
            </div>
            <div class="col-sm-12 text-left">
                <button class="btn btn-primary btn-s"
                        @click="addCustomFilter">Add Custom Filter
                </button>
            </div>
        </div>
        <div class="row">
            <div class="row">
            </div>
            <div class="col-sm-12 text-left" style="margin-bottom: 10px; margin-top: 20px">
                <button class="btn btn-info btn-s" v-bind:class="{'btn-selected': !this.hideAssigned}"
                        @click="showAssigned">{{this.showAssignedLabel}}
                </button>
            </div>
            <div class="col-sm-12 text-left" style="margin-bottom: 10px; margin-top: 10px">
                <button class="btn btn-info btn-s" v-bind:class="{'btn-selected': this.isolateUploadedViaCsv}"
                        @click="isolatePatientsUploadedViaCsv">{{this.showIsolatedViaCsvLabel}}
                </button>
            </div>
            <div class="col-sm-5 text-left">
                <button class="btn btn-info btn-xs"
                        v-bind:class="{'btn-selected': !this.hideStatus.includes('queue_auto_enrollment')}"
                        @click="showSelfEnrollment">Include Queued for Self-Enrollment
                </button>
                <button class="btn btn-info btn-xs"
                        v-bind:class="{'btn-selected': !this.hideStatus.includes('consented')}"
                        @click="showConsented">Include Consented
                </button>
                <button class="btn btn-info btn-xs"
                        v-bind:class="{'btn-selected': !this.hideStatus.includes('ineligible')}"
                        @click="showIneligible">Include Ineligible
                </button>
            </div>
            <div class="col-sm-2">
                <loader style="margin-left: 80px" v-if="loading"/>
            </div>
            <div class="col-sm-5 text-right" v-if="enrolleesAreSelected">
                <button class="btn btn-primary btn-s" @click="assignSelectedToCa">Assign To CA</button>
                <button class="btn btn-warning btn-s" @click="unassignSelectedFromCa">Unassign From CA</button>
                <button class="btn btn-danger btn-s" @click="markSelectedAsIneligible">Mark as Ineligible</button>
            </div>
            <div class="col-sm-12" style="margin-top: 1%">
                <button class="btn btn-primary btn-xs" @click="clearSelected">Clear Selected Patients</button>
            </div>
        </div>
        <div class="panel-body" id="enrollees">
            <v-server-table class="table" v-on:filter="listenTo" :url="getUrl()" :columns="columns" :options="options"
                            ref="table">
                <template slot="edit" slot-scope="props">
                    <input class="btn btn-warning btn-s" value="Edit" @click="editPatient(props.row)" type="button"/>
                </template>
                <template slot="h__edit" slot-scope="props">

                </template>
                <div slot="filter__select">
                    <input type="checkbox"
                           class="form-control check-all"
                           :checked="allSelected()"
                           @change="toggleAll()">
                </div>
                <template slot="select" slot-scope="props">
                    <input type="checkbox"
                           class="form-control"
                           :v-model="props.row.select"
                           :checked="selected(props.row.id)"
                           @change="toggleId(props.row.id)">
                </template>
                <template slot="total_time_spent" slot-scope="props">
                    {{formatSecondsToHHMMSS(props.row.total_time_spent)}}
                </template>
            </v-server-table>
        </div>
        <select-ca-modal ref="selectCaModal" :selected-enrollee-ids="selectedEnrolleeIds"></select-ca-modal>
        <unassign-ca-modal ref="unassignCaModal" :selected-enrollee-ids="selectedEnrolleeIds"></unassign-ca-modal>
        <mark-ineligible-modal ref="markIneligibleModal"
                               :selected-enrollee-ids="selectedEnrolleeIds"></mark-ineligible-modal>
        <edit-patient-modal ref="editPatientModal"></edit-patient-modal>
        <add-custom-filter-modal></add-custom-filter-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import SelectCaModal from './comps/modals/select-ca.modal'
    import UnassignCaModal from './comps/modals/unassign-ca.modal'
    import {Event} from 'vue-tables-2'
    import MarkIneligibleModal from "./comps/modals/mark-ineligible.modal";
    import AddCustomFilterModal from "./comps/modals/add-custom-filter.modal";
    import EditPatientModal from "./comps/modals/edit-patient.modal";
    import Loader from '../../components/loader';
    import Notifications from '../../components/notifications';

    let self;

    export default {
        name: "CaDirectorPanel",
        components: {
            'mark-ineligible-modal': MarkIneligibleModal,
            'modal': Modal,
            'select-ca-modal': SelectCaModal,
            'unassign-ca-modal': UnassignCaModal,
            'edit-patient-modal': EditPatientModal,
            'add-custom-filter-modal': AddCustomFilterModal,
            'loader': Loader,
            'notifications': Notifications

        },
        props: [],
        data() {
            return {
                loading: false,
                selectedEnrolleeIds: [],
                hideStatus: ['ineligible', 'consented', 'queue_auto_enrollment', 'enrolled', 'rejected', 'soft_rejected'],
                hideAssigned: true,
                isolateUploadedViaCsv: false,
                columns: ['select', 'edit', 'id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status', 'source', 'enrollment_non_responsive', 'auto_enrollment_triggered', 'practice_name', 'provider_name', 'requested_callback', 'total_time_spent', 'attempt_count', 'last_attempt_at',
                    'last_call_outcome', 'last_call_outcome_reason', 'address', 'address_2', 'city', 'state', 'zip', 'primary_phone', 'other_phone', 'home_phone', 'cell_phone', 'dob', 'preferred_days', 'preferred_window',
                    'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'has_copay', 'email', 'provider_pronunciation', 'provider_sex', 'last_encounter', 'eligibility_job_id', 'medical_record_id', 'created_at'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.hideStatus = self.hideStatus;
                            data.query.hideAssigned = self.hideAssigned;
                            data.query.isolateUploadedViaCsv = self.isolateUploadedViaCsv;
                        }
                        return data;
                    },
                    headings: {
                        enrollment_non_responsive : 'Send Regular Mail'
                    },
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['hideStatus', 'hideAssigned', 'id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status','source', 'requested_callback', 'eligibility_job_id', 'enrollment_non_responsive', 'last_attempt_at', 'auto_enrollment_triggered','medical_record_id', 'practice_name', 'provider_name', 'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'attempt_count'],
                    sortable: ['id', 'user_id', 'first_name', 'last_name', 'practice_name', 'provider_name', 'primary_insurance', 'status', 'source', 'created_at', 'state', 'city','enrollment_non_responsive', 'auto_enrollment_triggered', 'last_attempt_at', 'care_ambassador_name', 'attempt_count', 'requested_callback'],
                },
            }

        },
        computed: {
            enrolleesAreSelected() {
                return this.selectedEnrolleeIds.length !== 0;
            },
            showAssignedLabel() {
                return this.hideAssigned ? 'Show Assigned Patients Only' : 'Show Unassigned Patients'
            },
            showIsolatedViaCsvLabel(){
                return this.isolateUploadedViaCsv ? 'Show Patients from All Sources' : 'Isolate Patients Uploaded via CSV';
            }
        },
        methods: {
            formatSecondsToHHMMSS(seconds) {
                return new Date(1000 * seconds).toISOString().substr(11, 8);
            },
            allSelected() {
                if (this.$refs.table) {
                    return this.selectedEnrolleeIds.length === this.$refs.table.data.length;
                } else {
                    return false;
                }
            },
            clearSelected() {
                this.selectedEnrolleeIds = [];
            },
            refreshTable() {
                this.$refs.table.refresh();
            },
            getUrl() {
                return rootUrl('/admin/ca-director/enrollees');
            },
            assignSelectedToCa() {
                Event.$emit("modal-select-ca:show");
            },
            unassignSelectedFromCa() {
                Event.$emit("modal-unassign-ca:show");
            },
            markSelectedAsIneligible() {
                Event.$emit("modal-mark-ineligible:show");
            },
            editPatient(patient) {
                Event.$emit("modal-edit-patient:show", patient);
            },
            addCustomFilter() {
                Event.$emit("modal-add-custom-filter:show");
            },
            toggleAll() {
                let selected = [];
                if (this.selectedEnrolleeIds.length === 0) {
                    this.$refs.table.data.forEach(function (user) {
                        selected.push(user.id);
                    })
                    this.selectedEnrolleeIds = selected;
                } else {
                    this.selectedEnrolleeIds = [];
                }

            },
            toggleId(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    this.selectedEnrolleeIds.push(id);
                } else {
                    this.selectedEnrolleeIds.splice(pos, 1);
                }
            },
            selected(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    return false;
                } else {
                    return true;
                }
            },
            showIneligible() {
                Event.$emit('notifications-ca-panel:dismissAll');
                this.loading = true;
                if (this.hideStatus.includes('ineligible'))
                    this.hideStatus = this.hideStatus.filter(item => item !== 'ineligible');
                else
                    this.hideStatus.push('ineligible');

                const query = {
                    hideStatus: this.hideStatus,
                    hideAssigned: this.hideAssigned,
                    isolateUploadedViaCsv : this.isolateUploadedViaCsv
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                        this.loading = false;
                    })
                    .catch(err => {
                        let errors = err.response.data.errors ? err.response.data.errors : [];
                        this.loading = false;
                        Event.$emit('notifications-ca-panel:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
                    });
            },
            showSelfEnrollment() {
                Event.$emit('notifications-ca-panel:dismissAll');
                this.loading = true;
                if (this.hideStatus.includes('queue_auto_enrollment'))
                    this.hideStatus = this.hideStatus.filter(item => item !== 'queue_auto_enrollment');
                else
                    this.hideStatus.push('queue_auto_enrollment');

                const query = {
                    hideStatus: this.hideStatus,
                    hideAssigned: this.hideAssigned,
                    isolateUploadedViaCsv : this.isolateUploadedViaCsv
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                        this.loading = false;
                    })
                    .catch(err => {
                        let errors = err.response.data.errors ? err.response.data.errors : [];
                        this.loading = false;
                        Event.$emit('notifications-ca-panel:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
                    });
            },
            showConsented() {
                Event.$emit('notifications-ca-panel:dismissAll');
                this.loading = true;
                if (this.hideStatus.includes('consented')) {
                    this.hideStatus = this.hideStatus.filter(item => item !== 'consented');
                    this.hideAssigned = false;
                } else {
                    this.hideAssigned = true;
                    this.hideStatus.push('consented');
                }


                const query = {
                    hideStatus: this.hideStatus,
                    hideAssigned: this.hideAssigned,
                    isolateUploadedViaCsv : this.isolateUploadedViaCsv
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                        this.loading = false;
                    }).catch(err => {
                    let errors = err.response.data.errors ? err.response.data.errors : [];
                    this.loading = false;
                    Event.$emit('notifications-ca-panel:create', {
                        noTimeout: true,
                        text: errors,
                        type: 'error'
                    });
                })
            },
            showAssigned() {
                Event.$emit('notifications-ca-panel:dismissAll');
                this.loading = true;
                this.hideAssigned = !this.hideAssigned;
                const query = {
                    hideStatus: this.hideStatus,
                    hideAssigned: this.hideAssigned,
                    isolateUploadedViaCsv : this.isolateUploadedViaCsv
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                        this.loading = false;
                    }).catch(err => {
                    let errors = err.response.data.errors ? err.response.data.errors : [];
                    this.loading = false;
                    Event.$emit('notifications-ca-panel:create', {
                        noTimeout: true,
                        text: errors,
                        type: 'error'
                    });
                })
            },
            isolatePatientsUploadedViaCsv(){
                Event.$emit('notifications-ca-panel:dismissAll');
                this.loading = true;
                this.isolateUploadedViaCsv = ! this.isolateUploadedViaCsv
                const query = {
                    hideStatus: this.hideStatus,
                    hideAssigned: this.hideAssigned,
                    isolateUploadedViaCsv : this.isolateUploadedViaCsv
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                        this.loading = false;
                    }).catch(err => {
                    let errors = err.response.data.errors ? err.response.data.errors : [];
                    this.loading = false;
                    Event.$emit('notifications-ca-panel:create', {
                        noTimeout: true,
                        text: errors,
                        type: 'error'
                    });
                })
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            }

        },


        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            Event.$on('clear-selected-enrollees', this.clearSelected)
            Event.$on('refresh-table', this.refreshTable)
            console.info('mounted');
        }

    }


</script>

<style>
    .VueTables__child-row-toggler {
        width: 100%;
        height: 16px;
        line-height: 16px;
        display: block;
        margin: auto;
        text-align: center;
    }

    th {
        min-width: 80px;
    }

    .table {
        overflow-x: visible;

    }

    .table > tbody > tr td {
        white-space: nowrap;
        padding: 0px;
        line-height: 0.6;
        vertical-align: middle;
        text-align: center;
        padding-left: 5px;
        padding-right: 5px;
    }

    .btn-selected {
        background-color: #0d47a1;
    }

    tr.v-server-table__selected {
        background: #7d92f5 !important;
    }

    .panel-body {

    }
</style>