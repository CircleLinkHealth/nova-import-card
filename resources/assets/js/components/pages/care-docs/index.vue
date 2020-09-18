<template>
    <div class="container-fluid">
        <div class="col-md-12" style="margin-top: 15px">
            <div class="col-md-8 text-left" style="height: 30px; padding-top: 5px;">
                <div class="col-md-3 btn-group btn-group-toggle" data-toggle="buttons" style="min-width: 230px">
                    <label class="col-md-4 btn btn-secondary btn-s pointer btn-switch active"
                           v-bind:class="{'btn-info': !this.showPast}"
                           @click="showCurrentDocuments()">
                        <input type="radio" name="documents" id="current" autocomplete="off" checked> Current
                    </label>
                    <label class="col-md-4 btn btn-secondary btn-s pointer btn-switch"
                           v-bind:class="{'btn-info': this.showPast}"
                           @click="showPastDocuments()">
                        <input type="radio" name="documents" id="past" autocomplete="off"> Past
                    </label>
                </div>
                <button class="col-md-3 btn btn-info btn-s pointer btn-upload-documents"
                        @click="uploadCareDocument()">Upload Documents
                </button>
                <a v-if="!userEnrolledIntoAwv"
                   class="col-md-2 btn btn-info btn-s pointer btn-upload-documents"
                   style="margin-left: 10px"
                   target="_blank"
                   :href="getAwvUrl(`manage-patients/${this.patientId}/enroll`)">
                    Enroll into AWV
                </a>
            </div>
            <div class="col-md-1">
                <loader style="text-align: center" v-if="loading"/>
            </div>
        </div>

        <div class="col-md-12">
            <hr>
        </div>

        <div v-if="noDocsFound" class="col-md-12" style="padding-left: 42%">
            <div><span class="strong-custom">No Care Documents were found.</span></div>
        </div>
        <div class="col-md-12">
            <div v-if="showBanner" :class="bannerClass">{{this.errors.errors}}</div>
        </div>


        <div class="col-md-12">
            <div v-for="status in patientAWVStatuses">
                <div class="col-md-3">
                    <div class="panel panel-primary shadow">
                        <div class="panel-heading">
                            <h4>Wellness Survey</h4>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12  panel-section" style="margin-top: 20px">
                                <div>
                                    <button class="col-md-6 btn btn-m btn-static disabled"
                                            :class="getButtonColorFromStatus(status.hra_status)">
                                        {{getButtonTextFromStatus(status.hra_status)}}
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a v-if="userEnrolledIntoAwv" class="blue-link"
                                       style="float: right; padding-top: 7px"
                                       :href="getViewHraSurveyUrl()" target="_blank">View</a>
                                </div>
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 5px">
                                <div>
                                    {{userEnrolledIntoAwv ? status.hra_display_date : '&nbsp'}}
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-top: 6px">
                                <p><span class="strong-custom">Send assessment link to patient via:</span></p>
                            </div>
                            <div class="col-md-12  panel-section">
                                <button
                                        class="col-md-6 btn btn-method btn-width-100 btn-s"
                                        :class="{'isDisabled': !userEnrolledIntoAwv, 'disabled': !userEnrolledIntoAwv}"
                                        @click="openInNewTab(getAwvSendSmsForm('hra'))">
                                    SMS
                                </button>
                                <button
                                        class="col-md-6 btn btn-method btn-width-100 btn-s"
                                        :class="{'isDisabled': !userEnrolledIntoAwv, 'disabled': !userEnrolledIntoAwv}"
                                        @click="openInNewTab(getAwvSendEmailForm('hra'))">
                                    Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-primary shadow">
                        <div class="panel-heading">
                            <h4>Vitals</h4>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12  panel-section" style="margin-top: 20px">
                                <div>
                                    <button class="col-md-6 btn btn-m btn-static disabled"
                                            :class="getButtonColorFromStatus(status.vitals_status)">
                                        {{getButtonTextFromStatus(status.vitals_status)}}
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a v-if="userEnrolledIntoAwv" class="blue-link"
                                       style="float: right; padding-top: 7px"
                                       :href="getViewVitalsSurveyUrl()" target="_blank">View</a>
                                </div>
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 5px">
                                <div>
                                    {{userEnrolledIntoAwv ? status.v_display_date : '&nbsp'}}
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-top: 6px">
                                <p><span class="strong-custom">Send assessment link to provider via:</span></p>
                            </div>
                            <div class="col-md-12  panel-section">
                                <button
                                        class="col-md-6 btn btn-method btn-width-100 btn-s"
                                        :class="{'isDisabled': !userEnrolledIntoAwv, 'disabled': !userEnrolledIntoAwv}"
                                        @click="openInNewTab(getAwvSendSmsForm('vitals'))">
                                    SMS
                                </button>
                                <button
                                        class="col-md-6 btn btn-width-100 btn-method btn-s"
                                        :class="{'isDisabled': !userEnrolledIntoAwv, 'disabled': !userEnrolledIntoAwv}"
                                        @click="openInNewTab(getAwvSendEmailForm('vitals'))">
                                    Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!--<div v-for="(docs, type) in careDocs">-->
            <!--<div v-for="doc in docs" class="col-md-3">-->
            <!--<care-document-box :doc="doc" :type="type"></care-document-box>-->
            <!--</div>-->
            <!--</div>-->
            <div v-if="careDocs['PPP']">
                <div v-for="doc in careDocs['PPP']" class="col-md-3">
                    <care-document-box :doc="doc" :type="'PPP'" :patientId="patientId"></care-document-box>
                </div>
            </div>
            <div v-else>
                <div class="col-md-3">
                    <care-document-box :type="'PPP'" :patientId="patientId"></care-document-box>
                </div>
            </div>
            <div v-if="careDocs['Provider Report']">
                <div v-for="doc in careDocs['Provider Report']" class="col-md-3">
                    <care-document-box :doc="doc" :type="'Provider Report'" :patientId="patientId"></care-document-box>
                </div>
            </div>
            <div v-else>
                <div class="col-md-3">
                    <care-document-box :type="'Provider Report'" :patientId="patientId"></care-document-box>
                </div>
            </div>
            <div v-if="careDocs['Lab Results']">
                <div v-for="doc in careDocs['Lab Results']" class="col-md-3">
                    <care-document-box :doc="doc" :type="'Lab Results'" :patientId="patientId"></care-document-box>
                </div>
            </div>
        </div>


        <modal v-show="showUploadModal" name="upload-care-doc" class="modal-upload-care-doc" :no-title="true"
               :no-footer="true">
            <template slot="header">
                <button type="button" class="close" @click="showUploadModal = false">×</button>
                <h3 class="modal-title">Upload Care Document</h3>
            </template>
            <template slot="body">
                <div class="col-md-12">
                    <div class="col-md-12 row">
                        <p><span class="strong-custom">Select Document Type</span></p>
                    </div>
                    <div class="col-md-12 row">
                        <v-select max-height="200px" v-model="selectedDocumentType"
                                  :options="list">
                        </v-select>
                    </div>
                </div>
                <div class="col-md-12" style="margin-top: 20px">
                    <dropzone v-show="selectedDocumentType"
                              id="upload-pdf-dropzone"
                              ref="pdfCareDocsDropzone"
                              :headers="csrfHeader"
                              :url="uploadUrl"
                              @vdropzone-success-multiple="showSuccess"
                              acceptedFileTypes="application/pdf"
                              dictDefaultMessage="Drop a PDF here, or click to choose a file to upload."
                              v-on:vdropzone-sending="sendingEvent"
                              :maxFileSizeInMB="10"
                              :createImageThumbnails="false"
                              :addRemoveLinks="true"
                              :uploadMultiple="true">
                        <input type="hidden" name="csrf-token" :value="csrfToken">
                    </dropzone>
                </div>
            </template>
            <template slot="footer">
            </template>
        </modal>
    </div>
