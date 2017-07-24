<script>
    import {mapActions, mapGetters} from 'vuex'
    import {getPatientCarePlan} from '../../../store/actions'
    import {patientCarePlan} from '../../../store/getters'

    export default {
        computed: Object.assign(
            mapGetters({
                patientCarePlan: 'patientCarePlan'
            })
        ),

        created() {
            this.getPatientCarePlan(this.patientId)
        },

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content')
            }
        },

        methods: Object.assign({},
            mapActions(['getPatientCarePlan'])
        ),
    }
</script>

<template>
    <div>
        <div class="row">
            <div class="col-md-12 text-right">
                <a href="#" class="btn btn-info btn-sm inline-block">Upload PDF</a>
            </div>
        </div>
    </div>
</template>

<style>

</style>