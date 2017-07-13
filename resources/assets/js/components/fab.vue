<!--Don't know how to add compiled fab.css to style tag. Make sure 'public/css/fab.css' is included on the page-->
<style>
    @media print {
        .hidden-print, .hidden-print * {
            display: none !important;
        }
    }
</style>

<template>
    <div>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <section class="FAB hidden-print">
            <div class="FAB__mini-action-button hidden-print">
                <div class="mini-action-button--hide mini-action-button hidden-print">
                    <a :href="createNoteUrl">
                        <i class="mini-action-button__icon material-icons">speaker_notes</i>
                    </a>
                    <p class="mini-action-button__text--hide">Add Note</p>
                </div>

                <div class="mini-action-button--hide mini-action-button">
                    <a :href="createObservationUrl">
                        <i class="mini-action-button__icon material-icons">timeline</i>
                    </a>
                    <p class="mini-action-button__text--hide">Add Observation</p>
                </div>

                <div v-if="theCurrentUser.role.name == 'care-center'">
                    <div class="mini-action-button--hide mini-action-button">
                        <a :href="createActivityUrl">
                            <i class="mini-action-button__icon material-icons">local_hospital</i>
                        </a>
                        <p class="mini-action-button__text--hide">Add Offline Activity</p>
                    </div>
                </div>

                <div class="mini-action-button--hide mini-action-button">
                    <a :href="createAppointmentUrl">
                        <i class="mini-action-button__icon material-icons">today</i>
                    </a>
                    <p class="mini-action-button__text--hide">Add Appointment</p>
                </div>

                <div id="showAddCarePersonModal" class="mini-action-button--hide mini-action-button">
                    <p>
                        <i v-on:click="createCarePerson"
                           class="mini-action-button__icon material-icons">contact_mail</i>
                    </p>
                    <p class="mini-action-button__text--hide">Add Care Person</p>
                </div>

            </div>
            <div class="FAB__action-button hidden-print">
                <i class="action-button__icon material-icons hidden-print">add</i>
            </div>
        </section>

        <component :is="currentModal" :model="editedUser"></component>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {getCurrentUser, showForm} from "../store/actions";
    import {currentUser} from '../store/getters';

    export default {
        data() {
            return {
                createNoteUrl: $('meta[name="route.patient.note.create"]').attr('content'),
                createObservationUrl: $('meta[name="route.patient.observation.create"]').attr('content'),
                createActivityUrl: $('meta[name="route.patient.activity.create"]').attr('content'),
                createAppointmentUrl: $('meta[name="route.patient.appointment.create"]').attr('content'),

                currentModal: '',
                editedUser: null
            }
        },

        computed: Object.assign(
            mapGetters({
                theCurrentUser: 'currentUser'
            })
        ),

        created() {
            this.getCurrentUser()
        },

        mounted() {

        },

        methods: Object.assign(
            mapActions(['showForm', 'getCurrentUser']),
            {
                createCarePerson() {
                    this.editedUser = {}
                    this.currentModal = 'create-care-person'
                    this.showForm(true)
                }
            }
        ),
    }
</script>