<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background" style="margin-top: 10px">We Are Managing
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <div class="text-center" v-if="cpmProblems.length === 0 && ccdMonitoredProblems.length === 0">No Problems at this time</div>
                
                <ul class="subareas__list" v-if="(ccdMonitoredProblems.length > 0)">
                    <li class='subareas__item inline-block col-sm-6 print-row' :class="{ ccd: problem.type === 'ccd' }" 
                        v-for="(problem, index) in ccdMonitoredProblems" :key="index">
                        {{problem.type === 'ccd' ? ((problem.related() || {}).name || problem.name) : problem.name}}
                        <label class="label label-primary label-popover" v-if="problem.type === 'ccd'">
                            {{ problem.title() }}
                        </label>
                    </li>
                </ul>
            </div>
            <div class="col-xs-12" v-if="ccdProblemsForListing.length > 0">
                <h2 class="color-blue pointer" @click="toggleOtherConditions">
                    <span v-if="!isOtherConditionsVisible">See </span>Other Conditions
                    <span v-if="!isOtherConditionsVisible" class="font-22">({{ ccdProblemsForListing.length }})</span>
                    <span v-if="isOtherConditionsVisible" class="font-22">(Click to Minimize)</span>
                </h2>
                
                <ul class="row" v-if="isOtherConditionsVisible">
                    <li class='top-10 col-sm-6' 
                        v-for="(problem, index) in ccdProblemsForListing" :key="index">
                        {{problem.name}}
                    </li>
                </ul>
            </div>
        </div>
        <care-areas-modal ref="careAreasModal" :patient-id="patientId" :problems="problems"></care-areas-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import {Event} from 'vue-tables-2'
    import CareAreasModal from './modals/care-areas.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'care-areas',
        props: [
            'patient-id'
        ],
        mixins: [CareplanMixin],
        components: {
            'care-areas-modal': CareAreasModal
        },
        data() {
            return {
                cpmProblems: [],
                ccdProblems: [],
                allCpmProblems: [],
                isOtherConditionsVisible: false
            }
        },
        computed: {
            problems() {
                return [ ...this.ccdMonitoredProblems, ...this.ccdProblemsForListing ]
            },
            cpmProblemsForListing() {
                return this.cpmProblems.distinct(p => p.name)
            },
            ccdMonitoredProblems() {
                return this.ccdProblems.filter(problem => problem.is_monitored).distinct(p => p.name)
            },
            ccdProblemsForListing() {
                return this.ccdProblems.filter(problem => !problem.is_monitored).distinct(p => p.name)
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupCpmProblem(problem) {
                problem.instruction = this.setupDates(problem.instruction || (this.allCpmProblems.find(p => p.name == problem.name) || {}).instruction || {
                    name: null
                })
                problem.type = 'cpm'
                problem.title = () => `${problem.code} ${problem.name}`
                problem.count = () => this.cpmProblems.filter(p => p.name == problem.name).length
                return problem
            },
            getCpmProblems() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/problems/cpm`)).then(response => {
                    //console.log('care-areas:get-problems', response.data)
                    this.cpmProblems = response.data.map(this.setupCpmProblem)
                    Event.$emit('care-areas:problems', this.cpmProblems)
                }).catch(err => {
                    console.error('care-areas:get-problems', err)
                })
            },
            showModal() {
                Event.$emit('modal-care-areas:show')
            },
            ccdProblemName(ccdProblem) {
                let p = this.allCpmProblems.find(problem => problem.id == ccdProblem.cpm_id)
                return p ? p.name : ccdProblem.name
            },
            toggleOtherConditions () {
                this.isOtherConditionsVisible = !this.isOtherConditionsVisible
            }
        },
        mounted() {
            this.cpmProblems = (this.careplan().cpmProblems || []).map(this.setupCpmProblem)
            this.allCpmProblems = (this.careplan().allCpmProblems || [])

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems.map(this.setupCpmProblem)
            })

            Event.$on('care-areas:ccd-problems', (problems) => {
                this.ccdProblems = problems
                App.$emit('patient-problems-updated', this.ccdProblems);
            })

            Event.$on('care-areas:add', (problem) => {
                if (problem) {
                    if (problem.type == 'cpm') {
                        this.cpmProblems.push(problem)
                    }
                    else if (problem.type == 'ccd') {
                        this.ccdProblems.push(problem)
                    }
                }
            })

            Event.$on('care-areas:remove-cpm-problem', (id) => {
                this.cpmProblems = this.cpmProblems.filter(problem => problem.id != id)
                Event.$emit('care-areas:problems', this.cpmProblems)
            })

            Event.$on('care-areas:remove-ccd-problem', (id) => {
                this.ccdProblems = this.ccdProblems.filter(problem => problem.id != id)
                Event.$emit('care-areas:ccd-problems', this.ccdProblems)
            })

            Event.$on('care-areas:request-problems', () => Event.$emit('care-areas:problems', this.cpmProblems))

        }
    }
</script>

<style>
    .color-blue {
        color: #109ace;
    }

    .font-18 {
        font-size: 18px;
    }

    .font-22 {
        font-size: 22px;
    }

    li.ccd:hover {
        color: #109ace;
    }

    label.label.label-popover {
        background-color: #109ace;
        color: white;
        display: none;
        position: absolute;
        margin-left: 10px;
        z-index: 2;
        top: -5px;
        max-width: 300px;
        white-space: normal;
    }

    li:hover label.label.label-popover {
        display: inline-block;
    }
</style>