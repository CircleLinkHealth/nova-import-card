<template>
    <modal name="select-ca" class="modal-select-ca" :no-footer="true" :info="selectCaModalInfo">
        <template class="modal-container">
            <template slot="title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Assign Care Ambassador to selected Patient(s)</h3>
                    </div>
                </div>
            </template>
            <template class="modal-body">
                <div class="row">
                    <p>Select Care Ambassador:</p>
                </div>
                <div class="row">
                    <v-select max-height="200px" class="form-control" v-model="selectedAmbassador"
                              :options="ambassadorList">
                    </v-select>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <notifications ref="notificationsComponent" name="select-ca-modal"></notifications>
                    </div>
                </div>
                <loader v-if="loading"/>
            </template>
        </template>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js';
    import Modal from '../../../../../../../../SharedVueComponents/Resources/assets/js/admin/common/modal';
    import Notifications from '../../../../components/notifications';
    import Loader from '../../../../components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    let self;

    export default {
        name: "select-ca-modal",
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect
        },
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                ambassadorList: [],
                loading: false,
                list: [],
                selectedAmbassador: null,
                selectCaModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        self.assignEnrolleesToAmbassador();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        Event.$emit("modal-select-ca:hide");
                    }
                }
            }
        },
        methods: {


            assignEnrolleesToAmbassador() {

                this.loading = true;

                this.axios.post(rootUrl('/admin/ca-director/assign-ambassador'), {
                    ambassadorId: this.selectedAmbassador.value,
                    enrolleeIds: this.selectedEnrolleeIds
                })
                    .then(resp => {
                        this.loading = false;

                        if (resp.data.enrollees_unassigned) {
                            Event.$emit('notifications-ca-panel:create', {
                                noTimeout: true,
                                text: resp.data.message,
                                type: 'error'
                            });
                        }
                        Event.$emit('clear-selected-enrollees');
                        Event.$emit('refresh-table');
                        Event.$emit("modal-select-ca:hide");
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        Event.$emit('notifications-select-ca-modal:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
                    });
            }
        },
        created() {
            self = this;
        },
        mounted: function () {
            Event.$on('ambassadors-loaded', (ambassadors) => {
                this.ambassadorList = ambassadors
            })
        }
    }
</script>

<style>
    .modal-select-ca .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-select-ca .modal-container {
        width: 600px;
        height: 380px;
    }


    .modal-select-ca .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }


    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-select-ca .modal-body {
        height: 200px;
        width: 500px;
    }


</style>