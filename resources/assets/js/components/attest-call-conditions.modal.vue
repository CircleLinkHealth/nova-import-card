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
                    <div class="col-sm-12 add-condition">
                        <button v-on:click="toggleAddProblem()" type="button" class="btn btn-secondary">{{addConditionLabel}}</button>
                        <div v-if="addProblem" style="padding-top: 20px">
                            <add-condition :patient-id="patientId" :problems="problems"></add-condition>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <button v-on:click="hideModal()" type="button" class="btn btn-danger">Cancel</button>
                    <button v-on:click="hideAndSubmitForm()" type="button" class="btn btn-info right-0">Submit
                    </button>
                </div>
            </div>
        </template>
    </modal>
</template>
<script>
    import Modal from '../admin/common/modal';
    import {rootUrl} from '../app.config';
    import AddCondition from './careplan/add-condition';
    import CareplanMixin from './careplan/mixins/careplan.mixin';
    import {Event} from 'vue-tables-2';

    export default {
        name: "attest-call-conditions-modal",
        mixins: [CareplanMixin],
        components: {
            'add-condition': AddCondition,
            'modal': Modal,
        },
        props: {
            'patientId': String,
        },
        mounted() {
            App.$on('show-attest-call-conditions-modal', () => {
                this.$refs['attest-call-conditions-modal'].visible = true;
            });

            this.getPatientBillableProblems();

            Event.$on('full-conditions:add', (ccdProblem) => {
                if (ccdProblem) this.problems.push(ccdProblem)
                this.addProblem = false;
            })
        },
        data(){
            return {
                loading: false,
                problems: [],
                attestedProblems: [],
                addProblem: false
            }
        },
        computed: {
            addConditionLabel (){
                return this.addProblem ? 'Hide Other Condition Section' : 'Add Other Condition';
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
            },
            toggleAddProblem(){
                this.addProblem = ! this.addProblem;
            }
        },
    }
</script>

<style>
    .modal-attest-call-conditions .modal-container {
        width: 40%;
    }

    .add-condition {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-bottom: 10px;
    }
    .btn.btn-secondary {
        background-color: #ddd;
        padding: 10 20 10 20;
        margin-right: 0 !important;
        margin-bottom: 5px;
    }

    .btn.btn-danger {
        background-color: #d9534f;
    }

    .btn.btn-secondary.selected, .list-group-item.selected {
        background: #47beab;
        color: white;
    }
</style>