<template>
    <div>
        <v-server-table class="table" :url="getUrl()" :columns="columns" :options="options" ref="table">
            <template slot="actions" slot-scope="props">
                <button>...</button>
            </template>
        </v-server-table>
    </div>
</template>

<script>

    import {ServerTable} from 'vue-tables-2';

    let self;

    export default {
        name: "PatientList",
        components: {
            'v-server-table': ServerTable
        },
        data() {
            return {
                columns: ['name', 'provider', 'hra_status', 'vitals_status', 'eligibility', 'dob', 'actions'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            // data.query.hideStatus = self.hideStatus;
                            // data.query.hideAssigned = self.hideAssigned;
                        }
                        return data;
                    },
                    columnsClasses: {
                        // 'selected': 'blank',
                        // 'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name', 'provider', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                    sortable: ['name', 'provider', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                },
            };
        },
        methods: {
            getUrl() {
                return `/manage-patients/list`
            }
        },
        created() {
            self = this;
        }
    }


</script>

<style scoped>

</style>
