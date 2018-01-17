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
        <slot v-if="!socialService || socialService.instructions.length === 0">
            <div class="col-xs-12 text-center">
                No Instructions at this time
            </div>
        </slot>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul v-if="socialService && socialService.instructions.length > 0">
                    <li v-for="(instruction, index) in socialService.instructions" :key="index" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index">{{chunk}}</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import MiscModal from './modals/misc.modal'

    const MISC_ID = 5

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
                if (socialService) {
                    socialService.instructions = socialService.instructions || []
                }
                return socialService
            },
            getSocialService() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/misc/${MISC_ID}`)).then(response => {
                    console.log('social-services:get-social-service', response.data)
                    this.socialService = this.setupSocialService(response.data)
                    if (this.socialService) Event.$emit('misc:select', this.socialService)
                }).catch(err => {
                    console.error('social-services:get-social-service', err)
                })
            },
            showModal() {
                Event.$emit('modal-misc:show')

                setTimeout(() => Event.$emit('misc:page', 'Social Services'), 5)
            }
        },
        mounted() {
            this.getSocialService()

            Event.$on('misc:change', (misc) => {
                if (misc && misc.id === ((this.socialService || {}).id || MISC_ID)) {
                    this.socialService = misc
                }
            })

            Event.$on('misc:remove', (id) => {
                if (id && id === ((this.socialService || {}).id || MISC_ID)) {
                    this.socialService = null
                }
            })
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