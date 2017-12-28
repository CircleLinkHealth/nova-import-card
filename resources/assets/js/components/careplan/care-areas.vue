<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">We Are Managing
                    <span class="btn btn-primary glyphicon glyphicon-edit" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <slot v-if="problems.length === 0"></slot>
                <ul class="subareas__list">
                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row' v-for="(problem, index) in problems" :key="index">{{problem.name}}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'

    export default {
        name: 'care-areas',
        props: [
            'patient-id'
        ],
        data() {
            return {
                problems: []
            }
        },
        methods: {
            getProblems() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/problems`)).then(response => {
                    console.log('care-areas:get-problems', response.data)
                    this.problems = response.data
                }).catch(err => {
                    console.error('care-areas:get-problems', err)
                })
            }
        },
        mounted() {
            this.getProblems()
        }
    }
</script>

<style>
    
</style>