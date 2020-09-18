<template>
    <div>
        <section class="fab">
            <div class="mini-action-container">

                <div class="mini-action-button">
                    <a :href="createNoteUrl">
                        <i class="icon material-icons">speaker_notes</i>
                    </a>
                    <p class="text">Add Note</p>
                </div>

                <div class="mini-action-button">
                    <a :href="createObservationUrl">
                        <i class="icon material-icons">timeline</i>
                    </a>
                    <p class="text">Add Observation</p>
                </div>

                <div v-if="this.canAddOfflineActivity" class="mini-action-button">
                    <a :href="createActivityUrl">
                        <i class="icon material-icons">local_hospital</i>
                    </a>
                    <p class="text">Add Offline Activity</p>
                </div>

                <div v-if="isCareCoach" class="mini-action-button">
                    <a :href="createOfflineActivityTimeRequestUrl">
                        <i class="icon material-icons">local_hospital</i>
                    </a>
                    <p class="text">Request Offline Activity Time</p>
                </div>

                <div class="mini-action-button">
                    <a :href="createAppointmentUrl">
                        <i class="icon material-icons">today</i>
                    </a>
                    <p class="text">Add Appointment</p>
                </div>

                <div id="showAddCarePersonModal" class="mini-action-button">
                    <p>
                        <i v-on:click="createCarePerson"
                           class="icon material-icons">contact_mail</i>
                    </p>
                    <p class="text">Add Care Person</p>
                </div>

                <div v-if="hasPractice" class="mini-action-button">
                    <p>
                        <i v-on:click="createTask"
                           class="icon material-icons">calendar_today</i>
                    </p>
                    <p class="text">Add Activity</p>
                </div>

            </div>
            <div class="action-button">
                <i class="icon material-icons">add</i>
            </div>
        </section>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {setOpenModal} from "../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/actions";

    export default {
        data() {
            return {
                hasPractice: false,
                createNoteUrl: this.document().querySelector('meta[name="route.patient.note.create"]').getAttribute('content'),
                createObservationUrl: this.document().querySelector('meta[name="route.patient.observation.create"]').getAttribute('content'),
                createActivityUrl: this.document().querySelector('meta[name="route.patient.activity.create"]').getAttribute('content'),
                createOfflineActivityTimeRequestUrl: this.document().querySelector('meta[name="route.offline-activity-time-request.create"]').getAttribute('content'),
                createAppointmentUrl: this.document().querySelector('meta[name="route.patient.appointment.create"]').getAttribute('content'),
            }
        },

        props: {
            canAddOfflineActivity: {
                type: Boolean,
                required: true
            },
            isCareCoach: {
                type: Boolean,
                required: true
            },
        },

        mounted() {
            if (window['patientPractice']) {
                this.hasPractice = true;
            }
            else {
                this.hasPractice = false;
            }
        },
        methods: Object.assign(
            mapActions(['setOpenModal']),
            {
                createCarePerson() {
                    this.setOpenModal({
                        name: 'create-care-person'
                    });
                },
                createTask() {
                    this.setOpenModal({
                        name: 'add-task-modal',
                        props: {
                            patientId: window['patientId'],
                            practice: {
                                id: window['patientPractice'].id,
                                name: window['patientPractice'].name,
                            }
                        }
                    });
                },
                document() {
                    return (typeof (document) == 'undefined') ? {
                        querySelector: (query) => ({getAttribute: () => null})
                    } : document
                }
            }
        ),
    }
</script>

<style scoped>
    .fab {
        z-index: 999;
        position: fixed;
        bottom: 30px;
        right: 60px;
        width: 56px;
        height: 60px;
    }

    .fab .action-button, .fab .mini-action-button {
        cursor: pointer;
        position: absolute;
    }

    .fab .action-button {
        bottom: -18px;
        right: 1px;
    }

    .fab .action-button:hover .action-button__icon {
        -webkit-box-shadow: 0 0 8px rgba(0, 0, 0, 0.14), 0 8px 16px rgba(0, 0, 0, 0.28);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.14), 0 8px 16px rgba(0, 0, 0, 0.28);
    }

    .fab .mini-action-container {
        bottom: 0;
        right: 49px;
        display: none;
    }

    .fab:hover {
        height: 360px;
    }

    .fab:hover .mini-action-container {
        display: block;
    }

    .action-button .icon {
        -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.14), 0 4px 8px rgba(0, 0, 0, 0.28);
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.14), 0 4px 8px rgba(0, 0, 0, 0.28);
        background-color: #50b2e2;
        border-radius: 50%;
        color: #fff;
        padding: 16px;
    }

    .mini-action-button .icon {
        -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.14), 0 4px 8px rgba(0, 0, 0, 0.28);
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.14), 0 4px 8px rgba(0, 0, 0, 0.28);
        background-color: #47beab;
        border-radius: 50%;
        color: #fff;
        padding: 8px;
    }

    .mini-action-button .icon:hover {
        -webkit-box-shadow: 0 0 8px rgba(0, 0, 0, 0.14), 0 8px 16px rgba(0, 0, 0, 0.28);
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.14), 0 8px 16px rgba(0, 0, 0, 0.28);
    }

    .mini-action-button .text {
        display: none;
    }

    .mini-action-button:hover .text {
        background-color: #212121;
        border-radius: 3px;
        color: #fff;
        display: block;
        right: 60px;
        font-size: 14px;
        font-family: "Helvetica Neue";
        opacity: .8;
        padding: 5px 9px;
        position: absolute;
        top: 15px;
        white-space: nowrap;
    }

    .mini-action-button {
        position: fixed;
        text-align: center;
    }

    .mini-action-button:nth-child(1) {
        bottom: 60px;
    }

    .mini-action-button:nth-child(2) {
        bottom: 120px;
    }

    .mini-action-button:nth-child(3) {
        bottom: 180px;
    }

    .mini-action-button:nth-child(4) {
        bottom: 240px;
    }

    .mini-action-button:nth-child(5) {
        bottom: 300px;
    }

    .mini-action-button:nth-child(6) {
        bottom: 360px;
    }

    .mini-action-button:nth-child(7) {
        bottom: 420px;
    }

    .mini-action-button:nth-child(8) {
        bottom: 480px;
    }

    .mini-action-button:nth-child(9) {
        bottom: 540px;
    }

    .mini-action-button:nth-child(10) {
        bottom: 600px;
    }
</style>
