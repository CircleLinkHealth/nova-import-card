<template>
    <div class="container-fluid">
        <div class="panel-body" id="report">
            <div class="top-10">
                <loader v-if="loading"></loader>
            </div>
            <v-server-table class="table" v-on:filter="listenTo" :url="getUrl()" :columns="columns" :options="options"
                            ref="table">
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
        name: "nurse-daily-report",
        components: {
            'modal': Modal,
            'loader': Loader,
            'notifications': Notifications
        },
        props: [],
        data() {
            return {
                exportCSVText: 'Export as CSV',
                loaders: {
                    next: false,
                    excel: false,
                },
                loading: false,
                columns: ['name', 'Time Since Last Activity', '# Scheduled Calls Today', '# Completed Calls Today', '# Successful Calls Today', 'CCM Mins Today', 'last_activity'],
                options: {
                    perPage: 10,
                    perPageValues: [5, 10, 20, 50],
                    skin: "table-striped table-bordered table-hover",
                    filterByColumn: true,
                    filterable: ['name'],
                    sortable: ['name'],
                },
            }
        },
        methods: {
            refreshTable() {
                this.$refs.table.refresh();
            },
            getUrl() {
                return rootUrl('/admin/reports/nurse/daily/data');
            },
            listenTo(a) {
                this.info = JSON.stringify(a);
            },
        },
        created() {
            self = this;
            console.info('created');
        },
        mounted() {
            Event.$on('refresh-table', this.refreshTable)
            console.info('mounted');

            Event.$on('vue-tables.loading', function (data) {
                self.loading = true;
            });

            Event.$on('vue-tables.loaded', function (data) {
                self.loading = false
            });
        }
    }
</script>

<style>
    th {
        min-width: 130px;
    }
    .min-width-50 {
        min-width: 50px !important;
    }

    .min-width-80 {
        min-width: 80px !important;
    }

</style>