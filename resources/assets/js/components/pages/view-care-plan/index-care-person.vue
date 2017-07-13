<template>
    <div>
        <div v-show="carePerson.user.first_name && carePerson.user.last_name">
            <div class="col-md-7">
                <p style="margin-left: -10px;">
                    <strong>{{carePerson.formatted_type}}: </strong>{{carePerson.user.first_name}} {{carePerson.user.last_name}} <em>{{carePerson.user.primaryRole}}</em>
                </p>
            </div>
            <div class="col-md-3">
                <p v-if="carePerson.alert">Receives Alerts</p>
            </div>
            <div class="col-md-2">
                <div class="col-md-6 text-right">
                    <button class="btn btn-xs btn-danger problem-delete-btn"
                            v-on:click.stop.prevent="deleteCarePerson()"><span> <i
                            class="glyphicon glyphicon-remove"></i> </span></button>
                </div>

                <div class="col-md-6 text-left">
                    <button class="btn btn-xs btn-primary problem-edit-btn"
                            v-on:click.stop.prevent="editCarePerson()"><span> <i
                            class="glyphicon glyphicon-pencil"></i> </span></button>
                </div>
            </div>

            <component :is="currentModal" :carePerson="editedModel"></component>
        </div>
    </div>
</template>

<script>
    import {mapActions} from 'vuex'
    import {destroyCarePerson, showForm} from '../../../store/actions'

    export default {
        props: ['carePerson'],

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                currentModal: '',
                editedModel: {}
            }
        },

        methods: Object.assign(
            mapActions(['destroyCarePerson', 'showForm']),
            {
                deleteCarePerson()
                {
                    let disassociate = confirm('Are you sure you want to remove ' + this,carePerson.user.first_name
                        + ' '
                        + this.carePerson.user.last_name + ' from the CareTeam?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyCarePerson(this.carePerson)
                }
            },
            {
                editCarePerson()
                {
                    this.editedModel = this.carePerson
                    this.currentModal = 'update-care-person'
                    this.showForm(true)
                }
            }
        )
    }
</script>