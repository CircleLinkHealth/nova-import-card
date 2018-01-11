<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Social Services
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <slot v-if="socialService.instructions.length === 0">
            <div class="col-xs-12 text-center">
                No Instructions at this time
            </div>
        </slot>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul v-if="socialService.instructions.length > 0">
                    <li v-for="(instruction, index) in socialService.instructions" :key="index" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index">{{chunk}}</p>
                    </li>
                </ul>
            </div>
        </div>
        <misc-modal ref="instructionsModal" :patient-id="patientId"></misc-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import MiscModal from './modals/misc.modal'

    export default {
        name: 'social-services',
        props: [
            'patient-id',
            'url'
        ],
        components: {
            'misc-modal': MiscModal
        },
        data() {
            return {
                 socialService: {
                     instructions: []
                 }
            }
        },
        methods: {
            setupSocialService(socialService) {
                return socialService
            },
            getSocialService() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/misc/7`)).then(response => {
                    console.log('social-services:get-social-service', response.data)
                    this.socialService = this.setupSocialService(response.data)
                }).catch(err => {
                    console.error('social-services:get-social-service', err)
                })
            },
            showModal() {
                Event.$emit('modal-misc:show')
            }
        },
        mounted() {
            this.getSocialService()
        }
    }
</script>

<style>
    li.list-square {
        list-style-type: square;
    }

    .font-18 {
        font-size: 18px;
    }
</style>