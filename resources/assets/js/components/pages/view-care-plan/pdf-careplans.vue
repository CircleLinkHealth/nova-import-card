<script>
    import {mapActions, mapGetters} from 'vuex'
    import {getPatientCarePlan, destroyPdf, uploadPdfCarePlan, addNotification} from '../../../store/actions'
    import {patientCarePlan} from '../../../store/getters'
    import modal from '../../shared/modal.vue';
    import FileUpload from 'vue-upload-component'

    import CreateCarePerson from '../../CareTeam/create-care-person.vue'
    import UpdateCarePerson from '../../pages/view-care-plan/update-care-person.vue'
    import IndexCarePerson from '../../pages/view-care-plan/index-care-person.vue'
    import CareTeam from '../../pages/view-care-plan/care-team.vue'

    export default {
        computed: Object.assign(
            mapGetters({
                patientCarePlan: 'patientCarePlan'
            })
        ),

        components: {
            modal,
            FileUpload,
            CreateCarePerson,
            UpdateCarePerson,
            IndexCarePerson,
            CareTeam,
        },

        created() {
            this.getPatientCarePlan(this.patientId)
        },

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                showUploadModal: false,
                files: [],
                indexOfLastUploadedFile: -1,
                modeBeforeUpload: ''
            }
        },

        methods: Object.assign({},
            mapActions(['getPatientCarePlan', 'destroyPdf', 'uploadPdfCarePlan', 'addNotification']),
            {
                openModal() {
                    this.showUploadModal = true
                },

                deletePdf(pdf) {
                    let disassociate = confirm('Are you sure you want to delete this CarePlan?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyPdf(pdf.id)
                },
                uploadPdf() {
                    this.showUploadModal = false;

                    let formData = new FormData()

                    for (var i = this.indexOfLastUploadedFile + 1; i < this.files.length; i++) {
                        formData.set('files[' + i + ']', this.files[i].file) // set the filename with php
                        this.indexOfLastUploadedFile = i
                    }

                    formData.set('carePlanId', this.patientCarePlan.id)

                    this.modeBeforeUpload = this.patientCarePlan.mode

                    this.uploadPdfCarePlan(formData)

                    this.addNotification({
                        title: "Successfully uploaded PDF Careplan(s)",
                        text: "",
                        type: "success",
                        timeout: true
                    })

                    if (this.modeBeforeUpload === 'web') {
                        Vue.nextTick(() => {
                            window.location.replace(window.location.href + '/pdf')
                        })
                    }
                }
            }
        ),
    }
</script>

<template>
    <div class="col-md-12" style="padding-top: 2%;" v-cloak>
        <div class="row">
            <div class="col-md-12 text-right">
                <a @click="openModal()" class="btn btn-info btn-sm inline-block">Upload PDF</a>
                <slot></slot>
            </div>
        </div>

        <div class="row" v-if="patientCarePlan.mode == 'pdf'">
            <div class="col-md-6">
                <ul class="list-group">
                    <li v-for="(pdf, index) in patientCarePlan.pdfs" class="list-group-item pdf-careplan">
                        <a :href="pdf.url" target="_blank">{{pdf.label}} </a>
                        <button @click="deletePdf(pdf)" class="btn btn-xs btn-danger problem-delete-btn"><span><i
                                class="glyphicon glyphicon-remove"></i></span></button>
                    </li>
                </ul>
            </div>
        </div>

        <modal v-show="showUploadModal">
            <template slot="header">
                <button type="button" class="close" @click="showUploadModal = false">×</button>
                <h4 class="modal-title">Upload PDF CarePlan</h4>
            </template>
            <template slot="body">
                <file-upload id="drop-pdf-cp" @input="uploadPdf()" v-model="files" accept="application/pdf"
                             class="dropzone" multiple="multiple" drop="#drop-pdf-cp">
                    Drop a PDF here, or click to choose a file to upload.
                </file-upload>
            </template>
            <template slot="footer">
            </template>
        </modal>
    </div>
</template>

<style>
    li.pdf-careplan {
        font-size: 16px;
    }
</style>