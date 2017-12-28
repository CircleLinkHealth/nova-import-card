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
                <slot v-if="problems.length === 0"></slot>
                <ul class="subareas__list">
                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row' v-for="(problem, index) in problems" :key="index">{{problem.name}}</li>
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
            },
            showModal() {
                Event.$emit('modal-care-areas:show')
            }
        },
        mounted() {
            this.getProblems()
        }
    }
</script>

<style>
    
</style>