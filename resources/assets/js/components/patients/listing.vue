<template>
    <div>
        <div>
            <loader v-if="loaders.next || loaders.practices"></loader>
        </div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options">
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
                <input type="button" class="btn btn-success" 
                    :value="(columns.includes('program') ? 'Hide' : 'Show') + ' Program'" @click="toggleProgramColumn" >
            </div>
            <div class="col-sm-3"></div>
            <div class="col-sm-3"></div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config.js'
    import { Event } from 'vue-tables-2'
    import moment from 'moment'
    import loader from '../loader'

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
                page: 1,
                tableData: [],
                practices: [],
                nameDisplayType: NameDisplayType.FirstName,
                columns: ['name', 'provider', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading', 'ccm'],
                options: {
                    filterByColumn: true,
                    filterable: ['name', 'provider', 'program', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading'],
                    listColumns: {
                        provider: [],
                        ccmStatus: [],
                        careplanStatus: [],
                        program: []
                    }
                },
                loaders: {
                    next: null,
                    practices: null
                }
            }
        },
        methods: {
            toggleProgramColumn () {
                if (this.columns.indexOf('program') >= 0) {
                    this.columns.splice(this.columns.indexOf('program'), 1)
                }
                else {
                    this.columns.splice(2, 0, 'program')
                }
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
            getPatients () {
                if (!this.loaders.next) {
                    const self = this
                    this.loaders.next = this.axios.get(rootUrl(`api/patients?page=${this.page}`)).then(response => {
                        console.log('patient-list', response.data)
                        const pagination = response.data
                        const ids = this.tableData.map(patient => patient.id)
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
                            patient.registeredOn = (patient.patient_info || {}).created_at || ''
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
                        }).filter(patient => (ids.indexOf(patient.id) < 0))
                        this.tableData = this.tableData.concat(patients)
                        this.page++
                        this.loaders.next = null
                    }).catch(err => {
                        console.error('patient-list', err)
                        this.loaders.next = null
                    })
                }
            }
        },
        mounted() {
            this.getPractices().then(() => {
                this.getPatients()
            })
            const $table = this.$refs.tblPatientList
            Event.$on('vue-tables.pagination', (page) => {
                if (page === $table.totalPages) {
                    console.log('next table data')
                    this.getPatients();
                }
            })
        }
    }
</script>

<style>
    
</style>