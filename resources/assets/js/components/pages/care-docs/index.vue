<template>
    <div class="container-fluid">
        <div class="col-sm-12" style="margin-top: 20px">
            <div class="col-sm-4 text-left">
                <button class="btn btn-info btn-xs"
                        @click="">Current
                </button>
                <button class="btn btn-info btn-xs"
                        @click="">Past
                </button>
            </div>
            <div class="col-sm-8 text-left">
                <button class="btn btn-info btn-xs"
                        @click="uploadCareDocument()">Upload Documents
                </button>
            </div>
        </div>

        <div class="col-sm-12">
            <hr>
        </div>


            <div v-for="file in careDocs" class="col-xs-3 box">
                <div class="col-xs-12 box-title">
                    <h4>{{file.file_name}}</h4>
                </div>
                <div class="col-md-7">
                    <p style="margin-left: -10px;">
                        <strong>test </strong>test<em>test</em>
                    </p>
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
                            :url="uploadUrl()"
                              @vdropzone-files-added="afterComplete"
                            @vdropzone-success-multiple="showSuccess"
                            acceptedFileTypes="application/pdf"
                            dictDefaultMessage="Drop a PDF here, or click to choose a file to upload."

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


    let self;

    export default {
        name: "care-docs-index",
        components: {
            'upload-care-doc-modal': UploadCareDocModal,
            'modal': modal,
            'loader': Loader,
            'notifications': Notifications,
            'dropzone': Dropzone,
            'v-select': VueSelect

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
                    {label: 'Personalized Preventative Plan (PPP)', value: 1},
                    {label: 'Health Risk Assessment', value: 2},
                    {label: 'Provider Report', value: 3},
                    {label: 'Wellness Survey', value: 4},
                ],
                selectedDocumentType: null,
                careDocs: null,
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
        methods: {
            uploadCareDocument(){
                this.showUploadModal = true
            },
            getCareDocuments(){
                return this.axios
                    .get(rootUrl('/care-docs/' + this.patient.id))
                    .then(response => {
                        // this.loading = false;
                        this.careDocs = response.data;

                        return this.careDocs;
                    })
                    .catch(err => {
                        // this.loading = false;
                    });
            },
            uploadUrl() {
                return rootUrl('/care-docs/'+ this.patient.id + '/' + this.getSelectedType());
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
            afterComplete(){
                this.$refs.pdfCareDocsDropzone.url =   this.uploadUrl();
            },
            getSelectedType(){
                return this.selectedDocumentType != null ? this.selectedDocumentType.value : null;
            }
        }
    }
</script>

<style>
    .box{
        width: 250px;
        height: 300px;
        border-radius: 5px;
        background-color: #ffffff;
        margin-right: 30px;
        margin-bottom: 30px;
        padding: 0px;
    }

    .box-title {
        width: 250px;
        height: 50px;
        border-radius: 5px;
        background-color: #5cc0dd;
    }

    h4 {
        color: #ffffff;
    }

</style>