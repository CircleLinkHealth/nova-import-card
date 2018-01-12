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
            <div class="col-xs-12" v-if="futureAppointments.length > 0">
                <h3>Upcoming</h3>
                <ul v-if="appointments.length">
                    <li class="top-20" v-for="(appointment, index) in futureAppointments" :key="index">
                        <p>- {{appointment.type}}<span v-if="appointment.provider().specialty">,</span> <strong v-if="appointment.provider().specialty">({{appointment.provider().specialty}})</strong> on {{appointment.datetime}}
                            <span v-if="appointment.provider().user.display_name">with <strong>{{appointment.provider().user.display_name}};</strong> </span>
                                <span v-if="appointment.provider().user.address">A: {{appointment.provider().user.address}}</span> 
                                <span v-if="appointment.provider().location().phone">P: {{appointment.provider().location().phone}}</span>
                            
                        </p>
                    </li>
                </ul>
            </div>
            <div class="col-xs-12" v-if="pastAppointments.length > 0">
                <h3>Past</h3>
                <ul v-if="appointments.length">
                    <li class="top-20" v-for="(appointment, index) in pastAppointments" :key="index">
                         <p>- {{appointment.type}}<span v-if="appointment.provider().specialty">,</span> <strong v-if="appointment.provider().specialty">({{appointment.provider().specialty}})</strong> on {{appointment.datetime}}
                            <span v-if="appointment.provider().user.display_name">with <strong>{{appointment.provider().user.display_name}};</strong> </span>
                                <span v-if="appointment.provider().user.address">A: {{appointment.provider().user.address}}</span> 
                                <span v-if="appointment.provider().location().phone">P: {{appointment.provider().location().phone}}</span>
                            
                        </p>
                    </li>
                </ul>
            </div>
        </div>
        <!-- <appointments-modal ref="appointmentsModal" :patient-id="patientId" :appointments="appointments"></appointments-modal> -->
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import moment from 'moment'
    import VueCache from '../../util/vue-cache'

    export default {
        name: 'appointments',
        mixins: [VueCache],
        props: [
            'patient-id',
            'url'
        ],
        components: {
            //'appointments-modal': AppointmentsModal
        },
        data() {
            return {
                 appointments: []
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
                const dt = moment(new Date(appointment.date + ' ' + appointment.time))
                appointment.at = new Date(appointment.date + ' ' + appointment.time)
                appointment.datetime = dt.format('YYYY-MM-DD') + ' at ' + dt.format('h:mm A')
                appointment.created_at = new Date(appointment.created_at)
                appointment.updated_at = new Date(appointment.updated_at)
                appointment.provider = () => ({ user: {}, location: () => ({}) })

                /** A product of the VueCache mixin */
                this.cache().get(rootUrl(`api/providers/${appointment.provider_id}`)).then(provider => {
                    if (provider) {
                        provider.location = () => ((provider.user || {}).locations || [])[0] || {}
                        appointment.provider = () => provider
                    }
                })

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