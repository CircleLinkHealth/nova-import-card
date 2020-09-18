<template>
    <div>
        <div v-if="!showBanner">
            <div class="text-right">
                <div class="btn-container" data-step="5" data-intro="Use this button to show or hide the dispute form.">
                    <button id="toggle-invoice-dispute-form" class="btn btn-default" @click="showDisputeForm = ! showDisputeForm">
                        {{showDisputeForm ? 'Hide' : 'Show'}} Dispute Form
                    </button>
                </div>

                <div class="btn-container" data-step="4" data-intro="If you agree with the invoice, click this button and you're done!">
                    <button class="btn btn-success" @click="submitApproval" :disabled="loaders.approve">
                        Approve Invoice
                        <span class="loader-right">
                        <loader v-show="loaders.approve"></loader>
                    </span>
                    </button>
                </div>
            </div>

            <div v-show="showDisputeForm">
                <div class="form-group" :class="{'has-error':errors.has('reason')}" data-step="6" data-intro="Write a short message explaining which parts of this invoice you are disputing.">
                    <label for="dispute"><h3>Dispute Invoice</h3></label>
                    <textarea class="form-control" id="dispute" v-model="reason"
                              placeholder="Type reasons for dispute here" rows="8" required>
                    </textarea>
                    <span class="help-block">{{errors.get('reason')}}</span>
                </div>

                <div class="form-group text-right">
                    <div class="btn-container" data-step="7" data-intro="Click 'Dispute Invoice' and you're done! You may come back to this page to check the status of your dispute. We will also send you an email once the dispute has been resolved.">
                        <button class="btn btn-danger" @click="submitDispute" :disabled="loaders.dispute">
                            Dispute Invoice
                            <span class="loader-right">
                                <loader v-show="loaders.dispute"></loader>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="col-md-12 alert alert-success">
                <h4>{{bannerText}}</h4>
            </div>
        </div>
    </div>
</template>
<script>
    import LoaderComponent from '../../../../../SharedVueComponents/Resources/assets/js/components/loader';
    import {rootUrl} from "../../../../../SharedVueComponents/Resources/assets/js/app.config.js";
    import Errors from "../../../../../../resources/assets/js/components/src/Errors";

    export default {
        props: [
            'invoiceId',
        ],
        components: {
            'loader': LoaderComponent,
        },
        computed: {
            bannerClass() {
                return 'alert alert-';
            }
        },
        data() {
            return {
                loaders: {
                    approve: false,
                    dispute: false,
                },
                errors: new Errors(),
                reason: null,
                showDisputeForm: false,
                showBanner: false,
                bannerText: '',
            }
        },
        methods: {
            submitDispute() {
                let self = this;
                self.loaders.dispute = true;

                return this.axios.post(rootUrl('nurseinvoices/dispute'), {
                    reason: this.reason,
                    invoiceId: this.invoiceId,
                })
                    .then((response, status) => {
                        if (response) {
                            self.loaders.dispute = false;
                            self.bannerText = 'Your dispute has been submitted. We\'ll get back to you as soon as possible.';
                            self.showDisputeForm = false;

                            self.success()
                        }
                    }).catch(err => {
                        self.loaders.dispute = false;
                        this.errors.setErrors(err.response.data.errors ? err.response.data.errors : []);
                    });
            },
            submitApproval() {
                let self = this;
                self.loaders.approve = true;

                return this.axios.post(rootUrl('nurseinvoices/approve'), {
                    invoiceId: this.invoiceId,
                })
                    .then((response, status) => {
                        if (response) {
                            self.loaders.approve = false;
                            self.bannerText = 'Thank you for approving the invoice!';

                            self.success()
                        }
                    }).catch(err => {
                        self.loaders.approve = false;
                        this.errors.setErrors(err.response.data.errors ? err.response.data.errors : []);
                    });
            },
            success() {
                let self = this;

                self.errors.clear();
                self.showBanner = true;
            },
        }
    }
</script>
<style scoped>
    .loader-right {
        margin: 2px 0 0 7px;
        float: right;
    }

    .loader {
        width: 15px;
        height: 15px;
    }

    .btn-container {
        display: inline-block;
        padding-left: 2%;
    }
</style>