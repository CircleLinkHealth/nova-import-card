<template>
    <modal name="upload-care-doc" class="modal-upload-care-doc" :no-title="true" :no-footer="true">
        <template slot="header">
            <button type="button" class="close" @click="showUploadModal = false">×</button>
            <h4 class="modal-title">Upload Care Document</h4>
        </template>
        <template slot="body">
            <dropzone
                    id="upload-pdf-dropzone"
                    ref="pdfCareDocsDropzone"
                    :headers="csrfHeader"
                    :url="uploadUrl()"
                    @vdropzone-success-multiple="showSuccess"
                    acceptedFileTypes="application/pdf"
                    dictDefaultMessage="Drop a PDF here, or click to choose a file to upload."
                    :maxFileSizeInMB="10"
                    :createImageThumbnails="false"
                    :uploadMultiple="true">
                <input type="hidden" name="csrf-token" :value="csrfToken">
            </dropzone>
        </template>
        <template slot="footer">
        </template>
    </modal>
</template>

<script>
    import modal from '../../../../shared/modal.vue'
    import Dropzone from 'vue2-dropzone'
    import {rootUrl} from '../../../../../app.config.js'
    import {addNotification} from '../../../../../store/actions'


    let self;

    export default {
        name: "upload-care-doc-modal",
        components: {
            modal: modal,
            dropzone: Dropzone
        },
        props: {
            patient: {
                type: Object,
                required: true
            },
        },
        data() {
           return {
               csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
               csrfHeader: {
                   'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
               },
           }
        },
        methods: {
            uploadUrl() {
                return rootUrl('/care-docs/'+ this.patient.id);
            },
            deletePdf(pdf) {
                return true;
            },

            showSuccess() {

                this.$refs.pdfCareDocsDropzone.removeAllFiles()
                this.showUploadModal = false;

                this.addNotification({
                    title: "PDF(s) uploaded",
                    text: "",
                    type: "success",
                    timeout: true
                })
            },
        },
        mounted: function () {
            self = this;

            this.loading = false;
        }
    }
</script>

<style scoped>

</style>