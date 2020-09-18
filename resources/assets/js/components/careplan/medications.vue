<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">
                    <a :href="url">Medications</a>
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                    <span class="btn btn-primary" @click="toggleShowAll" style="margin-top: 6px; float: right;">
                        {{showAll ? 'Show Active Only' : 'Show Active & Inactive'}}
                    </span>
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
                <ul>
                    <li class="top-10"
                        v-for="(medication, index) in medicationsFiltered"
                        :key="index"
                        :class="{ 'not-active': !medication.active }">

                        <h4 v-if="medication.name">{{medication.name}}
                            <label class="label label-secondary" v-if="medication.group().name">{{medication.group().name}}</label>
                        </h4>
                        <h4 v-if="!medication.name">- {{medication.sig}}
                            <label class="label label-primary" v-if="medication.group().name">{{medication.group().name}}</label>
                        </h4>
                        <ul class="font-18" v-if="medication.name && medication.sig">
                            <li v-for="(sig, index) in medication.sig.split('\n')" class="list-square" :key="index">
                                {{sig}}
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <medications-modal ref="medicationsModal" :patient-id="patientId" :medications="medications"
                           :groups="groups"></medications-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import {Event} from 'vue-tables-2'
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
        mixins: [CareplanMixin],
        computed: {
            medicationsFiltered() {
                return this.showAll ? this.medications.slice(0) : this.medications.filter(m => m.active);
            },
            patientGroups() {
                return this.medications.map(m => this.groups.find(g => g.id == m.medication_group_id)).filter(Boolean)
            }
        },
        data() {
            return {
                showAll: false,
                medications: [],
                groups: []
            }
        },
        methods: {
            toggleShowAll() {
                this.showAll = !this.showAll;
            },
            setupDates(obj) {
                obj.created_at = new Date(obj.created_at)
                obj.updated_at = new Date(obj.updated_at)
                return obj
            },
            setupMedication(medication) {
                medication.activeBool = medication.active === 1;
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
                    //console.log('medications:get-medications', pagination)
                    this.medications = [...new Set(this.medications.concat(pagination.data.map(this.setupMedication)).distinct(medication => medication.id).sort(function(a, b) {
                        var nameA = (a.name || '').toUpperCase();
                        var nameB = (b.name || '').toUpperCase();
                        if (nameA < nameB) {
                            return -1;
                        }
                        if (nameA > nameB) {
                            return 1;
                        }

                        // names must be equal
                        return 0;
                    }))]
                    if (pagination.next_page_url) return this.getMedications(page + 1)
                }).catch(err => {
                    console.error('medications:get-medications', err)
                })
            },
            getMedicationGroups() {
                return this.axios.get(rootUrl(`api/medication/groups`)).then(response => {
                    // console.log('medications:get-medication-groups', response.data)
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

    .not-active {
        opacity: 0.5;
    }

    .toggle.btn:not(.off) {
        border: 1px solid #349686;
    }
</style>