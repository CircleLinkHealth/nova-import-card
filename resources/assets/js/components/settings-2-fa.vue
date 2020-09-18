<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Authy Settings (2FA)
                    <span class="loader-right">
                    <loader v-show="is_loading"></loader>
                </span>
                </h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div v-if="showBanner" :class="bannerClass" v-html="bannerText">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-4">
                        <div class="form-group" :class="{'has-error':errors.has('country_code')}">
                            <select v-model="country_code" id="country_code" class="form-control input-sm"
                                    :disabled="is_loading">
                                <option value="1" selected>USA (+1)</option>
                                <option value="357">Cyprus (+357)</option>
                                <option value="33">France (+33)</option>
                            </select>
                            <span class="help-block">{{errors.get('country_code')}}</span>
                        </div>
                    </div>
                    <div class="col-xs-8 col-sm-8 col-md-8">
                        <div class="form-group" :class="{'has-error':errors.has('phone_number')}">
                            <input type="tel" v-model="phone_number" class="form-control input-sm"
                                   :disabled="is_loading"
                                   placeholder="Phone Number">
                            <span class="help-block">{{errors.get('phone_number')}}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group" :class="{'has-error':errors.has('method')}">
                            <label for="method">Authenticate using</label>

                            <span class="info minimum-padding"
                                  data-tooltip="Everytime you log in, a password will be sent to you using the method you select below. We recommend using Authy App, which you can download from the App Store (iOS) or Play Store (Android).">
                                            <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        </span>


                            <select v-model="method"
                                    @change="onMethodChanged"
                                    id="method"
                                    class="form-control input-sm"
                                    :disabled="is_loading">
                                <option value="app" selected>Authy App (recommended)</option>
                                <option value="sms">SMS</option>
                                <option value="phone">Phone Call</option>
                                <option value="qr_code">Other Authenticator App</option>
                            </select>
                            <span class="help-block">{{errors.get('method')}}</span>
                        </div>
                    </div>
                </div>

                <div class="row" v-if="isQrCode && !qrCodeImageSrc">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="margin-top-10">
                            <div @click="requestQrCode" :disabled="is_loading || !hasEnteredPhone"
                                 class="btn btn-info btn-block margin-top-10">
                                Generate QR code
                            </div>
                            <span class="help-block">Warning: Generating a QR code will invalidate any existing QR code you may have generated in the past.</span>
                        </div>
                    </div>
                </div>

                <div class="row" v-if="isQrCode && !qrCodeVerified && qrCodeImageSrc">
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <img :src="qrCodeImageSrc"/>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12" :class="{'has-error':errors.has('token')}"
                         :disabled="is_loading">
                        <input type="text" v-model="token" id="token" class="form-control input-sm"
                               placeholder="Token via App, Chrome Extension, SMS, Voice or Authenticator app.">
                        <span class="help-block">{{errors.get('token')}}</span>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div @click="verifyToken" :disabled="is_loading"
                             class="btn btn-info btn-block margin-top-10">
                            Verify Token
                        </div>
                    </div>
                </div>

                <br/>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group" :class="{'has-error':errors.has('is_2fa_enabled')}">
                            <label for="is_2fa_enabled">Enable 2FA</label>

                            <span v-if="isAdmin" class="info minimum-padding"
                                  data-tooltip="Administrators are required to have 2FA enabled.">
                                <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                            </span>
                            <span v-else class="info minimum-padding"
                                  data-tooltip="You are required to have 2FA enabled.">
                                <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                            </span>

                            <input type="checkbox" v-model="is_2fa_enabled" id="is_2fa_enabled"
                                   :disabled="is_loading || isAdmin || forceEnable"
                                   class="form-control input-sm" style="display: inline-block;">
                            <span class="help-block">{{errors.get('is_2fa_enabled')}}</span>
                        </div>
                    </div>
                </div>

                <div @click="submitForm" :disabled="is_loading || (isQrCode && !qrCodeVerified)"
                     class="btn btn-info btn-block">
                    Save
                </div>

            </div>
        </div>
    </div>
