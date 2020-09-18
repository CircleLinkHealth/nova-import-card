<template>
    <div class="container-fluid">
        <div class="panel-body" id="enrollees">
            <div class="row">
                <div class="col-sm-8">
                    <input type="button" class="btn btn-success" :class="{ disabled: loaders.excel }"
                           :value="exportCSVText" @click="exportCSV">
                    <span class="pad-10"></span>
                </div>
            </div>
            <div class="row col-sm-12">
                <loader style="margin-top:20px; margin-left: 80px" v-if="loading"/>
            </div>
            <v-server-table class="table" v-on:filter="listenTo" :url="getUrl()" :columns="columns" :options="options"
                            ref="table">
                <template slot="status" slot-scope="props">
                    <div>
                        {{enrolleeStatusMap[props.row.status] || props.row.status}}
                    </div>
                </template>
                <div slot="filter__status">
                    <vue-multiselect
                            placeholder="Select Status"
                            @input="refreshTable"
                            label="text"
                            :multiple="true"
                            :searchable="false"
                            track-by="text"
                            openDirection="below"
                            v-model="statusFilter"
                            :options="statusOptions">
                    </vue-multiselect>
                </div>
                <template slot="total_time_spent" slot-scope="props">
                    {{formatSecondsToHHMMSS(props.row.total_time_spent)}}
                </template>
            </v-server-table>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal';
    import {Event} from 'vue-tables-2'
    import Loader from '../../components/loader';
    import Notifications from '../../components/notifications';
    import Multiselect from 'vue-multiselect';

    export default {
        name: "enrollee-list",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications,
            'vue-multiselect': Multiselect
        },
        props: [],
        data() {
            return {
                statusFilter: [
                ],
                exportCSVText: 'Export as CSV',
                loaders: {
                    next: false,
                    excel: false,
                },
                enrolleeStatusMap: {
                    call_queue: 'Call Queue',
                    consented: 'Consented',
                    soft_rejected: 'Soft Declined',
                    rejected: 'Hard Declined',
                    utc: 'Unreachable',
                    queue_auto_enrollment: 'Queued for Self-enrollment',
                },
                statusOptions: [
                    {id: 'call_queue', text: 'Call Queue'},
                    {id: 'consented', text: 'Consented'},
                    {id: 'soft_rejected', text: 'Soft Declined'},
                    {id: 'rejected', text: 'Hard Declined'},
                    {id: 'utc', text: 'Unreachable'},
                    {id: 'queue_auto_enrollment', text: 'Queued for Self-enrollment'},
                ],
                loading: false,
                hideStatus: ['ineligible'],
                columns: ['id', 'user_id', 'mrn', 'first_name', 'last_name', 'care_ambassador_name','status','source', 'enrollment_non_responsive', 'auto_enrollment_triggered', 'invited','practice_name', 'provider_name', 'lang', 'requested_callback', 'total_time_spent', 'attempt_count', 'last_attempt_at',
                    'last_call_outcome', 'last_call_outcome_reason', 'address', 'address_2', 'city', 'state', 'zip', 'primary_phone','home_phone', 'cell_phone',  'other_phone', 'dob', 'preferred_days', 'preferred_window',
                    'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'has_copay', 'email', 'last_encounter', 'created_at', 'updated_at'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.hideStatus = self.hideStatus;
                            data.query.status = self.statusFilter;
                        }
                        return data;
                    },
                    headings: {
                        enrollment_non_responsive : 'Send Regular Mail'
                    },
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2',
                        'id': 'min-width-80',
                        'edit': 'min-width-50',
                        'select': 'min-width-50',
                        'has-copay': 'min-width-50',
                        'user_id': 'min-width-80',
                        'mrn': 'min-width-80',
                        'lang': 'min-width-80',
                        'status': 'min-width-300'
                    },
                    perPage: 50,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['hideStatus', 'id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'source','invited', 'requested_callback', 'eligibility_job_id', 'enrollment_non_responsive', 'last_attempt_at', 'auto_enrollment_triggered','medical_record_id', 'practice_name', 'provider_name', 'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'attempt_count', 'primary_phone', 'home_phone', 'cell_phone', 'other_phone'],
                    sortable: ['id', 'user_id', 'first_name', 'last_name', 'practice_name', 'provider_name', 'primary_insurance', 'status', 'source', 'created_at', 'state', 'city','enrollment_non_responsive', 'auto_enrollment_triggered', 'last_attempt_at', 'care_ambassador_name', 'attempt_count', 'requested_callback'],
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
            requestAdapter(data) {
                if (typeof (self) !== 'undefined') {
                    data.query.hideStatus = self.hideStatus;
                }
                return data;
            },
            getUrl() {
                return rootUrl('/admin/enrollment/list/data');
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

                const filters = Object.keys(query)
                    .filter(item => item !== 'status')
                    .map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''

                const hideStatus = {
                    hideStatus: this.hideStatus,
                    status: this.statusFilter,
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

                    const str = 'id, user_id, mrn, first_name, last_name, care_ambassador_name, status, source, send_regular_mail, auto_enrollment_triggered, invited, practice_name, provider_name, lang, requested_callback, total_time_spent, attempt_count, last_attempt_at, last_call_outcome, last_call_outcome_reason, address, address_2, city, state, zip, primary_phone, home_phone, cell_phone, other_phone, dob, preferred_days, preferred_window, primary_insurance, secondary_insurance, tertiary_insurance, has_copay, email, last_encounter, created_at, updated_at\n'
                        + patients.join('\n');
                    const csvData = new Blob([str], {type: 'text/csv'});
                    const csvUrl = URL.createObjectURL(csvData);
                    const link = document.createElement('a');
                    link.download = `enrollee-list-${Date.now()}.csv`;
                    link.href = csvUrl;
                    link.click();
                    this.exportCSVText = 'Export as CSV';
                    this.loaders.excel = false
                })
            },
        },
        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            Event.$on('refresh-table', this.refreshTable)
            console.info('mounted');

            Event.$on('vue-tables.loading', function (data) {
                self.loading = true;
            });

            Event.$on('vue-tables.loaded', function (data) {
                self.loading = false;
            });
        }
    }
</script>

<style>
    th {
        min-width: 130px;
    }
    .min-width-50 {
        min-width: 50px !important;
    }

    .min-width-80 {
        min-width: 80px !important;
    }

    .min-width-200 {
        min-width: 200px !important;
    }

    .min-width-300 {
        min-width: 300px !important;
    }

</style>