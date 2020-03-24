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
                        <div v-if="showBanner" :class="bannerClass">{{bannerText}}</div>
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
                                  data-tooltip="Everytime you log in, a password will be sent to you using the method you select below. We recommend using Authy App, which you can download from the AppStore or PlayStore.">
                                            <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        </span>


                            <select v-model="method" id="method" class="form-control input-sm" :disabled="is_loading">
                                <option value="app" selected>Authy App (recommended)</option>
                                <option value="sms">SMS</option>
                                <option value="phone">Phone Call</option>
                            </select>
                            <span class="help-block">{{errors.get('method')}}</span>
                        </div>
                    </div>
                </div>

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

                <div @click="submitForm" :disabled="is_loading" class="btn btn-info btn-block">
                    Save
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
            }
        },
        data() {
            let data = {
                country_code: 1,
                phone_number: null,
                method: 'app',
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
                data.method = this.authyUser.authy_method;
                data.is_2fa_enabled = !!this.authyUser.is_authy_enabled;
            }

            return data;
        },
        methods: {
            submitForm() {
                let self = this;
                self.is_loading = true;

                return self.axios.post(rootUrl('api/account-settings/2fa'), {
                    country_code: this.country_code,
                    phone_number: this.phone_number,
                    method: this.method,
                    is_2fa_enabled: this.is_2fa_enabled,
                })
                    .then((response, status) => {
                        if (response) {
                            self.errors.clear()

                            self.bannerText = '2FA settings successfully saved!';
                            self.bannerType = 'success';
                            self.showBanner = true;

                            //if it's the first time the user is settign up 2FA, redirect them to home
                            //so that the 2FA box will show and they'll complete the process
                            if (_.isNull(this.authyUser)) {
                                window.location.href = '/';
                            }

                            this.is_loading = false;

                            console.log(response)
                        }
                    }).catch(err => {
                        this.is_loading = false;

                        console.log(err)

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