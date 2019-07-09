<template>
    <div>
        <v-server-table class="table" :url="getUrl()" :columns="columns" :options="options" ref="table">
            <template slot="eligibility" slot-scope="props">
                <!-- todo -->
                <span>Eligible</span>
            </template>
            <template slot="actions" slot-scope="props">
                <mdb-dropdown class="actions">
                    <mdb-dropdown-toggle slot="toggle" class="actions-toggle">...</mdb-dropdown-toggle>
                    <mdb-dropdown-menu right :dropup="isLastRow(props)">
                        <mdb-dropdown-item @click="sendHraLink">Send HRA link</mdb-dropdown-item>
                        <mdb-dropdown-item @click="sendVitalsLink">Send Vitals link</mdb-dropdown-item>
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
                        'actions': 'text-center'
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
                    sortIcon: {
                        base: 'fa',
                        is: 'fa-sort',
                        up: 'fa-sort-asc',
                        down: 'fa-sort-desc'
                    }
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
            },

            isLastRow(props) {
                return this.$refs.table.count === props.index;
            },

            sendHraLink() {

            },

            sendVitalsLink() {

            }
        },
        created() {
            self = this;
        }
    }


</script>

<style scoped>

    .dropdown.actions {
        border: none;
    }

    .actions-toggle {
        padding: 0;
        font-weight: 600;
        font-size: 20px;
        line-height: 1;
        letter-spacing: 3px;
        box-shadow: none;
        border: none;
        margin-top: -10px;
    }

    .actions-toggle:after {
        display: none;
    }

</style>
