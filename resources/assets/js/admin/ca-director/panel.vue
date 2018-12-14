<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 text-right">
                <button class="btn btn-primary btn-xs" @click="assignSelectedToCa">Assign To CA</button>
            </div>
        </div>
        <div class="panel-body" id="enrollees">
            <v-server-table :url="getUrl()" :data="tableData" :columns="columns" :options="options" ref="table">
                <div slot="filter__select">
                    <input type="checkbox"
                           class="form-control check-all"
                           v-model='allMarked'
                           @change="toggleAll()">
                </div>

                <template slot="select" slot-scope="props">
                    <input type="checkbox"
                           @change="unmarkAll()"
                           class="form-control"
                           :value="props.row.id"
                           v-model="markedRows">
                </template>

            </v-server-table>
        </div>
        <select-ca-modal ref="selectCaModal" :selected-patients="selectedPatients" :ambassadors="getAmbassadors"></select-ca-modal>
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
                selected: [],
                columns: [ 'select', 'id', 'user_id','first_name', 'last_name', 'batch_id', 'eligibility_job_id', 'medical_record_type', 'practice_id', 'provider_id', 'primary_insurance', 'primary_phone', 'created_at'],
                tableData: [],
                allMarked:false,
                markedRows:[],
                options: {
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10,25,50,100],
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
            selectedPatients() {
                return this.tableData.filter(row => row.selected && row.Patient);
            },
            getAmbassadors(){
                return axios.get(rootUrl('/admin/ca-director/ambassadors')).then(response => {
                    return response.data;
                });
            }
        },
        methods: {
            getUrl() {
                return rootUrl('/admin/ca-director/enrollees');
            },
            assignSelectedToCa() {

                Event.$emit("modal-select-ca:show")
            },
            unmarkAll() {
                this.allMarked = false;
            },
            toggleAll() {
                this.markedRows = this.allMarked?this.$refs.table.data.map(row=>row.id):[];
            },

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