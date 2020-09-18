<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    Watch out for
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="col-xs-12 text-center" v-if="patientSymptoms.length === 0">
            No Symptoms at this time
        </div>
        <div class="row gutter" v-if="patientSymptoms.length > 0">
            <div class="col-xs-12">
                <ul class="subareas__list">
                    <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row top-20' 
                        v-for="(symptom, index) in patientSymptoms" :key="index">
                        {{symptom.name}}
                    </li>
                </ul>
            </div>
        </div>
        <symptoms-modal ref="symptomsModal" :patient-id="patientId" :symptoms="symptoms"></symptoms-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import SymptomsModal from './modals/symptoms.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'symptoms',
        props: [
            'patient-id'
        ],
        components: {
            'symptoms-modal': SymptomsModal
        },
        mixins: [ CareplanMixin ],
        data() {
            return {
                 symptoms: []
            }
        },
        computed: {
            patientSymptoms() {
                return this.symptoms.filter(symptom => symptom.selected)
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupSymptom(symptom) {
                symptom.selected = false
                symptom.loaders = {
                    removeSymptom: null,
                    addSymptom: null
                }
                return symptom
            },
            getSymptoms(page) {
                if (!page) {
                    this.symptoms = []
                    page = 1
                }
                return this.axios.get(rootUrl(`api/symptoms?page=${page}`)).then(response => {
                    const pagination = response.data
                    // console.log('symptoms:get-symptoms', pagination)
                    pagination.data.map(this.setupSymptom).forEach(symptom => {
                        this.symptoms.push(symptom)
                    })
                    this.symptoms.sort((a, b) => a.name > b.name ? 1 : -1)
                    if (pagination.next_page_url) return this.getSymptoms(page + 1)
                }).catch(err => {
                    console.error('symptoms:get-symptoms', err)
                })
            },
            getPatientSymptoms() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/symptoms`)).then(response => {
                    // console.log('symptoms:get-patient-symptoms', response.data)
                    const symptomIDs = response.data.map(symptom => symptom.id)
                    this.symptoms.forEach(symptom => {
                        symptom.selected = symptomIDs.includes(symptom.id)
                    })
                }).catch(err => {
                    console.error('symptoms:get-patient-symptoms', err)
                })
            },
            showModal() {
                Event.$emit('modal-symptoms:show')
            }
        },
        mounted() {
            const symptomIDs = this.careplan().symptoms.map(symptom => symptom.id)
            this.symptoms = this.careplan().allSymptoms.map(this.setupSymptom).map(symptom => {
                symptom.selected = symptomIDs.includes(symptom.id)
                return symptom
            })

            Event.$on('problems:updated', this.getPatientSymptoms.bind(this))
            Event.$on('symptoms:select', (id) => {
                const symptom = this.symptoms.find(symptom => symptom.id === id)
                if (symptom) {
                    symptom.loaders.addSymptom = true
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/symptoms`), { symptomId: id }).then((response) => {
                        console.log('symptoms:select', response.data)
                        symptom.selected = true
                        symptom.loaders.addSymptom = false
                    }).catch(err => {
                        console.error('symptoms:select', err)
                        symptom.loaders.addSymptom = false
                    })
                }
            })

            Event.$on('symptoms:remove', (id) => {
                const symptom = this.symptoms.find(symptom => symptom.id === id)
                if (symptom) {
                        symptom.loaders.removeSymptom = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/symptoms/${id}`)).then((response) => {
                        console.log('symptoms:remove', response.data)
                        symptom.selected = false
                        symptom.loaders.removeSymptom = false
                    }).catch(err => {
                        console.error('symptoms:remove', err)
                        symptom.loaders.removeSymptom = false
                    })
                }
            })
        }
    }
</script>

<style>
    
</style>