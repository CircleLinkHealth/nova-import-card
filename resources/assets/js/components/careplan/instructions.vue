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
                    <li v-for="(instruction, index) in problem.instructions" :key="index" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index">{{chunk}}</p>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row gutter" v-if="ccdProblems">
            <div class="col-xs-12">
                <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">Full Conditions List:
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showFullConditionsModal" aria-hidden="true"></span>
                </h3>
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
        <full-conditions-modal ref="fullConditionsModal" :patient-id="patientId" :problems="ccdProblems"></full-conditions-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import FullConditionsModal from './modals/full-conditions.modal'

    export default {
        name: 'instructions',
        props: [
            'patient-id'
        ],
        components: {
            'full-conditions-modal': FullConditionsModal
        },
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
            },
            showFullConditionsModal() {
                Event.$emit('modal-full-conditions:show')
            }
        },
        mounted() {
            this.getCcdProblems()

            Event.$on('care-areas:problems', (problems) => {
                this.cpmProblems = problems.filter(problem => problem.instructions && problem.instructions.length > 0)
            })

            Event.$on('full-conditions:add', (ccdProblem) => {
                if (ccdProblem) this.ccdProblems.push(ccdProblem)
            })

            Event.$on('full-conditions:remove', (id) => {
                const index = this.ccdProblems.findIndex(problem => problem.id === id)
                this.ccdProblems.splice(index, 1)
            })
        }
    }
</script>

<style>
    
</style>