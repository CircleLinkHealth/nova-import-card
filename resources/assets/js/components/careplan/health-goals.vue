<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health Goals
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <slot v-if="goals.length === 0"></slot>
                <div class="text-center" v-if="!goals || goals.length === 0">No Health Goals at this time</div>
                <ul class="subareas__list" v-if="goals && goals.length > 0">
                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row' v-for="(goal, index) in goals" :key="index">{{goal.name}}</li>
                </ul>
            </div>
        </div>
        <health-goals-modal ref="healthGoalsModal" :patient-id="patientId" :goals="goals"></health-goals-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import HealthGoalsModal from './modals/health-goals.modal'

    export default {
        name: 'care-areas',
        props: [
            'patient-id'
        ],
        components: {
            'health-goals-modal': HealthGoalsModal
        },
        data() {
            return {
                goals: []
            }
        },
        methods: {
            getgoals() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/biometrics`)).then(response => {
                    console.log('health-goals:get-goals', response.data)
                }).catch(err => {
                    console.error('health-goals:get-goals', err)
                })
            },
            showModal() {
                Event.$emit('modal-health-goals:show')
            }
        },
        mounted() {
            this.getgoals()

            Event.$on('health-goals:goals', (goals) => {
                this.goals = goals
            })
        }
    }
</script>

<style>
    
</style>