<template>
    <modal name="appointments" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-appointments">
        <template scope="props" slot="title">
            <h3>Appointments</h3>
        </template>
        <template scope="props">
            <div class="row">
                <div class="col-sm-12" v-if="!pagination.index">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>
                                Create Appointment
                                <loader v-if="loaders.getProviders"></loader>
                            </h4>
                        </div>
                        <div class="col-sm-12 top-20">
                            <v-select class="form-control" v-model="newAppointment.provider" :options="providers" required></v-select>
                        </div>
                        <div class="col-sm-4 top-20">
                            <input type="date" class="form-control" v-model="newAppointment.date" :min="newAppointment.date" required />
                        </div>
                        <div class="col-sm-4 top-20">
                            <input type="time" class="form-control" v-model="newAppointment.time" required />
                        </div>
                        <div class="col-sm-4 top-20">
                            <input type="text" class="form-control" v-model="newAppointment.type" placeholder="Reason" required />
                        </div>
                        <div class="col-sm-12 top-20">
                            <textarea class="form-control" v-model="newAppointment.comment" placeholder="Comment" required></textarea>
                        </div>
                        <div class="col-sm-12 top-20 text-right">
                            <input type="button" class="btn btn-secondary selected" value="Add" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" v-if="pagination.index && futureAppointments.length > 0">
                    <h4>Upcoming Appointments</h4>
                    <ol class="list-group" v-for="(appointment, index) in futureAppointments" :key="index">
                        <li class="list-group-item pointer" @click="select(appointment)"
                        :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
                            <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeAppointment(index)" />
                        </li>
                    </ol>
                </div>
                <div class="col-sm-12" v-if="pagination.index && pastAppointments.length > 0">
                    <h4>Past Appointments</h4>
                    <ol class="list-group" v-for="(appointment, index) in pastAppointments" :key="index">
                        <li class="list-group-item pointer" @click="select(appointment)"
                        :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
                            <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeAppointment(index)" />
                        </li>
                    </ol>
                </div>
                <div class="col-sm-12" :class="{ 'appointment-container': pagination.pages().length > 20 }">
                    <div class="btn-group" :class="{ 'appointment-buttons': pagination.pages() > 20 }" role="group" aria-label="Appointments">
                        <button class="btn btn-secondary appointment-button" :class="{ selected: pagination.selected(index) }" 
                                v-for="(page, index) in pagination.pages()" :key="index" @click="pagination.select(index)">
                            {{page}}
                        </button>
                    </div>
                    <input type="button" class="btn btn-secondary" :class="{ selected: !pagination.index }" value="+" @click="pagination.select(-1)" />
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
    import VueSelect from 'vue-select'
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
            'v-select': VueSelect
        },
        computed: {
            pastAppointments() {
                return this.paginatedAppointments.filter(appointment => appointment.at <= new Date())
            },
            futureAppointments() {
                return this.paginatedAppointments.filter(appointment => appointment.at > new Date())
            },
            paginatedAppointments() {
                return this.appointments.slice(this.pagination.start(), this.pagination.start() + this.pagination.limit)
            }
        },
        data() {
            return {
                newAppointment: {
                    provider: null,
                    date: moment(new Date()).format('YYYY-MM-DD'),
                    time: moment(new Date()).format('HH:mm:ss'),
                    type: null,
                    comment: null
                },
                selectedAppointment: null,
                loaders: {
                    addAppointment: null,
                    removeAppointment: null,
                    getProviders: null
                },
                providers: [{ label: 'Select a Provider', value: null }],
                pagination: {
                    index: 1,
                    limit: 5,
                    total: () => this.appointments.length,
                    start: () => (this.pagination.index - 1) * this.pagination.limit,
                    select: (index) => {
                        this.pagination.index = index + 1
                    },
                    pages: () => {
                        return '0'.repeat(Math.ceil(this.pagination.total() / this.pagination.limit)).split('').map((a, i) => i + 1)
                    },
                    selected: (index) => (this.pagination.index === (index + 1))
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
                this.loaders.getProviders = true
                this.newAppointment.provider = this.providers[0]
                this.axios.get(rootUrl(`api/providers/list`)).then(response => {
                    this.providers = this.providers.concat(response.data.map(provider => ({ label: (provider.name || '').trim(), value: provider.id })).sort((a, b) => a.label > b.label ? 1 : -1))
                    console.log('appointments-modal:get-providers', this.providers)
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

    .modal-appointments .dropdown-toggle.clearfix {
        border: none !important;
    }

    .modal-appointments .dropdown.v-select.form-control {
        padding: 0;
    }
</style>