<template>
    <modal ref="attest-call-conditions-modal" name="attest-call-conditions-modal" :no-title="true" :no-footer="true"
           :no-cancel="true" :no-buttons="true"
           class-name="modal-attest-call-conditions">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div>
                        <h4 style="text-align: center">{{title}}</h4>
                    </div>
                </div>
                <div v-if="error" class="col-sm-12">
                    <span style="color: red"><i class="fas fa-exclamation-circle"></i>&nbsp{{error}}</span>
                </div>
                <div class="col-sm-12" v-bind:class="sectionSpaceClass">
                    <div v-for="problem in problemsToAttest">
                        <input type="checkbox" :id="problem.id" :value="problem.id" style="display: none !important"
                               v-model="attestedProblems">
                        <label :for="problem.id"><span> </span>{{problem.name}}</label><span v-if="problem.code">&nbsp;({{problem.code}})</span>
                    </div>
                    <div class="col-sm-12 add-condition">
                        <button v-on:click="toggleAddCondition()" type="button" class="btn btn-info">
                            {{addConditionLabel}}
                        </button>
                        <div v-if="addCondition" style="padding-top: 20px">
                            <add-condition :cpm-problems="cpmProblems" :patient-id="patient_id"
                                           :problems="problems" :code-is-required="true"
                                           :is-approve-billable-page="!isNotesPage"
                            :is-bhi="isBhi"></add-condition>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 text-right">
                    <button v-on:click="hideModal()" type="button" class="btn btn-danger">Cancel</button>
                    <button v-on:click="submitForm()" type="button" class="btn btn-info right-0">Submit
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
    import VueSelect from 'vue-select';

    let self;

    export default {
        name: "attest-call-conditions-modal",
        mixins: [CareplanMixin],
        components: {
            'add-condition': AddCondition,
            'modal': Modal,
            'v-select': VueSelect,
        },
        props: {
            'patientId': String,
            'cpmProblems': Array,
        },
        created(){
            self = this;
        },
        mounted() {

            App.$on('show-attest-call-conditions-modal', () => {
                this.$refs['attest-call-conditions-modal'].visible = true;
            });

            if(! this.cpmProblems){
                this.cpm_problems = this.careplan().allCpmProblems || []
            }else{
                this.cpm_problems = this.cpmProblems
            }

            if (this.patientId){
                this.patient_id = this.patientId;
            }

            //if in approve billable patients page, we get problems from the billing component
            if (this.isNotesPage) {
                this.getPatientBillableProblems();
            }

            Event.$on('full-conditions:add', (ccdProblem) => {

                let cpmProblem = this.cpm_problems.filter(function(p){
                    return p.id == ccdProblem.cpm_id;
                })[0];

                if (ccdProblem) {
                    this.problems.push({
                        id: ccdProblem.id,
                        name: ccdProblem.name,
                        code: ccdProblem.code,
                        is_behavioral: cpmProblem ? cpmProblem.is_behavioral : false
                    })
                    this.attestedProblems.push(ccdProblem.id)
                }
                this.addCondition = false;
            })

            App.$on('modal-attest-call-conditions:hide', () => {
                this.hideModal();
            })

            Event.$on('modal-attest-call-conditions:show', (data) => {
                this.$refs['attest-call-conditions-modal'].visible = true;
                this.patient_id = String(data.patient.id)
                this.attestedProblems = data.is_bhi ? data.patient.attested_bhi_problems : data.patient.attested_ccm_problems;
                this.problems = data.patient.problems
                this.isBhi = data.is_bhi
            })
        },
        data() {
            return {
                patient_id: null,
                problems: [],
                attestedProblems: [],
                addCondition: false,
                error: null,
                isBhi: false,
            }
        },
        computed: {
            addConditionLabel() {
                return this.addCondition ? 'Close Other Condition Section' : 'Add Other Condition';
            },
            errorExists() {
                return !!this.error;
            },
            sectionSpaceClass: function () {
                return {
                    'm-top-15': this.errorExists,
                    'm-top-5': !this.errorExists,
                }
            },
            title() {

                return this.isNotesPage ? 'Please select all conditions addressed in this call:' : (this.isBhi ? 'Edit BHI Problem Codes' : 'Edit CCM Problem Codes');
            },
            problemsToAttest() {

                let problemsToAttest = (self.problems || []).filter(function (p) {
                    return !!p.code;
                });
                //if in notes page, show all problems
                //if in approve billable patients page, show ccm or bhi, depending on the modal
                return self.isNotesPage ? problemsToAttest : (problemsToAttest || []).filter(function (p) {
                    return self.isBhi ? p.is_behavioral : !p.is_behavioral;
                });
            },
            isNotesPage() {
                //if patient id prop has been passed in, then this is for the notes pages, else, approve billable patients page
                return !!this.patientId
            }
        },
        methods: {
            getPatientBillableProblems() {
                this.axios.get(rootUrl(`/api/patients/` + this.patientId + `/problems/ccd`))
                    .then(resp => {
                        this.problems = resp.data;
                    })
                    .catch(err => {
                        this.error = err;
                    });
            },
            hideModal() {
                this.addCondition = false;
                this.attestedProblems = [];
                this.error = null;
                this.$refs['attest-call-conditions-modal'].visible = false;
            },
            submitForm() {
                if (this.attestedProblems.length == 0) {
                    this.error = "Please select at least one condition."
                    return;
                }

                if (this.addCondition) {
                    this.error = "It looks like you are still trying to enter a condition manually. Please press 'Add Condition' when you are finished, or 'Close Other Condition Section' if you are not adding a condition manually."
                    return;
                }
                App.$emit('call-conditions-attested', {
                    attested_problems: this.attestedProblems,
                    patient_id: this.patient_id,
                    is_bhi: this.isBhi
                });
            },
            toggleAddCondition() {
                this.addCondition = !this.addCondition;
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
        padding-top: 5px;
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

    .m-top-15 {
        margin-top: 15px;
    }

    .m-top-5 {
        margin-top: 5px;
    }
</style>