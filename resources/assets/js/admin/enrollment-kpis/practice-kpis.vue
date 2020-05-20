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
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.startDate = self.startDate;
                            data.query.endDate = self.endDate;
                        }
                        return data;
                    },
                    headings: {
                        name : 'Practice Name',
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
                        acq_cost: 'Patient Acq. Cost'
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
                return rootUrl(`/admin/enrollment/practice/kpis/data?start_date=${this.startDate}&end_date=${this.endDate}`);
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
            exportCSV() {

                const str = 'Practice Name,#Unique Patients Called,#Consented,#Unable to Contact,#Soft Declined,#Hard Declined,#Incomplete +3 Attempts,Labor Hours,Conversion %, Labor Rate,Total Cost,Patient Acq. Cost\n'
                    + this.tableData.map(item => Object.values(item).join(","))
                        .join("\n")
                        .replace(/(^\[)|(\]$)/gm, "");

                const csvData = new Blob([str], {type: 'text/csv'});
                const csvUrl = URL.createObjectURL(csvData);
                const link = document.createElement('a');
                link.download = `Practice KPIs from ${this.startDate} to ${this.endDate}.csv`;
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