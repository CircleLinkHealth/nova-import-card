<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Managing
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <slot v-if="cpmProblems.length === 0">
                    <div class="text-center" v-if="!cpmProblems || cpmProblems.length === 0">No Problems at this time</div>
                </slot>
                
                <ul class="subareas__list font-22" v-if="cpmProblems && cpmProblems.length > 0">
                    <li class='subareas__item inline-block col-sm-6 print-row' 
                        v-for="(problem, index) in cpmProblems" :key="index">
                        {{problem.name}}
                    </li>
                    <li class='subareas__item inline-block col-sm-6 print-row' 
                        v-for="(problem, index) in ccdMonitoredProblems" :key="index">
                        {{problem.name}}
                    </li>
                </ul>
            </div>
            <div class="col-xs-12" v-if="ccdProblems && ccdProblems.length > 0">
                <h2 class="color-blue">Other Conditions</h2>
                
                <ul class="font-22 row">
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
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import CareAreasModal from './modals/care-areas.modal'

    export default {
        name: 'care-areas',
        props: [
            'patient-id'
        ],
        components: {
            'care-areas-modal': CareAreasModal
        },
        data() {
            return {
                cpmProblems: [],
                ccdProblems: []
            }
        },
        computed: {
            problems() {
                return [ ...this.cpmProblems, ...this.ccdProblems ]
            },
            ccdMonitoredProblems() {
                return this.ccdProblems.filter(problem => problem.is_monitored)
            },
            ccdProblemsForListing() {
                return this.ccdProblems.filter(problem => !problem.is_monitored && !this.cpmProblems.find(cpm => (cpm.name == problem.name) || (cpm.id == problem.cpm_id)))
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupCpmProblem(problem) {
                problem.instruction = this.setupDates(problem.instruction || {
                    name: null
                })
                problem.type = 'cpm'
                return problem
            },
            getCpmProblems() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/problems/cpm`)).then(response => {
                    console.log('care-areas:get-problems', response.data)
                    this.cpmProblems = response.data.map(this.setupCpmProblem)
                    Event.$emit('care-areas:problems', this.cpmProblems)
                }).catch(err => {
                    console.error('care-areas:get-problems', err)
                })
            },
            showModal() {
                Event.$emit('modal-care-areas:show')
            }
        },
        mounted() {
            this.getCpmProblems()

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems.map(this.setupCpmProblem)
            })

            Event.$on('care-areas:ccd-problems', (problems) => {
                this.ccdProblems = problems
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
            })

            Event.$on('care-areas:remove-ccd-problem', (id) => {
                this.ccdProblems = this.ccdProblems.filter(problem => problem.id != id)
            })
        }
    }
</script>

<style>
    .color-blue {
        color: #109ace;
    }

    .font-22 {
        font-size: 22px;
    }
</style>