<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">We need you to complete 2FA
                    <span class="loader-right">
                    <loader v-show="isLoading"></loader>
                </span>
                </h3>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-12">
                        <div v-if="isApp && checkPollHandler">
                            <h5>We've send you a push notification on your phone!</h5>
                            Please use OneTouch in the app to complete the login process.
                            <a href="https://authy.com/download/" target="_blank">Don't have the app? Click here</a>
                        </div>
                        <div v-else>
                                <input type="text" v-model="token" id="token" class="form-control input-sm"
                                       placeholder="Enter verification token.">

                            <div style="padding-top: 3%;">
                                <a href="https://authy.com/download/" target="_blank">Don't have the app? Click here to get
                                    it.</a>
                            </div>

                            <div style="padding: 6% 0;">
                                <div @click="verifyToken" :disabled="isLoading" class="btn btn-info btn-block">
                                    Verify Token
                                </div>
                            </div>

                            <a>
                                <small @click="sendSms">receive the token via sms</small>
                            </a>
                            |
                            <a>
                                <small @click="voiceCall">receive a call and listen to the token.</small>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div v-if="isSms && showSendAgain" class="col-xs-12 col-sm-12 col-md-12" @click="sendSms">
                        Sms
                    </div>
                    <div v-if="isVoice && showSendAgain" class="col-xs-12 col-sm-12 col-md-12" @click="voiceCall">
                        Voice
                    </div>
                    <div v-if="isApp && showSendAgain" class="col-xs-12 col-sm-12 col-md-12"
                         @click="sendOneTouchRequest">
                        Send One Touch Request
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
    import LoaderComponent from './loader';
    import {rootUrl} from "../app.config";
    import {addNotification} from '../store/actions'
    import {mapActions} from 'vuex'

    export default {
        name: 'authy-perform-2fa',
        props: [
            'user',
        ],
        components: {
            'loader': LoaderComponent,
        },
        computed: {
            isSms() {
                return this.authyMethod === 'sms'
            },
            isVoice() {
                return this.authyMethod === 'phone'
            },
            isApp() {
                return this.authyMethod === 'app'
            }
        },
        data() {
            return {
                authyMethod: this.user.authy_method,
                authyId: this.user.authy_id,
                isAuthyEnabled: this.user.is_authy_enabled,
                isLoading: false,
                checkPollHandler: null,
                token: null,
                showSendAgain: false,
            }
        },
        methods: Object.assign(mapActions(['addNotification']), {
            verifyToken() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('api/2fa/token/verify'), {
                    token: this.token
                })
                    .then((response, status) => {
                        if (response) {
                            console.log(response);

                            this.success()
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("VerifyToken error: ", err);
                        alert(err.data.message);
                    });
            },
            startLoader() {
                this.isLoading = true;
            },
            stopLoader() {
                this.isLoading = false;
            },
            sendSms() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('api/2fa/token/sms'), {})
                    .then((response, status) => {
                        if (response) {
                            this.stopLoader();

                            console.log(response);

                            alert("Sms sent!");
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("Sms error: ", err);
                        alert("Problem sending sms");
                    });
            },
            voiceCall() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('api/2fa/token/voice'), {})
                    .then((response, status) => {
                        if (response) {
                            this.stopLoader();

                            console.log(response)

                            alert("We are calling you in the next few moments.");
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("Sms error: ", err);
                        alert("Problem sending sms");
                    });
            },
            setIntervalX(callback, delay, repetitions) {
                let x = 0;
                this.checkPollHandler = window.setInterval(function () {

                    callback();

                    if (++x === repetitions) {
                        window.clearInterval(intervalID);
                    }
                }, delay);
            },
            sendOneTouchRequest() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('api/2fa/one-touch-request/create'), {})
                    .then((response, status) => {
                        if (response) {
                            this.stopLoader();

                            console.log(response)

                            clearInterval(self.checkPollHandler)

                            self.setIntervalX(() => {
                                self.checkOneTouchRequestStatus()
                            }, 3000, 40)
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("SendOneTouchRequest error: ", err);
                        alert("Problem creating Approval Request");
                    });
            },
            checkOneTouchRequestStatus() {
                let self = this;
                this.startLoader();

                console.log('check status')

                return this.axios.post(rootUrl('api/2fa/one-touch-request/check-status'), {})
                    .then((response, status) => {
                        if (response) {
                            console.log("OneTouchRequest Status: ", response);

                            this.stopLoader();

                            if (response.data.approval_request_status === "approved") {
                                clearInterval(self.checkPollHandler)
                                this.success()
                            } else {
                                console.log("Approval Request not yet approved");
                            }
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("CheckOneTouchRequest error: ", err);
                    });
            },
            success() {
                this.startLoader();
                console.log("2FA Successful. Redirecting to home.");
                window.location.href = '/';
            }
        }),
        mounted() {
            //if user has a preference, send the token
            if (this.isAuthyEnabled && this.authyId && this.authyMethod) {
                if (this.isApp) {
                    let self = this;
                    this.sendOneTouchRequest();
                }

                if (this.isVoice()) {
                    let self = this;
                    this.voiceCall();
                }

                if (this.isSms()) {
                    let self = this;
                    this.sendSms();
                }

                setTimeout(function () {
                    self.showSendAgain = true;
                }, 30000);
            }

            //otherwise default to app
            if (!this.authyMethod) {
                this.authyMethod = 'app';
            }
        }
    }
</script>
<style>
    .loader-right {
        margin-top: -4px;
        float: right;
    }
</style>