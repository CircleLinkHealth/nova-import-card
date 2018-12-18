<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 text-left">
                <button class="btn btn-info btn-xs" v-bind:class="{'active': this._data.assigned}"
                        @click="showAssigned">Show Assigned
                </button>
                <button class="btn btn-info btn-xs" v-bind:class="{'active': this._data.consented}"
                        @click="showConsented">Show Consented
                </button>
                <button class="btn btn-info btn-xs" v-bind:class="{'active': this._data.ineligible}"
                        @click="showIneligible">Show Ineligible
                </button>
            </div>
            <div class="col-sm-6 text-right" v-if="enrolleesAreSelected">
                <button class="btn btn-primary btn-s" @click="assignSelectedToCa">Assign To CA</button>
                <button class="btn btn-danger btn-s" @click="markSelectedAsIneligible">Mark as Ineligible</button>
            </div>
        </div>
        <div class="panel-body" id="enrollees">
            <v-server-table :url="getUrl()" :columns="columns" :options="options" ref="table">
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
            </v-server-table>
        </div>
        <select-ca-modal ref="selectCaModal" :selected-enrollee-ids="selectedEnrolleeIds"></select-ca-modal>
        <mark-ineligible-modal ref="markIneligibleModal"
                               :selected-enrollee-ids="selectedEnrolleeIds"></mark-ineligible-modal>
        <edit-patient-modal ref="editPatientModal"></edit-patient-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import SelectCaModal from './comps/modals/select-ca.modal'
    import {Event} from 'vue-tables-2'
    import MarkIneligibleModal from "./comps/modals/mark-ineligible.modal";
    import EditPatientModal from "./comps/modals/edit-patient.modal";

    let self;

    export default {
        name: "CaDirectorPanel",
        components: {
            'mark-ineligible-modal': MarkIneligibleModal,
            'modal': Modal,
            'select-ca-modal': SelectCaModal,
            'edit-patient-modal': EditPatientModal,

        },
        props: [],
        data() {
            return {
                selectedEnrolleeIds: [],
                ineligible: false,
                consented: false,
                assigned: false,
                columns: ['select', 'edit', 'id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status', 'eligibility_job_id', 'medical_record_id', 'practice_name', 'provider_name', 'total_time_spent',
                    'last_call_outcome', 'last_call_outcome_reason', 'address', 'address_2', 'city', 'state', 'zip', 'primary_phone', 'other_phone', 'home_phone', 'cell_phone', 'dob', 'preferred_days', 'preferred_window',
                    'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'has_copay', 'email', 'cpm_problem_1', 'cpm_problem_2', 'soft_rejected_callback', 'created_at'],
                options: {
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['ineligible', 'consented', 'assigned', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status', 'eligibility_job_id', 'medical_record_id', 'practice_name', 'provider_name', 'primary_insurance', 'secondary_insurance', 'tertiary_insurance'],
                    sortable: ['first_name', 'last_name', 'practice_name', 'provider_name', 'primary_insurance', 'status', 'created_at', 'state', 'city'],
                    // listColumns: {
                    //     practice_id: [{
                    //         id: '8',
                    //         text: 'Demo'
                    //     },
                    //     ]
                    // }
                },
            }

        },
        computed: {
            enrolleesAreSelected() {
                return this.selectedEnrolleeIds.length !== 0;
            },
        },
        methods: {
            allSelected() {
                if (this.$refs.table) {
                    return this.selectedEnrolleeIds.length === this.$refs.table.data.length;
                } else {
                    return false;
                }
            },
            getUrl() {
                return rootUrl('/admin/ca-director/enrollees');
            },
            assignSelectedToCa() {
                Event.$emit("modal-select-ca:show");
            },
            markSelectedAsIneligible() {
                Event.$emit("modal-mark-ineligible:show");
            },
            editPatient(patient) {
                Event.$emit("modal-edit-patient:show", patient);
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
                }
                else {
                    this.selectedEnrolleeIds.splice(pos, 1);
                }
            },
            selected(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    return false;
                }
                else {
                    return true;
                }
            },
            showIneligible() {
                this._data.ineligible = !this._data.ineligible;
                const query = {
                    ineligible: this._data.ineligible,
                    consented: this._data.consented,
                    assigned: this._data.assigned
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                    })
            },
            showConsented() {
                this._data.consented = !this._data.consented;
                const query = {
                    ineligible: this._data.ineligible,
                    consented: this._data.consented,
                    assigned: this._data.assigned
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                    })
            },
            showAssigned() {
                this._data.assigned = !this._data.assigned;
                const query = {
                    ineligible: this._data.ineligible,
                    consented: this._data.consented,
                    assigned: this._data.assigned
                };
                this.axios.get(rootUrl(`/admin/ca-director/enrollees?query=${JSON.stringify(query)}&limit=100&ascending=1&page=1&byColumn=1`))
                    .then(resp => {
                        this.$refs.table.setData(resp.data);
                    })
            }
        },


        created() {
            console.info('created');
        },
        mounted() {
            console.info('mounted');
        }

    }


</script>

<style scoped>
    .panel-body {
        overflow-x: auto;
    }
</style>