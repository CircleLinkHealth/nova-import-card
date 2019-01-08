<template>
    <div class="container-fluid">
        <div class="col-sm-12" style="margin-top: 20px">
            <div class="col-sm-12 text-left">
                <button class="btn btn-secondary btn-xs" v-bind:class="{'btn-info': !this.showPast}"
                        @click="showCurrentDocuments()">Current
                </button>
                <button class="btn btn-secondary btn-xs" v-bind:class="{'btn-info': this.showPast}" style="margin-right: 40px"
                        @click="showPastDocuments()">Past
                </button>
                <button class="btn btn-info btn-xs"
                        @click="uploadCareDocument()">Upload Documents
                </button>
            </div>
        </div>

        <div class="col-sm-12">
            <hr>
        </div>


            <div v-for="(docs, type) in careDocs">
                <div v-for="doc in docs"  class="col-md-3">
                    <care-document-box :doc="doc" :type="type"></care-document-box>
                </div>
            </div>

        <modal v-show="showUploadModal" name="upload-care-doc" class="modal-upload-care-doc" :no-title="true" :no-footer="true">
            <template slot="header">
                <button type="button" class="close" @click="showUploadModal = false">×</button>
                <h3 class="modal-title">Upload Care Document</h3>
            </template>
            <template slot="body">
                <div class="col-md-12" style="margin: 20px">
                    <div class="row">
                        <p>Select Document Type</p>
                    </div>
                    <div class="row">
                        <v-select max-height="200px" class="form-control" v-model="selectedDocumentType"
                                  :options="list">
                        </v-select>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <notifications ref="notificationsComponent" name="select-ca-modal"></notifications>
                        </div>
                    </div>
                    <!--<loader v-if="loading"/>-->
                </div>
                <div class="col-md-12" style="margin: 20px">
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
    import Notifications from '../../../components/notifications';
    import VueSelect from 'vue-select';
    import CareDocumentBox from './comps/care-document-box';


    let self;

    export default {
        name: "care-docs-index",
        components: {
            'modal': modal,
            'loader': Loader,
            'notifications': Notifications,
            'dropzone': Dropzone,
            'v-select': VueSelect,
            'care-document-box': CareDocumentBox
        },
        data() {
            return {
                showUploadModal: false,
                csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
                csrfHeader: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                dropzoneOptions: {
                    clickable: false,
                    timeout: 3000,
                },
                list: [
                    {label: 'Personalized Preventative Plan (PPP)', value: 'PPP'},
                    {label: 'Lab Results', value: 'Lab Results'},
                    {label: 'Provider Report', value: 'Provider Report'},
                    {label: 'Wellness Survey', value: 'Wellness Survey'},
                    {label: 'Vitals', value: 'Vitals'},
                ],
                selectedDocumentType: null,
                careDocs: [],
                showPast: false,
            }

        },
        props: {
            patient:{
                type: Object,
                required: true,
            }
        },
        mounted() {

            self = this;

            this.getCareDocuments();
        },
        computed: {
            uploadUrl() {
                return rootUrl('/care-docs/'+ this.patient.id);
            },
        },
        methods: {
            uploadCareDocument(){
                this.showUploadModal = true
            },
            getCareDocuments(){
                return this.axios
                    .get(rootUrl('/care-docs/' + this.patient.id + '/' + this.showPast))
                    .then(response => {
                        // this.loading = false;

                        this.careDocs = response.data;

                        return this.careDocs;
                    })
                    .catch(err => {
                        // this.loading = false;
                    });
            },
            deletePdf(pdf) {
                return true;
            },

            showSuccess() {

                this.$refs.pdfCareDocsDropzone.removeAllFiles();
                this.selectedDocumentType = null;
                this.getCareDocuments();
                this.showUploadModal = false;

                this.addNotification({
                    title: "PDF(s) uploaded",
                    text: "",
                    type: "success",
                    timeout: true
                })
            },
            getSelectedType(){
                return this.selectedDocumentType !== null ? this.selectedDocumentType.value : null;
            },
            sendingEvent (file, xhr, formData) {
                formData.append('doc_type', this.selectedDocumentType.value);
            },
            showCurrentDocuments() {
                this.showPast = false;
                this.getCareDocuments();
            },
            showPastDocuments(){
                this.showPast = true;
                this.getCareDocuments();
            }

        }
    }
</script>

<style>

    .modal-upload-care-doc .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-upload-care-doc .modal-container {
        width: 600px;
        height: 380px;
    }




    .modal-upload-care-doc .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }



    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-upload-care-doc .modal-body {
        height: 200px;
        width: 500px;
    }

    .btn {
        min-width: 100px;
    }

</style>