<template>
    <div class="container-fluid">
        <div class="col-md-12" style="margin-top: 15px">
            <div class="col-md-5 text-left" style="height: 30px; padding-top: 5px">
                <button class="col-md-3 btn btn-secondary btn-xs pointer" v-bind:class="{'btn-info': !this.showPast}"
                        @click="showCurrentDocuments()">Current
                </button>
                <button class="col-md-3 btn btn-secondary btn-xs pointer" v-bind:class="{'btn-info': this.showPast}"
                        style="margin-right: 40px"
                        @click="showPastDocuments()">Past
                </button>
                <button class="col-md-3 btn btn-info btn-xs pointer"
                        @click="uploadCareDocument()">Upload Documents
                </button>
            </div>
            <div class="col-md-2">
                <loader style="margin-left: 80px; text-align: center" v-if="loading"/>
            </div>
        </div>

        <div class="col-md-12">
            <hr>
        </div>

        <div v-if="noDocsFound" class="col-md-12" style="padding-left: 42%">
            <div><strong>No Care Documents were found.</strong></div>
        </div>
        <div class="col-md-12">
            <div v-if="showBanner" :class="bannerClass">{{this.errors.errors}}</div>
        </div>


        <div class="col-md-12">
            <div v-for="status in patientAWVStatuses">
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Wellness Survey</h4>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12  panel-section" style="margin-top: 20px">
                                <div v-if="status.hra_status === 'pending' ">
                                    <button class="col-md-6 btn btn-danger btn-m">
                                        Not Started
                                    </button>
                                </div>
                                <div v-if="status.hra_status === 'in_progress' ">
                                    <button class="col-md-6 btn btn-warning btn-m">
                                        In Progress
                                    </button>
                                </div>
                                <div v-if="status.hra_status === 'completed' ">
                                    <button class="col-md-6 btn btn-success btn-m">
                                        Completed
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 5px">
                                <div class="col-md-6">
                                    {{status.year}}
                                </div>
                                <!--<div class="col-md-6">-->
                                <!--<a style="float: right" :href="viewApi()" target="_blank">View</a>-->
                                <!--</div>-->
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 10px">
                                <p><strong>Send Assessment Link to Provider via:</strong></p>
                            </div>
                            <div class="col-md-12  panel-section">
                                <button class="col-md-6 btn btn-method btn-s">
                                    SMS
                                </button>
                                <button class="col-md-6 btn btn-method btn-s">
                                    Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4>Vitals</h4>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12  panel-section" style="margin-top: 20px">
                                <div v-if="status.vitals_status === 'pending' ">
                                    <button class="col-md-6 btn btn-danger btn-m">
                                            Not Started
                                    </button>
                                </div>
                                <div v-if="status.vitals_status === 'in_progress' ">
                                    <button class="col-md-6 btn btn-warning btn-m">
                                        In Progress
                                    </button>
                                </div>
                                <div v-if="status.vitals_status === 'completed' ">
                                    <button class="col-md-6 btn btn-success btn-m" >
                                        Completed
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 5px">
                                <div class="col-md-6">
                                    {{status.year}}
                                </div>
                                <!--<div class="col-md-6">-->
                                <!--<a style="float: right" :href="viewApi()" target="_blank">View</a>-->
                                <!--</div>-->
                            </div>
                            <div class="col-md-12  panel-section" style="margin-top: 10px">
                                <p><strong>Send Assessment Link to Provider via:</strong></p>
                            </div>
                            <div class="col-md-12  panel-section">
                                <button class="col-md-6 btn btn-method btn-s">
                                    SMS
                                </button>
                                <button class="col-md-6 btn btn-method btn-s">
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
                    <care-document-box :doc="doc" :type="'PPP'"></care-document-box>
                </div>
            </div>
            <div v-else>
                <div class="col-md-3">
                    <care-document-box :type="'PPP'"></care-document-box>
                </div>
            </div>
            <div v-if="careDocs['Provider Report']">
                <div v-for="doc in careDocs['Provider Report']" class="col-md-3">
                    <care-document-box :doc="doc" :type="'Provider Report'"></care-document-box>
                </div>
            </div>
            <div v-else>
            <div class="col-md-3">
                <care-document-box :type="'Provider Report'"></care-document-box>
            </div>
            </div>
            <div v-if="careDocs['Lab Results']">
                <div v-for="doc in careDocs['Lab Results']" class="col-md-3">
                    <care-document-box :doc="doc" :type="'Lab Results'"></care-document-box>
                </div>
            </div>
            <div v-else>
                <div class="col-md-3">
                 <care-document-box :type="'Lab Results'"></care-document-box>
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
                        <p><strong>Select Document Type</strong></p>
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
    import {rootUrl} from '../../../app.config.js'
    import modal from '../../shared/modal.vue'
    import Dropzone from 'vue2-dropzone'
    import Loader from '../../../components/loader.vue';
    import VueSelect from 'vue-select';
    import CareDocumentBox from './comps/care-document-box';
    import Errors from "../../src/Errors";


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
            patient: {
                type: Object,
                required: true,
            }
        },
        mounted() {

            self = this;
            this.loading = true;

            this.getCareDocuments();
        },
        computed: {
            uploadUrl() {
                return rootUrl('/care-docs/' + this.patient.id);
            },
            bannerClass() {
                return 'alert alert-' + this.bannerType;
            }
        },
        methods: {
            uploadCareDocument() {
                this.showUploadModal = true
            },
            getCareDocuments() {
                return this.axios
                    .get(rootUrl('/care-docs/' + this.patient.id + '/' + this.showPast))
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
        width: 100px;
        max-height: 30px;
        margin: 2px;
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

</style>