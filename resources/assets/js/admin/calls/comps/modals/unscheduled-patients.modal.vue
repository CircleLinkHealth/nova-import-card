<template>
    <modal name="unscheduled-patients" :no-footer="true" :info="unscheduledPatientsModalInfo">
      <template scope="props" slot="title">
        <div class="row">
            <div :class="{ 'col-sm-12': !loaders.patients, 'col-sm-11': loaders.patients }">
                <select class="form-control" v-model="practiceId" @change="getPatients()">
                    <option :value="null">Select Practice</option>
                    <option v-for="(practice, index) in practices" :key="practice.id" :value="practice.id">{{practice.display_name}}</option>
                </select>
                <loader v-if="loaders.patients"></loader>
            </div>
        </div>
      </template>
      <template scope="props">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-center" v-if="!patients.length">
                    Looks like every patient has scheduled calls this month
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <v-client-table ref="unscheduledPatients" :data="patients" :columns="columns" :options="options">
                            <template slot="name" scope="props">
                                <a class="pointer" @click="triggerParentFilter(props.row.id, props.row.name)">{{props.row.name}}</a>
                            </template>
                        </v-client-table>
                    </div>
                </div>
            </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../components/loader'
    import {rootUrl} from '../../../../app.config'
    import {Event} from 'vue-tables-2'

    export default {
        name: 'unscheduled-patients-modal',
        components: {
            'modal': Modal,
            'loader': LoaderComponent
        },
        data() {
            return {
                unscheduledPatientsModalInfo: {

                },
                /**loaders and errors */
                errors: {
                    practices: null,
                    patients: null
                },
                loaders: {
                    practices: false,
                    patients: false
                },
                practices: [],
                patients: [],
                practiceId: null,
                columns: ['id', 'name', 'city', 'state'],
                options: {
                    filterable: false
                }
            }
        },
        methods: {
            getPractices() {
                this.loaders.practices = true
                this.axios.get(rootUrl(`api/practices`)).then(response => {
                    this.loaders.practices = false
                    this.practices = (response.data || []).distinct(practice => practice.id)
                    console.log('unscheduled-patients-get-practices', response.data)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('unscheduled-patients-get-practices', err)
                })
            },
            getPatients() {
                if (this.practiceId) {
                    this.loaders.patients = true
                    this.axios.get(rootUrl(`api/practices/${this.practiceId}/patients/without-inbound-calls`)).then(response => {
                        this.loaders.patients = false
                        this.patients = (response.data.data || []).map(patient => {
                            patient.name = patient.full_name
                            return patient;
                        })
                        console.log('unscheduled-patients-get-patients', response.data.data)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('unscheduled-patients-get-patients', err)
                    })
                }
            },
            triggerParentFilter(id, name) {
                Event.$emit('unscheduled-patients-modal:filter', {
                    practiceId: this.practiceId,
                    patientId: id,
                    patientName: name
                })
            }
        },
        mounted() {
            this.getPractices()
        }
    }
</script>

<style>
    div.loader {
        position: absolute;
        right: -25px;
        top: 2px;
    }

    .pointer {
        cursor: pointer;
    }
</style>