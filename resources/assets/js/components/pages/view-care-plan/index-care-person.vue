<template>
    <div>
        <div v-show="carePerson.user.full_name">
            <div class="col-md-7">
                <p style="margin-left: -10px;">
                    <strong>{{carePerson.formatted_type}}: </strong>{{carePerson.user.full_name}}<em>{{carePerson.user.primaryRole}}</em>
                </p>
            </div>
            <div class="col-md-3">
                <p v-if="carePerson.alert">Receives Alerts</p>
            </div>
            <div class="col-md-2">
                <button class="btn btn-xs btn-danger problem-delete-btn"
                        v-on:click.stop.prevent="deleteCarePerson()"><span> <i
                        class="glyphicon glyphicon-remove"></i> </span></button>

                <button class="btn btn-xs btn-primary problem-edit-btn"
                        v-on:click.stop.prevent="editCarePerson()"><span> <i
                        class="glyphicon glyphicon-pencil"></i> </span></button>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import {destroyCarePerson, setOpenModal} from '../../../store/actions'

    export default {
        props: ['carePerson'],

        computed: {
            name() {
                return this.carePerson.user.first_name
                    + ' '
                    + this.carePerson.user.last_name
            }
        },

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                currentModal: '',
                editedModel: {}
            }
        },

        methods: Object.assign({},
            mapActions(['destroyCarePerson', 'setOpenModal']),
            {
                deleteCarePerson() {
                    let disassociate = confirm('Are you sure you want to remove ' + name + ' from the CareTeam?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyCarePerson(this.carePerson)
                }
            },
            {
                editCarePerson() {
                    this.setOpenModal({
                        name: 'update-care-person',
                        props: {
                            carePerson: this.carePerson
                        },

                    })
                }
            }
        )
    }
</script>