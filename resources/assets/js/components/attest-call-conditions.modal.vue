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
                                           :patient-has-bhi="patientHasBhi"
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
            'attestationRequirements': Object
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
                this.patientHasBhi = data.patient_has_bhi
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
                patientHasBhi: false
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
                return self.isNotesPage || !self.patientHasBhi ? problemsToAttest : (problemsToAttest || []).filter(function (p) {
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
                if (this.isNotesPage){
                    //validate and set error messages if you should
                    if (! this.validateAttestedConditions()){
                        return;
                    }
                }

                //default - still run even if custom requirements get passed in. There's no scenario in which we would allow no problem to be attested on a call.
                if (this.attestedProblems.length == 0) {
                    this.error = "Please select at least one condition."
                    return;
                }

                if (this.addCondition) {
                    this.error = "It looks like you are still trying to enter a condition manually. Please press 'Add Condition' when you are finished, or 'Close Other Condition Section' if you are not adding a condition manually."
                    return;
                }

                this.error = null;

                App.$emit('call-conditions-attested', {
                    attested_problems: this.attestedProblems,
                    patient_id: this.patient_id,
                    is_bhi: this.isBhi
                });
            },
            toggleAddCondition() {
                this.addCondition = !this.addCondition;
            },
            problemIsBehavioral(pId){
                let p = this.problems.find(function (p) {
                    return p.id === pId;
                })
                if (! p.cpm_id){
                    return false;
                }
                let cpmProblem = this.cpm_problems.find(function (cpm) {
                    return cpm.id === p.cpm_id
                })
                if (cpmProblem){
                    return cpmProblem.is_behavioral
                }
                return false;
            },
            validateAttestedConditions(){
                if (! this.attestationRequirements){
                    return true;
                }
                let self = this;
                let ccmError;
                let bhiError;
                //if ccm 2
                //if complex require 2 CCM attested
                //else require any 2
                if (this.attestationRequirements.ccm_2){
                    if(this.attestationRequirements.is_complex){
                        let attestedCcm = 0;
                        this.attestedProblems.forEach(function (p) {
                            if (! self.problemIsBehavioral(p)){
                                attestedCcm++;
                            }
                        })
                        if (attestedCcm < 2){
                            ccmError = true;
                        }
                    }else {
                        if (this.attestedProblems.length < 2) {
                            this.error = "Please select at least two conditions."
                            return false;
                        }
                    }
                }
                if (this.attestationRequirements.is_complex && this.attestationRequirements.bhi_1){
                    let attestedBhi = 0;
                    this.attestedProblems.forEach(function (p) {
                        if (self.problemIsBehavioral(p)){
                            attestedBhi++;
                        }
                    })
                    if (attestedBhi === 0){
                        bhiError = true;
                    }
                }
                if (! ccmError && ! bhiError){
                    return true
                }
                let error;
                if (ccmError){
                    error = 'Please select at least two CCM conditions'
                }
                if (bhiError){
                    if (!error){
                        error = 'Please select at least one BHI condition';
                    }else{
                        error = error + ' and at least one BHI condition';
                    }
                }
                this.error = error +'.';
                return false;
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