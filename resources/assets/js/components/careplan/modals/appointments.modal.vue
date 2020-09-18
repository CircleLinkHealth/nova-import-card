<template>
    <modal name="appointments" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-appointments">
        <template slot-scope="props" slot="title">
            <h3>Appointments</h3>
        </template>
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12" v-if="!pagination.index">
                    <form @submit="addAppointment">
                        <div class="row">
                            <div class="col-sm-12">
                                <h4>
                                    Create Appointment
                                    <loader v-if="loaders.getProviders"></loader>
                                </h4>
                            </div>
                            <div class="col-sm-12 top-20">
                                <v-select class="form-control" v-model="newAppointment.provider" :options="providers"></v-select>
                            </div>
                            <div class="col-sm-8 top-20">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <datepicker input-class="form-control" class="form-control pad-0" :class="{ error: !newAppointment.isPending() }" format="MM-dd-yyyy"
                                                    v-model="newAppointment.date" :disabledDates="{ to: today }" placeholder="MM-DD-YYYY" required></datepicker>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="time" class="form-control" :class="{ error: !newAppointment.isPending() }" v-model="newAppointment.time" required />
                                    </div>
                                    <div class="col-sm-12" v-if="!newAppointment.isPending()">
                                        <h5 class="alert alert-danger">Please, select a future date/time</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 top-20">
                                <input type="text" class="form-control" v-model="newAppointment.type" placeholder="Reason" required />
                            </div>
                            <div class="col-sm-12 top-20">
                                <textarea class="form-control" v-model="newAppointment.comment" placeholder="Comment" required></textarea>
                            </div>
                            <div class="col-sm-12 top-20 text-right">
                                <loader v-if="loaders.addAppointment"></loader>
                                <button class="btn btn-secondary selected" :disabled="!newAppointment.isPending() || ((newAppointment.provider || {}).value == -1)">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12" v-if="pagination.index && futureAppointments.length > 0">
                    <h4>Upcoming Appointments</h4>
                    <ol class="list-group" v-for="(appointment, index) in futureAppointments" :key="appointment.id">
                        <li class="list-group-item pointer" @click="select(appointment)"
                            :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
                            <loader v-if="loaders.removeAppointment"></loader>
                            <input type="button" class="btn btn-danger absolute delete" value="x" @click="removeAppointment(index)" />
                        </li>
                    </ol>
                </div>
                <div class="col-sm-12" v-if="pagination.index && pastAppointments.length > 0">
                    <h4>Past Appointments</h4>
                    <ol class="list-group" v-for="(appointment, index) in pastAppointments" :key="appointment.id">
                        <li class="list-group-item pointer" @click="select(appointment)"
                            :class="{ selected: selectedAppointment && selectedAppointment.id === appointment.id, disabled: (selectedAppointment && selectedAppointment.id === appointment.id)  && loaders.removeAppointment }">
                            <appointment :appointment="appointment"></appointment>
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
    import { rootUrl } from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'
    import moment from 'moment'
    import VueSelect from 'vue-select'
    import AppointmentRender from '../renders/appointment.render'
    import Datepicker from 'vuejs-datepicker'

    export default {
        name: 'appointments-modal',
        props: {
            'patient-id': String,
            appointments: Array
        },
        components: {
            'appointment': AppointmentRender,
            'modal': Modal,
            'v-select': VueSelect,
            'datepicker': Datepicker
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
            },
            newAppointmentDate() {
                const d = this.newAppointment.date
                if (!d) return ''
                else return moment(d).format('MM-DD-YYYY')
            }
        },
        data() {
            return {
                newAppointment: {
                    provider: null,
                    min: moment(new Date()).format('MM-DD-YYYY'),
                    date: moment().add(1, 'days').format('MM-DD-YYYY'),
                    time: '09:00:00',
                    type: null,
                    comment: null,
                    isPending: () => (moment(this.newAppointmentDate + ' ' + this.newAppointment.time).toDate() > new Date())
                },
                today: moment().toDate(),
                selectedAppointment: null,
                loaders: {
                    addAppointment: null,
                    removeAppointment: null,
                    getProviders: null
                },
                providers: [{ label: 'Select Physician', value: -1 }, { label: 'Unknown', value: null }],
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
            reset() {
                this.newAppointment = {
                    provider: null,
                    date: moment().add(1, 'days').format('MM-DD-YYYY'),
                    time: '09:00:00',
                    type: null,
                    comment: null,
                    isPending: () => (moment(this.newAppointmentDate + ' ' + this.newAppointment.time).toDate() > new Date())
                }
            },
            removeAppointment(index) {
                if (this.selectedAppointment && this.selectedAppointment.isPending() && confirm('Are you sure you want to remove this appointment?')) {
                    this.loaders.removeAppointment = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/appointments/${this.selectedAppointment.id}`)).then(response => {
                        Event.$emit('appointments:remove', this.selectedAppointment.id)
                        this.loaders.removeAppointment = false
                    }).catch(err => {
                        console.error('appointments-modal:remove', err)
                        this.loaders.removeAppointment = false
                    })
                }
            },
            addAppointment(e) {
                e.preventDefault()
                this.newAppointment.provider_id = (this.newAppointment.provider || {}).value
                this.newAppointment.date = this.newAppointmentDate
                this.loaders.addAppointment = true
                return this.axios.post(rootUrl(`api/patients/${this.patientId}/appointments`), this.newAppointment).then(response => {
                    Event.$emit('appointments:add', response.data)
                    this.loaders.addAppointment = false
                    this.reset()
                    this.pagination.select(0)
                    this.select(this.appointments[0])
                }).catch(err => {
                    console.error('appointments-modal:add', err)
                    this.loaders.addAppointment = false
                })
            },
            select(appointment) {
                this.selectedAppointment = appointment
            },
            getProviders() {
                this.loaders.getProviders = true
                this.newAppointment.provider = this.providers[0]
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/providers`)).then(response => {
                    this.providers = this.providers.concat(response.data.map(provider => ({ label: (provider.name || '').trim(), value: provider.id })).sort((a, b) => a.label > b.label ? 1 : -1))
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
    @media only screen and (max-width: 768px) {
        /* For mobile phones: */
        .modal-appointments .modal-container {
            width: 95%;
        }
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

    .pad-0 {
        padding: 0px;
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

    .vdp-datepicker.form-control.error div {
        border: 1px solid red;
    }

    .vdp-datepicker input[type="text"] {
        border: none;
        border-radius: 5px;
        line-height: 29px;
        padding-left: 12px;
    }
</style>