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
    import {patientCareTeam} from '../../../store/getters';

    export default {
        computed: Object.assign({},
            mapGetters({
                patientCareTeam: 'patientCareTeam'
            }),
        ),

        methods: Object.assign(
            mapActions(['getPatientCareTeam']),
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