<template>
    <div class="panel panel-primary shadow">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <div class="col-md-12  panel-section" style="margin-top: 20px">
                <div v-if="doc" class="">
                    <button class="btn btn-success btn-static disabled col-md-6 btn-m">Available</button>
                </div>
                <div class="" v-else>
                    <button class="btn col-md-6 btn-default btn-static disabled btn-m">Unavailable</button>
                </div>
                <div v-if="doc" class="col-md-6">
                    <a class="blue-link" v-bind:class="{'isDisabled': !doc}" style="float: right; padding-top: 7px"
                       :href="viewApi()" target="_blank">View</a>
                </div>
            </div>
            <div>
                <div class="col-md-6" style="margin-top: 5px">
                    {{this.docDate}}
                </div>
            </div>
            <div class="col-md-12  panel-section" style="margin-top: 15px">
                <p><span class="strong-custom">Send document via:</span></p>
            </div>
            <div class="col-md-12  panel-section" style="margin-top: 9px">
                <button class="col-md-4 btn btn-method btn-width-60 btn-xs" v-bind:class="{'isDisabled': !doc, 'disabled': !doc}"
                        @click="openSendModal('direct')">
                    DIRECT
                </button>
                <button class="col-md-4 btn btn-method btn-width-60 btn-xs" v-bind:class="{'isDisabled': !doc, 'disabled': !doc}"
                        @click="openSendModal('fax')">
                    Fax
                </button>
                <button title="(Secure Link)" class="col-md-4 btn btn-method btn-width-60  btn-xs"
                        v-bind:class="{'isDisabled': !doc, 'disabled': !doc}" @click="openSendModal('email')">
                    Email
                </button>
            </div>
            <div class="col-md-12 panel-section" style="margin-top: 10px">
                <a class="blue-link" v-bind:class="{'isDisabled': !doc, 'disabled': !doc}" :href="downloadApi()">Download</a>
            </div>
        </div>
        <modal v-show="showSendModal" name="send-care-doc" class="modal-send-care-doc" :no-title="true"
               :no-footer="true">
            <template slot="header">
                <button type="button" class="close" @click="closeSendModal()">×</button>
                <h3 class="modal-title">Send Care Document</h3>
            </template>
            <template slot="body">
                <div class="col-md-12 form-group">
                    <div class="col-md-10 row">
                        <p><span class="strong-custom">Enter {{this.inputName}}:</span></p>
                    </div>
                    <div class="col-md-2 row">
                        <loader style="text-align: center" v-if="loading"/>
                    </div>
                </div>
                <div class="col-md-12">
                    <div v-if="showBanner" :class="bannerClass">{{this.errors}}</div>
                </div>
                <div class="col-md-12 form-group">
                    <div class="col-md-12 row">
                        <input id="addressOrFax" :type="this.inputType" v-model="addressOrFax" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <div class="col-md-12 row">
                        <button type="submit" @click="sendCareDocument()" class="btn btn-primary btn-large">Send
                        </button>
                    </div>
                </div>

            </template>
            <template slot="footer">
            </template>
        </modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js'
    import modal from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/modal.vue'
    import Loader from '../../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader.vue';

    let self;

    export default {
        name: "care-document-box",
        components: {
            'modal': modal,
            'loader': Loader,
        },
        data() {
            return {
                loading: false,
                showSendModal: false,
                addressOrFax: '',
                channel: '',
                inputName: '',
                inputType: '',
                errors: '',
                showBanner: false,
                bannerText: '',
                bannerType: 'info',
            }
        },
        props: {
            type: {
                type: String,
                required: true
            },
            doc: {
                type: Object,
                required: false
            },
            patientId: {
                type: String,
                required: true
            }
        },
        computed: {
            docDate() {
                if (!this.doc) {
                    return null;
                }
                var date = new Date(this.doc.created_at);
                var year = date.getFullYear();
                var month = (1 + date.getMonth()).toString();
                month = month.length > 1 ? month : '0' + month;
                var day = date.getDate().toString();
                day = day.length > 1 ? day : '0' + day;
                return year + '-' + month + '-' + day;
            },
            bannerClass() {
                return 'alert alert-' + this.bannerType;
            },
        },
        methods: {
            viewApi() {
                if (!this.doc) {
                    return null;
                }
                const query = {
                    file: this.doc
                };
                return rootUrl('/view-care-document/' + this.patientId + '/' + this.doc.id);
            },
            downloadApi() {
                if (!this.doc) {
                    return null;
                }
                const query = {
                    file: this.doc
                };
                return rootUrl('/download-care-document/' + this.patientId + '/' + this.doc.id);
            },
            openSendModal(channel) {
                if (!this.doc) {
                    return null;
                }
                switch (channel) {
                    case "email":
                        this.inputType = 'email';
                        this.inputName = 'email address';
                        break;

                    case "direct":
                        this.inputType = 'email';
                        this.inputName = 'DIRECT mail address';
                        break;

                    case "fax":
                        this.inputType = 'tel';
                        this.inputName = 'fax number';
                        break;
                }

                this.channel = channel
                this.showSendModal = true
            },
            sendCareDocument() {
                this.loading = true;

                return this.axios
                    .post(rootUrl('/send-care-doc/' + this.patientId + '/' + this.doc.id + '/' + this.channel + '/' + this.addressOrFax))
                    .then(response => {
                        this.loading = false;
                        this.closeSendModal();
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data ? err.response.data : [];
                        this.errors = errors;
                        this.bannerType = 'danger';
                        this.bannerText = errors;
                        this.showBanner = true;
                    });
            },
            closeSendModal() {
                // document.getElementById("addressOrFax").value = '';
                this.showSendModal = false;
                this.addressOrFax = '';
                this.errors = '';
                this.bannerType = '';
                this.bannerText = '';
                this.showBanner = false;

            }
        },
        mounted() {
            self = this;
        },

    }
</script>

<style>

    .panel {
        border: 0;
        width: 250px;
        height: 300px;
        border-radius: 5px;
    }

    .panel-primary > .panel-heading {
        background-color: #5cc0dd;
        border-color: #5cc0dd;
        font-family: Roboto, serif;
        padding-left: 20px;
    }


    h4 {
        color: #ffffff;
    }


    .panel-body {
        padding: 5px;
        font-family: Roboto, serif;
    }

    .panel-section {
        margin-bottom: 10px;

    }

    .btn-method {
        border-color: #5cc0dd;
        max-height: 30px;
        margin: 2px;
    }

    .btn-width-60 {
        width: 65px;
        min-width: 65px !important;
        height: 30px;
    }

    .modal-send-care-doc .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-send-care-doc .vue-modal-container {
        height: 40%;
        width: 40%;
    }

    .modal-send-care-doc .vue-modal-body {
        margin-left: 20px;
        margin-top: 20px;
    }

    .modal-send-care-doc .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .shadow {
        box-shadow: 1px 1px 1px 1px #ccc;
    }
</style>