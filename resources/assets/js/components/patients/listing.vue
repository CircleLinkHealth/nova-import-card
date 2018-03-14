<template>
    <div>
        <div>
            <loader v-if="loaders.next || loaders.practices"></loader>
        </div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options" id="patient-list-table">
            <template slot="name" scope="props">
                <div><a :href="rootUrl('manage-patients/' + props.row.id + '/summary')" target="_blank">{{props.row.name}}</a></div>
            </template>
            <template slot="filter__ccm">
                <div>(HH:MM:SS)</div>
            </template>
        </v-client-table>
        <div class="row text-center">
            <div class="col-sm-3">
                <input type="button" class="btn btn-success" 
                    :value="'Show by ' + (nameDisplayType ? 'Last' : 'First') + ' Name'" @click="changeNameDisplayType" >
            </div>
            <div class="col-sm-3">
                <a class="btn btn-success" :href="rootUrl('manage-patients/listing/pdf')" download="patient-list.pdf">Export as PDF</a>
            </div>
            <div class="col-sm-3">
                <input type="button" class="btn btn-success" 
                    value="Export as Excel" @click="exportExcel" >
            </div>
            <div class="col-sm-3">
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
                nameDisplayType: NameDisplayType.FirstName,
                columns: ['name', 'provider', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading', 'ccm'],
                loaders: {
                    next: null,
                    practices: null,
                    providers: null
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
                        provider: [],
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
                                            { id: 'patient_withdrawn', text: 'patient_withdrawn' }
                                        ],
                        program: this.practices.map(practice => ({ id: practice.display_name, text: practice.display_name }))
                    },
                    texts: {
                        count: `Showing {from} to {to} of ${((this.pagination || {}).total || 0)} records|${((this.pagination || {}).total || 0)} records|One record`
                    },
                    customSorting: {
                        name: (ascending) => (a, b) => 0,
                        provider: (ascending) => (a, b) => 0,
                        ccmStatus: (ascending) => (a, b) => 0,
                        careplanStatus: (ascending) => (a, b) => 0,
                        dob: (ascending) => (a, b) => 0,
                        phone: (ascending) => (a, b) => 0,
                        age: (ascending) => (a, b) => 0,
                        registeredOn: (ascending) => (a, b) => 0,
                        lastReading: (ascending) => (a, b) => 0,
                        ccm: (ascending) => (a, b) => 0,
                        program: (ascending) => (a, b) => 0
                    }
                }
            }
        },
        methods: {
            rootUrl,
            columnMapping (name) {
                const columns = {
                    program: 'practice'
                }
                return columns[name] ? columns[name] : (name || '').replace(/(?:^\w|[A-Z]|\b\w)/g, (letter, index) => (index == 0 ? letter.toLowerCase() : letter.toUpperCase())).replace(/\s+/g, '')
            },
            nextPageUrl () {
                const $table = this.$refs.tblPatientList
                const query = $table.$data.query
                
                const filters = Object.keys(query).map(key => ({ key, value: query[key] })).filter(item => item.value).map((item) => `&${this.columnMapping(item.key)}=${item.value}`).join('')
                const sortColumn = $table.orderBy.column ? `&sort_${this.columnMapping($table.orderBy.column)}=${$table.orderBy.ascending ? 'asc' : 'desc'}` : ''
                if (this.pagination) {
                    return rootUrl(`api/patients?page=${this.$refs.tblPatientList.page}&rows=${this.$refs.tblPatientList.limit}${filters}${sortColumn}`)
                }
                else {
                    return rootUrl(`api/patients?rows=${this.$refs.tblPatientList.limit}${filters}${sortColumn}`)
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
            },
            changeNameDisplayType () {
                if (this.nameDisplayType != NameDisplayType.FirstName) {
                    this.tableData.forEach(patient => {
                        patient.name = patient.firstName + ' ' + patient.lastName
                    })
                }
                else {
                    this.tableData.forEach(patient => {
                        patient.name = patient.lastName + ' ' + patient.firstName
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
                return this.loaders.providers = this.axios.get(rootUrl('api/providers')).then(response => {
                    console.log('patient-list:providers', response.data)
                    this.providers = response.data
                    this.loaders.providers = null
                    return this.providers
                }).catch(err => {
                    console.error('patient-list:providers', err)
                    this.loaders.providers = null
                })
            },
            getPatients () {
                if (!this.loaders.next) {
                    const self = this
                    this.loaders.next = this.axios.get(this.nextPageUrl()).then(response => {
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
                                patient.patient_info.age = Math.floor((new Date() - new Date(patient.patient_info.birth_date)) / (1000 * 60 * 60 * 24 * 365))
                                
                                const pad = (num, count = 2) => '0'.repeat(count - num.toString().length) + num
                                const seconds = patient.patient_info.cur_month_activity_time || 0
                                patient.patient_info.cur_month_activity_time = pad(Math.floor(seconds / 3600), 2) + ':' + pad(Math.floor(seconds / 60) % 60, 2) + ':' + pad(seconds % 60, 2);
                            }
                            return patient
                        }).map(patient => {
                            patient.name = (patient.name || '').trim()
                            patient.firstName = patient.name.split(' ')[0]
                            patient.lastName = patient.name.split(' ').slice(1).join(' ')
                            patient.provider = patient.billing_provider_name
                            patient.ccmStatus = (patient.patient_info || {}).ccm_status || ''
                            patient.careplanStatus = (patient.careplan || {}).status || ''
                            patient.dob = (patient.patient_info || {}).birth_date || ''
                            patient.program = (this.practices.find(practice => practice.id == patient.program_id) || {}).display_name || ''
                            patient.age = patient.patient_info.age || ''
                            patient.registeredOn = moment(patient.created_at || '').format('YYYY-MM-DD')
                            patient.lastReading = (patient.last_read || '').split(' ')[0] || 'No Readings'
                            patient.ccm = (patient.patient_info || {}).cur_month_activity_time || 0
                            return patient
                        }).map(patient => {
                            const loadColumnList = (list = [], item = null) => {
                                if ((item || '').trim() && !list.find(orb => orb.text == item)) {
                                    list.push({
                                        id: item,
                                        text: item
                                    })
                                }
                            }
                            loadColumnList(this.options.listColumns.provider, patient.provider)
                            loadColumnList(this.options.listColumns.ccmStatus, patient.ccmStatus)
                            loadColumnList(this.options.listColumns.careplanStatus, patient.careplanStatus)
                            loadColumnList(this.options.listColumns.program, patient.program)
                            return patient
                        })

                        if (!this.tableData.length) {
                            const arr = this.tableData.concat(patients)
                            const total = ((this.pagination || {}).total || 0)
                            this.tableData = [ ...arr, ...'0'.repeat(total - arr.length).split('').map((item, index) => ({ id: arr.length + index + 1 })) ]
                        }
                        else {
                            const from = ((this.pagination || {}).from || 0)
                            const to = ((this.pagination || {}).to || 0)
                            console.log(patients)
                            for (let i = from - 1; i < to; i++) {
                                this.tableData[i] = patients[i - from + 1]
                            }
                        }
                        
                        this.loaders.next = null
                    }).catch(err => {
                        console.error('patient-list', err)
                        this.loaders.next = null
                    })
                }
            },
            exportExcel () {
                const link = document.createElement('a')
                link.href = rootUrl('api/patients?excel')
                link.download = `patient-list-${Date.now()}.xlsx`
                link.click()
            }
        },
        mounted() {
            /**
             * load practices, then patients
             */
            this.getPractices().then(() => {
                this.getPatients()
            })

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
        }
    }
</script>

<style scoped>
</style>