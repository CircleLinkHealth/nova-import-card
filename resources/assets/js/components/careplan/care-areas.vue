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
                
                <ul class="subareas__list" v-if="cpmProblems && cpmProblems.length > 0">
                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row' 
                        v-for="(problem, index) in cpmProblems" :key="index">
                        {{problem.name}}
                    </li>
                </ul>
            </div>
            <div class="col-xs-12" v-if="ccdProblems && ccdProblems.length > 0">
                <h2 class="color-blue">Other Conditions</h2>
                
                <ul class="font-18 row">
                    <li class='top-10 col-sm-6' 
                        v-for="(problem, index) in ccdProblems" :key="index">
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
        }
    }
</script>

<style>
    .color-blue {
        color: #109ace;
    }
</style>