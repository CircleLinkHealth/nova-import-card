<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Allergies
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="col-xs-12 text-center" v-if="allergies.length === 0">
            No Allergies at this time
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul v-if="allergies && allergies.length > 0">
                    <li v-for="(allergy, index) in allergies" :key="index">
                        <p>{{allergy.name}}</p>
                    </li>
                </ul>
            </div>
        </div>
        <allergies-modal ref="allergiesModal" :patient-id="patientId" :allergies="allergies"></allergies-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import AllergiesModal from './modals/allergies.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'allergies',
        props: [
            'patient-id',
            'url'
        ],
        components: {
            'allergies-modal': AllergiesModal
        },
        mixins: [ CareplanMixin ],
        data() {
            return {
                 allergies: []
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupAllergy(allergy) {
                return allergy
            },
            getAllergies() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/allergies`)).then(response => {
                    console.log('allergies:get-allergies', response.data)
                    this.allergies = response.data.map(this.setupAllergy)
                }).catch(err => {
                    console.error('allergies:get-allergies', err)
                })
            },
            showModal() {
                Event.$emit('modal-allergies:show')
            }
        },
        mounted() {
            this.allergies = (this.careplan().allergies || []).map(this.setupAllergy)

            Event.$on('allergies:add', (allergy) => {
                if (allergy) this.allergies.push(this.setupAllergy(allergy))
            })

            Event.$on('allergies:remove', (id) => {
                const index = this.allergies.findIndex(allergy => allergy.id == id)
                if (index >= 0) this.allergies.splice(index, 1)
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