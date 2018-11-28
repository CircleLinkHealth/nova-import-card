<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Enter 2FA Token
                    <span class="loader-right">
                    <loader v-show="isLoading"></loader>
                </span>
                </h3>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-12">
                        <input type="text" v-model="token" id="token" class="form-control input-sm"
                               placeholder="Token via App, Chrome Extension, SMS, or Voice.">
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <input type="submit" value="SMS" @click="sendSms" class="btn btn-info btn-block">
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <input type="submit" value="Voice" @click="voiceCall" class="btn btn-info btn-block">
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <input type="submit" value="Push Notification" @click="sendOneTouchRequest"
                               class="btn btn-primary btn-block">
                    </div>
                </div>

                <div @click="verifyToken" :disabled="isLoading" class="btn btn-info btn-block">
                    Verify Token
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
        data() {
            return {
                authyMethod: this.user.authy_method ? this.user.authy_method : 'app',
                isLoading: false,
                checkPollHandler: null,
                token: null,
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
                        alert("Problem Checking Approval Request status");
                    });
            },
            success() {
                this.startLoader();
                console.log("2FA Successful. Redirecting to home.");
                window.location.href = '/';
            }
        }),
        mounted() {

        }
    }
</script>
<style>
    .loader-right {
        margin-top: -4px;
        float: right;
    }
</style>