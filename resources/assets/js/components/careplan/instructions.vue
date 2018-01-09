<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Follow these instructions
                </h2>
            </div>
        </div>
        <slot v-if="cpmProblems.length === 0">
            <div class="col-xs-12 text-center">
                No Instructions at this time
            </div>
        </slot>
        <div class="row gutter" v-if="cpmProblems.length > 0">
            <div class="col-xs-12" v-for="(problem, index) in cpmProblems" :key="index">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">For {{problem.name}}:</h3>
                <ul v-if="problem.instructions">
                    <li v-for="(instruction, index) in problem.instructions" :key="index">
                        <p v-if="instruction.name" v-for="(chunk, index) in instruction.name.split('\n')">{{chunk}}</p>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row gutter" v-if="ccdProblems">
            <div class="col-xs-12">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">Full Conditions List:</h3>
                <p v-if="ccdProblems.length === 0">
                    No instructions at this time
                </p>
                <ul>
                    <li v-for="(problem, index) in ccdProblems" :key="index">
                        <p>{{problem.name}}</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'

    export default {
        name: 'instructions',
        props: [
            'patient-id'
        ],
        data() {
            return {
                 cpmProblems: [],
                 ccdProblems: []
            }
        },
        methods: {
            getCcdProblems() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/problems/ccd`)).then(response => {
                    console.log('instructions:ccd', response.data)
                    this.ccdProblems = response.data
                }).catch(err => console.error('instructions:ccd', err))
            }
        },
        mounted() {
            this.getCcdProblems()

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems.filter(problem => problem.instructions && problem.instructions.length > 0)
            })
        }
    }
</script>

<style>
    
</style>