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
                    <div>
                        <label>Select Practice</label>
                    </div>
                    <select2 class="form-control" v-model="selectedPractice" @change="retrieve">
                        <option v-for="(practice, index) in practices" :key="index" :value="practice.id">{{practice.display_name}}
                        </option>
                    </select2>
                </div>
                <div class="col-sm-6">
                    <div>
                        <label>Select Month</label>
                    </div>
                    <select2 class="form-control" v-model="selectedMonth" @change="retrieve" :value="months[0].long">
                        <option v-for="(month, index) in months" :key="index" :value="month.long">{{month.long}}
                        </option>
                    </select2>
                </div>
            </div>
            <div class="col-sm-12 text-center line-50 row">
                <div class="col-sm-4">
                    <strong>Approved: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-green">
                        {{loading ? '' : noOfApproved}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Flagged: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-dark-orange">
                        {{loading ? '' : noOfFlagged}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Rejected: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-dark-red">
                        {{loading ? '' : noOfRejected}}
                    </span>
                </div>
            </div>
            <v-client-table ref="tblBillingReport" :data="tableData" :columns="columns" :options="options">
                <template slot="approved" scope="props">
                    <input class="row-select" v-model="props.row.approved" @change="approveOrReject($event, props.row, 'approve')" 
                        type="checkbox" :readonly="!!props.row.promises['approve_reject']" />
                    <span class="error-btn" v-if="props.row.errors.approve_reject" 
                        title="view error message"
                        @click="showErrorModal(props.row.id, 'approve_reject')">x</span>
                    <div class="loading" v-if="props.row.promises['approve_reject']"></div>
                </template>
                <template slot="rejected" scope="props">
                    <input class="row-select" v-model="props.row.rejected" @change="approveOrReject($event, props.row, 'reject')" 
                        type="checkbox" :readonly="!!props.row.promises['approve_reject']" />
                    <span class="error-btn" v-if="props.row.errors.approve_reject" 
                        title="view error message"
                        @click="showErrorModal(props.row.id, 'approve_reject')">x</span>
                    <div class="loading" v-if="props.row.promises['approve_reject']"></div>
                </template>
                <template slot="Patient" scope="props">
                    <a :href="props.row.patientUrl" target="_blank" class="blue">{{props.row.Patient}}</a>
                </template>
                <template slot="Problem 1" scope="props">
                    <div>
                        <span class="blue pointer"
                          @click="showProblemsModal(props.row, 1)">{{props.row['Problem 1'] || '&lt;Edit&gt;'}}</span>
                        <div class="loading" v-if="props.row.promises['problem_1']"></div>
                    </div>
                </template>
                <template slot="Problem 2" scope="props">
                    <div>
                        <span class="blue pointer"
                          @click="showProblemsModal(props.row, 2)">{{props.row['Problem 2'] || '&lt;Edit&gt;'}}</span>
                        <div class="loading" v-if="props.row.promises['problem_2']"></div>
                    </div>
                </template>
            </v-client-table>
            <patient-problem-modal ref="patientProblemModal" :cpm-problems="cpmProblems"></patient-problem-modal>
            <error-modal ref="errorModal"></error-modal>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import {Event} from 'vue-tables-2'
    import TextEditable from '../comps/text-editable'
    import PatientProblemModal from './comps/patient-problem-modal'
    import ErrorModal from './comps/error-modal'
    import moment from 'moment'
    import buildReport, {styles} from './excel'
    import Select2Component from '../../components/src/select2'

    export default {
        name: 'billing-report',
        props: {},
        components: {
            'text-editable': TextEditable,
            'patient-problem-modal': PatientProblemModal,
            'error-modal': ErrorModal,
            'select2': Select2Component
        },
        data() {
            return {
                selectedMonth: '',
                selectedPractice: 0,
                loading: true,
                practices: window.practices || [],
                cpmProblems: window.cpmProblems || [],
                practiceId: 0,
                columns: [
                    'MRN',
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
                    /*{
                        id: 1,
                        mrn: "",
                        "approved": true,
                        "rejected": false,
                        "Provider": "Dr. Demo MD",
                        "Patient": "Cecilia Z-Armstrong",
                        "patientUrl": "https://cpm-web.dev/manage-patients/345/careplan/sections/1",
                        "Practice": "Demo",
                        "DOB": "1918/09/22",
                        "Status": "enrolled",
                        "CCM Mins": 0,
                        "Problem 1": "Smoking",
                        "Problem 2": "Asthma",
                        "Problem 1 Code": "I10",
                        "Problem 2 Code": "I10",
                        "#Successful Calls": 0,
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
                        ],
                        promises: {
                            problem_1: false,
                            problem_2: false,
                            approve_reject: false
                        },
                        errors: {
                            approve_reject: null
                        }
                    },
                    {
                        id: 2,
                        mrn: "",
                        "approved": false,
                        "rejected": false,
                        "Provider": "  ",
                        "Patient": "Kenneth Z-Smitham ",
                        "patientUrl": "https://cpm-web.dev/manage-patients/345/careplan/sections/1",
                        "Practice": "Demo",
                        "DOB": "1958-09-08",
                        "Status": "enrolled",
                        "CCM Mins": 0,
                        "Problem 1": null,
                        "Problem 2": null,
                        "Problem 1 Code": null,
                        "Problem 2 Code": null,
                        "#Successful Calls": 0,
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
                        ],
                        promises: {
                            problem_1: false,
                            problem_2: false,
                            approve_reject: false
                        },
                        errors: {
                            approve_reject: null
                        }
                    }*/],
                options: {
                    rowClassCallback(row) {
                        if (row.qa) return 'bg-flagged'
                        return ''
                    }
                }
            }
        },
        methods: {
            $elem(html) {
                const div = document.createElement('div')
                div.innerHTML = html
                return div
            },
            approveOrReject(e, row, type) {
                const tablePatient = this.tableData.find(patient => patient.id == row.id)
                console.log('billing-approve-reject-patient', tablePatient, e)
                if (tablePatient) {
                    if (type == 'approve') {
                        tablePatient.approved = e.target.checked
                        tablePatient.rejected = false
                    }
                    else {
                        tablePatient.rejected = e.target.checked
                        tablePatient.approved = false
                    }

                    const errorKey = 'approve_reject'
                    tablePatient.promises['approve_reject'] = true
                    this.axios.post(rootUrl('admin/reports/monthly-billing/v2/status/update'), {
                        report_id: tablePatient.reportId,
                        approved: Number(tablePatient.approved),
                        rejected: Number(tablePatient.rejected)
                    }).then(response => {
                        tablePatient.promises['approve_reject'] = false
                        tablePatient.approved = !!(response.data.status || {}).approved
                        tablePatient.rejected = !!(response.data.status || {}).rejected
                        console.log('billing-approve-reject', response.data)
                    }).catch(err => {
                        tablePatient.promises['approve_reject'] = false
                        console.error('billing-approve-reject', err)
                        tablePatient.errors[errorKey] = err.message
                    })
                } 
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
                            MRN: patient.mrn,
                            approved: patient.approve,
                            rejected: patient.reject,
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
                            promises: {
                                problem_1: false,
                                problem_2: false,
                                approve_reject: false
                            },
                            errors: {
                                approve_reject: null
                            }
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
                    /** callback done function */
                    const tablePatient = self.tableData.find(pt => pt.id === patient.id)
                    console.log('table-patient', tablePatient, modified)
                    if (tablePatient) {
                        if (modified.id == 'Other') {
                            modified.name = (this.cpmProblems.find(problem => problem.id == modified.cpm_id) || {}).name || modified.name
                        }
                        if (type === 1) {
                            tablePatient['Problem 1 Code'] = modified.code
                            tablePatient['Problem 1'] = modified.name
                        }
                        else {
                            tablePatient['Problem 2 Code'] = modified.code
                            tablePatient['Problem 2'] = modified.name
                        }
                        const problemKey = (type === 1) ? 'problem_1' : 'problem_2'
                        tablePatient.promises[problemKey] = true
                        self.axios.post(rootUrl('admin/reports/monthly-billing/v2/storeProblem'), {
                            code: modified.code,
                            id: modified.id,
                            name: modified.name,
                            problem_no: problemKey,
                            report_id: tablePatient.reportId,
                            cpm_problem_id: modified.cpm_id
                        }).then(response => {
                            tablePatient.promises[problemKey] = false
                            console.log('billing-change-problem', response)
                        }).catch(err => {
                            tablePatient.promises[problemKey] = false
                            console.error('billing-change-problem', err)
                        })
                        console.log('table-patient-promises', tablePatient.promises)
                    }
                    else console.error('could not find tablePatient')
                })
            },

            showErrorModal(id, name) {
                const errors = (this.tableData.find(row => row.id === id) || {}).errors
                console.log(errors)
                Event.$emit('modal-error:show', { body: errors[name] }, () => {
                    errors[name] = null
                    console.log(errors)
                })
            },

            exportExcel() {
                const bytes = buildReport([
                    {
                        name: 'billable patients',
                        heading: [],
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

                const blob = new Blob([bytes], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'})
                const link = document.createElement('a')
                link.href = window.URL.createObjectURL(blob)
                link.download = `billable-patients-${this.practice.display_name.toLowerCase().replace(/ /g, '-')}-${this.selectedMonth.replace(', ', '-').toLowerCase()}-${Date.now()}.xlsx`
                link.click()
            }
        },
        computed: {
            months() {
                let months = []
                for (let i = 0; i >= -6; i--) {
                    let mDate = moment(new Date()).add(i * 30, 'days')
                    months.push({short: mDate.format('YYYY-MM-DD'), long: mDate.format('MMM, YYYY'), selected: i === 0})
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

    div.blue input, textarea {
        width: 100%;
    }

    .pointer {
        cursor: pointer;
    }

    .loading {
        border: 2px solid #f3f3f3;
        border-top: 2px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 2s linear infinite;
        margin-left: 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .bg-flagged {
        background-color: rgba(255, 252, 96, 0.408) !important;
    }

    .error-btn {
        display: inline-block;
        width: 15px;
        height: 15px;
        background-color: white;
        text-align: center;
        padding-top: 0px;
        border-radius: 8px;
        color: red;
        font-size: 10px;
        cursor: pointer;
        border: 1px solid red;
        padding-bottom: 14px;
    }
</style>