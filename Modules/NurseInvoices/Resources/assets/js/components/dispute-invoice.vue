<template>
    <div>
        <div class="form-group" :class="{'has-error':errors.has('reason')}">
            <label for="dispute"><h3>Dispute Invoice</h3></label>
            <textarea class="form-control" id="dispute" v-model="reason"
                      placeholder="Type reasons for dispute here" rows="8" required>
            </textarea>
            <span class="help-block">{{errors.get('reason')}}</span>
        </div>

        <div class="form-group text-right">
            <button id="submit" class="btn btn-danger" @click="submitForm" :disabled="isLoading">
                Dispute Invoice
                <span class="loader-right">
                    <loader v-show="isLoading"></loader>
                </span>
            </button>
        </div>
    </div>
</template>
<script>
    import LoaderComponent from '../../../../../../resources/assets/js/components/loader';
    import {rootUrl} from "../../../../../../resources/assets/js/app.config.js";
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
                isLoading: false,
                errors: new Errors(),
                reason: null
            }
        },
        methods: {
            startLoader() {
                this.isLoading = true;
            },
            stopLoader() {
                this.isLoading = false;
            },
            submitForm() {
                let self = this;
                this.startLoader();

                return this.axios.post(rootUrl('nurseinvoices/dispute'), {
                    reason: this.reason,
                    invoiceId: this.invoiceId,
                })
                    .then((response, status) => {
                        if (response) {
                            console.log(response);

                            this.success()
                        }
                    }).catch(err => {
                        this.stopLoader();
                        this.errors.setErrors(err.response.data.errors ? err.response.data.errors : []);
                    });
            },
            success() {
                let self = this;

                self.errors.clear();
                self.startLoader();

                console.log("success");
            },
        },
        created() {

        }
    }
</script>
<style scoped>
    .loader-right {
        margin-top: -4px;
        float: right;

        border: 5px solid #31C6F9;
        -webkit-animation: spin 1s linear infinite;
        animation: spin 1s linear infinite;
        border-top: 5px solid #555;
        border-radius: 50%;
        width: 15px;
        height: 15px;
    }
    .loader {
        width: 20px;
        height: 20px;
    }
</style>