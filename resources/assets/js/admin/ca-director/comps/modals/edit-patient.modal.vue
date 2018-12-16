<template>
    <modal name="edit-patient" class="modal-select-ca" :no-footer="true" :info="editPatientModalInfo">
        <template slot="title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Edit Patient Data</h3>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-sm-12">
                <notifications ref="notificationsComponent" name="edit-patient-modal"></notifications>
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
    import Textfield from "vue-mdl/src/textfield";

    let self;

    export default {
        name: "edit-patient-modal",
        props: {
            enrollee: {
                type: Object,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                enrolleeData: [],
                editPatientModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-edit-patient-modal:dismissAll');
                        Event.$emit("modal-edit-patient:hide");
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-edit-patient-modal:dismissAll');
                        Event.$emit("modal-edit-patient:hide");
                    }
                }
            }
        },
        methods: {

        },
        components: {
            Textfield,
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect
        },
        mounted: function () {
            self = this;

        }
    }
</script>

<style>
    .modal-edit-patient .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-edit-patient .modal-container {
        width: 800px;
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

    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-edit-patient .modal-body {
        min-height: 300px;
    }

    .selected-tag {
        width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    a.my-tool-tip {
        float: right;
        margin-right: 4px;
    }

</style>