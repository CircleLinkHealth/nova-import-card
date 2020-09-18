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
        <div class="col-xs-12 text-center" v-if="!socialService || socialService.instructions.length === 0">
            No Instructions at this time
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul v-if="socialService && socialService.instructions.length > 0">
                    <li v-for="instruction in socialService.instructions.slice(0, 1)" :key="instruction.id" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index" v-html="chunk || '<br>'"></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import MiscModal from './modals/misc.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'social-services',
        props: [
            'patient-id',
            'url',
            'misc-id'
        ],
        components: {
            'misc-modal': MiscModal
        },
        mixins: [ CareplanMixin ],
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
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/misc/${this.miscId}`)).then(response => {
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
            this.socialService = this.setupSocialService(this.careplan().misc.find(m => m.id == this.miscId))

            Event.$on('misc:change', (misc) => {
                if (misc && parseInt(misc.id) === parseInt(this.miscId)) {
                    this.socialService = this.setupSocialService(misc)
                    if (this.socialService) Event.$emit('misc:select', this.socialService)
                }
            })

            Event.$on('misc:remove', (id) => {
                if (id && id === ((this.socialService || {}).id || this.miscId)) {
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