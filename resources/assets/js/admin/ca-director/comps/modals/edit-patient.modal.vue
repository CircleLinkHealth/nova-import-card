<template>
    <modal name="edit-patient" class="modal-edit-patient" :no-footer="true" :info="editPatientModalInfo">
        <template class="modal-container">
            <template slot="title">
                <div class="row">
                    <div class="col-sm-6">
                        <h3>Edit Patient Data</h3>
                    </div>
                </div>
            </template>
            <template class="modal-body">
                <div class="patient-data">
                    <div class="patient-row">
                        <div class="col-md-3 patient-data-label">
                            <label for="first-name">First Name</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="first-name" v-model="enrolleeData.first_name"/>
                        </div>
                        <div class="col-md-3 patient-data-label">
                            <label for="last-name">Last Name</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="last-name" v-model="enrolleeData.last_name"/>
                        </div>
                    </div>
                    <div class="patient-row">
                        <div class="col-md-3 patient-data-label">
                            <label for="status">Status</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="status" v-model="enrolleeData.status"/>
                        </div>
                        <div class="col-md-3 patient-data-label">
                            <label for="lang">Language</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="lang" v-model="enrolleeData.lang"/>
                        </div>
                    </div>
                    <div class="patient-row">
                        <div class="col-md-3 patient-data-label">
                            <label for="address">Address</label>
                        </div>
                        <div class="col-md-3 ">
                            <input type="text" class="patient-data-textarea" id="address" v-model="enrolleeData.address"/>
                        </div>
                        <div class="col-md-3 patient-data-label">
                            <label for="address-2">Address 2</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="address-2" v-model="enrolleeData.address_2"/>
                        </div>
                    </div>
                    <div class="patient-row">
                        <div class="col-md-3 patient-data-label">
                            <label for="primary-phone">Primary Phone</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea"id="primary-phone" v-model="enrolleeData.primary_phone"/>
                        </div>
                        <div class="col-md-3 patient-data-label">
                            <label for="home-phone">Home Phone</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="home-phone" v-model="enrolleeData.home_phone"/>
                        </div>
                    </div>
                    <div class="patient-row">
                        <div class="col-md-3 patient-data-label">
                            <label for="cell-phone">Cell Phone</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="cell-phone" v-model="enrolleeData.cell_phone"/>
                        </div>
                        <div class="col-md-3 patient-data-label">
                            <label for="other-phone">Other Phone</label>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="patient-data-textarea" id="other-phone" v-model="enrolleeData.other_phone"/>
                        </div>
                    </div>
                </div>
            </template>
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

    let self;

    export default {
        name: "edit-patient-modal",
        props: [],
        data: () => {
            return {
                loading: false,
                enrolleeData: {
                    address: '',
                },
                editPatientModalInfo: {
                    okHandler: () => {
                        self.updateEnrolleeData();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-edit-patient-modal:dismissAll');
                        Event.$emit("modal-edit-patient:hide");
                    }
                }
            }
        },
        methods: {
            updateEnrolleeData() {
                Event.$emit('notifications-edit-patient-modal:dismissAll');

                this.axios
                    .post(rootUrl('/admin/ca-director/edit-enrollee'), this.enrollee)
                    .then(() => {

                    })
                    .catch(err => {

                    });
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
            Event.$on('modal-edit-patient:show', (enrollee) => {
                this.enrolleeData.first_name = enrollee.first_name;
                this.enrolleeData.last_name = enrollee.last_name;
                this.enrolleeData.status = enrollee.status;
                this.enrolleeData.lang = enrollee.lang;
                this.enrolleeData.address = enrollee.address;
                this.enrolleeData.address_2 = enrollee.address_2;
                this.enrolleeData.primary_phone = enrollee.primary_phone;
                this.enrolleeData.home_phone = enrollee.home_phone;
                this.enrolleeData.cell_phone = enrollee.cell_phone;
                this.enrolleeData.other_phone = enrollee.other_phone;

            });
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
        width: 900px;
        height: 600px;
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
        width: 820px;
        height: 400px;
    }

    .patient-data {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
        width: 800px;
        height: 400px;

    }

    .patient-row {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 10px;
        margin-bottom: 10px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    .patient-data-label{
        display: block;
        margin-top: 20px;
        padding-left: 20px;
    }
    .patient-data-textarea{
        display: block;
        resize: none;
        overflow: auto;
        border-radius: 10px;
        outline: none;
        padding: 1px;
        transition: border 0.5s;
        border: solid 1px #9da8ad;
        box-sizing:border-box;
    }


</style>