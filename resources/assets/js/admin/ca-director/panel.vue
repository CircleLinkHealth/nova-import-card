<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 text-left">
                <button class="btn btn-info btn-xs" @click="assignSelectedToCa">Show Assigned</button>
                <button class="btn btn-info btn-xs" @click="assignSelectedToCa">Show Consented</button>
                <button class="btn btn-info btn-xs" @click="assignSelectedToCa">Show Ineligible</button>

            </div>
            <div class="col-sm-6 text-right">
                <button class="btn btn-primary btn-xs" @click="assignSelectedToCa">Assign To CA</button>
                <button class="btn btn-danger btn-xs" @click="assignSelectedToCa">Mark as Ineligible</button>
            </div>
        </div>
        <div class="panel-body" id="enrollees">
            <v-server-table :url="getUrl()" :columns="columns" :options="options" ref="table">
                <div slot="filter__select">
                    <input type="checkbox"
                           class="form-control check-all"
                           :value='allSelected'
                           @change="toggleAll()">
                </div>
                <template slot="select" slot-scope="props">
                    <input type="checkbox"
                           class="form-control"
                           :v-model="props.row.select"
                           @change="toggleId(props.row.id)">
                </template>
            </v-server-table>
        </div>
        <select-ca-modal ref="selectCaModal" :selected-enrollee-ids="selectedEnrolleeIds"></select-ca-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import SelectCaModal from './comps/modals/select-ca.modal'
    import {Event} from 'vue-tables-2'

    export default {
        name: "CaDirectorPanel",
        components: {
            'modal': Modal,
            'select-ca-modal': SelectCaModal,

        },
        props: [],
        data() {
            return {
                selectedEnrolleeIds: [],
                columns: ['select', 'id', 'user_id', 'first_name', 'last_name', 'batch_id', 'eligibility_job_id', 'medical_record_type', 'practice_id', 'provider_id', 'primary_insurance', 'primary_phone', 'created_at'],
                options: {
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['practice_id', 'provider_id', 'primary_insurance'],
                    sortable: ['first_name', 'last_name', 'practice_id', 'provider_id', 'primary_insurance'],
                    listColumns: {
                        practice_id: [{
                            id: '8',
                            text: 'Demo'
                        },
                        ]
                    }
                },
            }

        },
        computed: {
            allSelected: function () {
                return false;
            },
            selectAll: {
                get: function () {
                    return this.users ? this.selected.length === this.users.length : false
                },
                set: function (value) {
                    var selected = []
                    if (value) {
                        this.users.forEach(function (user) {
                            selected.push(user.id)
                        })
                    }
                    this.selected = selected
                }
            },
            // selectedPatients() {
            //     return this.tableData.filter(row => row.selected && row.Patient);
            // }
        },
        methods: {
            getUrl() {
                return rootUrl('/admin/ca-director/enrollees');
            },
            assignSelectedToCa() {
                Event.$emit("modal-select-ca:show", {enrolleeIds: []});
            },
            toggleAll() {
                return
            },
            toggleId(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    this.selectedEnrolleeIds.push(id);
                }
                else {
                    this.selectedEnrolleeIds.splice(pos, 1);
                }
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