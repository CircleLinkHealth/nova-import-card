<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Other Notes
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="col-xs-12 text-center" v-if="!other || other.instructions.length === 0">
            No Instructions at this time
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul v-if="other && other.instructions.length > 0">
                    <li v-for="(instruction, index) in other.instructions.slice(0, 1)" :key="index" v-if="instruction.name">
                        <p v-for="(chunk, index) in instruction.name.split('\n')" :key="index" v-html="chunk || '<br>'"></p>
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
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'others',
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
                 other: {
                     instructions: []
                 }
            }
        },
        methods: {
            setupOther(other) {
                if (other) {
                    other.instructions = other.instructions || []
                }
                return other
            },
            getOther() {
                const existsOnWindowObject = this.getOtherFromWindow()

                if (!existsOnWindowObject) {
                    this.axios.get(rootUrl(`api/patients/${this.patientId}/misc/${this.miscId}`)).then(response => {
                        console.log('others:get-other', response.data)
                        this.other = this.setupOther(response.data)
                        if (this.other) Event.$emit('misc:select', this.other)
                    }).catch(err => {
                        console.error('others:get-other', err)
                    })
                }
            },
            showModal() {
                Event.$emit('modal-misc:show')

                setTimeout(() => Event.$emit('misc:page', 'Other'), 5)
            },
            getOtherFromWindow() {
                const other = this.careplan().other || null

                if (null !== other) {
                    this.other.instructions = other;
                    return true
                }

                return false
            }
        },
        mounted() {
            this.getOther()

            Event.$on('misc:change', (misc) => {
                if (misc && parseInt(misc.id) === parseInt(this.miscId)) {
                    this.other = this.setupOther(misc)
                    if (this.other) Event.$emit('misc:select', this.other)
                }
            })

            Event.$on('misc:remove', (id) => {
                if (id && id === ((this.other || {}).id || this.miscId)) {
                    this.other = null
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