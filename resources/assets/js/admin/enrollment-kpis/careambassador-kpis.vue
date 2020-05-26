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
        <v-client-table ref="table" :data="tableData" :columns="columns" :options="options"
                        id="table">
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
        name: "careambassador-kpis",
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
                columns: [ 'name','total_hours','total_seconds', 'no_enrolled', 'total_calls','calls_per_hour','mins_per_enrollment','conversion','hourly_rate','per_cost', 'earnings'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.startDate = self.startDate;
                            data.query.endDate = self.endDate;
                        }
                        return data;
                    },
                    headings: {
                        name : 'Ambassador Name',
                        total_hours: 'Total Hours',
                        total_seconds: 'Total Seconds',
                        no_enrolled: '#Enrolled',
                        total_calls: '#Called',
                        calls_per_hour: 'Calls/Hour',
                        mins_per_enrollment: 'Mins/Enrollment',
                        hourly_rate: 'Hourly Rate',
                        per_cost: 'Cost per Enrollment',
                    },
                    perPage: 50,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name','total_hours','total_seconds', 'no_enrolled', 'total_calls','calls_per_hour','mins_per_enrollment','conversion','hourly_rate','per_cost', 'earnings'],
                    sortable: ['name','total_hours', 'total_seconds', 'no_enrolled', 'total_calls','calls_per_hour','mins_per_enrollment','conversion','hourly_rate','per_cost', 'earnings'],
                },
            }

        },
        methods: {
            retrieveTableData(){
                const self = this
                this.loaders.next = true
                return this.axios.get(this.getUrl()).then(response => {
                    if (!response) {
                        //request was cancelled
                        return;
                    }
                    this.loaders.next = false
                    this.tableData = response.data;
                }).catch(err => {
                    this.loaders.next = false
                })
            },
            getUrl() {
                return rootUrl(`/admin/enrollment/ambassador/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}`);
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
            exportCSV() {

                const str = 'Ambassador Name,Total Hours,Total Seconds,#Enrolled,#Called,Calls/Hour,Mins/Enrollment,Conversion,Hourly Rate,Cost per Enrollment,Earnings\n'
                    + this.tableData.map(item => [
                        item.name,
                        item.total_hours,
                        item.total_seconds,
                        item.no_enrolled,
                        item.total_calls,
                        item.calls_per_hour,
                        item.mins_per_enrollment,
                        item.conversion,
                        item.hourly_rate,
                        item.per_cost,
                        item.earnings].join(","))
                        .join("\n")
                        .replace(/(^\[)|(\]$)/gm, "");

                const csvData = new Blob([str], {type: 'text/csv'});
                const csvUrl = URL.createObjectURL(csvData);
                const link = document.createElement('a');
                link.download = `Ambassador KPIs from ${this.startDate} to ${this.endDate}.csv`;
                link.href = csvUrl;
                link.click();
                this.loaders.excel = false
            },
            setStartDate(event){
                this.loaders.next = true
                this.startDate = event.currentTarget._value
                this.retrieveTableData();
            },
            setEndDate(event){
                this.loaders.next = true
                this.endDate = event.currentTarget._value
                this.retrieveTableData();
            }
        },
        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            this.loaders.next = true;
            this.startDate = moment().startOf('month').format('YYYY-MM-DD');
            this.endDate = moment().format('YYYY-MM-DD');
            this.retrieveTableData();
            console.info('mounted');
        }
    }
</script>

<style scoped>

</style>