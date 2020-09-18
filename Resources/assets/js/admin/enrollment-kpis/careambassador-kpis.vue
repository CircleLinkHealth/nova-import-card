<template>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-8">
                <input type="button" class="btn btn-success" :class="{ disabled: loaders.excel }"
                       :value="exportCSVText" @click="exportCSV">
                <span class="pad-10"></span>
            </div>
        </div>
        <div class="row" style="margin-top: 20px">
            <div class="col-sm-12" style="padding-left: 0 !important; padding-bottom: 20px !important">
                <div class="col-sm-6 text-left">
                    <input v-model="startDate"  type="date"
                           value="Start Date" @change="setStartDate">

                    <input v-model="endDate"  type="date"
                           value="End Date" @change="setEndDate">
                </div>
            </div>
        </div>
        <div class="top-10">
            <loader v-if="loaders.next || loaders.excel"></loader>
        </div>
        <v-server-table ref="table" :url="getUrlCom" v-on:filter="listenTo" :data="tableData" :columns="columns" :options="options"
                        id="table">
        </v-server-table>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import {Event} from 'vue-tables-2'
    import Loader from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader';
    import Notifications from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/notifications/notifications';
    import {CancelToken} from "axios";
    import moment from "moment";

    export default {
        name: "careambassador-kpis",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications,
        },
        props: [],
        computed: {
            getUrlCom(){
                return rootUrl(`/admin/enrollment/ambassador/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}`);
            },
        },
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
                columns: [ 'name','total_hours','total_seconds', 'no_enrolled', 'total_calls','calls_per_hour','mins_per_enrollment','conversion','hourly_rate','per_cost', 'earnings'],
                options: {
                    headings: {
                        name : 'Ambassador Name',
                        total_hours: 'Total Hours',
                        total_seconds: 'Total Seconds',
                        no_enrolled: '#Consented',
                        total_calls: '#Called',
                        calls_per_hour: 'Calls/Hour',
                        mins_per_enrollment: 'Mins/Consent',
                        hourly_rate: 'Hourly Rate',
                        per_cost: 'Cost per Consent',
                        earnings: 'Total Cost'
                    },
                    perPage: 10,
                    perPageValues: [5, 10, 20],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name'],
                    sortable: ['name'],
                },
            }

        },
        methods: {
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
            exportCSV() {
                let data = []
                this.loaders.excel = true

                const $table = this.$refs.table
                const query = $table.$data.query

                const filters = Object.keys(query).map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''

                const download = (page = 1) => {
                    return this.axios.get( rootUrl(`/admin/enrollment/ambassador/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}&rows=10&page=${page}&csv${filters}`)).then(response => {
                        const pagination = response.data
                        data = data.concat(pagination.data)
                        this.exportCSVText = `Export as CSV (${Math.ceil(pagination.meta.to / pagination.meta.total * 100)}%)`
                        if (pagination.meta.to < pagination.meta.total) return download(page + 1)
                        return pagination
                    }).catch(err => {
                        console.log('ambassador:csv:export', err)
                    })
                }
                return download().then(res => {

                    const str = 'Ambassador Name,Total Hours,Total Seconds,#Enrolled,#Called,Calls/Hour,Mins/Enrollment,Conversion,Hourly Rate,Cost Per Enrollment,Total Earnings\n'
                        + data.join('\n');
                    const csvData = new Blob([str], {type: 'text/csv'});
                    const csvUrl = URL.createObjectURL(csvData);
                    const link = document.createElement('a');
                    link.download = `Ambassador KPIs from ${this.startDate} to ${this.endDate}.csv`;
                    link.href = csvUrl;
                    link.click();
                    this.exportCSVText = 'Export as CSV';
                    this.loaders.excel = false
                })
            },
            setStartDate(event) {
                this.loaders.next = true
                this.startDate = event.currentTarget._value
                this.refreshTable();
            },
            setEndDate(event) {
                this.loaders.next = true
                this.endDate = event.currentTarget._value
                this.refreshTable();
            },
            refreshTable() {
                this.$refs.table.refresh();
            },
            columnMapping(name) {
                const columns = {}
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
            },
        },
        created() {
            self = this;
            console.info('created');
            this.startDate = moment().startOf('month').format('YYYY-MM-DD');
            this.endDate = moment().format('YYYY-MM-DD');
        },
        mounted() {
            console.info('mounted');
            Event.$on('vue-tables.loading', function (data) {
                self.loaders.next = true
            });

            Event.$on('vue-tables.loaded', function (data) {
                self.loaders.next = false
            });
        }
    }
</script>

<style scoped>

</style>