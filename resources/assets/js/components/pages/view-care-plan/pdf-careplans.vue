<script>
    import {mapActions, mapGetters} from 'vuex'
    import {getPatientCarePlan, destroyPdf} from '../../../store/actions'
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
            mapActions(['getPatientCarePlan', 'destroyPdf']),
            {
                deletePdf(pdf){
                    let disassociate = confirm('Are you sure you want to delete this CarePlan?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyPdf(pdf.id)
                    this.getPatientCarePlan(this.patientId)
                }
            }
        ),
    }
</script>

<template>
    <div class="col-md-8 col-md-offset-2" style="padding-top: 2%;">
        <div class="row">
            <div class="col-md-12 text-right">
                <a href="#" class="btn btn-info btn-sm inline-block">Upload PDF</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <ul class="list-group">
                    <li v-for="(pdf, index) in patientCarePlan.pdfs" class="list-group-item">
                        <a :href="pdf.url" target="_blank">{{pdf.label}} </a>
                        <button @click="deletePdf(pdf)" class="btn btn-xs btn-danger problem-delete-btn"><span><i class="glyphicon glyphicon-remove"></i></span></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style>

</style>