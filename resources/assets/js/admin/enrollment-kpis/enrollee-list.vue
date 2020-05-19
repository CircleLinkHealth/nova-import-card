<template>
    <div class="container-fluid">
        <div class="panel-body" id="enrollees">
            <v-server-table class="table" v-on:filter="listenTo" :url="getUrl()" :columns="columns" :options="options"
                            ref="table">
                <template slot="total_time_spent" slot-scope="props">
                    {{formatSecondsToHHMMSS(props.row.total_time_spent)}}
                </template>
            </v-server-table>
        </div>
    </div>
</template>

<script>
    import {rootUrl} from '../../app.config.js'
    import Modal from '../common/modal';
    import {Event} from 'vue-tables-2'
    import Loader from '../../components/loader';
    import Notifications from '../../components/notifications';

    export default {
        name: "enrollee-list",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications

        },
        props: [],
        data() {
            return {
                loading: false,
                hideStatus: ['ineligible'],
                hideAssigned: true,
                isolateUploadedViaCsv: false,
                columns: ['id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status', 'source', 'enrollment_non_responsive', 'auto_enrollment_triggered', 'practice_name', 'provider_name', 'requested_callback', 'total_time_spent', 'attempt_count', 'last_attempt_at',
                    'last_call_outcome', 'last_call_outcome_reason', 'address', 'address_2', 'city', 'state', 'zip', 'primary_phone', 'other_phone', 'home_phone', 'cell_phone', 'dob', 'preferred_days', 'preferred_window',
                    'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'has_copay', 'email', 'provider_pronunciation', 'provider_sex', 'last_encounter', 'eligibility_job_id', 'medical_record_id', 'created_at'],
                options: {
                    requestAdapter(data) {
                        if (typeof (self) !== 'undefined') {
                            data.query.hideStatus = self.hideStatus;
                            data.query.hideAssigned = self.hideAssigned;
                            data.query.isolateUploadedViaCsv = self.isolateUploadedViaCsv;
                        }
                        return data;
                    },
                    headings: {
                        enrollment_non_responsive : 'Send Regular Mail'
                    },
                    columnsClasses: {
                        'selected': 'blank',
                        'Type': 'padding-2'
                    },
                    perPage: 100,
                    perPageValues: [10, 25, 50, 100, 200],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['hideStatus', 'id', 'user_id', 'mrn', 'lang', 'first_name', 'last_name', 'care_ambassador_name', 'status','source', 'requested_callback', 'eligibility_job_id', 'enrollment_non_responsive', 'last_attempt_at', 'auto_enrollment_triggered','medical_record_id', 'practice_name', 'provider_name', 'primary_insurance', 'secondary_insurance', 'tertiary_insurance', 'attempt_count'],
                    sortable: ['id', 'user_id', 'first_name', 'last_name', 'practice_name', 'provider_name', 'primary_insurance', 'status', 'source', 'created_at', 'state', 'city','enrollment_non_responsive', 'auto_enrollment_triggered', 'last_attempt_at', 'care_ambassador_name', 'attempt_count', 'requested_callback'],
                },
            }

        },
        methods: {
            formatSecondsToHHMMSS(seconds) {
                return new Date(1000 * seconds).toISOString().substr(11, 8);
            },
            refreshTable() {
                this.$refs.table.refresh();
            },
            requestAdapter(data) {
                if (typeof (self) !== 'undefined') {
                    data.query.hideStatus = self.hideStatus;
                }
                return data;
            },
            getUrl() {
                return rootUrl('/admin/enrollment/list/data');
            },
            selected(id) {
                const pos = this.selectedEnrolleeIds.indexOf(id);
                if (pos === -1) {
                    return false;
                } else {
                    return true;
                }
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            }
        },
        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            Event.$on('refresh-table', this.refreshTable)
            console.info('mounted');
        }
    }
</script>

<style scoped>

</style>