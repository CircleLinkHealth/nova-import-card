<template>
    <modal name="health-goals" :no-title="true" :no-footer="true" :no-cancel="true" :no-buttons="true" class-name="modal-health-goals">
        <template scope="props">
            <div></div>
        </template>
    </modal>
</template>

<script>
    import { rootUrl } from '../../../app.config'
    import { Event } from 'vue-tables-2'
    import Modal from '../../../admin/common/modal'

    export default {
        name: 'health-goals-modal',
        props: {
            'patient-id': String,
            problems: Array
        },
        components: {
            'modal': Modal
        },
        computed: {
            patientHasSelectedProblem() {
                return this.problems.map(problem => problem.id).indexOf(this.selectedCpmProblemId) >= 0
            }
        },
        data() {
            return {
                
            }
        },
        methods: {
            
        },
        mounted() {

        }
    }
</script>

<style>
    .modal-health-goals .modal-container {
        width: 700px;
    }
</style>