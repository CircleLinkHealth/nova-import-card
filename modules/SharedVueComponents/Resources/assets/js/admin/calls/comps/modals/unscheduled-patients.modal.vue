<template>
    <modal name="unscheduled-patients" :no-footer="true" :info="unscheduledPatientsModalInfo"
           class-name="modal-show-unscheduled">
        <template slot-scope="props" slot="title">
            <div class="row">
                <div :class="{ 'col-sm-12': !loaders.patients, 'col-sm-11': loaders.patients }">
                    <v-select class="form-control" v-model="selectedPracticeData"
                              :options="practicesForSelect" :on-change="changePractice"></v-select>
                    <loader v-if="loaders.patients"></loader>
                </div>
            </div>
        </template>
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-center" v-if="!patients.length">
                        Looks like every patient has scheduled calls this month
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <v-client-table ref="unscheduledPatients" :data="patients" :columns="columns"
                                            :options="options">
                                <template slot="name" slot-scope="props">
                                    <a class="pointer"
                                       @click="triggerParentFilter(props.row.id, props.row.name, props.row.program_id)">{{props.row.name}}</a>
                                </template>
                            </v-client-table>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader'
    import {rootUrl} from '../../../../app.config'
    import {Event} from 'vue-tables-2'
    import VueCache from '../../../../util/vue-cache'
    import VueSelect from 'vue-select'

    const UNASSIGNED_VALUE = {label: 'Unassigned', value: null}

    export default {
        name: 'unscheduled-patients-modal',
        mixins: [VueCache],
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            'v-select': VueSelect
        },
        data() {
            return {
                unscheduledPatientsModalInfo: {},
                /**loaders and errors */
                errors: {
                    practices: null,
                    patients: null
                },
                loaders: {
                    practices: false,
                    patients: false
                },
                practices: [],
                patients: [],
                practiceId: null,
                practiceName: null,
                columns: ['id', 'name'],
                options: {
                    filterable: false
                },
                selectedPracticeData: UNASSIGNED_VALUE
            }
        },
        computed: {
            practicesForSelect() {
                return [UNASSIGNED_VALUE, ...this.practices.map(practice => ({
                    label: practice.display_name,
                    value: practice.id
                }))]
            },
            patientUrl() {
                const practice_addendum = this.practiceId ? `practices/${this.practiceId}/` : '';
                return rootUrl(`api/${practice_addendum}patients/without-scheduled-activities?autocomplete=true`);
            }
        },
        methods: {
            changePractice(practice) {
                if (practice) {
                    this.practiceId = practice.value;
                    this.practiceName = practice.label;
                    return this.getPatients();
                }
            },
            getPractices() {
                this.loaders.practices = true
                this.axios.get(rootUrl(`api/practices?admin-only=true`)).then(response => {
                    this.loaders.practices = false
                    this.practices = (response.data || []).distinct(practice => practice.id)
                    console.log('unscheduled-patients-get-practices', response.data)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('unscheduled-patients-get-practices', err)
                })
            },
            getPatients() {
                this.loaders.patients = true
                this.cache().get(this.patientUrl).then(patients => {
                    if (patients.data) {
                        patients = patients.data;
                    }
                    if (!Array.isArray(patients)) {
                        patients = Object.values(patients);
                    }
                    this.loaders.patients = false
                    this.patients = (patients || [])
                    //console.log('unscheduled-patients-get-patients', patients)
                }).catch(err => {
                    this.loaders.patients = false
                    this.errors.patients = err.message
                    console.error('unscheduled-patients-get-patients', err)
                })
            },
            triggerParentFilter(id, name, practiceId) {

                let pId;
                let pName;

                const p = this.practices.find(x => x.id === +practiceId);
                if (p) {
                    pId = p.id;
                    pName = p.display_name;
                }

                Event.$emit('unscheduled-patients-modal:filter', {
                    practiceId: pId,
                    practiceName: pName,
                    patientId: id,
                    patientName: name
                })
            }
        },
        created() {
            Event.$on('actions:add', (action) => {
                //remove patient from list
                const patientId = action.inbound_cpm_id;
                const index = this.patients.findIndex(x => x.id === patientId);
                if (index > -1) {
                    this.patients.splice(index, 1);
                }
            });
        },
        mounted() {
            this.getPractices();
            this.getPatients();
        }
    }
</script>

<style>

    .modal-show-unscheduled .modal-container {
        width: 600px;
    }

    div.loader {
        position: absolute;
        right: -25px;
        top: 2px;
    }

    .pointer {
        cursor: pointer;
    }
</style>
