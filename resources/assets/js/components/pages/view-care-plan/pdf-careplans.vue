<script>
    import {mapActions, mapGetters} from 'vuex'
    import {getPatientCarePlan, destroyPdf, uploadPdfCarePlan} from '../../../store/actions'
    import {patientCarePlan} from '../../../store/getters'
    import modal from '../../shared/modal.vue';
    import FileUpload from 'vue-upload-component'

    export default {
        computed: Object.assign(
            mapGetters({
                patientCarePlan: 'patientCarePlan'
            })
        ),

        components: {
            modal,
            FileUpload
        },

        created() {
            this.getPatientCarePlan(this.patientId)
        },

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                showUploadModal: false,
                files: []
            }
        },

        methods: Object.assign({},
            mapActions(['getPatientCarePlan', 'destroyPdf', 'uploadPdfCarePlan']),
            {
                deletePdf(pdf){
                    let disassociate = confirm('Are you sure you want to delete this CarePlan?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyPdf(pdf.id)
                },
                uploadPdf() {
                    this.showUploadModal = false;

                    let formData = new FormData()
                    let filesArr = []

                    for (var i = 0; i < this.files.length; i++) {
                        formData.set('files[' + i + ']', this.files[i].file) // set the filename with php
                    }

                    formData.set('carePlanId', this.patientCarePlan.id) // set the filename with php

                    this.uploadPdfCarePlan(formData)
                }
            }
        ),
    }
</script>

<template>
    <div class="col-md-8 col-md-offset-2" style="padding-top: 2%;" v-cloak>
        <div class="row">
            <div class="col-md-12 text-right">
                <a @click="showUploadModal = true" class="btn btn-info btn-sm inline-block">Upload PDF</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <ul v-if="patientCarePlan.pdfs.length > 0" class="list-group">
                    <li v-for="(pdf, index) in patientCarePlan.pdfs" class="list-group-item">
                        <a :href="pdf.url" target="_blank">{{pdf.label}} </a>
                        <button @click="deletePdf(pdf)" class="btn btn-xs btn-danger problem-delete-btn"><span><i
                                class="glyphicon glyphicon-remove"></i></span></button>
                    </li>
                </ul>
                <p v-else>No PDF CarePlans uploaded yet.</p>
            </div>
        </div>

        <modal v-show="showUploadModal">
            <template slot="header">
                <button type="button" class="close" @click="showUploadModal = false">×</button>
                <h4 class="modal-title">Upload PDF CarePlan</h4>
            </template>
            <template slot="body">
                <file-upload @input="uploadPdf()" v-model="files" accept="application/pdf" class="dropzone" multiple="multiple">
                    Drop a PDF here, or click to choose a file to upload.
                </file-upload>
            </template>
            <template slot="footer">
            </template>
        </modal>
    </div>
</template>

<style>

</style>