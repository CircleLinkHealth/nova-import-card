<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Two Factor Authentication
                    <span class="loader-right">
                    <loader v-show="isLoading"></loader>
                </span>
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div v-if="showBanner" :class="bannerClass">{{bannerText}}</div>
                    </div>

                    <div class="col-md-12">
                        <div v-if="isApp && checkPollHandler">
                            You may use OneTouch in <a href="https://authy.com/download/" target="_blank"><u>the app</u></a>,
                            or enter a token in the textbox below to complete the login process.
                        </div>

                        <div class="form-group margin-top-10" :class="{'has-error':errors.has('token')}"
                             :disabled="isLoading">
                            <input type="text" v-model="token" id="token" class="form-control input-sm"
                                   placeholder="Token via App, Chrome Extension, SMS, Voice or Authenticator app.">
                            <span class="help-block">{{errors.get('token')}}</span>
                        </div>

                        <div class="margin-top-10">
                            <div @click="verifyToken" :disabled="isLoading"
                                 class="btn btn-info btn-block margin-top-10">
                                Verify Token
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div v-if="!showOtherMethods" @click="showOtherMethods = true"
                             class="btn btn-default margin-top-10">Try other Methods
                        </div>

                        <div v-if="!authyMethod || showOtherMethods" class="margin-top-10">
                            <a v-if="!isApp" class="block clickable" href="https://authy.com/download/" target="_blank">
                                Download Authy app (recommended)
                            </a>
                            <br>
                            <a class="block clickable" @click="sendSms">
                                Send SMS token
                            </a>
                            <br>
                            <a class="block clickable" @click="voiceCall">
                                Receive a call and listen to the token.
                            </a>
                            <br>
                            <a class="block clickable" href="/settings">
                                Setup Other Authenticator App
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 margin-top-15">
                        <div v-if="isSms && showSendAgain" class="btn btn-submit btn-block" @click="sendSms">
                            Re-send SMS
                        </div>
                        <div v-if="isVoice && showSendAgain" class="btn btn-submit btn-block" @click="voiceCall">
                            Call Again
                        </div>
                        <div v-if="isApp && showSendAgain" class="btn btn-submit btn-block"
                             @click="sendOneTouchRequest">
                            Re-send OneTouch Request
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import LoaderComponent from './loader';
    import {rootUrl} from "../app.config";
    import Errors from "./src/Errors";

    export default {
        name: 'authy-perform-2fa',
        props: [
            'authyUser',
            'redirectTo'
        ],
        components: {
            'loader': LoaderComponent,
        },
        computed: {
            isSms() {
                return this.authyMethod === 'sms';
            },
            isVoice() {
                return this.authyMethod === 'phone';
            },
            isApp() {
                return this.authyMethod === 'app';
            },
            isQrCode() {
                return this.authyMethod === 'qr_code';
            },
            bannerClass() {
                return 'alert alert-' + this.bannerType;
            }
        },
        data() {
            return {
                authyMethod: this.authyUser.authy_method,
                authyId: this.authyUser.authy_id,
                isAuthyEnabled: this.authyUser.is_authy_enabled,
                isLoading: false,
                checkPollHandler: null,
                token: null,
                showSendAgain: false,
                showBanner: false,
                showOtherMethods: false,
                bannerText: '',
                bannerType: 'info',
                errors: new Errors()
            }
        },
        methods: {
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
                    })
                    .catch(err => {
                        this.stopLoader();

                        console.error("VerifyToken error: ", err);

                        let bannerText = '';
                        let errors = [];
                        if (err.response && err.response.data) {
                            if (err.response.data.errors) {
                                errors = err.response.data.errors;
                            }
                            if (err.response.data.message) {
                                bannerText = err.response.data.message;
                            }
                        }

                        this.errors.setErrors(errors);

                        self.bannerText = bannerText;
                        self.bannerType = 'danger';
                        self.showBanner = true;
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

                            self.bannerText = 'An SMS with your token is on its way!';
                            self.bannerType = 'info';
                            self.showBanner = true;
                        }
                    })
                    .catch(err => {
                        this.stopLoader();

                        console.error("Sms error: ", err);

                        let message = '';
                        if (err.response && err.response.data && err.response.data.errors && err.response.data.errors.message) {
                            message = err.response.data.errors.message;
                        }
                        message += 'Please try another method.';

                        self.bannerText = 'Could not send SMS. ' + message;
                        self.bannerType = 'danger';
                        self.showBanner = true;
                        self.showSendAgain = true
                    });
            },
            voiceCall() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('api/2fa/token/voice'), {})
                    .then((response, status) => {
                        if (response) {
                            this.stopLoader();

                            console.log(response);

                            self.bannerText = 'We are calling you to tell you your token!!';
                            self.bannerType = 'info';
                            self.showBanner = true;
                        }
                    })
                    .catch(err => {
                        this.stopLoader();

                        console.error("Voice error: ", err);

                        self.bannerText = 'Could not start call';
                        self.bannerType = 'danger';
                        self.showBanner = true;
                        self.showSendAgain = true
                    });
            },
            setIntervalX(callback, delay, repetitions) {
                let x = 0;

                this.checkPollHandler = window.setInterval(() => {

                    callback();

                    if (++x === repetitions) {
                        window.clearInterval(this.checkPollHandler);
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

                            self.bannerText = 'We sent a push notification to your phone!';
                            self.bannerType = 'info';
                            self.showBanner = true;
                        }
                    }).catch(err => {
                        this.stopLoader();

                        console.error("SendOneTouchRequest error: ", err);

                        self.bannerText = 'Could not send One Touch request.';
                        self.bannerType = 'danger';
                        self.showBanner = true;
                        self.showSendAgain = true
                    });
            },
            checkOneTouchRequestStatus() {
                let self = this;

                console.log('check status')

                return this.axios.post(rootUrl('api/2fa/one-touch-request/check-status'), {})
                    .then((response, status) => {
                        if (response) {
                            console.log("OneTouchRequest Status: ", response);

                            let status = response.data.approval_request_status;

                            if (status === "approved") {
                                clearInterval(self.checkPollHandler)
                                this.success()
                            } else if (status === "denied") {
                                clearInterval(self.checkPollHandler)
                                this.denied()
                            } else {
                                console.log("Approval Request not yet approved");
                            }
                        }
                    }).catch(err => {
                        console.error("CheckOneTouchRequest error: ", err);
                    });
            },
            success() {
                let self = this;

                self.errors.clear();
                self.startLoader();

                console.log(`2FA successful! Redirecting to ${self.redirectTo}.`);

                self.bannerText = '2FA successful! Redirecting...';
                self.bannerType = 'success';
                self.showBanner = true;

                window.location.href = self.redirectTo || '/';
            },
            denied() {
                let self = this;

                console.log("Request denied by User.");

                self.bannerText = 'Login request denied by User.';
                self.bannerType = 'warning';
                self.showBanner = true;
            }
        },
        created() {
            //if authyUser has a preference, send the token
            if (this.isAuthyEnabled && this.authyId && this.authyMethod) {
                let self = this;

                if (self.isApp) {
                    self.sendOneTouchRequest();
                }

                if (self.isVoice) {
                    self.voiceCall();
                }

                if (self.isSms) {
                    self.sendSms();
                }

                if (self.isQrCode) {
                    self.bannerText = 'Please type here the code from your Authenticator app';
                    self.bannerType = 'info';
                    self.showBanner = true;
                }

                if (!self.isQrCode) {
                    setTimeout(function () {
                        self.showSendAgain = true;
                    }, 10000);
                }
            }

            //otherwise default to app
            if (!this.authyMethod) {
                this.authyMethod = 'app';
            }
        }
    }
</script>
<style scoped>
    .loader-right {
        margin-top: -4px;
        float: right;
    }

    .margin-top-10 {
        margin-top: 10%;
    }

    .margin-top-15 {
        margin-top: 10%;
    }

    .clickable {
        cursor: pointer;
    }
</style>
