<template>
    <div>
        <div class="row">
            <div class="col-sm-12 text-right pad-10">
                <button class="btn btn-info btn-xs" @click="clearFilters">Clear Filters</button>
            </div>
        </div>
        <div class="top-10">
            <loader v-if="loaders.next || loaders.practices || loaders.providers"></loader>
        </div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options" id="patient-list-table">
            <template slot="name" scope="props">
                <div><a :href="rootUrl('manage-patients/' + props.row.id + '/view-careplan')">{{props.row.name}}</a></div>
            </template>
            <template slot="provider" scope="props">
                <div>{{ props.row.provider_name }}</div>
            </template>
            <template slot="careplanStatus" scope="props">
                <a :href="props.row.careplanStatus === 'qa_approved' ? rootUrl('manage-patients/' + props.row.id + '/view-careplan') : null">
                    {{ (({ qa_approved: 'Approve Now', to_enroll: 'To Enroll', provider_approved: 'Provider Approved', none: 'None', draft: 'Draft' })[props.row.careplanStatus] || props.row.careplanStatus) }}
                </a>
            </template>
            <template slot="filter__ccm">
                <div>(HH:MM:SS)</div>
            </template>
            <template slot="h__ccmStatus" slot-scope="props">
                CCM Status
            </template>
            <template slot="h__careplanStatus" slot-scope="props">
                Careplan Status
            </template>
            <template slot="h__dob" slot-scope="props">
                Date of Birth
            </template>
            <template slot="h__registeredOn" slot-scope="props">
                Registered On
            </template>
            <template slot="h__lastReading" slot-scope="props">
                Last Reading
            </template>
            <template slot="h__ccm" slot-scope="props">
                CCM
            </template>
        </v-client-table>
        <div class="row">
            <div class="col-sm-8">
                <input type="button" class="btn btn-success" 
                            :value="'Show by ' + (nameDisplayType ? 'First' : 'Last') + ' Name'" @click="changeNameDisplayType" >
                <span class="pad-10"></span>

                <a class="btn btn-success" :class="{ disabled: loaders.pdf }" @click="exportPdf"
                    :href="rootUrl('manage-patients/listing/pdf')" download="patient-list.pdf">Export as PDF</a>
                <span class="pad-10"></span>

                <input type="button" class="btn btn-success" :class="{ disabled: loaders.excel }"
                            value="Export as Excel" @click="exportExcel" >
                <span class="pad-10"></span>

                <input type="button" class="btn btn-success" 
                            :value="(columns.includes('program') ? 'Hide' : 'Show') + ' Program'" @click="toggleProgramColumn" >
            </div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config.js'
    import { Event } from 'vue-tables-2'
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
        data() {
            return {
                pagination: null,
                tableData: [],
                practices: [],
                providersForSelect: [],
                nameDisplayType: NameDisplayType.FirstName,
                columns: ['name', 'provider', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading', 'ccm'],
                loaders: {
                    next: false,
                    practices: null,
                    providers: false,
                    excel: false,
                    pdf: false
                },
                requests: {
                    next: null
                }
            }
        },
        computed: {
            options() {
                return {
                    filterByColumn: true,
                    sortable: ['name', 'provider', 'program', 'ccmStatus', 'careplanStatus', 'dob', 'age', 'registeredOn', 'ccm'],
                    filterable: ['name', 'provider', 'program', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading'],
                    listColumns: {
                        provider: this.providersForSelect,
                        ccmStatus: [ 
                                        { id: 'enrolled', text: 'enrolled' }, 
                                        { id: 'paused', text: 'paused' }, 
                                        { id: 'withdrawn', text: 'withdrawn' } 
                                    ],
                        careplanStatus: [
                                            { id: '', text: 'none' },
                                            { id: 'qa_approved', text: 'qa_approved' }, 
                                            { id: 'provider_approved', text: 'provider_approved' }, 
                                            { id: 'to_enroll', text: 'to_enroll' },
                                            { id: 'draft', text: 'draft' }
                                        ],
                        program: this.practices.map(practice => ({ id: practice.display_name, text: practice.display_name })).sort((p1, p2) => p1.id > p2.id ? 1 : -1).distinct(practice => practice.id)
                    },
                    texts: {
                        count: `Showing {from} to {to} of ${((this.pagination || {}).total || 0)} records|${((this.pagination || {}).total || 0)} records|One record`
                    },
                    perPage: this.isFilterActive() ? this.tableData.length : 10,
                    customSorting: {
                        name: (ascending) => iSort,
                        provider: (ascending) => iSort,
                        ccmStatus: (ascending) => iSort,
                        careplanStatus: (ascending) => iSort,
                        dob: (ascending) => iSort,
                        phone: (ascending) => iSort,
                        age: (ascending) => iSort,
                        registeredOn: (ascending) => iSort,
                        lastReading: (ascending) => iSort,
                        ccm: (ascending) => iSort,
                        program: (ascending) => iSort
                    }
                }
            }
        },
        methods: {
            rootUrl,
            isFilterActive () {
                return this.$refs.tblPatientList ? !!Object.values(this.$refs.tblPatientList.query).reduce((a, b) => a || b) : false
            },
            columnMapping (name) {
                const columns = {
                    program: 'practice'
                }
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
            },
            nextPageUrl () {
                const $table = this.$refs.tblPatientList
                const query = $table.$data.query
                
                const filters = Object.keys(query).map(key => ({ key, value: query[key] })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${encodeURIComponent(item.value)}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''
                if (this.pagination) {
                    return rootUrl(`api/patients?page=${this.$refs.tblPatientList.page}&rows=${this.isFilterActive() ? 'all' : this.$refs.tblPatientList.limit}${filters}${sortColumn}`)
                }
                else {
                    return rootUrl(`api/patients?rows=${this.isFilterActive() ? 'all' : this.$refs.tblPatientList.limit}${filters}${sortColumn}`)
                }
            },
            toggleProgramColumn () {
                if (this.columns.indexOf('program') >= 0) {
                    this.columns.splice(this.columns.indexOf('program'), 1)
                }
                else {
                    this.columns.splice(2, 0, 'program')
                }
            },
            activateFilters () {
                this.pagination = null
                this.tableData = []
                this.$refs.tblPatientList.setPage(1)
                this.getPatients()
                this.nameDisplayType = NameDisplayType.FirstName
            },
            changeNameDisplayType () {
                if (this.nameDisplayType !== NameDisplayType.FirstName) {
                    this.tableData.forEach(patient => {
                        if (patient.lastName && patient.firstName) patient.name = patient.firstName + ' ' + patient.lastName
                    })
                }
                else {
                    this.tableData.forEach(patient => {
                        if (patient.lastName && patient.firstName) patient.name = patient.lastName + ', ' + patient.firstName
                    })
                }
                this.nameDisplayType = Number(!this.nameDisplayType)
            },
            getPractices() {
                return this.loaders.practices = this.axios.get(rootUrl('api/practices')).then(response => {
                    console.log('patient-list:practices', response.data)
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
                    console.log('patient-list:providers', response.data)
                    this.providersForSelect = (response.data || []).map(provider => ({ id: provider.id, text: provider.name })).filter(provider => !!provider.text).sort((a, b) => a.text < b.text ? -1 : 1)
                    this.loaders.providers = false
                    return this.providersForSelect
                }).catch(err => {
                    console.error('patient-list:providers', err)
                    this.loaders.providers = false
                })
            },
            getPatients () {
                const self = this
                this.loaders.next = true
                return this.requests.next = this.axios.get(this.nextPageUrl(), {
                    before(request) {
                        if (this.requests.next) {
                            this.requests.next.abort()
                        }
                        this.requests.next = request
                    }
                }).then(response => {
                    console.log('patient-list', response.data)
                    const pagination = response.data
                    const ids = this.tableData.map(patient => patient.id)
                    this.pagination = {
                        current_page: pagination.current_page,
                        from: pagination.from,
                        last_page: pagination.last_page,
                        last_page_url: pagination.last_page_url,
                        next_page_url: pagination.next_page_url,
                        path: pagination.path,
                        per_page: pagination.per_page,
                        to: pagination.to,
                        total: pagination.total
                    }
                    const patients = (pagination.data || []).map(patient => {
                        if (((patient.careplan || {}).status || '').startsWith('{')) {
                            (patient.careplan || {}).status = JSON.parse((patient.careplan || {}).status).status
                        }
                        if (patient.patient_info) {
                            if (patient.patient_info.created_at) patient.patient_info.created_at = patient.patient_info.created_at.split('T')[0]
                            // patient.patient_info.age = (patient.patient_info.birth_date && (patient.patient_info.birth_date != '0000-00-00')) ? ((new Date()).getFullYear() - (new Date(patient.patient_info.birth_date)).getFullYear()) : (new Date()).getFullYear()
                            
                            const pad = (num, count = 2) => '0'.repeat(count - num.toString().length) + num
                            const seconds = patient.patient_info.cur_month_activity_time || 0
                            patient.patient_info.ccm = seconds
                            patient.patient_info.cur_month_activity_time = pad(Math.floor(seconds / 3600), 2) + ':' + pad(Math.floor(seconds / 60) % 60, 2) + ':' + pad(seconds % 60, 2);
                        }
                        return patient
                    }).map(patient => {
                        patient.name = (patient.name || '').trim()
                        patient.firstName = patient.name.split(' ')[0]
                        patient.lastName = patient.name.split(' ').slice(1).join(' ')
                        patient.provider = patient.billing_provider_id
                        patient.provider_name = patient.billing_provider_name
                        patient.ccmStatus = (patient.patient_info || {}).ccm_status || 'none'
                        patient.careplanStatus = (patient.careplan || {}).status || 'none'
                        patient.dob = (patient.patient_info || {}).birth_date || ''
                        patient.sort_dob = new Date((patient.patient_info || {}).birth_date || '')
                        patient.program = (this.practices.find(practice => practice.id == patient.program_id) || {}).display_name || ''
                        patient.age = (patient.patient_info || {}).age || ''
                        patient.registeredOn = moment(patient.created_at || '').format('YYYY-MM-DD')
                        patient.sort_registeredOn = new Date(patient.created_at)
                        patient.lastReading = (patient.last_read || '').split(' ')[0] || 'No Readings'
                        patient.ccm = (patient.patient_info || {}).cur_month_activity_time || 0
                        patient.sort_ccm = (patient.patient_info || {}).ccm
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
                        loadColumnList(this.options.listColumns.ccmStatus, patient.ccmStatus)
                        loadColumnList(this.options.listColumns.careplanStatus, patient.careplanStatus)
                        loadColumnList(this.options.listColumns.program, patient.program)
                        return patient
                    })

                    if (!this.tableData.length) {
                        const arr = patients.map((patient, i) => Object.assign({}, patient, { i: (i + 1) }))
                        const total = ((this.pagination || {}).total || 0)
                        this.tableData = [ ...arr, ...'0'.repeat(total - arr.length).split('').map((item, index) => ({ i: arr.length + index + 1, id: arr.length + index })) ]
                    }
                    else {
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
                                }
                                else return row
                            }
                            else {
                                return row
                            }
                        })
                    }
                    
                    this.loaders.next = false
                    this.requests.next = null
                }).catch(err => {
                    console.error('patient-list', err)
                    this.loaders.next = false
                    this.requests.next = null
                })
            },
            exportExcel () {
                if (!this.loaders.excel) {
                    this.loaders.excel = true
                    const link = document.createElement('a')
                    link.href = rootUrl('api/patients?excel')
                    link.download = `patient-list-${Date.now()}.xlsx`
                    link.click()
                }
            },
            exportPdf () {
                if (!this.loaders.pdf) {
                    this.loaders.pdf = true
                }
            },
            createHumanReadableFilterNames () {
                /**
                 * make sure the filter input placeholders have human readable text like "CCM Status" instead of "ccmStatus"
                 */
                const patientListElem = this.$refs.tblPatientList.$el

                const ccmStatusSelect = patientListElem.querySelector('select[name="vf__ccmStatus"]')
                ccmStatusSelect.querySelector('option').innerText = 'Select CCM Status'

                const careplanStatusSelect = patientListElem.querySelector('select[name="vf__careplanStatus"]');

                ([ ...(careplanStatusSelect.querySelectorAll('option') || []) ]).forEach(option => {
                    option.innerText = ({
                        qa_approved: 'Approve Now',
                        to_enroll: 'To Enroll',
                        provider_approved: 'Provider Approved',
                        none: 'None',
                        draft: 'Draft',
                        'Select careplanStatus': 'Select Careplan Status'
                    })[option.innerText] || option.innerText
                })

                const dobInput = patientListElem.querySelector('input[name="vf__dob"]')
                dobInput.setAttribute('placeholder', 'Filter by Date of Birth')

                const registeredOnInput = patientListElem.querySelector('input[name="vf__registeredOn"]')
                registeredOnInput.setAttribute('placeholder', 'Filter by Registered On')

                const lastReadingInput = patientListElem.querySelector('input[name="vf__lastReading"]')
                lastReadingInput.setAttribute('placeholder', 'Filter by Last Reading')
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

            Event.$on('vue-tables.filter::program', this.activateFilters)

            Event.$on('vue-tables.filter::ccmStatus', this.activateFilters)

            Event.$on('vue-tables.filter::careplanStatus', this.activateFilters)

            Event.$on('vue-tables.filter::dob', this.activateFilters)

            Event.$on('vue-tables.filter::phone', this.activateFilters)

            Event.$on('vue-tables.filter::age', this.activateFilters)

            Event.$on('vue-tables.filter::registeredOn', this.activateFilters)

            Event.$on('vue-tables.filter::lastReading', this.activateFilters)
    
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

</style>

<style scoped>
    a:hover {
        text-decoration: none;
    }

    a[href]:hover {
        text-decoration: underline;
    }
</style>
