<template>
    <div>
        <v-client-table ref="ccdRecords" :data="tableData" :columns="columns" :options="options">
            <template slot="selected" scope="props">
                <input class="row-select" v-model="props.row.selected" @change="select($event, props.row.id)" type="checkbox" />
            </template>
            <template slot="h__selected" scope="props">
                <input class="row-select" v-model="selected" @change="toggleAllSelect" type="checkbox" />
            </template>
            <template slot="Practice" scope="props">
                <select-editable :value="props.row.Practice" :values="props.row.practices()" :no-button="true"
                        :display-text="props.row.practice_name" :on-change="props.row.changePractice"></select-editable>
            </template>
            <template slot="Location" scope="props">
                <text-editable :value="props.row.Location" :no-button="true"></text-editable>
            </template>
            <template slot="Billing Provider" scope="props">
                <text-editable :value="props.row['Billing Provider']" :no-button="true"></text-editable>
            </template>
            <template slot="2+ Cond" scope="props">
                <input class="row-select" v-model="props.row['2+ Cond']" type="checkbox" />
            </template>
            <template slot="Medicare" scope="props">
                <input class="row-select" v-model="props.row.Medicare" type="checkbox" />
            </template>
            <template slot="Supplemental Ins" scope="props">
                <input class="row-select" v-model="props.row['Supplemental Ins']" type="checkbox" />
            </template>
            <template slot="h__Remove" scope="props">
                <input class="btn btn-danger btn-round" v-if="multipleSelected" @click="deleteMultiple" type="button" value="x" />
            </template>
            <template slot="Remove" scope="props">
                <input class="btn btn-danger btn-round" :class="{ 'btn-gray': multipleSelected }" type="button" @click="deleteOne(props.row.id)" value="x" />
                <div v-if="props.row.loaders.delete">
                    <loader></loader>
                </div>
            </template>
            <template slot="h__Submit" scope="props">
                <input class="btn btn-success btn-round" type="button" v-if="multipleSelected" @click="submitMultiple" value="✔" />
            </template>
            <template slot="Submit" scope="props">
                <input class="btn btn-success btn-round" v-if="!props.row.loaders.confirm" :class="{ 'btn-gray': multipleSelected }" type="button" @click="submitOne(props.row.id)" value="✔" />
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
    import { rootUrl } from '../../app.config'
    import TextEditable from '../../admin/calls/comps/text-editable'
    import SelectEditable from '../../admin/calls/comps/select-editable'
    import EventBus from '../../admin/time-tracker/comps/event-bus'
    import LoaderComponent from '../loader'
    import ErrorModal from '../../admin/billing/comps/error-modal'
    import ErrorModalButton from '../../admin/billing/comps/error-modal-button'

    export default {
        name: 'ccd-viewer',
        components: {
            'text-editable': TextEditable,
            'loader': LoaderComponent,
            'error-modal': ErrorModal,
            'error-modal-button': ErrorModalButton,
            'select-editable': SelectEditable
        },
        data() {
            return {
                url: rootUrl('api/ccd-importer/imported-medical-records'),
                selected: false,
                columns: ['selected', 'Name', 'DOB', 'Practice', 'Location', 'Billing Provider', '2+ Cond', 'Medicare', 'Supplemental Ins', 'Submit', 'Remove'],
                tableData: [],
                options: {
                    sortable: ['Name', 'DOB', 'Practice', 'Location', 'Billing Provider']
                },
                practices: [],
                errors: {
                    delete: null,
                    confirm: null
                },
                loaders: {
                    delete: false,
                    confirm: false
                }
            }
        },
        computed: {
            multipleSelected() {
                return this.tableData.filter(row => !!row.selected).length > 1
            }
        },
        methods: {
            getRowErrors(id) {
                return () => this.tableData.find(record => record.id === id).errors
            },
            setupRecord(record) {
                if (record.demographics) {
                    record.demographics.display_name = record.demographics.first_name + ' ' + record.demographics.last_name
                }
                const self = this;
                return {
                    id: record.id,
                    selected: false,
                    Name: record.demographics.display_name,
                    DOB: record.demographics.dob,
                    Practice: ((record.practice || {}).id || null),
                    Location: ((record.location || {}).id || null),
                    'Billing Provider': ((record.billing_provider || {}).id || null),
                    '2+ Cond': false,
                    Medicare: false,
                    'Supplemental Ins': false,
                    errors: {
                        delete: null,
                        confirm: null,
                        practices: null
                    },
                    loaders: {
                        delete: false,
                        confirm: false,
                        practices: false
                    },
                    practice_name: ((record.practice || {}).display_name || null),
                    practices: () => self.practices,
                    changePractice(id) {
                        self.changePractice(record.id, id)
                        console.log('change-practice-name', record.id, id)
                    }
                }
            },
            changePractice(recordId, practiceId) {
                const record = this.tableData.find(row => row.id === recordId)
                if (record) {
                    const practice = this.practices.find(practice => practice.id === practiceId)
                    if (practice) {
                        record.Practice = practice.id;
                        record.practice_name = practice.display_name
                        console.log(practice)
                    }
                }
            },
            getRecords() {
                this.axios.get(this.url).then((response) => {
                    const records = response.data || []
                    this.tableData = records.map(this.setupRecord)
                    console.log('get-records', this.tableData)
                }).catch(err => {
                    console.error(err)
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
                            }
                            else {
                                record.errors.delete = 'not found'
                            }
                        }
                        else {
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
                if (record) {
                    record.loaders.confirm = true
                    return this.axios.post(rootUrl('api/ccd-importer/records/confirm'), [record]).then((response) => {
                        record.loaders.confirm = false
                        console.log('submit-one', record, response.data)
                        this.$forceUpdate()
                        return response
                    }).catch((err) => {
                        record.loaders.confirm = false
                        record.errors.confirm = err.message
                        console.error('submit-one', record, err)
                    })
                }
                else {
                    record.errors.confirm = 'record not found'
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
                Event.$emit('modal-error:show', { body: errors[name] }, () => {
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
                        text: item.display_name
                    }))
                    console.log('get-practices', response.data)
                }).catch(err => {
                    this.loaders.practices = false
                    this.errors.practices = err.message
                    console.error('get-practices', err)
                })
            }
        },
        mounted() {
            this.getPractices()
            this.getRecords()

            EventBus.$on('vdropzone:success', (records) => {
                this.tableData = records.map(this.setupRecord)

                EventBus.$emit('vdropzone:remove-all-files')
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
</style>