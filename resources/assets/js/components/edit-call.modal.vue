<!--
The 'edit call' modal can be used from nurses, as opposed to 'add call' which is only used from admins.
-->

<template>
    <modal name="edit-call" ok-text="Save" :info="editCallModalInfo" :no-footer="true" class-name="modal-edit-call">
        <template slot="title">
            <div class="row">
                <h3 class="col-sm-12">
                    Schedule / Reschedule call
                </h3>
            </div>
        </template>
        <template slot-scope="props">
            <div class="preferences row">
                <div class="row">
                    <div class="col-sm-4">
                        Preferred days
                    </div>
                    <div class="col-sm-8">
                        {{patientPreferences.contact_days ? patientPreferences.contact_days : '-' }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        Preferred time
                    </div>
                    <div class="col-sm-8">
                        <span v-if="patientPreferences.contact_time">
                            {{patientPreferences.contact_time}} {{patientPreferences.contact_timezone}}
                        </span>
                        <span v-else-if="patientPreferences.contact_timezone">
                            {{patientPreferences.contact_timezone}}
                        </span>
                        <span v-else>
                            -
                        </span>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        Frequency
                    </div>
                    <div class="col-sm-8">
                        {{patientPreferences.calls_per_month}} (monthly)
                    </div>
                </div>
            </div>
            <br/>
            <form action="" @submit="submitForm">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row form-group">
                            <div class="col-sm-4">
                                Date <span class="required">*</span>
                            </div>
                            <div class="col-sm-8">
                                <input class="form-control" type="date" name="scheduled_date"
                                       v-model="formData.scheduled_date"
                                       required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                Time <span class="required">*</span>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control" type="time" name="window_start"
                                       v-model="formData.window_start"
                                       required/>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control" type="time" name="window_end" v-model="formData.window_end"
                                       required/>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                Notes <span class="required">*</span>
                            </div>
                            <div class="col-sm-8">
                                <textarea class="form-control" name="attempt_note" v-model="formData.attempt_note"
                                          required></textarea>
                                <button class="submit hidden"></button>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <notifications ref="notificationsComponent" name="edit-call-modal"></notifications>
                                <center>
                                    <loader v-if="loaders.submit"></loader>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </modal>
</template>

<script>
    import {Event} from 'vue-tables-2'
    import Modal from '../admin/common/modal'
    import LoaderComponent from './loader'
    import {rootUrl} from '../app.config'
    import {today} from '../util/today'
    import notifications from './notifications'
    import VueSelect from 'vue-select'
    import VueCache from '../util/vue-cache'

    const defaultFormData = {
        id: null,
        attempt_note: '',
        window_start: '09:00',
        window_end: '17:00',
        scheduled_date: today(),
        inbound_cpm_id: null, //the patient id
        outbound_cpm_id: null
    };

    export default {
        name: 'edit-call-modal',
        props: [
            'patientPreferences'
        ],
        mixins: [VueCache],
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            'v-select': VueSelect,
            notifications
        },
        data() {
            return {
                editCallModalInfo: {
                    okHandler() {
                        const form = this.$form();
                        this.errors().submit = null;
                        console.log("form:edit-call:submit", form);
                        form.querySelector('button.submit.hidden').click();
                    },
                    cancelHandler() {
                        this.errors().submit = null;
                        Event.$emit("modal-edit-call:hide");
                    },
                    $form: () => this.$el.querySelector('form'),
                    errors: () => this.errors
                },
                errors: {
                    submit: null
                },
                loaders: {
                    submit: false
                },
                //add default values
                formData: Object.assign({}, defaultFormData)
            }
        },
        computed: {},
        methods: {
            resetFormData(data) {
                //do not create new formData object, cz you will break Vue
                this.formData.id = data.id;
                this.formData.inbound_cpm_id = data.inbound_cpm_id;
                this.formData.outbound_cpm_id = data.outbound_cpm_id;
                this.formData.scheduled_date = data.scheduled_date;
                this.formData.window_start = data.window_start;
                this.formData.window_end = data.window_end;
                this.formData.attempt_note = data.attempt_note;
            },
            submitForm(e) {
                e.preventDefault();
                const formData = Object.assign({}, this.formData);
                this.loaders.submit = true;
                return this.axios.post(rootUrl(`manage-patients/${formData.inbound_cpm_id}/calls/reschedule`), formData)
                    .then((response, status) => {
                        if (response) {
                            this.loaders.submit = false;
                            const call = response.data;

                            //tricky part here
                            //make sure we update our data in this component
                            //cz if one edits again without closing and opening the modal
                            //the id must be the updated one
                            this.formData.id = call.id;

                            Event.$emit('notifications-edit-call-modal:create', {text: 'Call created successfully'});
                            Event.$emit("modal-edit-call:hide");
                            Event.$emit('calls:add', call);
                            console.log('calls:add', call);
                        }
                        else {
                            throw new Error('Could not create call. Patient already has a scheduled call');
                        }
                    })
                    .catch(err => {
                        this.errors.submit = err.message;
                        this.loaders.submit = false;
                        console.error('add-call', err);

                        let msg = err.message;
                        if (err.response && err.response.data) {
                            msg += `: ${err.response.data}`;
                        }

                        Event.$emit('notifications-edit-call-modal:create', {text: msg, type: 'error'});
                    });
            }
        },
        created() {
        },
        mounted() {
            Event.$on("modal-edit-call:show", (callToEdit) => {
                this.resetFormData(callToEdit);
            });

            console.log(this.patientPreferences);
        }
    }
</script>

<style>
    .modal-edit-call .modal-container {
        width: 520px;
    }

    .preferences {
        margin-top: -20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e5e5;
    }

    span.required {
        color: red;
        font-size: 29px;
        position: absolute;
        top: 0;
        margin-left: 0;
    }
</style>