<template>
    <div class="panel-body">
        <div class="col-sm-12 row">
            <div class="col-sm-6">
                <label>Select Practice</label>
                <select class="form-control" v-model="selectedPractice" @change="retrieve">
                    <option v-for="(practice, index) in practices" :key="index" :value="practice.id" :selected="practice.id == 8">{{practice.display_name}}</option>
                </select>
            </div>
            <div class="col-sm-6">
                <label>Select Month</label>
                <select class="form-control" v-model="selectedMonth" @change="retrieve">
                    <option v-for="(month, index) in months" :key="index" :value="month.long" :selected="month.selected">{{month.long}}</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12 text-center line-50 row">
            <div class="col-sm-4">
                <strong>Approved: </strong>
                <span class="color-green">
                    {{loading ? '...loading...' : noOfApproved}}
                </span>
            </div>
            <div class="col-sm-4">
                <strong>Flagged: </strong>
                <span class="color-dark-orange">
                    {{loading ? '...loading...' : noOfFlagged}}
                </span>
            </div>
            <div class="col-sm-4">
                <strong>Rejected: </strong>
                <span class="color-dark-red">
                    {{loading ? '...loading...' : noOfRejected}}
                </span>
            </div>
        </div>
        <v-client-table ref="tblBillingReport" :data="tableData" :columns="columns" :options="options">
            <template slot="approved" scope="props">
                <input class="row-select" v-model="props.row.approved" type="checkbox" :readonly="true" />
            </template>
            <template slot="rejected" scope="props">
                <input class="row-select" v-model="props.row.rejected" type="checkbox" :readonly="true" />
            </template>
            <template slot="Patient" scope="props">
                <text-editable :value="props.row.Patient" :class-name="'blue'" :no-button="true"></text-editable>
            </template>
        </v-client-table>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config.js'
    import { Event } from 'vue-tables-2'
    import TextEditable from '../comps/text-editable'
    import PatientProblemModal from './comps/patient-problem-modal'
    import moment from 'moment'

    export default {
        name: 'billing-report',
        props: {
        },
        components: {
            'text-editable': TextEditable
        },
        data() {
            return {
                selectedMonth: '',
                selectedPractice: 0,
                loading: true,
                practices: window.practices || [],
                practiceId: 0,
                columns: [
                    'id',
                    'Provider', 
                    'Patient', 
                    'Practice', 
                    'DOB', 
                    'Status', 
                    'CCM Mins', 
                    'Problem 1', 
                    'Problem 1 Code',
                    'Problem 2', 
                    'Problem 2 Code', 
                    '#Successful Calls', 
                    'approved',
                    'rejected'],
                tableData: [{"approved":true,"rejected":false,"Provider":"Dr. Demo MD","Patient":"Cecilia Z-Armstrong ","Practice":"Demo","DOB":"1918/09/22","Status":"enrolled","CCM Mins":0,"Problem 1":"Smoking","Problem 2":"Asthma","Problem 1 Code":"I10","Problem 2 Code":"I10","#Successful Calls":0},{"approved":false,"rejected":false,"Provider":"  ","Patient":"Kenneth Z-Smitham ","Practice":"Demo","DOB":"1958-09-08","Status":"enrolled","CCM Mins":0,"Problem 1":null,"Problem 2":null,"Problem 1 Code":null,"Problem 2 Code":null,"#Successful Calls":0}],
                options: {

                }
            }
        },
        methods: {
            $elem(html) {
                const div = document.createElement('div')
                div.innerHTML = html
                return div
            },
            retrieve() {
                this.axios.post(rootUrl('/admin/reports/monthly-billing/v2/data'), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    this.tableData = response.data.map((patient, index) => {
                        return {
                            id: index,
                            approved: this.$elem(patient.approve).querySelector('input').checked,
                            rejected:  this.$elem(patient.reject).querySelector('input').checked,
                            Provider: patient.provider,
                            Patient: this.$elem(patient.name).querySelector('a').innerText,
                            Practice: patient.practice,
                            DOB: patient.dob,
                            Status: patient.status,
                            'CCM Mins': patient.ccm,
                            'Problem 1': patient.problem1,
                            'Problem 2': patient.problem2,
                            'Problem 1 Code': patient.problem1_code,
                            'Problem 2 Code': patient.problem2_code,
                            '#Successful Calls': patient.no_of_successful_calls
                        }
                    })
                    this.loading = false
                    console.log('bills-report', this.tableData)
                }).catch(err => {
                    console.error(err)
                    this.loading = false
                })
            }
        },
        computed: {
            months() {
                let months = []
                let currentDate = moment(new Date())
                for (let i = 0; i >= -12; i--) {
                    let mDate = currentDate.add(i, 'M')
                    months.push({ short: mDate.format('YYYY-MM-DD'), long: mDate.format('MMM, YYYY'), selected: i === 0 })
                }
                return months
            },
            noOfApproved() {
                return this.tableData.filter(patient => patient.approved).length
            },
            noOfRejected() {
                return this.tableData.filter(patient => patient.rejected).length
            },
            noOfFlagged() {
                return this.tableData.filter(patient => patient.flagged).length
            }
        },
        mounted() {
            this.selectedMonth = this.months[0].long
            this.selectedPractice = this.practices[0].id
            this.retrieve()
        }
    }
</script>

<style>
    span.color-orange {
        color: orange
    }

    span.color-dark-orange {
        color: darkorange
    }

    span.color-green {
        color: green
    }

    span.color-dark-red {
        color: darkred
    }

    .line-50 {
        line-height: 50px
    }

    input[type='checkbox'][readonly] {
        pointer-events: none;
    }

    div.blue {
        color: #008cba
    }

    div.blue input,textarea {
        width: 100%;
    }
</style>