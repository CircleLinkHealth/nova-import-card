<template>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-sm-9">
                    Approve Billable Patients
                </div>
                <div class="col-sm-3 text-right">
                    <button title="Generate an Excel Sheet" @click="exportExcel">Export</button>
                </div>
            </div>
        </div>
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
                    <a :href="props.row.patientUrl" class="blue">{{props.row.Patient}}</a>
                </template>
                <template slot="Problem 1" scope="props">
                    <span class="blue pointer" @click="showProblemsModal(props.row, 1)">{{props.row['Problem 1'] || '&lt;Edit&gt;'}}</span>
                </template>
                <template slot="Problem 2" scope="props">
                    <span class="blue pointer" @click="showProblemsModal(props.row, 2)">{{props.row['Problem 2'] || '&lt;Edit&gt;'}}</span>
                </template>
            </v-client-table>
            <patient-problem-modal></patient-problem-modal>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config.js'
    import { Event } from 'vue-tables-2'
    import TextEditable from '../comps/text-editable'
    import PatientProblemModal from './comps/patient-problem-modal'
    import moment from 'moment'
    import buildReport, { styles } from './excel'

    export default {
        name: 'billing-report',
        props: {
        },
        components: {
            'text-editable': TextEditable,
            'patient-problem-modal': PatientProblemModal
        },
        data() {
            return {
                selectedMonth: '',
                selectedPractice: 0,
                loading: true,
                practices: window.practices || [],
                practiceId: 0,
                columns: [
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
                tableData: [
                    {
                        id: 1,
                        "approved":true,
                        "rejected":false,
                        "Provider":"Dr. Demo MD",
                        "Patient":"Cecilia Z-Armstrong",
                        "patientUrl":"https://cpm-web.dev/manage-patients/345/careplan/sections/1",
                        "Practice":"Demo",
                        "DOB":"1918/09/22",
                        "Status":"enrolled",
                        "CCM Mins":0,
                        "Problem 1":"Smoking",
                        "Problem 2":"Asthma",
                        "Problem 1 Code":"I10",
                        "Problem 2 Code":"I10",
                        "#Successful Calls":0,
                        qa: 0,
                        reportId: 9,
                        problems: [
                            {
                                id: 1,
                                name: 'Smoking',
                                code: 'I10'
                            },
                            {
                                id: 2,
                                name: 'Asthma',
                                code: 'I11'
                            }
                        ]
                    },
                    {
                        id: 2,
                        "approved":false,
                        "rejected":false,
                        "Provider":"  ",
                        "Patient":"Kenneth Z-Smitham ",
                        "patientUrl":"https://cpm-web.dev/manage-patients/345/careplan/sections/1",
                        "Practice":"Demo",
                        "DOB":"1958-09-08",
                        "Status":"enrolled",
                        "CCM Mins":0,
                        "Problem 1":null,
                        "Problem 2":null,
                        "Problem 1 Code":null,
                        "Problem 2 Code":null,
                        "#Successful Calls":0,
                        qa: 1,
                        reportId: 10,
                        problems: [
                            {
                                id: 1,
                                name: 'Tobacco',
                                code: 'T01'
                            },
                            {
                                id: 2,
                                name: 'Syphilis',
                                code: 'SP2'
                            }
                        ]
                    }],
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
                this.loading = true
                this.axios.post(rootUrl('admin/reports/monthly-billing/v2/data'), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    this.tableData = response.data.map((patient, index) => {
                        return {
                            id: index,
                            approved: patient.approve,
                            rejected:  patient.reject,
                            reportId: patient.report_id,
                            qa: patient.qa,
                            problems: patient.problems || [],
                            Provider: patient.provider,
                            Patient: this.$elem(patient.name).querySelector('a').innerText,
                            patientUrl: this.$elem(patient.name).querySelector('a').href,
                            Practice: patient.practice,
                            DOB: patient.dob,
                            Status: patient.status,
                            'CCM Mins': patient.ccm,
                            'Problem 1': patient.problem1,
                            'Problem 2': patient.problem2,
                            'Problem 1 Code': patient.problem1_code,
                            'Problem 2 Code': patient.problem2_code,
                            '#Successful Calls': patient.no_of_successful_calls,
                        }
                    }).sort((pA, pB) => pB.qa - pA.qa)
                    this.loading = false
                    console.log('bills-report', this.tableData)
                }).catch(err => {
                    console.error(err)
                    this.loading = false
                })
            },

            showProblemsModal(patient, type) {
                const self = this
                Event.$emit('modal-patient-problem:show', patient, type, function (modified) {
                    const tablePatient = self.tableData.find(pt => pt.id === patient.id)
                    console.log('table-patient', tablePatient, modified)
                    if (type === 1) {
                        tablePatient['Problem 1 Code'] = modified.code
                        tablePatient['Problem 1'] = modified.name
                    }
                    else {
                        tablePatient['Problem 2 Code'] = modified.code
                        tablePatient['Problem 2'] = modified.name
                    }
                    self.axios.post(rootUrl('admin/reports/monthly-billing/v2/storeProblem'), {
                        code: modified.code,
                        id: modified.id,
                        name: modified.name,
                        problem_no: (type === 1) ? 'problem_1' : 'problem_2',
                        report_id: tablePatient.reportId
                    }).then(response => {
                        console.log('billing-change-problem', response)
                    }).catch(err => {
                        console.error('billing-change-problem', err)
                    })
                })
            },

            exportExcel() {
                const bytes = buildReport([
                    {
                        name: 'billable patients',
                        heading: [
                            
                        ],
                        merges: [],
                        specification: this.columns.reduce((a, b) => {
                            a[b] = {
                                displayName: b,
                                headerStyle: styles.cellNormal,
                                width: 100
                            }
                            return a
                        }, {}),
                        data: this.tableData
                    }
                ])

                const blob = new Blob([bytes], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
                const link = document.createElement('a')
                link.href = window.URL.createObjectURL(blob)
                link.download = `billable-patients-${this.practice.display_name.toLowerCase().replace(/ /g, '-')}-${this.selectedMonth.replace(', ', '-').toLowerCase()}-${Date.now()}.xlsx`
                link.click()
            }
        },
        computed: {
            months() {
                let months = []
                for (let i = 0; i >= -12; i--) {
                    let mDate = moment(new Date()).add(i * 30, 'days')
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
                return this.tableData.filter(patient => patient.qa).length
            },
            practice() {
                return this.practices.find(p => p.id === this.selectedPractice)
            }
        },
        mounted() {
            this.tableData = this.tableData.sort((pA, pB) => pB.qa - pA.qa)
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

    .blue {
        color: #008cba
    }

    div.blue input,textarea {
        width: 100%;
    }

    .pointer {
        cursor: pointer;
    }
</style>