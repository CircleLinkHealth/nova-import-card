<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Enter 2FA Token
                    <span class="loader-right">
                    <loader v-show="is_loading"></loader>
                </span>
                </h3>
            </div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-12">
                        <input type="text" name="token" id="token" class="form-control input-sm"
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
                        <input type="submit" value="Push Notification" @click="sendApprovalRequest"
                               class="btn btn-primary btn-block">
                    </div>
                </div>

                <div @click="verifyToken" :disabled="is_loading" class="btn btn-info btn-block">
                    Save
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
                is_loading: false,
            }
        },
        methods: Object.assign(mapActions(['addNotification']), {
            verifyToken() {
                this.is_loading = true;
            },
            sendSms() {
            },
            voiceCall() {
            },
            sendApprovalRequest() {
                this.is_loading = true;

                return this.axios.post(rootUrl('api/2fa/approval-request/create'), {})
                    .then((response, status) => {
                        if (response) {
                            this.is_loading = false;

                            console.log(response)

                            setTimeout(() => {
                                this.checkApprovalRequestStatus()
                            }, 5000, 24)
                        }
                    }).catch(err => {
                        this.is_loading = false;

                        console.error("SendApprovalRequest error: ", err);
                        alert("Problem creating Approval Request");
                    });
            },
            checkApprovalRequestStatus() {
                this.is_loading = true;

                console.log('check status')

                return this.axios.post(rootUrl('api/2fa/approval-request/check-status'), {})
                    .then((response, status) => {
                        if (response) {
                            console.log("ApprovalRequest Status: ", response);

                            this.is_loading = false;

                            if (data.approval_request.status === "approved") {
                                $window.location.href = $window.location.origin + "/protected";
                                $interval.cancel(pollingID);
                            } else {
                                console.log("One Touch Request not yet approved");
                            }
                        }
                    }).catch(err => {
                        this.is_loading = false;

                        console.error("CheckApprovalRequest error: ", err);
                        alert("Problem Checking Approval Request status");
                    });
            },
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