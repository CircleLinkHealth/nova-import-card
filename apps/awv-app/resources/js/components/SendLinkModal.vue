<template>
    <mdb-modal v-on:close="cancel">
        <mdb-modal-header>
            <mdb-modal-title>{{title}}</mdb-modal-title>
        </mdb-modal-header>
        <mdb-modal-body>

            <div class="container">

                <div class="spinner-overlay" v-show="waiting">
                    <div class="text-center">
                        <mdb-icon icon="spinner" :spin="true"/>
                    </div>
                </div>

                <mdb-alert v-if="isVitals" color="warning">
                    Reminder: Do not send links to the Vitals Questionnaire to patients!
                </mdb-alert>

                <div class="row text-center" v-show="!(onlySms || onlyEmail)">
                    <div class="col-md-6">
                        <mdb-btn outline="success" :disabled="waiting" @click.native="selectEmail">Email</mdb-btn>
                    </div>
                    <div class="col-md-6">
                        <mdb-btn outline="info" :disabled="waiting" @click.native="selectSMS">SMS</mdb-btn>
                    </div>
                </div>

                <br/>

                <template v-if="isMailChannel">

                    <template v-if="email">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="userEmail" :value="email"
                                           v-model="selectedEmail">
                                    <label class="custom-control-label" for="userEmail">{{email}}</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="custom-control custom-radio" @click.native="">
                                    <input type="radio" class="custom-control-input" id="otherEmail" value="other"
                                           v-model="selectedEmail">
                                    <label class="custom-control-label" for="otherEmail">Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <mdb-input v-if="selectedEmail === 'other'" label="Enter Email"
                                           v-model="customEmail"></mdb-input>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="row">
                            <div class="col-md-12">
                                <mdb-input label="Enter Email" v-model="customEmail"></mdb-input>
                            </div>
                        </div>
                    </template>

                </template>

                <template v-if="isSmsChannel">

                    <template v-if="phoneNumbers.length">
                        <div class="row">
                            <div class="col-md-12" v-for="(phoneNumber,index) in phoneNumbers">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" :id="index" :value="phoneNumber"
                                           v-model="selectedPhoneNumber">
                                    <label class="custom-control-label" :for="index">{{phoneNumber}}</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="otherNumber" value="other"
                                           v-model="selectedPhoneNumber">
                                    <label class="custom-control-label" for="otherNumber">Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <mdb-input v-if="selectedPhoneNumber === 'other'" label="Enter Phone Number"
                                           v-model="customPhoneNumber"></mdb-input>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="row">
                            <div class="col-md-12">
                                <mdb-input label="Enter Phone Number" v-model="customPhoneNumber"></mdb-input>
                            </div>
                        </div>
                    </template>
                </template>

                <mdb-alert v-if="error" color="danger">
                    {{error}}
                </mdb-alert>

            </div>

        </mdb-modal-body>
        <mdb-modal-footer>
            <mdb-btn color="warning" @click.native="cancel">Cancel</mdb-btn>
            <mdb-btn color="primary" @click.native="sendLink" :disabled="waiting">Send</mdb-btn>
        </mdb-modal-footer>
    </mdb-modal>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbInput,
        mdbModal,
        mdbModalBody,
        mdbModalFooter,
        mdbModalHeader,
        mdbModalTitle,
        mdbIcon
    } from 'mdbvue';

    const CHANNEL_MAIL = "mail";
    const CHANNEL_SMS = "sms";

    export default {
        name: "SendLinkModal",
        components: {
            mdbIcon,
            mdbModal,
            mdbModalBody,
            mdbModalFooter,
            mdbModalHeader,
            mdbModalTitle,
            mdbBtn,
            mdbAlert,
            mdbInput
        },
        props: ['options', 'onlySms', 'onlyEmail'],
        data() {
            return {
                patientId: null,
                waiting: false,
                error: null,
                selectedChannel: null,
                selectedEmail: null,
                selectedPhoneNumber: null,
                customEmail: null,
                customPhoneNumber: null,
                email: null,
                phoneNumbers: []
            };
        },
        created() {

        },
        mounted() {
            this.patientId = this.options.patientId;
            if (!this.isVitals) {
                this.getPatientContactInfo();
            }

            if (this.onlySms) {
                this.selectSMS();
            }
            else if (this.onlyEmail) {
                this.selectEmail();
            }

        },
        methods: {
            getPatientContactInfo() {
                if (!this.patientId) {
                    return;
                }

                this.waiting = true;

                const url = `/manage-patients/${this.patientId}/contact-info`;
                axios.get(url)
                    .then(resp => {
                        this.waiting = false;
                        this.phoneNumbers = resp.data && resp.data.phone_numbers ? resp.data.phone_numbers.map((x) => x.number) : [];
                        this.email = resp.data && resp.data.email ? resp.data.email : null;
                    })
                    .catch((error) => {
                        this.waiting = false;
                        this.handleError(error);
                    });
            },

            selectEmail() {
                this.error = null;
                this.selectedChannel = CHANNEL_MAIL;
                if (!this.email) {
                    this.selectedEmail = 'other';
                }
            },

            selectSMS() {
                this.error = null;
                this.selectedChannel = CHANNEL_SMS;
                if (!this.phoneNumbers || this.phoneNumbers.length === 0) {
                    this.selectedPhoneNumber = 'other';
                }
            },

            validate() {

                if (this.options.debug) {
                    return true;
                }

                let result = false;
                if (this.selectedChannel === CHANNEL_MAIL && this.selectedEmail === 'other') {
                    result = this.validateEmail(this.customEmail);
                    if (!result) {
                        this.error = "Invalid Email";
                    }
                }
                else if (this.selectedChannel === CHANNEL_SMS && this.selectedPhoneNumber === 'other') {
                    result = this.validatePhone(this.customPhoneNumber);
                    if (!result) {
                        this.error = "Invalid Phone number.";
                    }
                }
                return result;
            },

            validateEmail(email) {
                const re = /\S+@\S+\.\S+/;
                return re.test(email);
            },

            validatePhone(phone) {
                const res = this.sanitizePhoneNumber(phone);
                return res.startsWith('+') ? res.length === 11 : res.length === 10;
            },

            sanitizePhoneNumber(value) {
                let res = value.toString().trim();
                res = res.replace(/\(/g, '');
                res = res.replace(/\)/g, '');
                res = res.replace(/\s/g, '');
                return res;
            },

            sendLink() {

                if (!this.validate()) {
                    return;
                }

                this.error = null;
                this.waiting = true;

                const url = `/manage-patients/${this.patientId}/send-link/${this.isVitals ? 'vitals' : 'hra'}`;
                let target;
                if (this.selectedChannel === CHANNEL_MAIL) {
                    target = this.selectedEmail === 'other' ? this.customEmail : this.selectedEmail;
                }
                else {
                    target = this.selectedPhoneNumber === 'other' ? this.customPhoneNumber : this.selectedPhoneNumber;
                    target = this.sanitizePhoneNumber(target);
                }

                const req = {
                    target_patient_id: this.patientId,
                    channel: this.selectedChannel,
                    channel_value: target,
                };

                axios.post(url, req)
                    .then(resp => {
                        this.waiting = false;
                        if (typeof this.options.success !== 'undefined'){
                            //this is for send assessmen link component, so we trigger the success alert
                            this.options.success();
                        }
                        this.options.onDone();

                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    })
            },
            cancel() {
                this.options.onDone();
            },

            handleError(error) {
                console.log(error);
                if (error.response && error.response.status === 504) {
                    this.error = "Server took too long to respond. Please try again.";
                }
                else if (error.response && error.response.status === 500) {
                    this.error = "There was an error with our servers. Please contact CLH support.";
                    console.error(error.response.data);
                }
                else if (error.response && error.response.status === 404) {
                    this.error = "Not Found [404]";
                }
                else if (error.response && error.response.status === 419) {
                    this.error = "Not Authenticated [419]";
                    //reload the page which will redirect to login
                    window.location.reload();
                }
                else if (error.response && error.response.data) {
                    const errors = [error.response.data.error];
                    Object.keys(error.response.data.errors || []).forEach(e => {
                        errors.push(error.response.data.errors[e]);
                    });
                    this.error = errors.join('<br/>');
                } else {
                    this.error = error.message;
                }
            }
        },
        computed: {

            isVitals() {
                return this.options.survey === "vitals";
            },
            isMailChannel() {
                return this.selectedChannel === CHANNEL_MAIL;
            },
            isSmsChannel() {
                return this.selectedChannel === CHANNEL_SMS;
            },
            title() {
                return this.isVitals ? "Send Vitals Link" : "Send HRA Link";
            }
        }
    }
</script>

<style scoped>

    .container {
        position: relative;
    }

</style>
