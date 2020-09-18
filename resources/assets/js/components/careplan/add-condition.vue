<template>
    <div class="row">
        <form @submit="addCcdProblem">
            <div class="col-sm-12">
                <label class="label label-danger font-14" v-if="patientHasSelectedProblem">
                    Condition is already in care plan. Please add a new condition.
                </label>
                <label class="label label-danger font-14" v-if="newProblemIcd10CodeDuplicateError">
                    {{newProblemIcd10CodeDuplicateError}}
                </label>
                <label class="label label-danger font-14" v-if="showNoProblemSelected">
                    Please select a condition from the dropdown below.
                </label>
            </div>
            <div v-if="isApproveBillablePage && isBhi">
                <div class="col-sm-12 top-10">
                    <v-select
                            placeholder="Select a Problem"
                            :required="true"
                            v-model="newProblem.name"
                            :value="newProblem.name"
                            label="name"
                            index="name"
                            @input="resolveIcd10Code"
                            :options="cpmProblemsForBHISelect">
                    </v-select>
                </div>
            </div>
            <div v-else>
                <div class="col-sm-12 top-10">
                    <v-complete
                            class="v-complete" placeholder="Enter a Condition" :required="true"
                            v-model="newProblem.name"
                            :value="newProblem.name" :limit="99"
                            :suggestions="cpmProblemsForAutoComplete"
                            :class="{ error: patientHasSelectedProblem }" :threshold="0.8"
                            @input="resolveIcd10Code">
                    </v-complete>
                </div>
                <div v-if="shouldSelectIsMonitored">
                    <div class="col-sm-6 font-14 top-20">
                        <label><input type="radio" :value="true" v-model="newProblem.is_monitored"/> For Care
                            Management</label>
                    </div>
                    <div class="col-sm-6 font-14 top-20">
                        <label><input type="radio" :value="false" v-model="newProblem.is_monitored"/> Other
                            Condition</label>
                    </div>
                </div>
                <div class="col-sm-12 top-20" v-if="newProblem.is_monitored">
                    <input type="text" :required="codeIsRequired" class="form-control" v-model="newProblem.icd10"
                           @input="checkForIcd10CodeDuplicates"
                           placeholder="ICD10 Code"/>
                </div>
            </div>
            <div class="col-sm-12 text-right top-20">
                <loader v-if="loaders.addProblem"></loader>
                <input type="submit" class="btn btn-secondary right-0 selected" value="Add Condition"
                       :disabled="shouldDisableSubmitNewCondition"/>
            </div>
        </form>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import {Event} from 'vue-tables-2'
    import Modal from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/common/modal'
    import VueSelect from 'vue-select'
    import VueComplete from 'v-complete'
    import Collapsible from '../collapsible'
    import CareplanMixin from './mixins/careplan.mixin'
    import AddConditionMixin from './mixins/add-condition.mixin'


    let self;

    export default {
        name: "add-condition",
        props: {
            'patient-id': String,
            'problems': Array,
            'shouldSelectIsMonitored': Boolean,
            'cpmProblems': Array,
            'codeIsRequired': Boolean,
            'isApproveBillablePage': Boolean,
            'isNotesPage': Boolean,
            'patientHasBhi': Boolean,
            'isBhi': Boolean
        },
        mixins: [
            CareplanMixin,
            AddConditionMixin
        ],
        components: {
            'modal': Modal,
            'v-select': VueSelect,
            'v-complete': VueComplete,
            'collapsible': Collapsible
        },
        data() {
            return {
                loaders: {
                    addInstruction: null,
                    addProblem: null,
                    editProblem: null,
                    removeProblem: null,
                    removeInstruction: null,
                    addCode: null,
                    removeCode: null,
                    editCode: null,
                },
                patient_id: null,
                is_approve_billable_page: false,
                patient_has_bhi: true,
                is_bhi: false,
                newProblemIcd10CodeDuplicateError: null,
            }
        },
        computed: {
            shouldDisableSubmitNewCondition() {
                return this.patientHasSelectedProblem || !!this.newProblemIcd10CodeDuplicateError;
            },
            cpmProblemsForBHISelect() {
                return self.cpmProblemsForAutoComplete
            },
            cpmProblemsForAutoComplete() {
                let probs = self.getAddConditionCpmProblems();

                if (self.isApproveBillablePage && self.patient_has_bhi) {
                    probs = probs.filter(function (p) {

                        if (!p.code) {
                            return false;
                        }

                        return self.isBhi ? p.is_behavioral : !p.is_behavioral;
                    })
                }

                return probs.filter(p => p && p.name)
                    .reduce((pA, pB) => {
                        return pA.concat([{
                            name: pB.name,
                            id: pB.id,
                            code: pB.code,
                            is_snomed: false,
                        }, ...(pB.is_behavioral ? pB.snomeds.map(snomed => ({
                            name: snomed.icd_10_name,
                            id: pB.id,
                            code: snomed.icd_10_code,
                            is_snomed: true,
                        })) : [])])
                    }, [])
                    .distinct(p => p.name)
                    .sort((a, b) => (+b.is_snomed) - (+a.is_snomed) || b.name.localeCompare(a.name));
            },
        },
        methods: {
            addCcdProblem(e) {
                e.preventDefault()

                if (this.newProblem.name.length == 0) {
                    this.showNoProblemSelected = true
                    return;
                }

                this.loaders.addProblem = true
                return this.axios.post(rootUrl(`api/patients/${this.patient_id}/problems/ccd`), {
                    name: this.newProblem.name,
                    cpm_problem_id: this.newProblem.cpm_problem_id,
                    is_monitored: this.newProblem.is_monitored,
                    icd10: this.newProblem.icd10
                }).then(response => {
                    this.loaders.addProblem = false
                    Event.$emit('problems:updated', {})

                    //If another condition is created and is attested for the patient from admin/billing/index.vue,
                    //we need patient id to add it to the patient's existing problems in the client table
                    response.data.patient_id = this.patient_id;
                    Event.$emit('full-conditions:add', response.data)

                    this.reset()
                    this.selectedProblem = response.data
                    setImmediate(() => this.checkPatientBehavioralStatus())
                }).catch(err => {
                    console.error('full-conditions:add', err)
                    this.loaders.addProblem = false
                })
            },
            resolveIcd10Code() {
                this.showNoProblemSelected = false;
                const autoCompleteProblem = this.cpmProblemsForAutoComplete.find(p => p.name == this.newProblem.name)
                this.newProblem.icd10 = (autoCompleteProblem || {}).code || (this.problems.find(p => p.name == this.newProblem.name) || {}).code
                this.newProblem.cpm_problem_id = (autoCompleteProblem || {}).id

                this.checkForIcd10CodeDuplicates();
            },
            checkForIcd10CodeDuplicates() {
                let isNotCareAreasModal = this.isNotesPage || this.isApproveBillablePage;
                if (isNotCareAreasModal && !this.showNoProblemSelected && this.newProblem.icd10.length > 0) {
                    let matchingProblem = this.problems.find(p => p.code == this.newProblem.icd10)

                    if (matchingProblem) {
                        this.newProblemIcd10CodeDuplicateError = matchingProblem.name + ' has the same ICD-10 code as this one.';
                    } else {
                        this.newProblemIcd10CodeDuplicateError = null;
                    }
                }
            },
            reset() {
                this.newProblem.name = ''
                this.newProblem.problem = ''
                this.newProblem.is_monitored = true
                this.newProblem.icd10 = null
            },
        },
        created() {
            self = this
        },
        mounted() {
            this.patient_has_bhi = this.patientHasBhi

            Event.$on('modal-attest-call-conditions:show', (data) => {
                this.patient_id = String(data.patient.id)
            })
        }
    }
</script>

<style>
    .v-complete ul {
        max-height: 200px !important;
        overflow: scroll;
    }
</style>