<template>
    <div>
        <v-client-table ref="ccdRecords" :data="tableData" :columns="columns" :options="options">

        </v-client-table>
    </div>
</template>

<script>
    import { rootUrl } from '../../app.config'

    export default {
        name: 'ccd-viewer',
        data() {
            return {
                url: rootUrl('api/ccd-importer/imported-medical-records'),
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