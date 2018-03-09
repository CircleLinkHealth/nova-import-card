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
                <div class="col-sm-4">
                    <div>
                        <label>Select Practice</label>
                    </div>
                    <select2 class="form-control" v-model="selectedPractice" @change="changePractice">
                        <option v-for="(practice, index) in practices" :key="index" :value="practice.id">{{practice.display_name}}
                        </option>
                    </select2>
                </div>
                <div class="col-sm-4">
                    <div>
                        <label>Select Month</label>
                    </div>
                    <select2 class="form-control" v-model="selectedMonth" @change="changePractice" :value="months[0].long">
                        <option v-for="(month, index) in months" :key="index" :value="month.long">{{month.long}}
                        </option>
                    </select2>
                </div>
                <div class="col-sm-4">
                    <div>
                        <label>Set Chargeable Service</label>
                    </div>
                    <select2 class="form-control" v-model="selectedService" @change="changeService">
                        <option :value="null">Set Default Code</option>
                        <option v-for="(service, index) in chargeableServices" :key="index" :value="service.id">{{service.code}}
                        </option>
                    </select2>
                </div>
            </div>
            <div class="col-sm-12 text-center line-50 row">
                <div class="col-sm-4">
                    <strong>Approved: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-green">
                        {{loading ? '' : counts.approved}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Flagged: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-dark-orange">
                        {{loading ? '' : counts.flagged}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Rejected: </strong>
                    <div class="loading" v-if="loading"></div>
                    <span class="color-dark-red">
                        {{loading ? '' : counts.rejected}}
                    </span>
                </div>
            </div>
            <v-client-table ref="tblBillingReport" :data="tableData" :columns="columns" :options="options">
                <template slot="approved" scope="props">
                    <input class="row-select" v-model="props.row.approved" @change="approveOrReject($event, props.row, 'approve')" 
                        type="checkbox" :readonly="!!props.row.promises['approve_reject']" style="display:block;"/>
                    <span class="error-btn" v-if="props.row.errors.approve_reject" 
                        title="view error message"
                        @click="showErrorModal(props.row.id, 'approve_reject')">x</span>
                    <div class="loading" v-if="props.row.promises['approve_reject']"></div>
                </template>
                <template slot="rejected" scope="props">
                    <input class="row-select" v-model="props.row.rejected" @change="approveOrReject($event, props.row, 'reject')" 
                        type="checkbox" :readonly="!!props.row.promises['approve_reject']" style="display:block;"/>
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
                <template slot="chargeable_services" scope="props">
                    <div class="blue pointer" @click="showChargeableServicesModal(props.row)">
                        <div v-if="props.row.chargeable_services.length">
                            <label class="label label-info margin-5 inline-block" v-for="service in props.row.chargeables()" :key="service.id">{{service.code}}</label>
                        </div>
                        <div v-if="!props.row.chargeable_services.length">&lt;Edit&gt;</div>
                        <div class="loading" v-if="props.row.promises['update_chargeables']"></div>
                    </div>
                </template>
            </v-client-table>
            <patient-problem-modal ref="patientProblemModal" :cpm-problems="cpmProblems"></patient-problem-modal>
            <chargeable-services-modal ref="chargeableServicesModal" :services="chargeableServices"></chargeable-services-modal>
            <error-modal ref="errorModal"></error-modal>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import {Event} from 'vue-tables-2'
    import TextEditable from '../comps/text-editable'
    import PatientProblemModal from './comps/patient-problem-modal'
    import ChargeableServicesModal from './comps/chargeable-services-modal'
    import ErrorModal from './comps/error-modal'
    import moment from 'moment'
    import buildReport, {styles} from '../../excel'
    import Select2Component from '../../components/src/select2'

    export default {
        name: 'billing-report',
        props: {},
        components: {
            'text-editable': TextEditable,
            'patient-problem-modal': PatientProblemModal,
            'error-modal': ErrorModal,
            'select2': Select2Component,
            'chargeable-services-modal': ChargeableServicesModal
        },
        data() {
            return {
                selectedMonth: '',
                selectedPractice: 0,
                selectedService: null,
                loading: true,
                practices: window.practices || [],
                cpmProblems: window.cpmProblems || [],
                chargeableServices: [],
                practiceId: 0,
                url: null,
                counts: {
                    approved: 0,
                    rejected: 0,
                    flagged: 0,
                    total () {
                        return this.approved + this.rejected + this.flagged
                    }
                },
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
                    'rejected',
                    'chargeable_services'],
                tableData: []
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
                        if ((response.data || {}).counts) {
                            this.counts.approved = ((response.data || {}).counts || {}).approved || 0
                            this.counts.rejected = ((response.data || {}).counts || {}).rejected || 0
                            this.counts.flagged = ((response.data || {}).counts || {}).toQA || 0
                        }
                        console.log('billing-approve-reject', response.data)
                    }).catch(err => {
                        tablePatient.promises['approve_reject'] = false
                        console.error('billing-approve-reject', err)
                        tablePatient.errors[errorKey] = err.message
                    })
                } 
            },
            changePractice() {
                this.tableData = []
                this.retrieve()
                this.getCounts()
            },
            changeService(id) {
                console.log('billing:chargeable-service:default', id)
                const service = this.chargeableServices.find(s => s.id == id)
                const practice = this.practices.find(p => p.id == this.selectedPractice)
                if (id && service && practice && !this.loading && confirm(`Are you sure you want to set ${service.code} as the default chargeable service for ${practice.name} in ${this.selectedMonth}?`)) {
                    this.loading = true
                    return this.axios.post(rootUrl('admin/reports/monthly-billing/v2/updatePracticeServices'), {
                        practice_id: this.selectedPractice,
                        date: this.selectedMonth,
                        default_code_id: id
                    }).then(response => {
                        (response.data || []).forEach(summary => {
                            const tableItem = this.tableData.find(row => row.id == summary.id)
                            if (tableItem) {
                                tableItem.chargeable_services = (summary.chargeable_services || []).map(item => item.id)
                            }
                        })
                        this.loading = false
                        console.log('billing:chargeable-services:default:update', response.data)
                    }).catch(err => {
                        console.error('billing:chargeable-services:default:update', err)
                        
                        this.loading = false
                    })
                }
            },
            getChargeableServices() {
                return this.axios.get(rootUrl('admin/reports/monthly-billing/v2/services')).then(response => {
                    this.chargeableServices = (response.data || []).map(service => {
                        service.selected = null
                        return service
                    })
                    console.log('billing:chargeable-services', this.chargeableServices)
                }).catch(err => {
                    console.error('billing:chargeable-services', err)
                })
            },
            getCounts() {
                return this.axios.get(rootUrl(`admin/reports/monthly-billing/v2/counts?practice_id=${this.selectedPractice}&date=${this.selectedMonth}`)).then(response => {
                    console.log('billing:counts', response.data)
                    this.counts.approved = (response.data || {}).approved || 0
                    this.counts.rejected = (response.data || {}).rejected || 0
                    this.counts.flagged = (response.data || {}).toQA || 0
                    return this.counts
                })
            },
            retrieve() {
                this.loading = true;
                this.axios.post(this.url || rootUrl(`admin/reports/monthly-billing/v2/data`), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    const pagination = response.data || []
                    const ids = this.tableData.map(i => i.id)
                    this.url = pagination.next_page_url
                    this.tableData = this.tableData.concat(pagination.data.filter(patient => !ids.includes(patient.id)).map((patient, index) => {
                        const item = {
                            id: patient.id,
                            MRN: patient.mrn,
                            approved: patient.approve,
                            rejected: patient.reject,
                            reportId: patient.report_id,
                            qa: patient.qa,
                            problems: patient.problems || [],
                            Provider: patient.provider,
                            Patient: patient.name,
                            patientUrl: patient.url,
                            Practice: patient.practice,
                            DOB: patient.dob,
                            Status: patient.status,
                            'CCM Mins': patient.ccm,
                            'Problem 1': patient.problem1,
                            'Problem 2': patient.problem2,
                            'Problem 1 Code': patient.problem1_code,
                            'Problem 2 Code': patient.problem2_code,
                            '#Successful Calls': patient.no_of_successful_calls,
                            chargeable_services: (patient.chargeable_services || []).map(item => item.id),
                            promises: {
                                problem_1: false,
                                problem_2: false,
                                approve_reject: false,
                                update_chargeables: false
                            },
                            errors: {
                                approve_reject: null
                            },
                            chargeables: () => {
                                return item.chargeable_services.map(id => this.chargeableServices.find(service => service.id == id)).filter(Boolean)
                            },
                            onChargeableServicesUpdate: (serviceIDs) => {
                                item.chargeable_services = serviceIDs
                                console.log('service-ids', serviceIDs, item)
                                item.promises.update_chargeables = true
                                return this.axios.post(rootUrl('admin/reports/monthly-billing/v2/updateSummaryServices'), {
                                    report_id: item.reportId,
                                    patient_chargeable_services: serviceIDs
                                }).then(response => {
                                    console.log('billing:chargeable-services:update', response.data)
                                    item.promises.update_chargeables = false
                                }).catch(err => {
                                    console.error('billing:chargeable-services:update', err)
                                    item.promises.update_chargeables = false
                                })
                            }
                        }
                        return item
                    }).sort((pA, pB) => pB.qa - pA.qa))
                    this.loading = false;
                    console.log('bills-report', this.tableData.slice(0))
                }).catch(err => {
                    console.error(err)
                    this.loading = false
                })
            },

            showChargeableServicesModal(row) {
                const self = this
                Event.$emit('modal-chargeable-services:show', {
                    title: 'Select Chargeable Services for ' + row.Patient,
                    row
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
                let dates = []
                const months = [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dev'
                    ]
                let currentMonth = (new Date()).getMonth()
                let currentYear = (new Date()).getFullYear()
                for (let i = 0; i >= -6; i--) {
                    let mDate = moment(new Date())
                    const month = months[currentMonth < 0 ? 12 + currentMonth : currentMonth];
                    const year = currentMonth < 0 ? currentYear - 1 : currentYear
                    dates.push({ long: month + ', ' + year, selected: i === 0 })
                    currentMonth--;
                }
                return dates
            },
            practice() {
                return this.practices.find(p => p.id == this.selectedPractice)
            },
            options() {
                return {
                    rowClassCallback(row) {
                        if (row.qa) return 'bg-flagged'
                        return ''
                    },
                    texts: {
                        count: `Showing {from} to {to} of ${this.counts.total()} records|${this.counts.total()} records|One record`
                    },
                    perPage: 15,
                    perPageValues: [
                        15,
                        30,
                        50
                    ]
                }
            }
        },
        mounted() {
            this.tableData = this.tableData.sort((pA, pB) => pB.qa - pA.qa)
            this.selectedMonth = this.months[0].long
            this.selectedPractice = this.practices[0].id
            this.retrieve()
            this.getChargeableServices()
            this.getCounts()

            Event.$on('vue-tables.pagination', (page) => {
                const $table = this.$refs.tblBillingReport
                if (page === $table.totalPages) {
                    console.log('next page clicked')
                    this.retrieve();
                }
            })
        }
    }
</script>

<style scoped>
    .inline-block {
        display: inline-block;
    }

    input[type="checkbox"] {
        display: inline-block !important;
    }

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