<template>
    <div>
        <v-client-table ref="tblPatientList" :data="tableData" :columns="columns" :options="options">
            <template slot="Name" scope="props">
                <div>{{props.row.name}}</div>
            </template>
            <template slot="Provider" scope="props">
                <div>{{(props.row.provider_info || {}).name || ''}}</div>
            </template>
            <template slot="CCM Status" scope="props">
                <div>{{(props.row.patient_info || {}).ccm_status || ''}}</div>
            </template>
            <template slot="CarePlan Status" scope="props">
                <div>{{(props.row.careplan || {}).status || ''}}</div>
            </template>
            <template slot="DOB" scope="props">
                <div>{{(props.row.patient_info || {}).birth_date || ''}}</div>
            </template>
            <template slot="Age" scope="props">
                <div>{{(props.row.patient_info || {}).age || ''}}</div>
            </template>
            <template slot="Registered On" scope="props">
                <div>{{(props.row.patient_info || {}).created_at || ''}}</div>
            </template>
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
                columns: ['Name', 'Provider', 'CCM Status', 'CarePlan Status', 'DOB', 'Phone', 'Age', 'Registered On', 
                            'Last Reading', 'CCM'],
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
                                patient.patient_info.created_at = moment(new Date(patient.patient_info.created_at)).format('YYYY-MM-DD')
                                patient.patient_info.age = Math.floor((new Date() - new Date(patient.patient_info.birth_date)) / (1000 * 60 * 60 * 24 * 365))
                            }
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