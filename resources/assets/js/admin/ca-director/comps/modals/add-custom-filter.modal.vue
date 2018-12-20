<template>
    <modal name="add-custom-filter" class="" :no-footer="true" :info="addCustomFilterModalInfo">
        <template class="modal-container">
            <template slot="title">
                <div class="col-sm-12" style="text-align: center">
                    <h3>Add Custom Filter</h3>
                </div>
            </template>
            <template class="modal-body">
                <div class="row">
                    <p>Select Practice to add Filter:</p>
                </div>
                <div class="row">
                    <v-select max-height="200px" class="form-control" v-model="customFilterData.practice_id"
                              :options="list">
                    </v-select>
                </div>
                <div class="row">
                    <p>Select Filter Type</p>
                </div>
                <div class="row">
                    <v-select max-height="200px" class="form-control" v-model="customFilterData.filter_type"
                              :options="filterTypes">
                    </v-select>
                </div>

                <loader v-if="loading"/>
            </template>
        </template>
        <div class="row">
            <div class="col-sm-12">
                <notifications ref="notificationsComponent" name="add-custom-filter-modal"></notifications>
            </div>
        </div>
        <loader v-if="loading"/>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js';
    import Modal from '../../../common/modal';
    import Notifications from '../../../../components/notifications';
    import Loader from '../../../../components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    let practices = null;

    let self;

    export default {
        name: "add-custom-filter-modal",
        props: [],
        data: () => {
            return {
                loading: false,
                list: [],
                customFilterData: {
                    'practice_id': '',
                    'filter_name': '',
                    'filter_type': '',
                },
                filterTypes: [
                    {label: 'Insurance', value: 'insurance'},
                    {label: 'Provider', value: 'provider'},
                ],
                addCustomFilterModalInfo: {
                    okHandler: () => {
                        self.addCustomFilter();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-add-custom-filter-modal:dismissAll');
                        Event.$emit("modal-add-custom-filter:hide");
                    }
                }
            }
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect
        },
        methods: {
            getPractices() {
                return this.axios
                    .get(rootUrl('/api/practices'))
                    .then(response => {
                        this.loading = false;
                        practices = response.data;
                        this.list = practices.map(x => {
                            return {label: x.display_name, value: x.id};
                        });
                        return this.list;
                    })
                    .catch(err => {
                        this.loading = false;
                    });
            },
            addCustomFilter() {
                Event.$emit('notifications-add-custom-filter-modal:dismissAll');

                this.axios
                    .post(rootUrl('/admin/ca-director/add-enrollee-custom-filter'), this.customFilterData)
                    .then(() => {
                        this.loading = false;
                        Event.$emit("modal-add-custom-filter:hide");
                        this.$parent.$refs.table.refresh();
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        Event.$emit('notifications-add-custom-filter-modal:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
                    });
            }
        },
        mounted: function () {
            self = this;
            this.getPractices();
        }
    }
</script>


<style>
    .modal-edit-patient .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-edit-patient .modal-container {
        width: 900px;
        height: 600px;
        margin-top: 20px;
    }

    .modal-edit-patient .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-edit-patient .glyphicon-remove {
        width: 20px;
        height: 20px;
        color: #d44a4a;
        vertical-align: middle;
        font-size: 20px;
    }

    span.required {
        color: red;
        font-size: 18px;
        position: absolute;
        top: 2px;
    }

    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-edit-patient .modal-body {
        width: 820px;
        height: 400px;
        margin: 0px;
        padding: 0px;
    }

    h4 {
        display: block;
        font-weight: bold;
        text-underline-mode: true;
        color: #42b983;
    }
</style>