</template>
<script>
    import LoaderComponent from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader';
    import {rootUrl} from "../app.config";
    import Errors from "./src/Errors";

    export default {
        name: 'settings-2fa',
        props: {
            authyUser: {
                type: Object,
                default: function () {
                    //setting defaults like this does not always work. @todo: investigate at some point
                    return {
                        country_code: 1,
                        phone_number: '',
                        authy_method: 'app',
                        is_authy_enabled: false
                    }
                }
            },
            globalRole: {
                type: Object,
                default: function () {
                    //setting defaults like this does not always work. @todo: investigate at some point
                    return {
                        id: null,
                        name: null,
                        display_name: null,
                    }
                }
            },
            forceEnable: {
                type: Boolean,
                default: false
            }
        },
        components: {
            'loader': LoaderComponent,
        },
        computed: {
            bannerClass() {
                return 'alert alert-' + this.bannerType;
            },
            isAdmin() {
                return this.globalRole.name === 'administrator';
            },
            isQrCode() {
                return this.method === 'qr_code';
            },
            hasEnteredPhone() {
                //naive phone validation
                return this.phone_number && this.phone_number.length >= 8;
            }
        },
        data() {
            let data = {
                country_code: 1,
                phone_number: null,
                method: 'app',
                qrCodeImageSrc: null,
                qrCodeVerified: false,
                token: null, //needed when verifying QR code
                is_2fa_enabled: null,
                is_loading: false,
                errors: new Errors(),
                showBanner: false,
                bannerText: '',
                bannerType: 'info',
            };

            if (this.authyUser) {
                data.country_code = this.authyUser.country_code;
                data.phone_number = this.authyUser.phone_number;
                data.is_2fa_enabled = !!this.authyUser.is_authy_enabled;
                if (this.authyUser.authy_method) {
                    data.method = this.authyUser.authy_method;
                }
            }

            return data;
        },
        methods: {
            onMethodChanged(ev) {
                if (!this.isQrCode) {
                    return;
                }

                if (!this.hasEnteredPhone) {
                    this.bannerText = 'Please enter your phone number and click the button to generate a QR code.';
                    this.bannerType = 'info';
                    this.showBanner = true;
                }
            },
            requestQrCode() {
                let self = this;

                const prom = self.authyUser ? Promise.resolve() : self.updateAuthyUser(true);
                return prom
                    .then(() => {
                        self.is_loading = true;
                        return self.axios.post(rootUrl('api/account-settings/2fa/qr-code'), {});
                    })
                    .then((response, status) => {
                        if (!response || !response.data || !response.data.qr_code) {
                            throw new Error("qr_code not found in response");
                        }

                        self.is_loading = false;
                        self.bannerText = 'Please scan this QR code with your authenticator app and then verify below.';
                        self.bannerType = 'info';
                        self.showBanner = true;

                        self.qrCodeImageSrc = response.data.qr_code;
                    })
                    .catch(err => {
                        self.is_loading = false;

                        console.error("QR code error: ", err);

                        let errors = Object.values(err.response.data.errors ? err.response.data.errors : {}).flat();
                        const errorMessages = ['Could not generate QR code.'].concat(errors);

                        self.bannerText = errorMessages.join('<br/>');
                        self.bannerType = 'danger';
                        self.showBanner = true;
                    });
            },
            verifyToken() {
                let self = this;
                self.is_loading = true;

                return self.axios
                    .post(rootUrl('api/2fa/token/verify'), {
                        token: this.token,
                        is_setup: true
                    })
                    .then((response, status) => {
                        self.is_loading = false;
                        self.qrCodeVerified = true;
                        this.errors.clear();
                    })
                    .catch(err => {
                        console.log(err);
                        this.is_loading = false;
                        const errors = err.response.data.errors ? err.response.data.errors : [];
                        if (err.response.data.message) {
                            errors['token'] = [err.response.data.message];
                        }
                        this.errors.setErrors(errors);
                    });
            },
            /**
             * Call with register phone only if you need
             * to generate a QR code from Authy but you don't have
             * the user registered to Authy yet.
             *
             * @param registerPhoneOnly
             * @returns {Promise<unknown>}
             */
            updateAuthyUser(registerPhoneOnly) {
                const data = {
                    country_code: this.country_code,
                    phone_number: this.phone_number,
                    is_2fa_enabled: this.is_2fa_enabled
                };

                if (!registerPhoneOnly) {
                    data['method'] = this.method;
                }
                return self.axios.post(rootUrl('api/account-settings/2fa'), data);
            },
            submitForm() {
                let self = this;
                self.is_loading = true;

                return this.updateAuthyUser(false)
                    .then((response, status) => {
                        if (response) {
                            self.errors.clear();

                            self.bannerText = '2FA settings successfully saved!';
                            self.bannerType = 'success';
                            self.showBanner = true;

                            // if it's the first time the user is settign up 2FA, redirect them to home
                            // so that the 2FA box will show and they'll complete the process
                            // UPDATE:  why stay on the same page? credit to nektarios for bringing this up
                            // if (_.isNull(this.authyUser)) {
                            window.location.href = '/';
                            // }

                            this.is_loading = false;

                            console.log(response);
                        }
                    })
                    .catch(err => {
                        this.is_loading = false;

                        console.log(err);

                        let errors = err.response.data.errors ? err.response.data.errors : [];
                        this.errors.setErrors(errors);
                    });
            }
        },
        mounted() {
            if (this.isAdmin || this.forceEnable) {
                this.is_2fa_enabled = true;
            }
        }
    }
</script>
<style scoped>
    .loader-right {
        margin-top: -4px;
        float: right;
    }
</style>
