<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    <a :href="url">Appointments</a>
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <slot v-if="appointments.length === 0">
            <div class="col-xs-12 text-center">
                No Appointments at this time
            </div>
        </slot>
        <div class="row gutter" v-if="appointments.length > 0">
            <div class="col-xs-12">
                <h3>Future</h3>
                <ul v-if="appointments.length">
                    <li class="top-20" v-for="(appointment, index) in futureAppointments" :key="index">
                        <p>- {{appointment.type}}, <strong>{{appointment.provider().specialty}}</strong> on {{appointment.date}} at {{appointment.type}} with 
                            {{appointment.provider().display_name}}; A: {{appointment.provider().location.name}} P: {{appointment.provider().location.phone}}
                        </p>
                    </li>
                </ul>
            </div>
            <div class="col-xs-12">
                <h3>Past</h3>
                <ul v-if="appointments.length">
                    <p>- {{appointment.type}}, <strong>{{appointment.provider().specialty}}</strong> on {{appointment.date}} at {{appointment.type}} with 
                        {{appointment.provider().display_name}}; A: {{appointment.provider().location.name}} P: {{appointment.provider().location.phone}}
                    </p>
                </ul>
            </div>
        </div>
        <!-- <appointments-modal ref="appointmentsModal" :patient-id="patientId" :appointments="appointments"></appointments-modal> -->
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    //import AppointmentsModal from './modals/appointments.modal'

    export default {
        name: 'appointments',
        props: [
            'patient-id',
            'url'
        ],
        components: {
            //'appointments-modal': AppointmentsModal
        },
        data() {
            return {
                 appointments: [],
                 groups: []
            }
        },
        computed: {
            pastAppointments() {
                return this.appointments.filter(appointment => appointment.at <= new Date())
            },
            futureAppointments() {
                return this.appointments.filter(appointment => appointment.at > new Date())
            }
        },
        methods: {
            setupAppointment(appointment) {
                appointment.at = new Date(appointment.date + ' ' + appointment.time)
                appointment.created_at = new Date(appointment.created_at)
                appointment.updated_at = new Date(appointment.updated_at)
                return appointment
            },
            getAppointments(page) {
                if (!page) {
                    this.appointments = []
                    page = 1
                }
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/appointments?page=${page}`)).then(response => {
                    const pagination = response.data
                    console.log('appointments:get-appointments', pagination)
                    this.appointments = this.appointments.concat(pagination.data.map(this.setupAppointment))
                    if (pagination.to < pagination.total) return this.getAppointments(page + 1)
                }).catch(err => {
                    console.error('appointments:get-appointments', err)
                })
            },
            showModal() {
                Event.$emit('modal-appointments:show')
            }
        },
        mounted() {
            this.getAppointments()
        }
    }
</script>

<style>
    li.list-square {
        list-style-type: square;
    }

    .font-18 {
        font-size: 18px;
    }
</style>