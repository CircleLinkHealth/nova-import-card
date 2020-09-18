<template>
    <modal name="mark-ineligible" class="modal-mark-ineligible" :no-title="true" :no-footer="true" :info="markIneligibleModalInfo">
        <div class="row">
            <p>
                You have selected <b>{{this.enrolleeCount()}}</b> patient(s).
                <br>
                <br>
                <br>
                <b>Warning:</b>
                Are you sure you want to mark selected patient(s) as ineligible?
                <br>
                (you will have to enable the <b>Show Ineligible</b> filter to view them)
            </p>
        </div>
        <div class="row">
            <div class="col-sm-12" style="width: 90%">
                <notifications ref="notificationsComponent" name="mark-ineligible-modal"></notifications>
            </div>
        </div>
        <loader v-if="loading"/>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js';
    import Modal from '../../../common/modal';
    import Notifications from '../../../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/notifications/notifications';
    import Loader from '../../../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    let self;

    export default {
        name: "mark-ineligible-modal",
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                markIneligibleModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-mark-ineligible-modal:dismissAll');
                        self.markEnrolleesAsIneligible();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-mark-ineligible-modal:dismissAll');
                        Event.$emit("modal-mark-ineligible:hide");
                    }
                }
            }
        },
        methods: {

            markEnrolleesAsIneligible() {

                this.loading = true;

                this.axios.post(rootUrl('/admin/ca-director/mark-ineligible'), {
                    enrolleeIds: this.selectedEnrolleeIds
                })
                    .then(resp => {
                        this.loading = false;
                        Event.$emit('clear-selected-enrollees');
                        Event.$emit('refresh-table');
                        Event.$emit("modal-mark-ineligible:hide");
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        Event.$emit('notifications-mark-ineligible-modal:create', {
                            noTimeout: true,
                            text:  errors,
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
    .modal-mark-ineligible .modal-wrapper {
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-mark-ineligible .modal-container {
        width: 600px;
        height: 300px;
    }




    .modal-mark-ineligible .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-mark-ineligible .glyphicon-remove {
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


    .modal-mark-ineligible .modal-body {
        height: 200px;
        width: 600px;
    }



</style>