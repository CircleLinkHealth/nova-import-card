<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    <a :href="url">Medications</a>
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
             <div class="col-xs-12 text-center" v-if="medications.length === 0">
                 <p>
                     No Medications at this time
                 </p>
            </div>
        </div>

        <div class="row gutter" v-if="medications.length > 0">
            <div class="col-xs-12">
                <ul v-if="medications.length">
                    <li class="top-10" v-for="(medication, index) in medications" :key="index">
                        <h4 v-if="medication.name">{{medication.name}} 
                            <label class="label label-secondary" v-if="medication.group().name">{{medication.group().name}}</label>
                        </h4>
                        <h4 v-if="!medication.name">- {{medication.sig}}
                            <label class="label label-primary" v-if="medication.group().name">{{medication.group().name}}</label>
                        </h4>
                        <ul class="font-18" v-if="medication.name && medication.sig">
                            <li v-for="(sig, index) in medication.sig.split('\n')" class="list-square" :key="index">{{sig}}</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <medications-modal ref="medicationsModal" :patient-id="patientId" :medications="medications" :groups="groups"></medications-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import { Event } from 'vue-tables-2'
    import MedicationsModal from './modals/medications.modal'
    import CareplanMixin from './mixins/careplan.mixin'

    export default {
        name: 'medications',
        props: [
            'patient-id',
            'url'
        ],
        components: {
            'medications-modal': MedicationsModal
        },
        mixins: [ CareplanMixin ],
        computed: {
            patientGroups() {
                return this.medications.map(m => this.groups.find(g => g.id == m.medication_group_id)).filter(Boolean)
            }
        },
        data() {
            return {
                 medications: [],
                 groups: []
            }
        },
        methods: {
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupMedication(medication) {
                medication.title = () => (medication.name || (medication.sig ? medication.sig.split('\n')[0] : 'No Title'))
                medication.name = medication.name || ''
                medication.group = () => (this.groups.find(g => g.id == medication.medication_group_id) || {})
                medication.groupName = (this.groups.find(g => g.id == medication.medication_group_id) || {}).name || 'Select a Medication Type'
                return medication
            },
            getMedications(page) {
                if (!page) {
                    this.medications = []
                    page = 1
                }
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/medication?page=${page}`)).then(response => {
                    const pagination = response.data
                    console.log('medications:get-medications', pagination)
                    this.medications = this.medications.concat(pagination.data.map(this.setupMedication))
                    if (pagination.next_page_url) return this.getMedications(page + 1)
                }).catch(err => {
                    console.error('medications:get-medications', err)
                })
            },
            getMedicationGroups() {
                return this.axios.get(rootUrl(`api/medication/groups`)).then(response => {
                    console.log('medications:get-medication-groups', response.data)
                    this.groups = response.data || []
                }).catch(err => {
                    console.error('medications:get-medication-groups', err)
                })
            },
            showModal() {
                Event.$emit('modal-medications:show')
            }
        },
        mounted() {
            this.groups = this.careplan().medicationGroups
            this.medications = this.careplan().medications.map(this.setupMedication)
            this.getMedications(2)

            Event.$on('problems:updated', this.getMedicationGroups.bind(this))
            
            Event.$on('medication:remove', (id) => {
                this.medications = this.medications.filter((medication) => medication.id != id)
            })
            Event.$on('medication:add', (medication) => {
                this.medications.push(this.setupMedication(medication))
            })
            Event.$on('medication:edit', (medication) => {
                this.medications = this.medications.map(m => (m.id != medication.id) ? m : this.setupMedication(medication))
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

    .top-10 {
        margin-top: 10px;
    }

    .label-primary {
        background-color: #109ace;
    }

    .label-secondary {
        background-color: #47beab;
    }
</style>