</template>

<script>
    import {rootUrl} from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/app.config.js'
    import modal from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/shared/modal.vue'
    import Dropzone from 'vue2-dropzone'
    import Loader from '../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader.vue';
    import VueSelect from 'vue-select';
    import CareDocumentBox from './comps/care-document-box';
    import Errors from "../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/src/Errors";


    let self;

    export default {
        name: "care-docs-index",
        components: {
            'modal': modal,
            'loader': Loader,
            'dropzone': Dropzone,
            'v-select': VueSelect,
            'care-document-box': CareDocumentBox
        },
        data() {
            return {
                loading: false,
                showUploadModal: false,
                csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
                csrfHeader: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                list: [
                    {label: 'Personalized Preventative Plan (PPP)', value: 'PPP'},
                    {label: 'Lab Results', value: 'Lab Results'},
                    {label: 'Provider Report', value: 'Provider Report'},
                    {label: 'Wellness Survey', value: 'Wellness Survey'},
                    {label: 'Vitals', value: 'Vitals'},
                ],
                selectedDocumentType: null,
                patientAWVStatuses: [],
                careDocs: [],
                noDocsFound: false,
                showPast: false,
                errors: new Errors(),
                showBanner: false,
                bannerText: '',
                bannerType: 'info',
            }

        },
        props: {
            patientId: {
                type: String,
                required: true,
            },
            awvUrl: {
                type: String,
                required: true
            }
        },
        mounted() {

            self = this;
            this.loading = true;

            this.getCareDocuments();

            if (!this.awvUrl || this.awvUrl.length === 0) {
                console.error('Wellness Care Docs:Missing AWV url. Please contact support.');
            }

        },
        computed: {
            uploadUrl() {
                return rootUrl('/care-docs/' + this.patientId);
            },
            bannerClass() {
                return 'alert alert-' + this.bannerType;
            },
            userEnrolledIntoAwv() {

                //if we do not have the awvUrl, then we cannot show the button
                //so, just pretend that user is enrolled
                if (!this.awvUrl || this.awvUrl.length === 0) {
                    return true;
                }

                if (this.patientAWVStatuses.length === 0) {
                    return false;
                }

                //assuming that first in list is the current
                if (!this.patientAWVStatuses[0].hasOwnProperty('vitals_status') || this.patientAWVStatuses[0]['vitals_status'] == null || this.patientAWVStatuses[0]['vitals_status'].length === 0) {
                    return false;
                }

                if (!this.patientAWVStatuses[0].hasOwnProperty('hra_status') || this.patientAWVStatuses[0]['hra_status'] == null || this.patientAWVStatuses[0]['hra_status'].length === 0) {
                    return false;
                }

                return true;
            }
        },
        methods: {

            getAwvUrl: function (path) {
                if (!this.awvUrl) {
                    return null;
                }

                if (this.awvUrl[this.awvUrl.length - 1] === "/") {
                    return this.awvUrl + path;
                } else {
                    return this.awvUrl + "/" + path;
                }
            },

            getViewHraSurveyUrl() {
                return this.getAwvUrl(`survey/hra/${this.patientId}`);
            },

            getViewVitalsSurveyUrl() {
                return this.getAwvUrl(`survey/vitals/${this.patientId}`);
            },

            getAwvSendSmsForm(survey) {
                return this.getAwvUrl(`manage-patients/${this.patientId}/` + survey + `/sms/send-assessment-link`);
            },

            getAwvSendEmailForm(survey) {
                return this.getAwvUrl(`manage-patients/${this.patientId}/` + survey + `/email/send-assessment-link`);
            },

            openInNewTab(url) {
                if (!this.userEnrolledIntoAwv) {
                    return;
                }
                window.open(url, "_blank");
            },

            getButtonTextFromStatus(status) {
                switch (status) {
                    case "pending":
                        return "Not Started";

                    case "in_progress":
                        return "In Progress";

                    case "completed":
                        return "Completed";

                    default:
                        return 'Not Enrolled';
                }
            },

            getButtonColorFromStatus(status) {
                switch (status) {
                    case "pending":
                        return "btn-danger";

                    case "in_progress":
                        return "btn-warning";

                    case "completed":
                        return "btn-success";

                    default:
                        return "btn-default";
                }
            },

            uploadCareDocument() {
                this.showUploadModal = true
            },

            getCareDocuments() {
                return this.axios
                    .get(rootUrl('/care-docs/' + this.patientId + '/' + this.showPast))
                    .then(response => {
                        this.loading = false;
                        this.careDocs = response.data.files;
                        this.patientAWVStatuses = response.data.patientAWVStatuses;
                        this.noDocsFound = Object.keys(response.data).length === 0;

                        return this.careDocs;
                    })
                    .catch(err => {
                        this.loading = false;
                        let errors = err.response.data.errors ? err.response.data.errors : [];

                        this.errors.setErrors(errors);

                        self.bannerText = err.response.data.message;
                        self.bannerType = 'danger';
                        self.showBanner = true;

                    });
            },

            showSuccess() {

                this.$refs.pdfCareDocsDropzone.removeAllFiles();
                this.selectedDocumentType = null;
                this.getCareDocuments();
                this.showUploadModal = false;

            },
            getSelectedType() {
                return this.selectedDocumentType !== null ? this.selectedDocumentType.value : null;
            },
            sendingEvent(file, xhr, formData) {
                formData.append('doc_type', this.selectedDocumentType.value);
            },
            showCurrentDocuments() {
                this.loading = true;
                this.showPast = false;
                this.getCareDocuments();
            },
            showPastDocuments() {
                this.loading = true;
                this.showPast = true;
                this.getCareDocuments();
            },
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

    .panel-primary > .panel-heading {
        background-color: #5cc0dd;
        border-color: #5cc0dd;
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

    .panel-section {
        margin-bottom: 10px;

    }

    .btn-method {
        border-color: #5cc0dd;
        color: #5cc0dd;
        max-height: 30px;
        margin: 2px;
    }

    .btn-width-100 {
        width: 100px;
    }

    .modal-upload-care-doc .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
        padding: 0px;
    }

    .pointer {
        cursor: pointer;
    }

    .modal-upload-care-doc .vue-modal-container {
        min-height: 50%;
        min-width: 50%;
    }

    .modal-upload-care-doc .vue-modal-body {
        margin-left: 20px;
        margin-top: 20px;
    }

    .modal-upload-care-doc .dropzone {
        max-width: 95%;
        margin-top: 20px;
    }

    .modal-title {
        text-align: center;
        color: #5cc0dd;
    }

    .margin-top-10 {
        margin-top: 10%;
    }

    .margin-top-15 {
        margin-top: 10%;
    }

    .btn {
        min-width: 100px;
    }

    .vue-dropzone .dz-preview .dz-remove {
        font-size: initial;
    }

    .dropzone .dz-preview .dz-error-message {
        margin-top: 55px;
    }

    .shadow {
        box-shadow: 1px 1px 1px 1px #ccc;
    }

    .blue-link {
        color: #5cc0dd;
        font-weight: 700;
    }

    .btn-switch {
        max-width: 100px;
        background-color: #ffffff;
        border: solid 1px #f2f2f2;
    }

    .btn-upload-documents {
        max-width: 150px;
    }

    .isDisabled {
        color: grey !important;
        opacity: 0.5;
    }

</style>