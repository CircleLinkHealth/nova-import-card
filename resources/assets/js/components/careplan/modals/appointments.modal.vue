<template>
    <modal name="appointments" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-appointments">
        <template scope="props" slot="title">
            <h3>Appointments</h3>
        </template>
        <template scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>
                                Create Appointment
                                <loader v-if="loaders.getProviders"></loader>
                            </h4>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <select2 class="form-control" v-model="newAppointment.providerId">
                                <option :value="null">Select a Provider</option>
                                <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{provider.user.display_name}}</option>
                            </select2>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <input type="date" class="form-control" v-model="newAppointment.date" />
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <input type="time" class="form-control" v-model="newAppointment.time" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" v-if="futureAppointments.length > 0">
                    <h4>Upcoming</h4>
                    <ol class="list-group" v-for="(appointment, index) in futureAppointments" :key="index">
                        <li class="list-group-item pointer" @click="select(appointment)"
                        :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
                            <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeAppointment(index)" />
                        </li>
                    </ol>
                </div>
                <div class="col-sm-12" v-if="pastAppointments.length > 0">
                    <h4>Past</h4>
                    <ol class="list-group" v-for="(appointment, index) in pastAppointments" :key="index">
                        <li class="list-group-item pointer" @click="select(appointment)"
                        :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
                            <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeAppointment(index)" />
                        </li>
                    </ol>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'
    import moment from 'moment'
    import Select2Component from '../../src/select2'
    import AppointmentRender from '../renders/appointment.render'

    export default {
        name: 'appointments-modal',
        props: {
            'patient-id': String,
            appointments: Array
        },
        components: {
            'appointment': AppointmentRender,
            'modal': Modal,
            'select2': Select2Component
        },
        computed: {
            pastAppointments() {
                return this.appointments.filter(appointment => appointment.at <= new Date())
            },
            futureAppointments() {
                return this.appointments.filter(appointment => appointment.at > new Date())
            }
        },
        data() {
            return {
                newAppointment: {
                    providerId: null,
                    date: moment(new Date()).format('YYYY-MM-DD'),
                    time: moment(new Date()).format('HH:mm:ss')
                },
                selectedAppointment: null,
                loaders: {
                    addAppointment: null,
                    removeAppointment: null,
                    getProviders: null
                },
                providers: [],
                pagination: {
                    index: 0,
                    limit: 5
                }
            }
        },
        methods: {
            removeAppointment(index) {

            },
            addAppointment(e) {

            },
            select(appointment) {
                this.selectedAppointment = appointment
            },
            getProviders() {
                this.pagination.index++
                this.loaders.getProviders = true
                this.axios.get(rootUrl(`api/providers?page=${this.pagination.index}`)).then(response => {
                    const pagination = response.data
                    console.log('appointments-modal:get-providers', pagination)
                    this.providers = this.providers.concat(pagination.data)
                    this.loaders.getProviders = false
                }).catch(err => {
                    console.error('appointments-modal:get-providers', err)
                    this.loaders.getProviders = false
                })
            }
        },
        mounted() {
            this.getProviders()
        }
    }
</script>

<style>
    .modal-appointments .modal-container {
        width: 1000px;
    }

    .appointment-container {
        overflow-x: scroll;
    }

    .appointment-buttons {
        width: 2000px;
    }

    .appointment-button span.delete {
        width: 20px;
        height: 20px;
        font-size: 12px;
        background-color: #FA0;
        color: white;
        padding: 1px 5px;
        border-radius: 50%;
        position: absolute;
        top: -8px;
        right: -10px;
        cursor: pointer;
        display: none;
    }

    .appointment-button.selected span.delete {
        display: inline-block;
    }

    button.appointment-button div.loader.absolute {
        right: -13px;
        top: 15px;
    }

    .pad-top-10 {
        padding-top: 10px;
    }

    input.color-black {
        color: black;
    }
</style>