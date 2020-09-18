<template>
    <modal name="edit-patient" class="modal-edit-patient" :no-footer="true" :info="editPatientModalInfo">
        <template class="modal-container">
            <template slot="title">
                <div class="col-sm-12" style="text-align: center">
                    <h3>Edit Patient Data</h3>
                </div>
            </template>
            <template class="modal-body">
                <div class="form">
                    <div class="form-row col-md-12">
                        <h4>Demographics</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <label for="first-name">First Name:</label>
                            <input type="text" class="form-control" id="first-name"
                                   v-model="enrolleeData.first_name"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last-name">Last Name:</label>
                            <input type="text" class="form-control" id="last-name"
                                   v-model="enrolleeData.last_name"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="form-group col-md-6">
                            <label for="lang">Language:</label>
                            <input type="text" class="form-control" id="lang" v-model="enrolleeData.lang"/>
                        </div>
                        <div class="form-group col-md-6">
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="form-group col-md-6">
                            <label for="address">Address:</label>
                            <input type="text" class="form-control" id="address"
                                   v-model="enrolleeData.address"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="address-2">Address 2:</label>
                            <input type="text" class="form-control" id="address-2"
                                   v-model="enrolleeData.address_2"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="form-group col-md-6">
                            <label for="city">City:</label>
                            <input type="text" class="form-control" id="city"
                                   v-model="enrolleeData.city"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="state">State:</label>
                            <input type="text" class="form-control" id="state"
                                   v-model="enrolleeData.state"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="form-group col-md-6 ">
                            <label for="zip">Zip:</label>
                            <input type="text" class="form-control" id="zip"
                                   v-model="enrolleeData.zip"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <hr>
                        <div class="form-group col-md-12">
                            <h4>Status</h4>
                        </div>
                        <div class="form-group col-md-3">
                            <select v-model="enrolleeData.status">
                                <option v-for="status in statuses" v-bind:value="status.id">
                                    {{ status.text }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                        </div>
                    </div>
                    <div>

                    </div>
                    <div class="form-row col-md-12">
                        <hr>
                        <h4 class="form-group col-md-12">Patient Phones</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <label for="primary-phone">Primary Phone:</label>
                            <input type="text" class="form-control" id="primary-phone"
                                   v-model="enrolleeData.phones.primary_phone"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="home-phone">Home Phone:</label>
                            <input type="text" class="form-control" id="home-phone"
                                   v-model="enrolleeData.phones.home_phone"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="form-group col-md-6">
                            <label for="cell-phone">Cell Phone:</label>
                            <input type="text" class="form-control" id="cell-phone"
                                   v-model="enrolleeData.phones.cell_phone"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="other-phone">Other Phone:</label>
                            <input type="text" class="form-control" id="other-phone"
                                   v-model="enrolleeData.phones.other_phone"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <hr>
                        <h4 class="form-group col-md-12">Patient Insurances</h4>
                        <hr>
                        <div class="form-group col-md-6">
                            <label for="primary-insurance">Primary Insurance:</label>
                            <input type="text" class="form-control" id="primary-insurance"
                                   v-model="enrolleeData.primary_insurance"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="secondary-insurance">Secondary Insurance:</label>
                            <input type="text" class="form-control" id="secondary-insurance"
                                   v-model="enrolleeData.secondary_insurance"/>
                        </div>
                    </div>
                    <div class="form-row col-md-12">
                        <div class="col-md-6 ">
                            <label for="tertiary-insurance">Tertiary Insurance:</label>
                            <input type="text" class="form-control" id="tertiary-insurance"
                                   v-model="enrolleeData.tertiary_insurance"/>
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
    import Notifications from '../../../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/notifications';
    import Loader from '../../../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    let self;

    export default {
        name: "edit-patient-modal",
        props: [],
        data: () => {
            return {
                statuses: [
                    {id: 'call_queue', text: 'Call Queue'},
                    {id:'enrolled', text: 'Enrolled'},
                    {id:'consented', text: 'Consented'},
                    {id:'soft_rejected', text: 'Soft Declined'},
                    {id:'rejected', text: 'Hard Declined'},
                    {id:'utc', text: 'Unreachable'},
                    {id:'ineligible',text: 'Ineligible'},
                    {id:'queue_auto_enrollment', text:'Queued for Self-enrollment'},
                ],
                loading: false,
                enrolleeData: {
                    status: '',
                    phones: {
                        primary_phone: '',
                        cell_phone: '',
                        home_phone: '',
                        other_phone: '',
                    }
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
                    .post(rootUrl('/admin/ca-director/edit-enrollee'), this.enrolleeData)
                    .then(() => {
                        this.loading = false;
                        Event.$emit('refresh-table');
                        Event.$emit("modal-edit-patient:hide");

                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        Event.$emit('notifications-edit-patient-modal:create', {
                            noTimeout: true,
                            text: errors,
                            type: 'error'
                        });
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
                this.enrolleeData.id = enrollee.id;
                this.enrolleeData.first_name = enrollee.first_name;
                this.enrolleeData.last_name = enrollee.last_name;
                this.enrolleeData.status = enrollee.status;
                this.enrolleeData.lang = enrollee.lang;
                this.enrolleeData.address = enrollee.address;
                this.enrolleeData.address_2 = enrollee.address_2;
                this.enrolleeData.city = enrollee.city;
                this.enrolleeData.state = enrollee.state;
                this.enrolleeData.zip = enrollee.zip;
                this.enrolleeData.phones.primary_phone = enrollee.primary_phone;
                this.enrolleeData.phones.home_phone = enrollee.home_phone;
                this.enrolleeData.phones.cell_phone = enrollee.cell_phone;
                this.enrolleeData.phones.other_phone = enrollee.other_phone;
                this.enrolleeData.primary_insurance = enrollee.primary_insurance;
                this.enrolleeData.secondary_insurance = enrollee.secondary_insurance;
                this.enrolleeData.tertiary_insurance = enrollee.tertiary_insurance;
            });
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