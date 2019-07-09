<template>
    <div>
        <v-server-table class="table" :url="getUrl()" :columns="columns" :options="options" ref="table">
            <template slot="eligibility" slot-scope="props">
                <!-- todo -->
                <span>Eligible</span>
            </template>
            <template slot="actions" slot-scope="props">
                <mdb-dropdown>
                    <mdb-dropdown-toggle slot="toggle">...</mdb-dropdown-toggle>
                    <mdb-dropdown-menu right>
                        <mdb-dropdown-item>Send HRA link</mdb-dropdown-item>
                        <mdb-dropdown-item>Send Vitals link</mdb-dropdown-item>
                    </mdb-dropdown-menu>
                </mdb-dropdown>
            </template>
        </v-server-table>
    </div>
</template>

<script>

    import {mdbDropdown, mdbDropdownItem, mdbDropdownMenu, mdbDropdownToggle} from 'mdbvue';

    let self;

    export default {
        name: "PatientList",
        components: {mdbDropdown, mdbDropdownItem, mdbDropdownMenu, mdbDropdownToggle},
        data() {
            return {
                columns: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob', 'actions'],
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
                    listColumns: {
                        hra_status: [
                            {id: 'null', text: 'N/A'},
                            {id: 'pending', text: 'Not Started'},
                            {id: 'in_progress', text: 'In Progress'},
                            {id: 'completed', text: 'Completed'},
                        ],
                        vitals_status: [
                            {id: 'null', text: 'N/A'},
                            {id: 'pending', text: 'Not Started'},
                            {id: 'in_progress', text: 'In Progress'},
                            {id: 'completed', text: 'Completed'},
                        ]
                    },
                    //todo: eligibility should have a dropdown for filters
                    filterable: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                    sortable: ['patient_name', 'provider_name', 'hra_status', 'vitals_status', 'eligibility', 'dob'],
                },
            };
        },
        methods: {
            getUrl() {
                return `/manage-patients/list`
            },

            getStatus(status) {
                switch (status) {
                    case 'pending':
                        return 'Pending';
                    case 'in_progress':
                        return 'In Progress';
                    case 'completed':
                        return 'Completed';
                }
                return 'N/A';
            }
        },
        created() {
            self = this;
        }
    }


</script>

<style scoped>

</style>
