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
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div>
                                        <label>Select Practice</label>
                                    </div>
                                    <select2 class="form-control" v-model="selectedPractice">
                                        <option v-for="(practice, index) in practices" :key="index"
                                                :value="practice.id">{{practice.display_name}}
                                        </option>
                                    </select2>
                                </div>
                                <div class="col-sm-4">
                                    <div>
                                        <label>Select Month</label>
                                    </div>
                                    <select2 v-if="months && months.length" class="form-control" v-model="selectedMonth"
                                             :value="months[0].label">
                                        <option v-for="(month, index) in months" :key="index" :value="month.label">
                                            {{month.label}}
                                        </option>
                                    </select2>
                                </div>
                                <div class="col-sm-4">
                                    <div>&nbsp;</div>
                                    <button class="btn btn-info" @click="changePractice" :disabled="loaders.billables">
                                        Load
                                    </button>
                                    <loader class="inline-block absolute" v-if="loaders.billables"></loader>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="row">
                                <div class="col-sm-12">
                                    <form v-if="!isSoftwareOnly">
                                        <div class="row">
                                            <div class="col-sm-8">
                                                <div>
                                                    <label v-if="typeof practice !== 'undefined'">
                                                        Set <a target="_blank"
                                                               :href="`/practices/${practice.name}/chargeable-services`">Chargeable
                                                        Services</a>
                                                    </label>
                                                </div>
                                                <select2 class="form-control" v-model="selectedService"
                                                         :disabled="isClosed">
                                                    <option :value="null">Set Default Code</option>
                                                    <option v-for="(service, index) in selectedPracticeChargeableServices"
                                                            :key="index"
                                                            :value="service.id">{{service.code}}
                                                    </option>
                                                </select2>
                                            </div>
                                            <div class="col-sm-4 text-right">
                                                <div>&nbsp;</div>
                                                <div>
                                                    <button class="btn btn-info" @click="attachChargeableService"
                                                            :disabled="loaders.chargeableServices || isClosed">Attach
                                                    </button>
                                                    <button class="btn btn-danger" @click="detachChargeableService"
                                                            :disabled="loaders.chargeableServices || isClosed">Detach
                                                    </button>
                                                    <loader class="inline-block absolute"
                                                            v-if="loaders.chargeableServices"></loader>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12 text-right" v-if="tableData.length > 0">
                                    <div>&nbsp;</div>
                                    <button class="btn btn-danger" v-if="!isClosed" @click="closeMonth">Save and Lock
                                        Month
                                    </button>
                                    <loader v-if="loaders.closeMonth"></loader>
                                    <button class="btn btn-success" v-if="isClosed" @click="openMonth">Unlock / Edit
                                        Month
                                    </button>
                                    <loader v-if="loaders.openMonth"></loader>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 text-center line-50 row">
                <div class="col-sm-4">
                    <strong>Approved: </strong>
                    <loader v-if="loaders.billables"></loader>
                    <span class="color-green">
                        {{loaders.billables ? '' : counts.approved}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Flagged: </strong>
                    <loader v-if="loaders.billables"></loader>
                    <span class="color-dark-orange">
                        {{loaders.billables ? '' : counts.flagged}}
                    </span>
                </div>
                <div class="col-sm-4">
                    <strong>Rejected: </strong>
                    <loader v-if="loaders.billables"></loader>
                    <span class="color-dark-red">
                        {{loaders.billables ? '' : counts.rejected}}
                    </span>
                </div>
            </div>
            <v-client-table ref="tblBillingReport" :data="tableData" :columns="columns" :options="options">
                <template slot="approved" slot-scope="props">
                    <input class="row-select" v-model="props.row.approved"
                           @change="approveOrReject($event, props.row, 'approve')"
                           type="checkbox" :readonly="!!props.row.promises['approve_reject']" style="display:block;"/>
                    <span class="error-btn" v-if="props.row.errors.approve_reject"
                          title="view error message"
                          @click="showErrorModal(props.row.id, 'approve_reject')">x</span>
                    <loader v-if="props.row.promises['approve_reject']"></loader>
                </template>
                <template slot="rejected" slot-scope="props">
                    <input class="row-select" v-model="props.row.rejected"
                           @change="approveOrReject($event, props.row, 'reject')"
                           type="checkbox" :readonly="!!props.row.promises['approve_reject']" style="display:block;"/>
                    <span class="error-btn" v-if="props.row.errors.approve_reject"
                          title="view error message"
                          @click="showErrorModal(props.row.id, 'approve_reject')">x</span>
                    <loader v-if="props.row.promises['approve_reject']"></loader>
                </template>
                <template slot="Patient" slot-scope="props">
                    <a :href="props.row.patientUrl" target="_blank" class="blue">{{props.row.Patient}}</a>
                </template>
                <template slot="CCM Problem 1" slot-scope="props">
                    <div>
                        <span class="blue pointer"
                              @click="showCcmModal(props.row, 1)">{{props.row['CCM Problem 1'] || '&lt;Edit&gt;'}}</span>
                        <loader v-if="props.row.promises['problem_1']"></loader>
                    </div>
                </template>
                <template slot="CCM Problem 2" slot-scope="props">
                    <div>
                        <span class="blue pointer"
                              @click="showCcmModal(props.row, 2)">{{props.row['CCM Problem 2'] || '&lt;Edit&gt;'}}</span>
                        <loader v-if="props.row.promises['problem_2']"></loader>
                    </div>
                </template>
                <template slot="BHI Problem" slot-scope="props">
                    <div>
                        <span class="blue pointer"
                              @click="showBhiModal(props.row, 3)">{{props.row['BHI Problem'] || '&lt;Edit&gt;'}}</span>
                        <loader v-if="props.row.promises['bhi_problem']"></loader>
                    </div>
                </template>
                <template slot="chargeable_services" slot-scope="props">
                    <div class="blue" :class="isSoftwareOnly ? '' : 'pointer'" @click="showChargeableServicesModal(props.row)">
                        <div v-if="props.row.chargeable_services.length">
                            <label class="label label-info margin-5 inline-block"
                                   v-for="service in props.row.chargeables()" :key="service.id">{{service.code}}</label>
                        </div>
                        <div v-if="!props.row.chargeable_services.length">&lt;Edit&gt;</div>
                        <loader v-if="props.row.promises['update_chargeables']"></loader>
                    </div>
                </template>
            </v-client-table>
            <div class="text-right" v-if="tableData.length > 0">
                <button class="btn btn-danger" v-if="!isClosed" @click="closeMonth">Save and Lock Month</button>
                <loader v-if="loaders.closeMonth"></loader>
                <button class="btn btn-success" v-if="isClosed" @click="openMonth">Unlock / Edit Month</button>
                <loader v-if="loaders.openMonth"></loader>
            </div>
            <patient-problem-modal ref="patientProblemModal" :cpm-problems="cpmProblems"></patient-problem-modal>
            <chargeable-services-modal ref="chargeableServicesModal"
                                       :services="selectedPracticeChargeableServices"></chargeable-services-modal>
            <error-modal ref="errorModal"></error-modal>
            <notifications ref="notifications" name="billing"></notifications>
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
    import buildReport, {styles} from '../../excel'
    import Select2Component from '../../components/src/select2'
    import Loader from '../../components/loader'
    import timeDisplay from '../../util/time-display'
    import NotificationsComponent from '../../components/notifications'
    import SERVICES from '../../constants/services.types'

    export default {
        name: 'billing-report',
        props: {},
        components: {
            'text-editable': TextEditable,
            'patient-problem-modal': PatientProblemModal,
            'error-modal': ErrorModal,
            'select2': Select2Component,
            'chargeable-services-modal': ChargeableServicesModal,
            'loader': Loader,
            'notifications': NotificationsComponent
        },
        data() {
            return {
                //from server side
                isSoftwareOnly: !!isSoftwareOnly,
                months: dates,
                practices: practices,
                chargeableServicesPerPractice: practices.reduce((map, x) => {
                    map[x.id] = x['chargeable_services'];
                    return map;
                }, {}),
                cpmProblems: cpmProblems,
                chargeableServices: chargeableServices,
                //
                selectedMonth: null,
                selectedPractice: 0,
                selectedService: null,
                loading: true,
                loaders: {
                    practices: false,
                    billables: false,
                    chargeableServices: false,
                    openMonth: false,
                    closeMonth: false
                },
                url: null,
                counts: {
                    approved: 0,
                    rejected: 0,
                    flagged: 0,
                    total() {
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
                    'BHI Mins',
                    'CCM Problem 1',
                    'CCM Problem 1 Code',
                    'CCM Problem 2',
                    'CCM Problem 2 Code',
                    'BHI Problem',
                    'BHI Problem Code',
                    '#Successful Calls',
                    'approved',
                    'rejected',
                    'chargeable_services'],
                tableData: [],
                isClosed: false
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
                        tablePatient.actorId = (response.data || {}).actor_id
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
                this.url = null;
                this.$refs.tblBillingReport.setPage(1)
                this.getCounts()
                this.retrieve()
            },
            detachChargeableService(e) {
                if (e) e.preventDefault()
                const id = this.selectedService
                console.log('billing:chargeable-service:default', id)
                const service = this.chargeableServices.find(s => s.id == id)
                const practice = this.practices.find(p => p.id == this.selectedPractice)
                if (id && service && practice && !this.loaders.chargeableServices && confirm(`Are you sure you want to remove ${service.code} from all chargeable services for ${practice.name} in ${this.selectedMonth}?`)) {
                    return this.updateChargeableService(id, true)
                }
            },
            attachChargeableService(e) {
                if (e) e.preventDefault()
                const id = this.selectedService
                console.log('billing:chargeable-service:default', id)
                const service = this.chargeableServices.find(s => s.id == id)
                const practice = this.practices.find(p => p.id == this.selectedPractice)
                if (id && service && practice && !this.loaders.chargeableServices && confirm(`Are you sure you want to set ${service.code} as the default chargeable service for ${practice.name} in ${this.selectedMonth}?`)) {
                    return this.updateChargeableService(id)
                }
            },
            updateChargeableService(id, isDetach = false) {
                this.loaders.chargeableServices = true
                const data = {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth,
                    default_code_id: id
                }
                if (isDetach) {
                    data.detach = true
                }
                return this.axios.post(rootUrl('admin/reports/monthly-billing/v2/updatePracticeServices'), data).then(response => {
                    (response.data || []).forEach(summary => {
                        const tableItem = this.tableData.find(row => row.id == summary.id)
                        if (tableItem) {
                            tableItem.chargeable_services = (summary.chargeable_services || []).map(item => item.id)
                        }
                    })
                    this.loaders.chargeableServices = false
                    console.log('billing:chargeable-services:default:update', response.data)
                }).catch(err => {
                    console.error('billing:chargeable-services:default:update', err)

                    this.loaders.chargeableServices = false
                })
            },

            /*
            //todo - these are supplied from the view it-self. stop sending them from the view and use this.
            getChargeableServices() {
                this.loaders.chargeableServices = true
                return this.axios.get(rootUrl('admin/reports/monthly-billing/v2/services')).then(response => {
                    this.chargeableServices = (response.data || []).map(service => {
                        service.selected = null
                        return service
                    })
                    console.log('billing:chargeable-services', this.chargeableServices)
                    this.loaders.chargeableServices = false
                }).catch(err => {
                    console.error('billing:chargeable-services', err)
                    this.loaders.chargeableServices = false
                })
            },
            */
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
                this.loaders.billables = true
                this.axios.post(this.url || rootUrl(`admin/reports/monthly-billing/v2/data`), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    console.log('billables:response', response)
                    const pagination = response.data || []
                    const ids = this.tableData.map(i => i.id)
                    this.url = pagination.next_page_url
                    this.isClosed = !!Number(response.headers['is-closed'])
                    this.tableData = this.tableData.concat(pagination.data.filter(patient => !ids.includes(patient.id)).map((patient, index) => {
                        const item = {
                            id: patient.id,
                            MRN: patient.mrn,
                            approved: patient.approve,
                            rejected: patient.reject,
                            reportId: patient.report_id,
                            actorId: patient.actor_id,
                            qa: patient.qa,
                            problems: patient.problems || [],
                            Provider: patient.provider,
                            Patient: patient.name,
                            patientUrl: patient.url,
                            Practice: patient.practice,
                            DOB: patient.dob,
                            Status: patient.status,
                            'CCM Mins': timeDisplay(patient.ccm_time),
                            'BHI Mins': timeDisplay(patient.bhi_time),
                            'CCM Problem 1': patient.problem1,
                            'CCM Problem 2': patient.problem2,
                            'CCM Problem 1 Code': patient.problem1_code,
                            'CCM Problem 2 Code': patient.problem2_code,
                            'BHI Problem': patient.bhi_problem,
                            'BHI Problem Code': patient.bhi_problem_code,
                            '#Successful Calls': patient.no_of_successful_calls,
                            chargeable_services: (patient.chargeable_services || []).map(item => item.id),
                            promises: {
                                problem_1: false,
                                problem_2: false,
                                bhi_problem: false,
                                approve_reject: false,
                                update_chargeables: false
                            },
                            errors: {
                                approve_reject: null
                            },
                            chargeables: () => {
                                //we need the chargeableService for the practice (not all chargeableServices)
                                const practiceChargeableServices = this.selectedPracticeChargeableServices;
                                return item.chargeable_services.map(id => practiceChargeableServices.find(service => service.id == id)).filter(Boolean)
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
                            },
                            isBhiEligible() {
                                return !!this.chargeables().find(service => service.code === SERVICES.CPT_99484);
                            },
                            isCcmEligible() {
                                return !!this.chargeables().find(service => service.code === SERVICES.CPT_99490);
                            },
                            hasOver20MinutesBhiTime() {
                                return patient.bhi_time >= 1200;
                            },
                            hasOver20MinutesCCMTime() {
                                return patient.ccm_time >= 1200;
                            },
                        }
                        return item
                    }).sort((pA, pB) => pB.qa - pA.qa))
                    this.loaders.billables = false
                    console.log('bills-report', this.tableData.slice(0))
                }).catch(err => {
                    console.error(err)
                    this.loaders.billables = false
                })
            },

            showChargeableServicesModal(row) {

                if (this.isSoftwareOnly) {
                    return;
                }

                const self = this
                Event.$emit('modal-chargeable-services:show', {
                    title: 'Select Chargeable Services for ' + row.Patient,
                    row
                })
            },

            showBhiModal(patient, type) {
                if (!patient.isBhiEligible()) {
                    Event.$emit('notifications-billing:create', {
                        text: `Cannot edit BHI Problem. Check that both Practice and Patient are chargeable for ${SERVICES.CPT_99484}.`,
                        type: 'warning',
                        interval: 5000
                    });
                    return;
                }

                if (!patient.hasOver20MinutesBhiTime()) {
                    Event.$emit('notifications-billing:create', {
                        text: 'Cannot edit BHI Problem. The Patient has less than 20 minutes BHI time.',
                        type: 'warning',
                        interval: 5000
                    });
                    return;
                }

                this.showProblemsModal(patient, type);
            },

            showCcmModal(patient, type) {
                // if (!patient.isCcmEligible()) {
                //     Event.$emit('notifications-billing:create', {
                //         text: `Cannot edit CCM Problem. Check that both Practice and Patient are chargeable for ${SERVICES.CPT_99490}.`,
                //         type: 'warning',
                //         interval: 5000
                //     });
                //     return;
                // }

                if (!patient.hasOver20MinutesCCMTime()) {
                    Event.$emit('notifications-billing:create', {
                        text: 'Cannot edit CCM Problem. The Patient has less than 20 minutes CCM time.',
                        type: 'warning',
                        interval: 5000
                    });
                    return;
                }

                this.showProblemsModal(patient, type);
            },

            showProblemsModal(patient, type) {
                const self = this;
                Event.$emit('modal-patient-problem:show', patient, type, function (modified) {
                    /** callback done function */
                    const tablePatient = self.tableData.find(pt => pt.id === patient.id)
                    console.log('table-patient', tablePatient, modified)
                    if (tablePatient) {
                        if (modified.id == 'Other') {
                            modified.name = (this.cpmProblems.find(problem => problem.id == modified.cpm_id) || {}).name || modified.name
                        }
                        if (type === 1) {
                            tablePatient['CCM Problem 1 Code'] = modified.code
                            tablePatient['CCM Problem 1'] = modified.name
                        }
                        else if (type == 2) {
                            tablePatient['CCM Problem 2 Code'] = modified.code
                            tablePatient['CCM Problem 2'] = modified.name
                        }
                        else if (type == 3) {
                            tablePatient['BHI Problem Code'] = modified.code
                            tablePatient['BHI Problem'] = modified.name
                        }
                        const problemKey = (type === 1) ? 'problem_1' : (type === 2 ? 'problem_2' : 'bhi_problem')
                        tablePatient.promises[problemKey] = true
                        return self.axios.post(rootUrl('admin/reports/monthly-billing/v2/storeProblem'), {
                            code: modified.code,
                            id: modified.id,
                            name: modified.name,
                            problem_no: problemKey,
                            report_id: tablePatient.reportId,
                            cpm_problem_id: modified.cpm_id
                        }).then((response) => {
                            tablePatient.promises[problemKey] = false
                            console.log('billing-change-problem', response)
                            if (problemKey == 'bhi_problem' && !response) {
                                Event.$emit('notifications-billing:create', {
                                    text: 'An error occurred when performing this action',
                                    type: 'error',
                                    interval: 3000
                                })
                            }
                        }).catch(err => {
                            tablePatient.promises[problemKey] = false
                            console.error('billing-change-problem', err)
                            Event.$emit('notifications-billing:create', {
                                text: err.message,
                                type: 'error',
                                interval: 3000
                            })
                        })
                        console.log('table-patient-promises', tablePatient.promises)
                    }
                    else console.error('could not find tablePatient')
                })
            },

            showErrorModal(id, name) {
                const errors = (this.tableData.find(row => row.id === id) || {}).errors
                console.log(errors)
                Event.$emit('modal-error:show', {body: errors[name]}, () => {
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
                        data: this.tableData.map(row => (Object.assign({}, row, {
                            chargeable_services: row.chargeable_services.map(id => (this.chargeableServices.find(service => service.id == id) || {}).code)
                        })))
                    }
                ])

                const blob = new Blob([bytes], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'})
                const link = document.createElement('a')
                link.href = window.URL.createObjectURL(blob)
                link.download = `billable-patients-${this.practice.display_name.toLowerCase().replace(/ /g, '-')}-${this.selectedMonth.replace(', ', '-').toLowerCase()}-${Date.now()}.xlsx`
                link.click()
            },
            openMonth() {
                this.loaders.openMonth = true
                return this.$http.post(rootUrl('admin/reports/monthly-billing/v2/open'), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    this.loaders.openMonth = false
                    console.log('billable:open-month', response.data)
                    this.changePractice()
                }).catch(err => {
                    this.loaders.openMonth = false
                    console.error('billable:open-month', err)
                })
            },
            closeMonth() {
                this.loaders.closeMonth = true
                return this.$http.post(rootUrl('admin/reports/monthly-billing/v2/close'), {
                    practice_id: this.selectedPractice,
                    date: this.selectedMonth
                }).then(response => {
                    this.loaders.closeMonth = false
                    console.log('billable:close-month', response.data)
                    this.changePractice()
                }).catch(err => {
                    this.loaders.closeMonth = false
                    console.error('billable:close-month', err)
                })
            }
        },
        computed: {
            practice() {
                return this.practices.find(p => +p.id === +this.selectedPractice);
            },
            selectedPracticeChargeableServices() {
                return this.chargeableServicesPerPractice[this.selectedPractice] || [];
            },
            options() {
                const $vm = this;
                return {
                    rowClassCallback(row) {
                        if ($vm.isClosed) return 'bg-closed'
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

            this.tableData = this.tableData.sort((pA, pB) => pB.qa - pA.qa);
            this.selectedMonth = (this.months[0] || {}).label;
            this.selectedPractice = this.practices[0].id;

            // this.retrieve();

            //todo
            //this.getChargeableServices();

            // this.getCounts();

            Event.$on('vue-tables.pagination', (page) => {
                const $table = this.$refs.tblBillingReport;
                if (page === $table.totalPages) {
                    console.log('next page clicked');
                    this.retrieve();
                    this.getCounts()
                }
            })
        }
    }
</script>

<style>
    .inline-block {
        display: inline-block;
    }

    .absolute {
        position: absolute;
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

    .bg-flagged {
        background-color: rgba(255, 252, 96, 0.408) !important;
    }

    .bg-closed * {
        color: #aaa !important;
    }

    .bg-closed label {
        color: white !important;
    }

    .bg-closed span.blue.pointer, .bg-closed div.blue.pointer, .bg-closed input {
        pointer-events: none;
    }

    .bg-closed input {
        opacity: 0.7;
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

<style>
    .select2-container {
        width: 100% !important;
    }

    div.notifications-billing {
        position: fixed;
        right: 0px;
        top: 150px;
        max-width: 400px;
    }

    div.notifications-billing div.alert {
        margin-right: 30px;
    }
</style>
