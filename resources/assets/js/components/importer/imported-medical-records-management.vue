<template>
    <div>
        <notifications>
            <template slot-scope="props">
                <a :href="props.note.href" target="_blank" v-if="props.note.href">{{props.note.message}}</a>
                <span v-if="!props.note.href">
                   {{props.note.message}}
                   <a :href="props.note.link.href" target="_blank" v-if="props.note.link">{{props.note.link.text}}</a>
               </span>
            </template>
        </notifications>


        <div v-if="loaders.records">
            <center>
                <loader></loader>
            </center>
        </div>

        <v-client-table ref="ccdRecords" :data="tableData" :columns="columns" :options="options" v-cloak>
            <template slot="selected" slot-scope="props">
                <input class="row-select" v-model="props.row.selected" @change="select($event, props.row.id)"
                       type="checkbox"/>
            </template>
            <template slot="h__selected">
                <input class="row-select" v-model="selected" @change="toggleAllSelect" type="checkbox"/>
            </template>
            <template slot="Name" slot-scope="props">
                <a v-if="props.row.patient_id" :href="linkToCarePlan(props.row.patient_id)" target="_blank">
                    {{props.row.Name}}
                </a>
                <p v-else>{{props.row.Name}}</p>
            </template>
            <template slot="Practice" slot-scope="props">
                <v-select
                        v-model="props.row.Practice"
                        @input="props.row.changePractice(props.row.Practice)"
                        :options="props.row.practices()"
                        class="form-control"
                        required
                >
                </v-select>

            </template>
            <template slot="Location" slot-scope="props">
                <div v-if="props.row.loaders.locations">
                    <div class="placeholder-dropdown">
                        <loader></loader>
                    </div>
                </div>

                <v-select v-else
                          v-model="props.row.Location"
                          @input="props.row.changeLocation(props.row.Location)"
                          :options="props.row.locations"
                          :disabled="! props.row.Practice"
                          class="form-control">
                </v-select>
            </template>
            <template slot="Billing Provider" slot-scope="props">
                <div v-if="props.row.loaders.providers">
                    <div class="placeholder-dropdown">
                        <loader></loader>
                    </div>
                </div>

                <v-select v-else
                          v-model="props.row['Billing Provider']"
                          @input="props.row.changeProvider(props.row['Billing Provider'])"
                          :options="props.row.providers"
                          :disabled="! props.row.Location"
                          class="form-control">
                </v-select>
            </template>
            <template v-if="isAdmin" slot="Care Coach" slot-scope="props">
                <div v-if="props.row.loaders.nurses">
                    <div class="placeholder-dropdown">
                        <loader></loader>
                    </div>
                </div>

                <v-select v-else
                          v-model="props.row['Care Coach']"
                          @input="props.row.changeNurse(props.row['Care Coach'])"
                          :options="props.row.nurses"
                          :disabled="! props.row.Practice"
                          class="form-control">
                </v-select>
            </template>
            <template slot="2+ CCM Cond" slot-scope="props">
                <input class="row-select" v-model="props.row['2+ CCM Cond']" type="checkbox" disabled/>
            </template>
            <template slot="1+ BHI Cond" slot-scope="props">
                <input class="row-select" v-model="props.row['1+ BHI Cond']" type="checkbox" disabled/>
            </template>
            <template slot="Medicare" slot-scope="props">
                <input class="row-select" v-model="props.row.Medicare" type="checkbox" disabled/>
            </template>
            <template slot="duplicate" slot-scope="props">
                <a :href="rootUrl(`manage-patients/${props.row.duplicate_id}/view-careplan`)" target="_blank"
                   v-if="props.row.duplicate_id">View</a>
            </template>
            <template slot="h__Remove">
                <input class="btn btn-danger btn-round" v-if="multipleSelected" @click="deleteMultiple" type="button"
                       value="x"/>
            </template>
            <template slot="Remove" slot-scope="props">
                <input class="btn btn-danger btn-round" :class="{ 'btn-gray': multipleSelected }" type="button"
                       @click="deleteOne(props.row.id)" value="x"/>
                <div v-if="props.row.loaders.delete">
                    <loader></loader>
                </div>
            </template>
            <template slot="h__Submit">
                <input class="btn btn-success btn-round" type="button" v-if="multipleSelected" @click="submitMultiple"
                       value="✔"/>
            </template>
            <template slot="Submit" slot-scope="props">
                <input class="btn btn-default btn-round" v-if="!props.row.loaders.confirm"
                       :class="{ 'btn-gray': multipleSelected }" type="button" @click="submitOne(props.row.id)"
                       value="✔" :disabled="!props.row.validate()"/>
                <div v-if="props.row.loaders.confirm">
                    <loader></loader>
                </div>
                <error-modal-button :errors="getRowErrors(props.row.id)" name="confirm"></error-modal-button>
            </template>
        </v-client-table>
        <error-modal ref="errorModal"></error-modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import TextEditable from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/calls/comps/text-editable'
    import EventBus from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/time-tracker/comps/event-bus'
    import LoaderComponent from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader'
    import ErrorModal from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/billing/comps/error-modal'
    import ErrorModalButton from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/admin/billing/comps/error-modal-button'
    import NotificationComponent from '../notifications'
    import VueCache from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/util/vue-cache'
    import {mapGetters} from 'vuex'
    import {currentUser} from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/store/getters';
    import VueSelect from "vue-select";
    import GetsNurses from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/mixins/gets-nurses'
    import moment from "moment";

    export default {
        name: 'imported-medical-records-management',
        mixins: [
            GetsNurses,
            VueCache,
        ],
        components: {
            'v-select': VueSelect,
            'text-editable': TextEditable,
            'loader': LoaderComponent,
            'error-modal': ErrorModal,
            'error-modal-button': ErrorModalButton,
            'notifications': NotificationComponent
        },
        data() {
            return {
                url: rootUrl('api/ccd-importer/imported-medical-records'),
                selected: false,
                tableData: [],
                options: {
                    sortable: ['Name', 'DOB']
                },
                practices: [],
                errors: {
                    delete: null,
                    confirm: null
                },
                loaders: {
                    delete: false,
                    confirm: false,
                    records: false
                }
            }
        },
        computed: Object.assign(
            mapGetters({
                authUser: 'currentUser'
            }), {
                multipleSelected() {
                    return this.tableData.filter(row => !!row.selected).length > 1
                },
                isAdmin() {
                    return this.authUser.role.name === 'administrator'
                },
                columns() {
                    if (this.isAdmin) {
                        return ['selected', 'Name', 'DOB', 'Practice', 'Location', 'Billing Provider', 'Care Coach', 'duplicate', '2+ CCM Cond', '1+ BHI Cond', 'Medicare', 'Submit', 'Remove']
                    }

                    return ['selected', 'Name', 'DOB', 'Practice', 'Location', 'Billing Provider', 'duplicate', '2+ CCM Cond', '1+ BHI Cond', 'Medicare', 'Submit', 'Remove'];
                }
            }
        ),
        methods: Object.assign(
            {
                rootUrl,
                getRowErrors(id) {
                    return () => this.tableData.find(record => record.id === id).errors
                },
                setupRecord(record) {
                    if(record.dob) {
                        record.dob = moment(record.dob).format('MM-DD-YYYY');
                    }
                    const self = this;
                    const practice = {
                        label: (record.practice || {display_name: ''}).display_name,
                        value: record.practice_id
                    };
                    const location = {
                        label: (record.location || {name: ''}).name,
                        value: (record.location || {}).id
                    };
                    const billingProvider = {
                        label: (record.billing_provider || {display_name: ''}).display_name + ' ' + (record.billing_provider || {suffix: ''}).suffix,
                        value: (record.billing_provider || {}).id
                    };
                    const careCoach = {
                        label: (record.nurse_user || {display_name: ''}).display_name + ' ' + (record.nurse_user || {suffix: ''}).suffix,
                        value: record.nurse_user_id
                    };

                    if (practice.value) {
                        self.changePractice(record.id, practice);
                    }

                    return {
                        id: record.id,
                        patient: record.patient,
                        patient_id: record.patient_id,
                        selected: false,
                        Name: record.display_name,
                        DOB: record.dob,
                        Practice: practice,
                        practice_id: practice.value,
                        location: record.location,
                        Location: location,
                        location_id: location.value,
                        'Billing Provider': billingProvider,
                        billing_provider_id: billingProvider.value,
                        'Care Coach': careCoach,
                        nurse_user_id: careCoach.value,
                        nurse_user: record.nurse_user,
                        '2+ CCM Cond': (record.validation_checks || {}).has_at_least_2_ccm_conditions,
                        '1+ BHI Cond': (record.validation_checks || {}).has_at_least_1_bhi_condition,
                        Medicare: (record.validation_checks || {}).has_medicare,
                        duplicate_id: record.duplicate_id,
                        errors: {
                            delete: null,
                            confirm: null,
                            practices: null,
                            locations: null,
                            nurses: null,
                            providers: null
                        },
                        loaders: {
                            delete: false,
                            confirm: false,
                            practices: false,
                            locations: false,
                            providers: false,
                            nurses: false,
                            update: false
                        },
                        practices: () => self.practices,
                        locations: [],
                        providers: [],
                        nurses: [],
                        changePractice(selectedOption) {
                            self.changePractice(record.id, selectedOption);
                        },
                        changeLocation(selectedOption) {
                            self.changeLocation(record.id, selectedOption);
                        },
                        changeProvider(selectedOption) {
                            self.changeProvider(record.id, selectedOption);
                        },
                        changeNurse(selectedOption) {
                            self.changeNurse(record.id, selectedOption);
                        },
                        validate() {
                            const record = this
                            return (!!record.Practice.value && !!record['Billing Provider'].value && !!record.Location.value)
                        }
                    }
                },
                changePractice(recordId, selectedPractice) {
                    const record = this.tableData.find(row => row.id === recordId);
                    if (!record) {
                        return;
                    }
                    record.Practice = selectedPractice;
                    record.practice_id = selectedPractice.value;

                    record.providers = [];

                    record.Location = {label: null, value: null};
                    record.location_id = null;
                    record.locations = [];
                    record.loaders.locations = true;
                    this.getLocations(selectedPractice.value)
                        .then(locations => {
                            //console.log('get-practice-locations', practiceId, locations)
                            record.locations = locations;
                            record.loaders.locations = false;

                            if (_.isNull(record.Location.value) && 1 === record.locations.length) {
                                record.Location = {label: record.locations[0].name, value: record.locations[0].id};
                            }

                            this.changeLocation(recordId, record.Location)
                        })
                        .catch(err => {
                            record.loaders.locations = false;
                            record.errors.locations = err.message;
                            console.error('get-practice-locations', err)
                        });

                    record['Care Coach'] = {label: null, value: null};
                    record.nurse_user_id = null;
                    record.nurses = [];
                    record.loaders.nurses = true;
                    this.getNurses(true)
                        .then(nurses => {
                            record.loaders.nurses = false;
                            record.nurses = nurses.filter(nurse => (nurse.practices || []).includes(parseInt(selectedPractice.value)))
                                .map(nurse => {
                                    return {
                                        label: nurse.display_name,
                                        value: nurse.id
                                    };
                                });

                            if (_.isNull(record['Care Coach'].value) && 1 === record.nurses.length) {
                                record['Care Coach'] = {
                                    label: record.nurses[0].label,
                                    value: record.nurses[0].value
                                };
                            }
                            this.changeNurse(recordId, record['Care Coach']);
                        })
                        .catch(err => {
                            record.loaders.nurses = false;
                            record.errors.nurses = err.message;
                            console.error('get-nurses', err);
                        });
                },
                changeLocation(recordId, selectedLocation) {
                    const record = this.tableData.find(row => row.id === recordId);
                    if (!record) {
                        return;
                    }
                    record.Location = selectedLocation;
                    record.location_id = selectedLocation.value;

                    if (!selectedLocation.value) {
                        return;
                    }
                    record.providers = [];
                    record.loaders.providers = true;
                    this.getProviders(record.Practice.value, selectedLocation.value).then(providers => {
                        record.providers = providers
                        record.loaders.providers = false
                        if (!(record.providers || []).find(provider => parseInt(provider.id) === parseInt(record.billing_provider_id))) {
                            record['Billing Provider'] = {label: '', value:''};
                            record.billing_provider_id = null
                        }
                        if (_.isNull(record.billing_provider_id) && 1 === record.providers.length) {
                            record['Billing Provider'] = {
                                label: record.providers[0].display_name,
                                value: record.providers[0].id
                            };
                            record.billing_provider_id = record.providers[0].id;
                        }
                        console.log('get-practice-location-providers', providers)
                    }).catch(err => {
                        record.loaders.providers = false
                        record.errors.providers = err.message
                        console.error('get-practice-location-providers', err)
                    });
                },
                changeProvider(recordId, selectedProvider) {
                    const record = this.tableData.find(row => row.id === recordId);

                    if (!record) {
                        return
                    }

                    const provider = record.providers.find(p => p.id === selectedProvider.value);

                    if (provider) {
                        record['Billing Provider'] = selectedProvider;
                        record.billing_provider_id = provider.id
                        return;
                    }

                    record['Billing Provider'] = {label: '', value:''};
                    record.billing_provider_id = null
                },
                changeNurse(recordId, selectedNurse) {
                    if (_.isNull(selectedNurse)) return;

                    const record = this.tableData.find(row => row.id === recordId);
                    if (_.isNull(record.nurse_user_id) && 1 === record.nurses.length) {
                        record['Care Coach'] = {label: record.nurses[0].display_name, value: record.nurses[0].id};
                        record.nurse_user_id = record.nurses[0].id
                    }
                    if (record) {
                        const nurse = this.nurses.find(nurseUser => nurseUser.id === selectedNurse.value);
                        if (nurse) {
                            record['Care Coach'] = {label: nurse.display_name, value: nurse.id};
                            record.nurse_user_id = nurse.id;
                        }
                    }
                },
                updateRecord(recordId) {
                    const record = this.tableData.find(row => row.id === recordId);
                    if (record && record.practice_id && record.location_id && record.billing_provider_id) {
                        const practiceId = record.practice_id;
                        const locationId = record.location_id;
                        const billingProviderId = record.billing_provider_id;

                        record.loaders.update = true
                        this.axios.post(rootUrl('importer/train/store?json'), {
                            imported_medical_record_id: recordId,
                            practiceId,
                            locationId,
                            billingProviderId
                        }).then(response => {
                            record.loaders.update = false
                            console.log('ccd-viewer:update-record', response)
                        }).catch(err => {
                            record.loaders.update = false
                            console.error('ccd-viewer:update-record')
                        })
                    }
                    console.log('update-record', record)
                },
                getRecords() {
                    this.loaders.records = true
                    return this.axios.get(this.url).then((response) => {
                        const records = response.data || []
                        this.tableData = records.map(this.setupRecord)
                        this.tableData.forEach(row => {
                            row.changePractice(row.Practice)
                        })
                        this.loaders.records = false
                        return this.tableData
                    }).catch(err => {
                        console.error(err)
                        this.loaders.records = false
                    })
                },
                select(e, id) {
                    const row = this.tableData.find(row => row.id === id)
                    if (row) {
                        row.selected = e.target.checked
                    }
                },
                deleteOne(id, force) {
                    if (force || confirm('Are you sure you want to delete this record?')) {
                        const record = this.tableData.find(item => item.id === id)
                        record.loaders.delete = true
                        return this.axios.get(rootUrl('api/ccd-importer/records/delete?records=' + id)).then((response) => {
                            record.loaders.delete = false
                            if (Array.isArray(response.data.deleted)) {
                                if (response.data.deleted.some(item => item == id)) {
                                    this.tableData.splice(this.tableData.findIndex(item => item.id === id), 1)
                                } else {
                                    record.errors.delete = 'not found'
                                }
                            } else {
                                record.errors.delete = 'unknown response'
                            }
                            console.log('ccd-viewer:delete-one', id, response.data)
                            return response
                        }).catch((err) => {
                            record.errors.delete = err
                            record.loaders.delete = false
                            console.error('ccd-viewer:delete-one', err)
                        })
                    }
                },
                deleteMultiple() {
                    if (confirm('Multiple: Are you sure you want to delete these records?')) {
                        return Promise.all(this.tableData.filter(record => record.selected).map(record => this.deleteOne(record.id, true))).then(responses => {
                            console.log('ccd-viewer:delete-multiple', responses)
                        }).catch(errors => {
                            console.error('ccd-viewer:delete-multiple', errors)
                        })
                    }
                },
                submitMultiple() {
                    this.errors.confirm = true
                    return Promise.all(this.tableData.filter(record => record.selected).map(record => this.submitOne(record.id))).then(responses => {
                        console.log('ccd-viewer:submit-multiple', responses)
                        this.errors.confirm = false
                    }).catch(errors => {
                        console.error('ccd-viewer:submit-multiple', errors)
                        this.errors.confirm = false
                    })
                },
                submitOne(id) {
                    const record = this.tableData.find(r => r.id === id)

                    if (!record) {
                        record.errors.confirm = 'record not found'
                        return;
                    }

                    if (!(!!record.practice_id && !!record.billing_provider_id && !!record.location_id)) {
                        record.errors.confirm = 'select a practice, location and provider'
                        return;
                    }


                    if (!record.duplicate_id || (record.duplicate_id && confirm(`This patient may be a duplicate of ${record.duplicate_id}. Are you sure you want to proceed with creating a careplan?`))) {
                        record.loaders.confirm = true
                        return this.axios.post(rootUrl('api/ccd-importer/records/confirm'), [record]).then((response) => {
                            record.loaders.confirm = false
                            if ((response.data || []).some(item => item.id === id && item.completed)) {
                                this.tableData.splice(this.tableData.findIndex(item => item.id === id), 1)
                            }
                            console.log('submit-one', record, response.data)
                            if (((response.data || [])[0] || {}).completed) {
                                const patient = (((response.data || [])[0] || {}).patient || {})
                                EventBus.$emit('notifications:create', {
                                    message: `Patient Created (${patient.id}): ${patient.display_name}`,
                                    href: rootUrl(`manage-patients/${patient.id}/view-careplan`),
                                    noTimeout: true
                                })
                            } else {
                                EventBus.$emit('notifications:create', {
                                    message: `Error when creating patient ${record.Name}`,
                                    type: 'warning',
                                    noTimeout: true
                                })
                            }
                            return this.getRecords()
                        }).catch((err) => {
                            record.loaders.confirm = false
                            record.errors.confirm = err.message
                            console.error('submit-one', record, err)
                        })
                    }
                },
                toggleAllSelect(e) {
                    this.tableData = this.tableData.map(row => {
                        row.selected = this.selected;
                        return row;
                    })
                },
                showErrorModal(id, name) {
                    const errors = (this.tableData.find(row => row.id === id) || {}).errors
                    console.log(errors)
                    Event.$emit('modal-error:show', {body: errors[name]}, () => {
                        errors[name] = null
                        console.log(errors)
                    })
                },
                getPractices() {
                    this.loaders.practices = true
                    return this.axios.get(rootUrl('api/practices')).then(response => {
                        this.loaders.practices = false
                        this.practices = (response.data || []).map(item => Object.assign(item, {
                            value: item.id,
                            label: item.display_name
                        }))
                        console.log('get-practices', response.data)
                    }).catch(err => {
                        this.loaders.practices = false
                        this.errors.practices = err.message
                        console.error('get-practices', err)
                    })
                },
                getLocations(practiceId) {
                    return this.cache().get(rootUrl(`api/practices/${practiceId}/locations`)).then(response => {
                        console.log(response)
                        return (response || []).map(item => Object.assign(item, {
                            value: item.id,
                            label: item.name
                        }))
                    })
                },
                getProviders(practiceId, locationId) {
                    return this.cache().get(rootUrl(`api/practices/${practiceId}/locations/${locationId}/providers`)).then(response => {
                        return (response || []).map(item => Object.assign(item, {
                            value: item.id,
                            label: item.display_name
                        }))
                    })
                },
                linkToCarePlan(patientId) {
                    return rootUrl('manage-patients/'+patientId+'/view-careplan')
                }
            }),
        mounted() {
            this.getPractices();
            this.getRecords();

            EventBus.$on('vdropzone:success', () => {
                const oldRecords = this.tableData.slice(0)
                this.getRecords().then((records) => {
                    const newRecords = records.filter(record => !oldRecords.find(row => row.id == record.id))

                    newRecords.filter(row => !!row.duplicate_id).distinct(row => row.duplicate_id).map(row => {
                        EventBus.$emit('notifications:create', {
                            message: `Imported Patient "${row.Name}" is a possible duplicate of`,
                            link: {
                                href: rootUrl(`manage-patients/${row.duplicate_id}/view-careplan`),
                                text: ` existing patient with ID ${row.duplicate_id}`
                            },
                            noTimeout: true,
                            type: 'error'
                        })
                    })
                })
            })
        }
    }
</script>

<style>
    input.float-left {
        float: initial;
        width: 100%;
    }

    .btn-round {
        border-radius: 50%;
        margin-left: 14%;
        padding: 3px 7px;
        font-size: 11px;
    }

    .btn-gray {
        background-color: #999;
        border-color: transparent;
    }

    .row-select {
        display: inline-block !important;
    }

    .dropdown.v-select.form-control {
        width: 200px;
        height: 40px;
        padding: 0;
    }

    .table-responsive {
        overflow-x: visible;
        min-height: .01%;
    }

    .v-select .dropdown-toggle {
        width: 200px;
        height: 40px;
        position: relative;
        overflow: hidden;
    }

    .placeholder-dropdown {
        background-color: #eee;
        border: none !important;
        width: 200px;
        height: 40px;
    }

    .placeholder-dropdown .loader {
        width: 15px;
        height: 15px;
        position: relative;
        left: 175px;
        top: 12px;
        border: 3px solid #31C6F9;
        -webkit-animation: spin 1s linear infinite;
        animation: spin 1s linear infinite;
        border-top: 4px solid #555;
        border-radius: 50%;
    }
</style>
