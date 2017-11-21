<template>
    <div>
        <v-client-table ref="ccdRecords" :data="tableData" :columns="columns" :options="options">
            <template slot="selected" scope="props">
                <input class="row-select" v-model="props.row.selected" type="checkbox" />
            </template>
            <template slot="h__selected" scope="props">
                <input class="row-select" v-model="selected" type="checkbox" />
            </template>
            <template slot="Practice" scope="props">
                <text-editable :value="props.row.Practice"></text-editable>
            </template>
            <template slot="Location" scope="props">
                <text-editable :value="props.row.Location"></text-editable>
            </template>
            <template slot="Billing Provider" scope="props">
                <text-editable :value="props.row['Billing Provider']"></text-editable>
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
                columns: ['selected', 'Name', 'DOB', 'Practice', 'Location', 'Billing Provider', '2+ Cond', 'Medicare', 'Supplemental Ins'],
                tableData: [],
                options: {

                }
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
                            'Supplemental Ins': false
                        }
                    })
                }).catch(err => {
                    console.error(err)
                })
            }
        },
        mounted() {
            this.getRecords()
        }
    }
</script>

<style>
    
</style>