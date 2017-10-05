<template>
    <ul class="col-xs-12">
        <li v-for="(carePerson, index) in patientCareTeam" class="col-xs-12">
            <index-care-person :carePerson="carePerson"></index-care-person>
        </li>
    </ul>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex'
    import {getPatientCareTeam} from '../../../store/actions'

    export default {
        methods: Object.assign(
            mapActions(['getPatientCareTeam']),
        ),

        created() {
            let patientId = this.patientId

            if (!patientId) {
                return;
            }

            window.axios.get('user/' + patientId + '/care-team').then(
                (resp) => {
                    this.patientCareTeam = resp.data
                },
                (resp) => {
                    console.log(resp.data)
                }
            );

            //not working for some reason
            this.getPatientCareTeam(this.patientId);
        },

        mounted() {
            //not working for some reason
            this.getPatientCareTeam(this.patientId);
        },

        data() {
            return {
                patientId: $('meta[name=patient_id]').attr('content'),
                patientCareTeam: []
            }
        }
    }
</script>