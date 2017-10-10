<script>
    import {mapActions, mapGetters} from 'vuex'
    import {getPatientCarePlan, destroyPdf, uploadPdfCarePlan, addNotification} from '../../../store/actions'
    import {patientCarePlan} from '../../../store/getters'
    import modal from '../../shared/modal.vue'
    import Dropzone from 'vue2-dropzone'

    import CreateCarePerson from '../../CareTeam/create-care-person.vue'
    import UpdateCarePerson from '../../pages/view-care-plan/update-care-person.vue'
    import IndexCarePerson from '../../pages/view-care-plan/index-care-person.vue'
    import CareTeam from '../../pages/view-care-plan/care-team.vue'
    import CarePlanApi from '../../../api/patient-care-plan'

    export default {
        components: {
            modal,
            Dropzone,
            CreateCarePerson,
            UpdateCarePerson,
            IndexCarePerson,
            CareTeam,
        },

        created() {
            this.getPatientCarePlan(this.patientId)
            this.apiUrl = window.axios.defaults.baseURL + '/care-plans/' + this.patientCareplanId + '/pdfs'
        },

        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                patientCareplanId: $('meta[name="patient_careplan_id"]').attr('content'),
                showUploadModal: false,
                modeBeforeUpload: '',
                apiUrl: null,
                csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
                csrfHeader: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                patientCarePlan: {}
            }
        },

        methods: Object.assign({},
            mapActions(['destroyPdf', 'uploadPdfCarePlan', 'addNotification']),
            {
                getPatientCarePlan(patientId) {
                    if (!patientId) {
                        return
                    }

                    CarePlanApi.getPatientCareplan(carePlan => {
                        if (!carePlan) {
                            return
                        }
                        this.patientCarePlan = carePlan;
                    }, error => {
                        console.log(error)
                    }, patientId)
                },

                openModal() {
                    this.showUploadModal = true
                },

                deletePdf(pdf) {
                    let disassociate = confirm('Are you sure you want to delete this CarePlan?');

                    if (!disassociate) {
                        return true;
                    }

                    this.destroyPdf(pdf.id)

                    //delete pdf locally
                    //this is a quickfix for https://github.com/CircleLinkHealth/cpm-web/issues/693
                    //@todo: fix vuex
                    let removeThis = null;
                    for (let i = 0; i < this.patientCarePlan.pdfs.length; i++) {
                        if (this.patientCarePlan.pdfs[i].id == pdf.id) {
                            removeThis = i;
                            break;
                        }
                    }

                    if (!_.isNull(removeThis)) {
                        this.patientCarePlan.pdfs.splice(removeThis, 1)
                    }
                },

                showSuccess() {
                    this.$refs.pdfCareplansDropzone.removeAllFiles()
                    this.showUploadModal = false;
                    this.modeBeforeUpload = this.patientCarePlan.mode

                    this.addNotification({
                        title: "PDF Careplan(s) uploaded",
                        text: "",
                        type: "success",
                        timeout: true
                    })

                    if (this.modeBeforeUpload === 'web') {
                        setTimeout(() => {
                            window.location.replace(window.location.href + '/pdf')
                        }, 1000)
                    }

                    if (this.modeBeforeUpload === 'pdf') {
                        this.getPatientCarePlan(this.patientId)
                    }
                },
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
                <dropzone
                        id="upload-pdf-dropzone"
                        ref="pdfCareplansDropzone"
                        :headers="csrfHeader"
                        :url="apiUrl"
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
    </div>
</template>

<style>
    li.pdf-careplan {
        font-size: 16px;
    }
</style>