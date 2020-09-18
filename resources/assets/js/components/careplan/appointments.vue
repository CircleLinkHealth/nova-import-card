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
        <div class="col-xs-12 text-center" v-if="appointments.length === 0">
            No Appointments at this time
        </div>
        <div class="row gutter" v-if="appointments.length > 0">
            <div class="col-xs-12" v-if="futureAppointments.length > 0">
                <h3>Upcoming</h3>
                <ul v-if="appointments.length">
                    <li class="top-20" v-for="(appointment, index) in futureAppointments" :key="index">
                        <appointment :appointment="appointment"></appointment>
                    </li>
                </ul>
            </div>
            <div class="col-xs-12" v-if="pastAppointments.length > 0">
                <h3>Past</h3>
                <ul v-if="appointments.length">
                    <li class="top-20" v-for="(appointment, index) in pastAppointments" :key="index">
                        <appointment :appointment="appointment"></appointment>
                    </li>
                </ul>
            </div>
        </div>
        <appointments-modal ref="appointmentsModal" :patient-id="patientId" :appointments="appointments"></appointments-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import moment from 'moment'
    import VueCache from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/util/vue-cache'
    import AppointmentsModal from './modals/appointments.modal'
    import AppointmentRender from './renders/appointment.render'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'appointments',
        mixins: [ VueCache, CareplanMixin ],
        props: [
            'patient-id',
            'url'
        ],
        components: {
            'appointment': AppointmentRender,
            'appointments-modal': AppointmentsModal
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
                const dt = moment(appointment.date + ' ' + appointment.time)
                appointment.at = dt.toDate()
                appointment.datetime = dt.format('MM-DD-YYYY') + ' at ' + dt.format('h:mm A')
                appointment.created_at = moment(appointment.created_at).toDate()
                appointment.updated_at = moment(appointment.updated_at).toDate()
                appointment.provider = () => ({ user: {}, location: () => ({}) })
                appointment.isPending = () => (appointment.at > new Date())

                /** A product of the VueCache mixin */
                if (appointment.provider_id) {
                    this.cache().get(rootUrl(`api/providers/${appointment.provider_id}`)).then(provider => {
                        if (provider) {
                            provider.location = () => ((provider.user || {}).locations || [])[0] || {}
                            appointment.provider = () => provider
                        }
                    })
                }
                return appointment
            },
            getAppointments(page) {
                if (!page) {
                    this.appointments = []
                    page = 1
                }
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/appointments?page=${page}`)).then(response => {
                    const pagination = response.data
                    // console.log('appointments:get-appointments', pagination)
                    this.appointments = this.appointments.concat(pagination.data.map(this.setupAppointment))
                    this.appointments.sort((a, b) => a.at < b.at ? 1 : -1)
                    if (pagination.next_page_url) return this.getAppointments(page + 1)
                }).catch(err => {
                    console.error('appointments:get-appointments', err)
                })
            },
            showModal() {
                Event.$emit('modal-appointments:show')

                setTimeout(() => Event.$emit('misc:page', 'Appointments'), 5)
            }
        },
        mounted() {
            this.appointments = (this.careplan().appointments || []).map(this.setupAppointment)
            this.getAppointments(2)

            Event.$on('appointments:add', (appointment) => {
                if (appointment) this.appointments.push(this.setupAppointment(appointment))
                this.appointments = this.appointments.sort((a, b) => a.at < b.at ? 1 : -1)
            })

            Event.$on('appointments:remove', (id) => {
                this.appointments.splice(this.appointments.findIndex(appointment => appointment.id === id), 1)
            })
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