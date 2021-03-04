import CareplanMixin from './careplan.mixin';
import {Event} from 'vue-tables-2';
import {rootUrl} from '../../../../../../vendor/circlelinkhealth/sharedvuecomponents-module/Resources/assets/js/app.config'

export default {
    data: function () {
        return {
            newProblem: {
                name: '',
                problem: '',
                is_monitored: true,
                icd10: null,
                cpm_problem_id: null
            },
            patient_id: null,
            showNoProblemSelected: false
        }
    },
    mixins: [CareplanMixin],
    computed: {
        patientHasSelectedProblem() {
            if (!this.selectedProblem) return (this.newProblem.name !== '') && this.problems.findIndex(problem => (problem.name || '').toLowerCase() == (this.newProblem.name || '').toLowerCase()) >= 0
            else return (this.selectedProblem.name !== '') && this.problems.findIndex(problem => (problem != this.selectedProblem) && ((problem.name || '').toLowerCase() == (this.selectedProblem.name || '').toLowerCase())) >= 0
        }
    },
    methods: {
        getSystemCodes() {
            let codes = this.careplan().allCpmProblemCodes || null

            if (codes !== null) {
                this.codes = codes
                return true
            }

            return this.axios.get(rootUrl(`api/problems/codes`)).then(response => {
                this.codes = response.data
            }).catch(err => {
                console.error('full-conditions:get-system-codes', err)
            })
        },
        getAddConditionCpmProblems() {
            if (!this.cpmProblems) {
                return this.careplan().allCpmProblems || [];
            } else {
                return this.cpmProblems;
            }
        }
    },
    mounted() {
        if (this.patientId) {
            this.patient_id = this.patientId;
        }

        this.getSystemCodes()

    }
}
