<template>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-8">
                <input type="button" class="btn btn-success" :class="{ disabled: loaders.excel }"
                       :value="exportCSVText" @click="exportCSV">
                <span class="pad-10"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-right pad-10">
                <div class="col-sm-6 text-left">
                    <input v-model="startDate"  type="date"
                           value="Start Date" @click="setStartDate">

                    <input v-model="endDate"  type="date"
                           value="End Date" @click="setEndDate">
                </div>
            </div>
        </div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options"
                        id="patient-list-table">
        </v-client-table>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import {Event} from 'vue-tables-2'
    import Loader from '../../components/loader';
    import Notifications from '../../components/notifications';
    import {CancelToken} from "axios";
    import moment from "moment";

    export default {
        name: "practice-kpis",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications

        },
        props: [],
        data() {
            return {
                exportCSVText: 'Export as CSV',
                loaders: {
                    next: false,
                    excel: false,
                },
                startDate: null,
                endDate: null,
                loading: false,
                tableData: [],
                columns: ['name', 'unique_patients_called', 'consented', 'utc', 'soft_declined', 'hard_declined', '+3_attempts', 'labor_hours', 'conversion', 'labor_rate', 'total_cost', 'acq_cost'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.startDate = self.startDate;
                            data.query.endDate = self.endDate;
                        }
                        return data;
                    },
                    perPage: 50,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name', 'unique_patients_called', 'consented', 'utc', 'soft_declined', 'hard_declined', '+3_attempts', 'labor_hours', 'conversion', 'labor_rate', 'total_cost', 'acq_cost'],
                    sortable: ['name', 'unique_patients_called', 'consented', 'utc', 'soft_declined', 'hard_declined', '+3_attempts', 'labor_hours', 'conversion', 'labor_rate', 'total_cost', 'acq_cost'],
                },
            }

        },
        methods: {
            formatSecondsToHHMMSS(seconds) {
                return new Date(1000 * seconds).toISOString().substr(11, 8);
            },
            refreshTable() {
                this.$refs.table.refresh();
            },
            retrieveTableData(){
                const self = this
                this.loaders.next = true
                return this.axios.get(this.getUrl()).then(response => {
                    if (!response) {
                        //request was cancelled
                        return;
                    }
                    this.tableData = response.data;
                }).catch(err => {
                    this.loaders.next = false
                })
            },
            requestAdapter(data) {
                if (typeof (self) !== 'undefined') {
                    data.query.startDate = self.startDate;
                    data.query.endDate = self.endDate;
                }
                return data;
            },
            getUrl() {
                return rootUrl('/admin/enrollment/practice/kpis/data');
            },
            selected(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    return false;
                } else {
                    return true;
                }
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
            columnMapping(name) {
                const columns = {}
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
            },
            exportCSV() {
                let patients = []
                this.loaders.excel = true

                const $table = this.$refs.table
                const query = $table.$data.query

                const filters = Object.keys(query).map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''

                const hideStatus = {
                    hideStatus: this.hideStatus,
                };

                const download = (page = 1) => {
                    return this.axios.get( rootUrl(`/admin/enrollment/list/data?query=${JSON.stringify(hideStatus)}&rows=50&page=${page}&csv${filters}`)).then(response => {
                        const pagination = response.data
                        patients = patients.concat(pagination.data)
                        this.exportCSVText = `Export as CSV (${Math.ceil(pagination.meta.to / pagination.meta.total * 100)}%)`
                        if (pagination.meta.to < pagination.meta.total) return download(page + 1)
                        return pagination
                    }).catch(err => {
                        console.log('patients:csv:export', err)
                    })
                }
                return download().then(res => {

                    const str = 'id, user_id, mrn, first_name, last_name, care_ambassador_name, status, source, enrollment_non_responsive, lang, auto_enrollment_triggered, practice_name, provider_name, requested_callback, total_time_spent, attempt_count, last_attempt_at, last_call_outcome, last_call_outcome_reason, address, address_2, city, state, zip, primary_phone, other_phone, home_phone, cell_phone, dob, preferred_days, preferred_window, primary_insurance, secondary_insurance, tertiary_insurance, has_copay, email, last_encounter, created_at, updated_at\n'
                        + patients.join('\n');
                    const csvData = new Blob([str], {type: 'text/csv'});
                    const csvUrl = URL.createObjectURL(csvData);
                    const link = document.createElement('a');
                    link.download = `patient-list-${Date.now()}.csv`;
                    link.href = csvUrl;
                    link.click();
                    this.exportCSVText = 'Export as CSV';
                    this.loaders.excel = false
                })
            },
            setStartDate(){

            },
            setEndDate(){

            }
        },
        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            this.retrieveTableData();
            console.info('mounted');
        }
    }
</script>

<style scoped>

</style>