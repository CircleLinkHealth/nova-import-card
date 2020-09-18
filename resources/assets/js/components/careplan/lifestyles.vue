<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    We are Informing You About:
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="col-xs-12 text-center" v-if="patientLifestyles.length === 0">
            No Lifestyles at this time
        </div>
        <div class="row gutter" v-if="patientLifestyles.length > 0">
            <div class="col-xs-12">
                <ul class="subareas__list">
                    <li class='subareas__item inline-block col-xs-6 col-sm-4 print-row top-20' 
                        v-for="(lifestyle, index) in patientLifestyles" :key="index">
                        {{lifestyle.name}}
                    </li>
                </ul>
            </div>
        </div>
        <lifestyles-modal ref="lifestylesModal" :patient-id="patientId" :lifestyles="lifestyles"></lifestyles-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import LifestylesModal from './modals/lifestyles.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'lifestyles',
        props: [
            'patient-id'
        ],
        components: {
            'lifestyles-modal': LifestylesModal
        },
        mixins: [ CareplanMixin ],
        data() {
            return {
                 lifestyles: []
            }
        },
        computed: {
            patientLifestyles() {
                return this.lifestyles.filter(lifestyle => lifestyle.selected)
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupLifestyle(lifestyle) {
                lifestyle.selected = false
                lifestyle.loaders = {
                    removeLifestyle: null,
                    addLifestyle: null
                }
                return lifestyle
            },
            getLifestyles(page) {
                if (!page) {
                    this.lifestyles = []
                    page = 1
                }
                return this.axios.get(rootUrl(`api/lifestyles?page=${page}`)).then(response => {
                    const pagination = response.data
                    console.log('lifestyles:get-lifestyles', pagination)
                    pagination.data.map(this.setupLifestyle).forEach(lifestyle => {
                        this.lifestyles.push(lifestyle)
                    })
                    this.lifestyles.sort((a, b) => a.name > b.name ? 1 : -1)
                    if (pagination.to < pagination.total) return this.getLifestyles(page + 1)
                }).catch(err => {
                    console.error('lifestyles:get-lifestyles', err)
                })
            },
            getPatientLifestyles() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/lifestyles`)).then(response => {
                    console.log('lifestyles:get-patient-lifestyles', response.data)
                    const lifestyleIDs = response.data.map(lifestyle => lifestyle.id)
                    this.lifestyles.forEach(lifestyle => {
                        lifestyle.selected = lifestyleIDs.includes(lifestyle.id)
                    })
                }).catch(err => {
                    console.error('lifestyles:get-patient-lifestyles', err)
                })
            },
            showModal() {
                Event.$emit('modal-lifestyles:show')
            }
        },
        mounted() {
            const lifestyleIDs = this.careplan().lifestyles.map(lifestyle => lifestyle.id)
            this.lifestyles = this.careplan().allLifestyles.map(this.setupLifestyle).map((lifestyle) => {
                lifestyle.selected = lifestyleIDs.includes(lifestyle.id)
                return lifestyle
            })

            Event.$on('problems:updated', this.getPatientLifestyles.bind(this))
            Event.$on('lifestyles:select', (id) => {
                const lifestyle = this.lifestyles.find(lifestyle => lifestyle.id === id)
                if (lifestyle) {
                    lifestyle.loaders.addLifestyle = true
                    return this.axios.post(rootUrl(`api/patients/${this.patientId}/lifestyles`), { lifestyleId: id }).then((response) => {
                        console.log('lifestyles:select', response.data)
                        lifestyle.selected = true
                        lifestyle.loaders.addLifestyle = false
                    }).catch(err => {
                        console.error('lifestyles:select', err)
                        lifestyle.loaders.addLifestyle = false
                    })
                }
            })

            Event.$on('lifestyles:remove', (id) => {
                const lifestyle = this.lifestyles.find(lifestyle => lifestyle.id === id)
                if (lifestyle) {
                        lifestyle.loaders.removeLifestyle = true
                    return this.axios.delete(rootUrl(`api/patients/${this.patientId}/lifestyles/${id}`)).then((response) => {
                        console.log('lifestyles:remove', response.data)
                        lifestyle.selected = false
                        lifestyle.loaders.removeLifestyle = false
                    }).catch(err => {
                        console.error('lifestyles:remove', err)
                        lifestyle.loaders.removeLifestyle = false
                    })
                }
            })
        }
    }
</script>

<style>
    
</style>