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
                    <br>
                    <div v-if="showBhiLink">
                        <span style="color: red">If you did not discuss a BHI condition click <a v-bind:class="{'disabled': !twoCcmConditionsAttested}" href="javascript:" @click.prevent="submitForm('true')"><strong><u>here</u></strong></a></span>
                    </div>
                </div>
                <div>

                </div>
                <div class="col-sm-12" v-bind:class="sectionSpaceClass">
                    <div v-for="problem in problemsToAttest">
                        <input type="checkbox" :id="problem.id" :value="problem.id" style="display: none !important"
                               v-model="attestedProblems">
                        <label  v-bind:class="{'bhi-problem': problemIsBhi(problem)}" :for="problem.id"><span> </span>{{problem.name}}</label><span v-if="problem.code">&nbsp;({{problem.code}})</span>
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
        created() {
            self = this;
        },
        mounted() {

            App.$on('show-attest-call-conditions-modal', () => {
                this.$refs['attest-call-conditions-modal'].visible = true;
            });

            if (this.patientId) {
                this.patient_id = this.patientId;
            }

            //if in approve billable patients page, we get problems from the billing component
            if (this.isNotesPage) {
                this.getPatientBillableProblems();
            }

            Event.$on('full-conditions:add', (ccdProblem) => {

                let cpmProblem = this.getCpmProblems().filter(function (p) {
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
                patientHasBhi: false,
                showBhiLink: false,
            }
        },
        computed: {
            twoCcmConditionsAttested(){
                return  this.getCcmAttestedConditionsCount() >= 2;
            },
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
            problemIsBhi(problem){
                if (! this.isNotesPage){
                    return false;
                }
                return window.enableBhiAttestation.patientIsBhiEligible && problem.is_behavioral;
            },
            getCpmProblems() {
                if (!this.cpmProblems) {
                    return this.careplan().allCpmProblems || [];
                } else {
                    return this.cpmProblems;
                }
            },
            getPatientBillableProblems() {
                this.axios.get(rootUrl(`/api/patients/` + this.patientId + `/problems/ccd`))
                    .then(resp => {
                        //filter unique code
                        let monitoredProblems = resp.data.filter(p => p.is_monitored)
                        this.problems = Array.from(new Set(monitoredProblems.map(x=>x.code)))
                            .map(code => { return monitoredProblems.find(p => p.code === code)});
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
            submitForm(bypassBhiValidation = null) {

                if (this.isNotesPage) {
                    //validate and set error messages if you should
                    if (!this.validateAttestedConditions(bypassBhiValidation)) {
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
                    is_bhi: this.isBhi,
                    bypassed_bhi_validation: this.getBhiAttestedConditionsCount() === 0 && !!bypassBhiValidation
                });
            },
            toggleAddCondition() {
                this.addCondition = !this.addCondition;
            },
            problemIsBehavioral(pId) {
                let p = this.problems.find(function (p) {
                    return p.id === pId;
                })

                if (!p){
                    return false;
                }

                if (p.is_behavioral !== undefined){
                    return p.is_behavioral;
                }

                if (!p.cpm_id) {
                    return false;
                }

                let cpmProblem = this.getCpmProblems().find(function (cpm) {
                    return cpm.id === p.cpm_id
                })

                if (cpmProblem) {
                    return cpmProblem.is_behavioral
                }
                return false;
            },
            isBhiEligible(){
                return window.TimeTracker.bhiTimeInSeconds() >= (10 * 60);
            },
            isCcmEligible(){
                //check time as well. In some cases the CCM CS is wrongly attached to bhi patients (e.g. They have BHI problems that are not related to CPM Problems)
                //and the system percieves them as CCM problems while auto-attaching services on PatientMonthlySummary@attachChargeableServicesToFulfill
                return this.attestationRequirements.has_ccm && window.TimeTracker.ccmTimeInSeconds() > 0;
            },
            isPcmEligible(){
                //same as CCM
                return this.attestationRequirements.has_pcm && window.TimeTracker.ccmTimeInSeconds() > 0;
            },
            getCcmAttestedConditionsCount(){
                let attestedCcm = 0;

                //if a patient is BHI eligible we distinguish between ccm and bhi problems
                //if they don't, bhi problems can be considered as ccm
                if (this.isBhiEligible()){
                    this.attestedProblems.forEach(function (p) {
                        if (!self.problemIsBehavioral(p)) {
                            attestedCcm++;
                        }
                    })

                    return attestedCcm;
                }

                attestedCcm = this.attestedProblems.length;

                return attestedCcm;
            },
            getBhiAttestedConditionsCount(){
                let attestedBhi = 0;

                //only count BHI problems if patient is BHI eligible
                if (this.isBhiEligible()){
                    this.attestedProblems.forEach(function (p) {
                        if (self.problemIsBehavioral(p)) {
                            attestedBhi++;
                        }
                    })
                }

                return attestedBhi;
            },
            passesCcmValidation(){
                //include pcm
                //if patient wants to bypass bhi validation and does not require +2 ccm validation or already has attested to 2 ccm conditions, then we can bypass
                return !this.attestationRequirements.has_ccm || (this.attestationRequirements.attested_ccm_problems >= 2 || this.getCcmAttestedConditionsCount() >= 2);
            },
            validateAttestedConditions(bypassBhiValidation = null) {

                if (!this.attestationRequirements || this.attestationRequirements.disabled) {
                    return true;
                }

                let self = this;
                let ccmError;
                let bhiError;
                let pcmError;
                let currentCcmAttestedConditions = this.getCcmAttestedConditionsCount();
                let currentBhiAttestedConditions = this.getBhiAttestedConditionsCount();

                bhiError = this.isBhiEligible() && this.attestationRequirements.bhi_problems_attested === 0 && currentBhiAttestedConditions === 0;

                ccmError = this.isCcmEligible() && this.attestationRequirements.ccm_problems_attested === 0 && currentCcmAttestedConditions < 2;

                //PCM error is irrelevant if patient HAS bhi and HAS NOT CCM: e.g.
                //PCM requires only 1 condition to be attested so:
                //if CCM exists we need +2 CCM conditions, so it cancels out
                //if patient is not bhi eligible, that condition can be BHI OR CMM, and this falls under default validation of we need any 1 problem to be attested
                pcmError = this.attestationRequirements.has_pcm && ! this.attestationRequirements.has_ccm && this.isBhiEligible() && currentCcmAttestedConditions === 0

                let skipBhiValidation =  this.passesCcmValidation() && !!bypassBhiValidation;

                if (skipBhiValidation){
                    bhiError = null;
                }


                if (!ccmError && !bhiError && !pcmError){
                    return true;
                }

                let message;

                if (! this.isBhiEligible() && ccmError){
                    this.error = "Please select at least two conditions.";
                    return false;
                }

                this.showBhiLink = false;

                if (this.isBhiEligible()){
                    if (bhiError) {
                        this.showBhiLink = true;
                        if (ccmError){
                            message = 'Please select 2 CCM conditions and  the BHI condition(s) discussed on this call.';
                        }else if(pcmError){
                            message = 'Please select 1 CCM condition and  the BHI condition(s) discussed on this call.';
                        }else{
                            message = 'Please select the BHI condition(s) discussed on this call.';
                        }
                    }
                    this.error = message;
                    return false;
                }

                return true;
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

    .bhi-problem {
        color: #5cc0dd !important;
        font-weight: bolder;
    }

    .m-top-15 {
        margin-top: 15px;
    }

    .m-top-5 {
        margin-top: 5px;
    }
    a.disabled {
        cursor: not-allowed;
    }

</style>