<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    <a :href="url">Allergies</a>
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <slot v-if="allergies.length === 0">
            <div class="col-xs-12 text-center">
                No Allergies at this time
            </div>
        </slot>
        <div class="row gutter">
            <div class="col-xs-12">
                <ul class="subareas__list" v-if="allergies && allergies.length > 0">
                    <li class='subareas__item inline-block col-xs-6 col-sm-3 print-row' 
                        v-for="(allergy, index) in allergies" :key="index">
                        {{allergy.name}}
                    </li>
                </ul>
            </div>
        </div>
        <allergies-modal ref="allergiesModal" :patient-id="patientId" :allergies="allergies"></allergies-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import AllergiesModal from './modals/allergies.modal'

    export default {
        name: 'allergies',
        props: [
            'patient-id',
            'url'
        ],
        components: {
            'allergies-modal': AllergiesModal
        },
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
            this.getAllergies()

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