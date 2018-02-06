<template>
    <div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options">
        </v-client-table>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config.js'
    import { Event } from 'vue-tables-2'
    import moment from 'moment'
    
    export default {
        name: 'PatientList',
        data() {
            return {
                page: 1,
                tableData: [],
                columns: ['name', 'provider', 'ccmStatus', 'careplanStatus', 'dob', 'phone', 'age', 'registeredOn', 'lastReading', 'ccm'],
                options: {
                    filterByColumn: true
                },
                loaders: {
                    next: null
                }
            }
        },
        methods: {
            getPatients () {
                if (!this.loaders.next) {
                    const self = this
                    this.loaders.next = this.axios.get(rootUrl('api/patients')).then(response => {
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
                            patient.name = patient.name
                            patient.provider = patient.billing_provider_name
                            patient.ccmStatus = (patient.patient_info || {}).ccm_status || ''
                            patient.careplanStatus = (patient.careplan || {}).status || ''
                            patient.dob = (patient.patient_info || {}).birth_date || ''
                            patient.age = patient.patient_info.age || ''
                            patient.registeredOn = (patient.patient_info || {}).created_at || ''
                            patient.lastReading = (patient.last_read || '').split(' ')[0] || 'No Readings'
                            patient.ccm = (patient.patient_info || {}).cur_month_activity_time || 0
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
            this.getPatients()
        }
    }
</script>

<style>
    
</style>