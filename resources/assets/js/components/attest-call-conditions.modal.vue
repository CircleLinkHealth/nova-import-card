<template>
    <modal ref="attest-call-conditions-modal" name="attest-call-conditions-modal" :no-title="true" :no-footer="true"
           :no-cancel="true" :no-buttons="true"
           class-name="modal-attest-call-conditions">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <h4 style="text-align: center">Please select all conditions addressed in this call</h4>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div v-for="problem in problems">
                        <input type="checkbox"  :id="problem.id" :value="problem.id" v-model="attestedProblems">
                        <label :for="problem.id"><span> </span>{{problem.name}}</label>
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <button v-on:click="hideModal()" type="button" class="btn btn-danger">Cancel</button>
                    <button v-on:click="hideAndSubmitForm()" type="button" class="btn btn-primary right-0">Submit
                    </button>
                </div>
            </div>
        </template>
    </modal>
</template>
<script>
    import Modal from '../admin/common/modal';
    import {rootUrl} from '../app.config';
    import LoaderComponent from './loader'

    export default {
        name: "attest-call-conditions-modal",
        components: {
            'modal': Modal,
            'loader': LoaderComponent
        },
        props: {
            'patientId': String,
        },
        mounted() {
            App.$on('show-attest-call-conditions-modal', () => {
                this.$refs['attest-call-conditions-modal'].visible = true;
            });

            this.getPatientBillableProblems();
        },
        data(){
            return {
                loading: false,
                problems: [],
                attestedProblems: []
            }
        },
        methods: {
            getPatientBillableProblems(){
                this.loading = true;

                this.axios.get(rootUrl(`/api/patients/`+ this.patientId + `/problems/ccd`))
                    .then(resp => {
                        console.log(resp.data);
                        this.problems = resp.data;
                        this.loading = false;
                    })
                    .catch(err => {

                    });
            },
            hideModal() {
                this.attestedConditions = [];
                this.$refs['attest-call-conditions-modal'].visible = false;
            },
            hideAndSubmitForm() {
                App.$emit('call-conditions-attested', this.attestedProblems);
                this.hideModal();
            }
        },
    }
</script>

<style>
    .modal-attest-call-conditions .modal-container {
        width: 50%;
    }
</style>