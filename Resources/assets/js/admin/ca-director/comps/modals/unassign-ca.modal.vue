<template>
    <modal name="unassign-ca" class="modal-unassign-ca" :no-title="true" :no-footer="true" :info="unassignCaModalInfo">
        <div class="row">
            <p>
                You have selected <b>{{this.enrolleeCount()}}</b> patient(s).
                <br>
                <br>
                <br>
                <b>Warning:</b>
                Are you sure you want to unassign these patients from their current Care Ambassadors?
            </p>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <notifications ref="notificationsComponent" name="unassign-ca-modal"></notifications>
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

    let self;

    export default {
        name: "unassign-ca.modal",
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                unassignCaModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-unassign-ca-modal:dismissAll');
                        self.unassignCaFromEnrollees();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-unassign-ca-modal:dismissAll');
                        Event.$emit("modal-unassign-ca:hide");
                    }
                }
            }
        },
        methods: {

            unassignCaFromEnrollees() {

                this.loading = true;

                this.axios.post(rootUrl('/admin/ca-director/unassign-ca'), {
                    enrolleeIds: this.selectedEnrolleeIds
                })
                    .then(resp => {
                        this.loading = false;
                        Event.$emit('clear-selected-enrollees');
                        Event.$emit('refresh-table');
                        Event.$emit("modal-unassign-ca:hide");
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        Event.$emit('notifications-unassign-ca-modal:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
                    });
            },
            enrolleeCount(){
                return this.selectedEnrolleeIds.length;
            }
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect
        },
        mounted: function () {
            self = this;

            this.loading = false;
        }
    }
</script>

<style>
    .modal-unassign-ca .modal-wrapper {
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-unassign-ca .modal-container {
        width: 680px;
        height: 200px;
    }




    .modal-unassign-ca .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-unassign-ca .glyphicon-remove {
        width: 20px;
        height: 20px;
        color: #d44a4a;
        vertical-align: middle;
        font-size: 20px;
    }

    .width-90 {
        float: left;
        width: 90%;
    }

    .width-82 {
        float: left;
        width: 82%;
    }

    .width-18 {
        float: left;
        width: 18%;
    }

    .width-10 {
        float: left;
        width: 10%;
    }

    .padding-left-5 {
        padding-left: 5px;
    }

    .padding-top-7 {
        padding-top: 7px;
    }

    span.required {
        color: red;
        font-size: 18px;
        position: absolute;
        top: 2px;
    }


    .modal-unassign-ca .modal-body {
        height: 90px;
        width: 600px;
    }


</style>