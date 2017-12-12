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
                <text-editable :value="props.row.Practice" :no-button="true"></text-editable>
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
            </template>
            <template slot="h__Submit" scope="props">
                <input class="btn btn-success btn-round" type="button" v-if="multipleSelected" @click="submitMultiple" value="✔" />
            </template>
            <template slot="Submit" scope="props">
                <input class="btn btn-success btn-round" :class="{ 'btn-gray': multipleSelected }" type="button" @click="submitOne(props.row)" value="✔" />
            </template>
        </v-client-table>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'
    import TextEditable from '../../admin/calls/comps/text-editable'

    export default {
        name: 'ccd-viewer',
        components: {
            'text-editable': TextEditable
        },
        data() {
            return {
                url: rootUrl('api/ccd-importer/imported-medical-records'),
                selected: false,
                columns: ['selected', 'Name', 'DOB', 'Practice', 'Location', 'Billing Provider', '2+ Cond', 'Medicare', 'Supplemental Ins', 'Submit', 'Remove'],
                tableData: [],
                options: {
                    sortable: ['Name', 'DOB', 'Practice', 'Location', 'Billing Provider']
                }
            }
        },
        computed: {
            multipleSelected() {
                return this.tableData.filter(row => !!row.selected).length > 1
            }
        },
        methods: {
            getRecords() {
                this.axios.get(this.url).then((response) => {
                    const records = response.data || []
                    this.tableData = records.map(record => {
                        if (record.demographics) {
                            record.demographics.display_name = record.demographics.first_name + ' ' + record.demographics.last_name
                        }
                        return {
                            id: record.id,
                            selected: false,
                            Name: record.demographics.display_name,
                            DOB: record.demographics.dob,
                            Practice: record.practice || 'No Practice',
                            Location: record.location || 'No Location',
                            'Billing Provider': record.billing_provider || 'No Billing Provider',
                            '2+ Cond': false,
                            Medicare: false,
                            'Supplemental Ins': false,
                            errors: {
                                delete: null
                            },
                            loaders: {
                                delete: false
                            }
                        }
                    })
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

            },
            submitOne(row) {

            },
            toggleAllSelect(e) {
                this.tableData = this.tableData.map(row => {
                    row.selected = this.selected;
                    return row;
                })
            }
        },
        mounted() {
            this.getRecords()
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