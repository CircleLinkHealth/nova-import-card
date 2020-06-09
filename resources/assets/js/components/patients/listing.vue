<template>
    <div>
        <div class="row">
            <div class="col-sm-12 text-right pad-10">
                <div class="col-sm-6 text-left" v-if="this.showProviderPatientsButton">
                    <button v-if="this.showPracticePatients" class="btn btn-info btn-xs"
                            @click="togglePracticePatients">Show My Patients
                    </button>
                    <button v-if="!this.showPracticePatients" class="btn btn-info btn-xs"
                            @click="togglePracticePatients">Show Practice Patients
                    </button>
                </div>
                <div v-bind:class="{'col-sm-6': this.showProviderPatientsButton}">
                    <button class="btn btn-info btn-xs" @click="clearFilters">Clear Filters</button>
                </div>
            </div>
        </div>
        <div class="top-10">
            <loader v-if="loaders.next || loaders.practices || loaders.providers"></loader>
        </div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options"
                        id="patient-list-table">
            <template slot="name" slot-scope="props">
                <div><a class="in-table-link" :href="rootUrl('manage-patients/' + props.row.id + '/view-careplan')" target="_blank">{{props.row.name}}</a>
                </div>
            </template>
            <template slot="provider" slot-scope="props">
                <div>{{ props.row.provider_name }}</div>
            </template>
            <template slot="practice" slot-scope="props">
                <div>{{ props.row.practice_name }}</div>
            </template>
            <template slot="filter__practice">
                <div>
                </div>
            </template>
            <template slot="location" slot-scope="props">
                <div>{{ props.row.location_name }}</div>
            </template>
            <template slot="filter__location">
                <div>
                </div>
            </template>
            <template slot="ccmStatus" slot-scope="props">
                <div>
                    {{ccmStatusMap[props.row.ccmStatus] || props.row.ccmStatus}}
                </div>
            </template>
            <template slot="h__ccmStatusDate" slot-scope="props">
                CCM Status Change
            </template>
            <template slot="careplanStatus" slot-scope="props">
                <a v-if="canApproveCareplans && props.row.careplanStatus === 'qa_approved'" class="in-table-link" :href="rootUrl('manage-patients/' + props.row.id + '/view-careplan')">
                    <b>{{carePlanStatusMap[props.row.careplanStatus] || props.row.careplanStatus}}</b>
                </a>

                <a v-else-if="isAdmin && props.row.careplanStatus === 'draft'" class="in-table-link" :href="rootUrl('manage-patients/' + props.row.id + '/view-careplan')">
                    <b>{{carePlanStatusMap[props.row.careplanStatus] || props.row.careplanStatus}}</b>
                </a>

                <p v-else>
                    {{carePlanStatusMap[props.row.careplanStatus] || props.row.careplanStatus}}
                </p>
            </template>
            <template slot="filter__ccm">
                <div>(HH:MM:SS)</div>
            </template>
            <template slot="filter__bhi">
                <div>(HH:MM:SS)</div>
            </template>
            <template slot="h__ccmStatus" slot-scope="props">
                CCM Status
            </template>
            <template slot="h__careplanStatus" slot-scope="props">
                Careplan Status
            </template>
            <template slot="h__withdrawnReason" slot-scope="props">
                Withdrawn Reason
            </template>
            <template slot="withdrawnReason" slot-scope="props">
                <div class="withdrawn-reason-column"><span :title="props.row.withdrawnReason">{{ props.row.withdrawnReason }}</span>
                </div>
            </template>
            <template slot="h__dob" slot-scope="props">
                Date of Birth
            </template>
            <template slot="h__mrn" slot-scope="props">
                MRN
            </template>
            <template slot="h__registeredOn" slot-scope="props">
                Registered On
            </template>
            <template slot="h__bhi" slot-scope="props">
                BHI
            </template>
            <template slot="h__ccm" slot-scope="props">
                CCM
            </template>
        </v-client-table>
        <div class="row">
            <div class="col-sm-8">
                <input type="button" class="btn btn-patients-table"
                       :value="'Show by ' + (nameDisplayType ? 'First' : 'Last') + ' Name'"
                       @click="changeNameDisplayType">
                <span class="pad-10"></span>

                <a v-if="! this.hideDownloadButtons" class="btn btn-patients-table" :class="{ disabled: loaders.pdf }" @click="exportPdf"
                   :href="rootUrl('manage-patients/listing/pdf')" download="patient-list.pdf">Export as PDF</a>
                <span class="pad-10"></span>

                <input v-if="! this.hideDownloadButtons" type="button" class="btn btn-patients-table" :class="{ disabled: loaders.excel }"
                       :value="exportCSVText" @click="exportCSV">
                <span class="pad-10"></span>

                <input type="button" class="btn btn-patients-table"
                       :value="(columns.includes('practice') ? 'Hide' : 'Show') + ' Practice'"
                       @click="toggleProgramColumn">
            </div>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import {Event} from 'vue-tables-2'
    import {CancelToken} from 'axios'
    import moment from 'moment'
    import loader from '../loader'

    /**
     * Determines whether to show patient name format as
     * - {FirstName} {LastName} or
     * - {LastName} {FirstName}
     */
    const NameDisplayType = {
        FirstName: 0,
        LastName: 1
    }

    const createCustomSort = (prop, ascending) => (a, b) => ((!a[prop] && (a[prop] !== 0)) ? 1 : ((!b[prop] && (b[prop] !== 0)) ? -1 : ascending ? (a[prop] < b[prop] ? -1 : 1) : (a[prop] < b[prop] ? 1 : -1)))
    const iSort = (a, b) => (a.i - b.i)

    export default {
        name: 'PatientList',
        components: {
            loader
        },
        props: {
            isAdmin: {
                type: Boolean,
                required: true,
            },
            showProviderPatientsButton: {
                type: Boolean,
                required: true,
            },
            urlFilter: {
                type: String,
                required: false,
                default: () => ''
            },
            hideDownloadButtons: {
                type: Boolean,
                required: false,
                default: false
            },
            canApproveCareplans: {
                type: Boolean,
                required: false,
                default: false,
            },
        },
        data() {
            let carePlanStatusMap;
            if (this.isAdmin) {
                carePlanStatusMap = {
                    to_enroll: 'To Enroll',
                    qa_approved: 'CLH Approved',
                    provider_approved: 'Provider Approved',
                    none: 'None',
                    draft: 'Approve Now',
                    g0506: 'G0506'
                };
            } else {
                carePlanStatusMap = {
                    qa_approved: 'Approve Now',
                    provider_approved: 'Approved',
                };
            }

            return {
                pagination: null,
                tableData: [],
                practices: [],
                providersForSelect: [],
                locationsForSelect: [],
                showPracticePatients: false,
                nameDisplayType: NameDisplayType.FirstName,
                columns: ['name', 'provider', 'location', 'ccmStatus', 'ccmStatusDate', 'careplanStatus', 'withdrawnReason', 'dob', 'mrn', 'phone', 'age', 'registeredOn', 'bhi', 'ccm'],
                loaders: {
                    next: false,
                    practices: null,
                    providers: false,
                    locations: false,
                    excel: false,
                    pdf: false
                },
                tokens: {
                    next: null
                },
                exportCSVText: 'Export as CSV',
                ccmStatusMap: {
                    enrolled: 'Enrolled',
                    to_enroll: 'To Enroll',
                    patient_rejected: 'Patient Declined',
                    withdrawn: 'Withdrawn',
                    withdrawn_1st_call: 'Withdrawn 1st Call',
                    paused: 'Paused',
                    unreachable: 'Unreachable',
                },
                carePlanStatusMap
            }
        },
        computed: {
            options() {

                let careplanStatus = [
                    {id: 'qa_approved', text: this.carePlanStatusMap['qa_approved']},
                    {id: 'provider_approved', text: this.carePlanStatusMap['provider_approved']},
                ];
                if (this.isAdmin) {
                    careplanStatus.push({id: '', text: this.carePlanStatusMap['none']});
                    careplanStatus.push({id: 'g0506', text: this.carePlanStatusMap['g0506']});
                    careplanStatus.push({id: 'draft', text: this.carePlanStatusMap['draft']});
                }

                return {
                    filterByColumn: true,
                    sortable: ['name', 'provider', 'practice', 'ccmStatus', 'ccmStatusDate', 'careplanStatus', 'withdrawnReason', 'dob', 'age', 'mrn', 'registeredOn', 'bhi', 'ccm'],
                    filterable: ['name', 'provider', 'practice', 'location', 'ccmStatus', 'ccmStatusDate', 'careplanStatus', 'withdrawnReason', 'dob', 'phone', 'age', 'mrn', 'registeredOn'],
                    listColumns: {
                        provider: this.providersForSelect,
                        location: this.locationsForSelect,
                        ccmStatus: [
                            {id: 'enrolled', text: 'Enrolled'},
                            {id: 'paused', text: 'Paused'},
                            {id: 'withdrawn', text: 'Withdrawn'},
                            {id: 'withdrawn_1st_call', text: 'Wthdrn 1st Call'},
                            {id: 'to_enroll', text: 'To Enroll'},
                            {id: 'unreachable', text: 'Unreachable'},
                            {id: 'patient_rejected', text: 'Patient Rejected'}
                        ],
                        careplanStatus,
                        practice: this.practices.map(practice => ({
                            id: practice.id,
                            text: practice.display_name
                        })).sort((p1, p2) => p1.text > p2.text ? 1 : -1).distinct(practice => practice.id)
                    },
                    texts: {
                        count: `Showing {from} to {to} of ${((this.pagination || {}).total || 0)} records|${((this.pagination || {}).total || 0)} records|One record`
                    },
                    perPage: this.isFilterActive() ? this.tableData.length : 10,
                    customSorting: {
                        name: (ascending) => iSort,
                        provider: (ascending) => iSort,
                        location: (ascending) => iSort,
                        ccmStatus: (ascending) => iSort,
                        ccmStatusDate: (ascending) => iSort,
                        careplanStatus: (ascending) => iSort,
                        dob: (ascending) => iSort,
                        mrn: (ascending) => iSort,
                        withdrawnReason: (ascending) => iSort,
                        phone: (ascending) => iSort,
                        age: (ascending) => iSort,
                        registeredOn: (ascending) => iSort,
                        bhi: (ascending) => iSort,
                        ccm: (ascending) => iSort,
                        practice: (ascending) => iSort
                    },
                    noResults: 'No patients match these criteria at this time.'
                }
            }
        },
        methods: {
            rootUrl,
            isFilterActive() {
                return this.$refs.tblPatientList ? !!Object.values(this.$refs.tblPatientList.query).reduce((a, b) => a || b) : false
            },
            columnMapping(name) {
                const columns = {

                }
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
            },
            nextPageUrl() {
                const $table = this.$refs.tblPatientList
                const query = $table.$data.query

                const filters = Object.keys(query).map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''

                if (this.pagination) {
                    return rootUrl(`api/patients?page=${this.$refs.tblPatientList.page}&rows=${this.$refs.tblPatientList.limit}${filters}${sortColumn}&showPracticePatients=${this.showPracticePatients}&${this.urlFilter}`)
                } else {
                    return rootUrl(`api/patients?rows=${this.$refs.tblPatientList.limit}${filters}${sortColumn}&showPracticePatients=${this.showPracticePatients}&${this.urlFilter}`)
                }
            },
            filterData() {
                const $table = this.$refs.tblPatientList
                const query = $table.$data.query
                const activeFilters = Object.keys(query).map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value)

                return activeFilters.reduce((a, filter) => {
                    a[filter.key] = filter.value
                    return a
                }, {})
            },
            toggleProgramColumn() {
                if (this.columns.indexOf('practice') >= 0) {
                    this.columns.splice(this.columns.indexOf('practice'), 1)
                } else {
                    this.columns.splice(2, 0, 'practice')
                }
            },
            togglePracticePatients() {
                this.showPracticePatients = !this.showPracticePatients
                this.activateFilters()
            },
            activateFilters() {
                this.pagination = null
                this.tableData = []
                this.$refs.tblPatientList.setPage(1)
                this.getPatients()
                this.nameDisplayType = NameDisplayType.FirstName
            },
            changeNameDisplayType() {
                if (this.nameDisplayType !== NameDisplayType.FirstName) {
                    this.tableData.forEach(patient => {
                        if (patient.lastName && patient.firstName) patient.name = patient.firstName + ' ' + patient.lastName
                    })
                } else {
                    this.tableData.forEach(patient => {
                        if (patient.lastName && patient.firstName) patient.name = patient.lastName + ', ' + patient.firstName
                    })
                }
                this.nameDisplayType = Number(!this.nameDisplayType)
            },
            getPractices() {
                return this.loaders.practices = this.axios.get(rootUrl('api/practices')).then(response => {
                    this.practices = response.data
                    this.loaders.practices = null
                    return this.practices
                }).catch(err => {
                    console.error('patient-list:practices', err)
                    this.loaders.practices = null
                })
            },
            getProviders() {
                this.loaders.providers = true
                return this.axios.get(rootUrl('api/providers/list')).then(response => {
                    this.providersForSelect = (response.data || []).map(provider => ({
                        id: provider.user_id,
                        text: provider.name
                    })).filter(provider => !!provider.text).sort((a, b) => a.text < b.text ? -1 : 1)
                    this.loaders.providers = false
                    return this.providersForSelect
                }).catch(err => {
                    console.error('patient-list:providers', err)
                    this.loaders.providers = false
                })
            },
            getLocations() {
                this.loaders.locations = true
                return this.axios.get(rootUrl('api/locations/list')).then(response => {
                    this.locationsForSelect = (response.data || []).map(location => ({
                        id: location.id,
                        text: location.name
                    })).filter(location => !!location.text).sort((a, b) => a.text < b.text ? -1 : 1)
                    this.loaders.locations = false
                    return this.locationsForSelect
                }).catch(err => {
                    console.error('patient-list:locations', err)
                    this.loaders.locations = false
                })
            },
            getStatusDate(patient) {
                if (patient.patient_info.ccm_status === 'paused') {
                    return moment(patient.patient_info.date_paused).format('MM-DD-YYYY')
                }
                if (patient.patient_info.ccm_status === 'withdrawn') {
                    return moment(patient.patient_info.date_withdrawn).format('MM-DD-YYYY')
                }
                if (patient.patient_info.ccm_status === 'unreachable') {
                    return moment(patient.patient_info.date_unreachable).format('MM-DD-YYYY')
                }
            },
            getPatients() {
                const self = this
                this.loaders.next = true
                return this.axios.get(this.nextPageUrl(), {
                    cancelToken: new CancelToken((c) => {
                        if (this.tokens.next) {
                            this.tokens.next()
                        }
                        this.tokens.next = c
                    })
                }).then(response => {
                    if (!response) {
                        //request was cancelled
                        return;
                    }
                    const pagination = response.data
                    const ids = this.tableData.map(patient => patient.id)
                    this.pagination = {
                        current_page: pagination.meta.current_page,
                        from: pagination.meta.from,
                        last_page: pagination.meta.last_page,
                        last_page_url: pagination.links.last,
                        next_page_url: pagination.links.next,
                        path: pagination.meta.path,
                        per_page: pagination.meta.per_page,
                        to: pagination.meta.to,
                        total: pagination.meta.total
                    }
                    const patients = (pagination.data || []).map(patient => {
                        if (patient.careplan &&
                            typeof patient.careplan.status === "string" &&
                            patient.careplan.status.startsWith('{')) {
                            const statusObj = JSON.parse(patient.careplan.status);
                            if (statusObj && statusObj.status) {
                                patient.careplan.status = statusObj.status;
                            }
                        }
                        if (patient.patient_info) {
                            if (patient.patient_info.created_at) patient.patient_info.created_at = patient.patient_info.created_at.split('T')[0]
                            // patient.patient_info.age = (patient.patient_info.birth_date && (patient.patient_info.birth_date != '0000-00-00')) ? ((new Date()).getFullYear() - (new Date(patient.patient_info.birth_date)).getFullYear()) : (new Date()).getFullYear()
                            if (patient.patient_info.date_paused) patient.patient_info.date_paused = patient.patient_info.date_paused.split('T')[0]
                            if (patient.patient_info.date_withdrawn) patient.patient_info.date_withdrawn = patient.patient_info.date_withdrawn.split('T')[0]
                            if (patient.patient_info.date_unreachable) patient.patient_info.date_unreachable = patient.patient_info.date_unreachable.split('T')[0]
                        }
                        return patient
                    }).map(patient => {
                        patient.name = (patient.name || '').trim()
                        patient.firstName = patient.name.split(' ')[0]
                        patient.lastName = patient.name.split(' ').slice(1).join(' ')
                        patient.provider = patient.billing_provider_id
                        patient.provider_name = patient.billing_provider_name
                        patient.location = patient.location_id || 'N/A'
                        patient.location_name = patient.location_name || 'N/A'
                        patient.ccmStatus = (patient.patient_info || {}).ccm_status || 'none'
                        patient.careplanStatus = (patient.careplan || {}).status || 'none'
                        patient.dob = (patient.patient_info || {}).birth_date || ''
                        patient.sort_dob = new Date((patient.patient_info || {}).birth_date || '')
                        patient.practice = patient.program_id
                        patient.practice_name = (this.practices.find(practice => practice.id == patient.program_id) || {}).display_name || ''
                        patient.age = (patient.patient_info || {}).age || ''
                        patient.mrn = (patient.patient_info || {}).mrn_number || ''
                        patient.withdrawnReason = (patient.patient_info || {}).withdrawn_reason || ''
                        patient.registeredOn = moment(patient.created_at || '').format('MM-DD-YYYY')
                        patient.ccmStatusDate = (this.getStatusDate(patient) || '')
                        patient.sort_registeredOn = new Date(patient.created_at)
                        patient.sort_ccmStatusDate = new Date(patient.ccmStatusDate)

                        const pad = (num, count = 2) => '0'.repeat(count - num.toString().length) + num
                        const seconds = patient.ccm_time || 0
                        patient.ccm = pad(Math.floor(seconds / 3600), 2) + ':' + pad(Math.floor(seconds / 60) % 60, 2) + ':' + pad(seconds % 60, 2);
                        patient.sort_ccm = seconds;

                        const bhiSeconds = patient.bhi_time || 0
                        patient.bhi = pad(Math.floor(bhiSeconds / 3600), 2) + ':' + pad(Math.floor(bhiSeconds / 60) % 60, 2) + ':' + pad(bhiSeconds % 60, 2);
                        patient.sort_bhi = bhiSeconds;
                        return patient
                    }).map(patient => {
                        const loadColumnList = (list = [], item = null) => {
                            if ((item || '').toString().trim() && !list.find(orb => orb.text == item)) {
                                list.push({
                                    id: item,
                                    text: item
                                })
                            }
                        }
                        // loadColumnList(this.options.listColumns.provider, patient.provider)
                        //loadColumnList(this.options.listColumns.ccmStatus, patient.ccmStatus)
                        //loadColumnList(this.options.listColumns.careplanStatus, patient.careplanStatus)
                        // loadColumnList(this.options.listColumns.practice, patient.practice)
                        return patient
                    })

                    const filterData = this.filterData()

                    if (!this.tableData.length) {
                        const arr = patients.map((patient, i) => Object.assign({}, patient, {i: (i + 1)}))
                        const total = ((this.pagination || {}).total || 0)
                        this.tableData = [...arr, ...'0'.repeat(total - arr.length).split('').map((item, index) => Object.assign({
                            i: arr.length + index + 1,
                            id: arr.length + index
                        }, filterData))]
                    } else {
                        const from = ((this.pagination || {}).from || 0)
                        const to = ((this.pagination || {}).to || 0)

                        let counterIndex = (from + 0)

                        this.tableData = this.tableData.map((row, i) => {
                            if (row.i === counterIndex) {
                                const patient = patients[counterIndex - from]
                                if (patient) {
                                    patient.i = (i + 1)
                                    counterIndex += 1
                                    return patient
                                } else return Object.assign({}, filterData, row)
                            } else {
                                return Object.assign({}, filterData, row)
                            }
                        })
                    }
                    setTimeout(() => {
                        if (this.pagination) {
                            this.$refs.tblPatientList.count = this.pagination.total;
                        } else {
                            this.$refs.tblPatientList.count = 0;
                        }
                        this.loaders.next = false;
                    }, 1000);

                    return filterData;
                }).catch(err => {
                    console.error('patient-list', err)
                    this.loaders.next = false
                })
            },
            exportCSV() {
                let patients = []
                this.loaders.excel = true

                const $table = this.$refs.tblPatientList
                const query = $table.$data.query
                const filters = Object.keys(query).map(key => ({
                    key,
                    value: query[key]
                })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''

                const download = (page = 1) => {
                    return this.axios.get( rootUrl(`api/patients?rows=50&page=${page}&csv${filters}${sortColumn}&showPracticePatients=${this.showPracticePatients}`)).then(response => {
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

                    const str = 'name,provider,practice,location,ccm status,careplan status, withdrawn reason, dob,mrn,phone,age,registered on,bhi,ccm,ccm status change\n'
                        + patients.join('\n');
                    const csvData = new Blob([str], {type: 'text/csv'});
                    const csvUrl = URL.createObjectURL(csvData);
                    const link = document.createElement('a');
                    link.download = `patient-list-${Date.now()}.csv`;
                    link.href = csvUrl;
                    link.click();
                    this.exportCSVText = 'Export as CSV';
                    this.loaders.excel = false
                })
            },
            exportPdf() {
                if (!this.loaders.pdf) {
                    this.loaders.pdf = true
                }
            },
            createHumanReadableFilterNames() {
                /**
                 * make sure the filter input placeholders have human readable text like "CCM Status" instead of "ccmStatus"
                 */
                const patientListElem = this.$refs.tblPatientList.$el

                const ccmStatusSelect = patientListElem.querySelector('select[name="vf__ccmStatus"]')
                ccmStatusSelect.querySelector('option').innerText = 'Select CCM Status'

                window.ccmStatusSelect = ccmStatusSelect;

                ([...(ccmStatusSelect.querySelectorAll('option') || [])]).forEach(option => {
                    option.innerText = ({
                        enrolled: 'Enrolled',
                        to_enroll: 'To Enroll',
                        patient_rejected: 'Patient Declined',
                        withdrawn: 'Withdrawn',
                        paused: 'Paused',
                        unreachable: 'Unreachable'
                    })[option.innerText] || option.innerText
                });

                const careplanStatusSelect = patientListElem.querySelector('select[name="vf__careplanStatus"]');

                ([...(careplanStatusSelect.querySelectorAll('option') || [])]).forEach(option => {
                    option.innerText = ({
                        qa_approved: 'Approve Now',
                        to_enroll: 'To Enroll',
                        provider_approved: 'Provider Approved',
                        none: 'None',
                        draft: 'Draft',
                        patient_rejected: 'Patient Declined',
                        g0506: 'G0506',
                        'Select careplanStatus': 'Select Careplan Status'
                    })[option.innerText] || option.innerText
                })

                const dobInput = patientListElem.querySelector('input[name="vf__dob"]')
                dobInput.setAttribute('placeholder', 'Filter by Date of Birth')

                const mrnInput = patientListElem.querySelector('input[name="vf__mrn"]')
                mrnInput.setAttribute('placeholder', 'Filter by MRN')

                const withdrawnReasonInput = patientListElem.querySelector('input[name="vf__withdrawnReason"]')
                withdrawnReasonInput.setAttribute('placeholder', 'Filter by Reason')

                const registeredOnInput = patientListElem.querySelector('input[name="vf__registeredOn"]')
                registeredOnInput.setAttribute('placeholder', 'Filter by Registered On')

                const ccmStatusDateInput = patientListElem.querySelector('input[name="vf__ccmStatusDate"]')
                ccmStatusDateInput.setAttribute('placeholder', 'Filter by CCM Status Date')
            },
            clearFilters() {
                Object.keys(this.$refs.tblPatientList.query).forEach((key) => {
                    const obj = {}
                    obj[key] = ''
                    this.$refs.tblPatientList.setFilter(obj)
                })
                this.$refs.tblPatientList.setOrder()
                this.activateFilters()
            }
        },
        mounted() {
            /**
             * load practices, then patients
             */
            this.getPractices().then(() => {
                this.getPatients()
            })

            this.getProviders()
            this.getLocations()

            /**
             * listen to table pagination event and ...
             * load next patients when user is on the last page
             */
            const $table = this.$refs.tblPatientList
            Event.$on('vue-tables.pagination', (page) => {
                this.getPatients()
            })

            Event.$on('vue-tables.filter::name', this.activateFilters)

            Event.$on('vue-tables.filter::provider', this.activateFilters)

            Event.$on('vue-tables.filter::practice', this.activateFilters)

            Event.$on('vue-tables.filter::location', this.activateFilters)

            Event.$on('vue-tables.filter::ccmStatus', this.activateFilters)

            Event.$on('vue-tables.filter::careplanStatus', this.activateFilters)

            Event.$on('vue-tables.filter::withdrawnReason', this.activateFilters)

            Event.$on('vue-tables.filter::dob', this.activateFilters)

            Event.$on('vue-tables.filter::mrn', this.activateFilters)

            Event.$on('vue-tables.filter::phone', this.activateFilters)

            Event.$on('vue-tables.filter::age', this.activateFilters)

            Event.$on('vue-tables.filter::registeredOn', this.activateFilters)

            Event.$on('vue-tables.filter::ccmStatusDate', this.activateFilters)

            Event.$on('vue-tables.sorted', this.activateFilters)

            Event.$on('vue-tables.limit', this.activateFilters)

            this.createHumanReadableFilterNames()
        }
    }
</script>

<style>
    .pad-10 {
        padding: 10px;
    }

    .table-bordered > tbody > tr > td {
        white-space: nowrap;
    }

    .withdrawn-reason-column {
        max-width: 250px;
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }

    .btn-patients-table {
        color: #fff;
        background-color: #47beaa;
        border-color: #47beaa;
    }

    #patient-list-table > div.table-responsive > table > thead {
        background-color: #d2e2ef !important;
    }
</style>

<style scoped>
    a:hover {
        text-decoration: none;
    }

    a[href]:hover {
        text-decoration: underline;
    }

    .in-table-link {
        color: #337ab7 !important;
        text-decoration: underline;
    }
</style>
