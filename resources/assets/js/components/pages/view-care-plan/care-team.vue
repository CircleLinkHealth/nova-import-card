<template>

    <div id="care-team" class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 id="care-team-label"
                    class="patient-summary__subtitles patient-summary--careplan-background">
                    Care Team
                    <span class="btn btn-primary glyphicon glyphicon-plus" @click="createCarePerson" aria-hidden="true"></span>
                </h2>
            </div>
            <div class="col-xs-12">
                <div class="v-pdf-careplans">
                    <ul class="col-xs-12">
                        <li v-for="(carePerson, index) in patientCareTeam" :key="index" class="col-xs-12">
                            <div class="col-md-7">
                                <p style="margin-left: -10px;">
                                    <strong>{{carePerson.formatted_type}}: </strong>{{fullName(carePerson)}}<em>{{carePerson.user.primaryRole}}</em>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <p v-show="carePerson.alert">Receives Alerts</p>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-xs btn-danger problem-delete-btn"
                                        v-on:click.stop.prevent="deleteCarePerson(carePerson)"><span> <i
                                        class="glyphicon glyphicon-remove"></i> </span></button>

                                <button class="btn btn-xs btn-primary problem-edit-btn"
                                        v-on:click.stop.prevent="editCarePerson(carePerson)"><span> <i
                                        class="glyphicon glyphicon-pencil"></i> </span></button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {getPatientCareTeam} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/actions'
    import {patientCareTeam} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/getters'
    import UpdateCarePerson from './update-care-person.vue'

    export default {
        components: {
            UpdateCarePerson
        },

        computed: Object.assign(
            mapGetters({
                patientCareTeam: 'patientCareTeam'
            })
        ),

        methods: Object.assign(
            mapActions(['getPatientCareTeam', 'destroyCarePerson', 'setOpenModal']),
            {
                deleteCarePerson(carePerson) {
                    let disassociate = confirm('Are you sure you want to remove ' + this.fullName(carePerson) + ' from the CareTeam?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyCarePerson(carePerson)
                }
            },
            {
                editCarePerson(carePerson) {
                    this.setOpenModal({
                        name: 'update-care-person',
                        props: {
                            carePerson: carePerson
                        },
                    })
                }
            },
            {
                createCarePerson() {
                    this.setOpenModal({
                        name: 'create-care-person'
                    });
                },
            },
            {
                fullName(carePerson) {
                    let suffix = carePerson.user.suffix === 'non-clinical' ? '' : carePerson.user.suffix
                    return carePerson.user.first_name + ' ' + carePerson.user.last_name + ' ' + suffix
                }
            },
        ),

        mounted() {
            this.getPatientCareTeam(this.patientId);
        },

        data() {
            return {
                patientId: $('meta[name=patient_id]').attr('content'),
            }
        }
    }
</script>