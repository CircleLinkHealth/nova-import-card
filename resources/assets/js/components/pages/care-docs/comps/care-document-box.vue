<template>
    <div class="panel panel-primary shadow">
        <div class="panel-heading">
            <h4>{{ type }}</h4>
        </div>
        <div class="panel-body">
            <div class="col-md-12  panel-section" style="margin-top: 20px">
                <div v-if="doc" class="">
                    <button class="btn btn-success  col-md-6 btn-m">Available</button>
                </div>
                <div class="" v-else>
                    <button class="btn col-md-6 btn-default btn-m">Unavailable</button></div>
                <div class="col-md-6">
                    <a  v-bind:class="{'isDisabled': !doc}" style="float: right" :href="viewApi()" target="_blank">View</a>
                </div>
            </div>
            <div>
                <div class="col-md-6" style="margin-top: 5px">
                    {{this.docDate}}
                </div>
            </div>
            <div class="col-md-12  panel-section"  style="margin-top: 15px">
                <p><strong>Send document via:</strong></p>
            </div>
            <div class="col-md-12  panel-section" style="margin-top: 9px">
                <button class="col-md-4 btn btn-method btn-width-60 btn-xs" v-bind:class="{'isDisabled': !doc}">
                    DIRECT
                </button>
                <button class="col-md-4 btn btn-method btn-width-60 btn-xs" v-bind:class="{'isDisabled': !doc}">
                    Fax
                </button>
                <button title="(Secure Link)"class="col-md-4 btn btn-method btn-width-60  btn-xs" v-bind:class="{'isDisabled': !doc}" @click="sendCareDocument('email')">
                    Email
                </button>
            </div>
            <div class="col-md-12 panel-section">
                <a  v-bind:class="{'isDisabled': !doc}" :href="downloadApi()">Download</a>
            </div>
        </div>
        <modal v-show="showSendModal" name="send-care-doc" class="modal-send-care-doc" :no-title="true"
               :no-footer="true">
            <template slot="header">
                <button type="button" class="close" @click="showSendModal = false">×</button>
                <h3 class="modal-title">Send Care Document</h3>
            </template>
            <template slot="body">
                <div class="col-md-12 form-group">
                    <div class="col-md-12 row">
                        <p><strong>Enter {{getInputName()}}:</strong></p>
                    </div>
                </div>
                <!--ALLOW FAX-->
                <div class="col-md-12 form-group">
                    <div class="col-md-12 row">
                        <input type="email" v-model="this.formData.email" name="email" required>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <div class="col-md-12 row">
                        <button type="submit" class="btn btn-primary btn-large">Send</button>
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
    import modal from '../../../shared/modal.vue'

    export default {
        name: "care-document-box",
        components: {
            'modal': modal,
        },
        data() {
            return {
                loading: false,
                showSendModal: false,
                csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
                csrfHeader: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                formData: {
                    'year': '',
                    'patient_id': '',
                    'email': '',
                },
                channel: ''
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
            }
        },
        computed: {
            docDate () {
                if (! this.doc){
                    return null;
                }
                var date = new Date (this.doc.created_at);
                var year = date.getFullYear();
                var month = (1 + date.getMonth()).toString();
                month = month.length > 1 ? month : '0' + month;
                var day = date.getDate().toString();
                day = day.length > 1 ? day : '0' + day;
                return year + '-' + month + '-' + day;
            }
        },
        methods: {
            viewApi() {
                if (! this.doc){
                    return null;
                }
                const query = {
                    file: this.doc
                };
                return rootUrl('/view-care-document/' + this.$parent.patient.id + '/' + this.doc.id);
            },
            downloadApi() {
                if (! this.doc){
                    return null;
                }
                const query = {
                    file: this.doc
                };
                return rootUrl('/download-care-document/' + this.$parent.patient.id + '/' + this.doc.id);
            },
            sendCareDocument(channel) {
                this.channel = channel
                this.showSendModal = true
            },
            getInputName(){
                switch (this.channel) {
                    case "email":
                        return "email address";

                    case "direct":
                        return "DIRECT mail address";

                    case "fax":
                        return "fax number";
                }

                return this.channel;
            }
        }
    }
</script>

<style>

    .panel {
        border: 0;
        width: 250px;
        height: 300px;
        border-radius: 5px;
    }

    .panel-primary>.panel-heading {
        background-color: #5cc0dd;
        border-color:  #5cc0dd;
        font-family: Roboto;
        padding-left: 20px;
    }


    h4 {
        color: #ffffff;
    }


    .panel-body {
        padding: 5px;
        font-family: Roboto;
    }

    .panel-section{
        margin-bottom: 10px;

    }

    .btn-method{
        border-color: #5cc0dd;
        max-height: 30px;
        margin: 2px;
    }

    .btn-width-60{
        width: 65px;
        min-width: 65px !important;
        height: 30px;
    }

    .isDisabled {
        color: currentColor;
        cursor: not-allowed;
        opacity: 0.5;
        text-decoration: none;
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
        box-shadow:         1px 1px 1px 1px #ccc;
    }
</style>