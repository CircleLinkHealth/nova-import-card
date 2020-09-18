<template>
    <modal name="add-custom-filter" class="modal-add-custom-filter" :no-footer="true" :info="addCustomFilterModalInfo">
        <template class="modal-container">
            <template slot="title">
                <div class="col-sm-12" style="text-align: center">
                    <h3>Add Custom Filter</h3>
                </div>
            </template>
            <template class="modal-body">
                <div class="form">
                    <div class="form-row col-md-12">
                        <h5>(<strong>Warning:</strong> Adding a filter will permanently omit patients that match the filter criteria) </h5>
                    </div>
                    <div class="form-row col-md-12">
                        <h4>Select Practice to add Filter:</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <v-select max-height="200px" class="form-control" v-model="customFilterData.practice_id"
                                      :options="list">
                            </v-select>
                        </div>

                    </div>
                    <div class="form-row col-md-12">
                        <h4>Select Filter Type</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <v-select max-height="200px" class="form-control" v-model="customFilterData.filter_type"
                                      :options="filterTypes">
                            </v-select>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <h4>Filter Name</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <input type="text" class="form-control" id="first-name"
                                   v-model="customFilterData.filter_name"/>
                        </div>
                    </div>
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
    import Notifications from '../../../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/notifications/notifications';
    import Loader from '../../../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader';
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
                list: [
                    {
                        label: 'All Practices',
                        value: 'all'
                    },
                ],
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
                        practices.map(x => {
                            this.list.push({label: x.display_name, value: x.id});
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
                        Event.$emit("refresh-table");
                        Event.$emit("modal-add-custom-filter:hide");


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
    .modal-add-custom-filter .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-add-custom-filter .modal-container {
        width: 900px;
        height: 600px;
        margin-top: 20px;
    }

    .modal-add-custom-filter .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-add-custom-filter .glyphicon-remove {
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

    .modal-add-custom-filter .modal-body {
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