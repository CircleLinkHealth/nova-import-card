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
                    <input v-model="startDate" type="date"
                           value="Start Date" @change="setStartDate">

                    <input v-model="endDate" type="date"
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
    import Loader from '../../components/loader';
    import Notifications from '../../components/notifications';
    import moment from "moment";
    import {Event} from 'vue-tables-2'

    export default {
        name: "practice-kpis",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications,
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
                columns: ['name', 'unique_patients_called', 'consented', 'utc', 'soft_declined', 'hard_declined', 'incomplete_3_attempts', 'labor_hours', 'conversion', 'labor_rate', 'total_cost', 'acq_cost'],
                options: {
                    headings: {
                        name: 'Practice Name',
                        unique_patients_called: '#Unique Patients Called',
                        consented: '#Consented',
                        utc: '#Unable to Contact',
                        soft_declined: '#Soft Declined',
                        hard_declined: '#Hard Declined',
                        incomplete_3_attempts: '#Incomplete +3 Attempts',
                        labor_hours: 'Labor Hours',
                        conversion: 'Conversion %',
                        labor_rate: 'Labor Rate',
                        total_cost: 'Total Cost',
                        acq_cost: 'Pt. Consent Cost'
                    },
                    perPage: 10,
                    perPageValues: [10, 25],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name'],
                    sortable: ['name'],
                },
            }

        },
        computed: {
            getUrlCom(){
                return rootUrl(`/admin/enrollment/practice/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}`);
            },
        },
        methods: {
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
            columnMapping(name) {
                const columns = {}
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
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
                    return this.axios.get( rootUrl(`/admin/enrollment/practice/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}&rows=10&page=${page}&csv${filters}`)).then(response => {
                        const pagination = response.data
                        data = data.concat(pagination.data)
                        this.exportCSVText = `Export as CSV (${Math.ceil(pagination.meta.to / pagination.meta.total * 100)}%)`
                        if (pagination.meta.to < pagination.meta.total) return download(page + 1)
                        return pagination
                    }).catch(err => {
                        console.log('practice:csv:export', err)
                    })
                }
                return download().then(res => {

                    const str = 'Practice Name,#Unique Patients Called,#Consented,#Unable to Contact,#Soft Declined,#Hard Declined,#Incomplete +3 Attempts,Labor Hours,Conversion %, Labor Rate,Total Cost,Patient Acq. Cost\n'
                        + data.join('\n');
                    const csvData = new Blob([str], {type: 'text/csv'});
                    const csvUrl = URL.createObjectURL(csvData);
                    const link = document.createElement('a');
                    link.download = `Practice KPIs from ${this.startDate} to ${this.endDate}.csv`;
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
        },
        created() {
            self = this;
            console.info('created');
            this.startDate = moment().startOf('month').format('YYYY-MM-DD');
            this.endDate = moment().format('YYYY-MM-DD');
        },
        mounted() {
            const self = this;
            this.startDate = moment().startOf('month').format('YYYY-MM-DD');
            this.endDate = moment().format('YYYY-MM-DD');


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