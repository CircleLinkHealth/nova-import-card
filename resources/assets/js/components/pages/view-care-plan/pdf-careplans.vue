<script>
    import {mapActions} from 'vuex'
    import {addNotification, destroyPdf, uploadPdfCarePlan} from '../../../store/actions'
    import modal from '../../shared/modal.vue'
    import Dropzone from 'vue2-dropzone'

    import UpdateCarePerson from '../../pages/view-care-plan/update-care-person.vue'
    import CarePlanApi from '../../../api/patient-care-plan'
    import {rootUrl} from '../../../app.config'

    export default {
        components: {
            modal,
            Dropzone,
            UpdateCarePerson
        },

        props: [
            'mode',
            'isProvider',
            'isAdmin',
            'isCareCoach',
            'providerCanApproveOwnCarePlans',
            'showReadyForDrButton',
            'readyForDrButtonDisabled',
            'readyForDrButtonAlreadyClicked',
            'routeApproveOwn',
            'routeApprove',
            'routeApproveViewNext',
            'routeSwitchToPdf',
            'routePrintCarePlan',
            'routeCarePlanNotEligible',
            'patientCarePlanPdfsHasItems',
            'shouldShowApprovalButton',
        ],

        created() {
            self = this;
            this.getPatientCarePlan(this.patientId)
            this.apiUrl = this.axios.defaults.baseURL + '/care-plans/' + this.patientCareplanId + '/pdfs'
        },
        mounted() {
            App.$on('set-patient-problems', (problems) => {
                this.patientProblemNames = problems;
            });

            //Once approver has confirmed that Diabetes Conditions are Correct, add the field needed to bypass validation in the back-end and submit form
            App.$on('confirm-diabetes-conditions', () => {
                let form = $('#form-approve');
                $("<input>").attr("type", "hidden").attr("name", "confirm_diabetes_conditions").appendTo(form);
                form.submit();
            });

            //update problems if they have changed in care-areas modal
            App.$on('patient-problems-updated', (problems) => {
                let problemNames = problems.map(function (problem) {
                    return problem.name;
                });
                this.patientProblemNames = problemNames;
            });

            $('#form-approve').submit(function (e) {
                e.preventDefault();
                const form = this;

                if (self.patientHasBothTypesOfDiabetes && self.patientCarePlan.status === 'draft') {
                    $(":input").each(function () {
                        if ($(this).attr('name') === "confirm_diabetes_conditions") {
                            form.submit();
                        }
                    });

                    App.$emit('show-diabetes-check-modal');

                    return;
                } else {
                    form.submit();
                }
            })

        },
        data() {
            return {
                patientId: $('meta[name="patient_id"]').attr('content'),
                patientCareplanId: $('meta[name="patient_careplan_id"]').attr('content'),
                showUploadModal: false,
                showReadyForDrModal: false,
                modeBeforeUpload: '',
                apiUrl: null,
                csrfToken: document.head.querySelector('meta[name="csrf-token"]').content,
                csrfHeader: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                patientCarePlan: {},
                patientProblemNames: [],
                Modes: {
                    Web: 'web',
                    Pdf: 'pdf'
                }
            }
        },
        computed: {
            pdfSwitchUrl() {
                return rootUrl(`manage-patients/switch-to-pdf-careplan/${this.patientCareplanId}`)
            },
            viewCareplanUrl() {
                return rootUrl('manage-patients/' + this.patientId + '/view-careplan')
            },
            assessmentUrl() {
                return rootUrl('manage-patients/' + this.patientId + '/view-careplan/assessment')
            },
            patientHasBothTypesOfDiabetes() {
                return this.patientProblemNames.includes('Diabetes Type 1') && this.patientProblemNames.includes('Diabetes Type 2');
            },
            providerCanApproveOwnCarePlansSubmitButtonText() {
                return this.providerCanApproveOwnCarePlans ? "View all Practice Care Plans" : "View Assigned Care Plans Only";
            },
            csrfInput() {
                return `<input type="hidden" name="_token" value="${this.csrfToken}">`;
            },
            readyForDrButtonTooltip() {
                if (this.readyForDrButtonDisabled) {
                    return 'Successful welcome call note required';
                }
                if (this.readyForDrButtonAlreadyClicked) {
                    return 'You have approved this Care Plan. Please proceed to save clinical note!';
                }
                return '';
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

                        carePlan.pdfs = (carePlan.pdfs || []).map(pdf => {
                            if (pdf.created_at) pdf.created_at = new Date(pdf.created_at)
                            if (pdf.deleted_at) pdf.deleted_at = new Date(pdf.deleted_at)
                            if (pdf.updated_at) pdf.updated_at = new Date(pdf.updated_at)
                            return pdf
                        }).sort((pdfA, pdfB) => pdfB.updated_at - pdfA.updated_at)

                        // console.log(carePlan)

                        this.patientCarePlan = carePlan;
                        // console.log('patient-careplan', this.patientCarePlan)
                    }, error => {
                        console.log(error)
                    }, patientId)
                },

                openModal() {
                    this.showUploadModal = true
                },
                openReadyForDrModal() {
                    this.showReadyForDrModal = true;
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
                    this.modeBeforeUpload = this.patientCarePlan.mode

                    console.log('mode:before:upload', this.modeBeforeUpload)

                    if (this.modeBeforeUpload === 'web') {
                        location.href = rootUrl(`manage-patients/${this.patientId}/view-careplan/pdf`)
                    }

                    if (this.modeBeforeUpload === 'pdf') {
                        this.getPatientCarePlan(this.patientId)
                    }

                    this.$refs.pdfCareplansDropzone.removeAllFiles()
                    this.showUploadModal = false;

                    this.addNotification({
                        title: "PDF Careplan(s) uploaded",
                        text: "",
                        type: "success",
                        timeout: true
                    })
                },

                notEligibleClick() {
                    if (confirm('CAUTION: Clicking "confirm" will delete this patient’s entire record from Care Plan Manager. This action cannot be undone. Do you want to delete this patients entire record?')) {
                        document.getElementById('not-eligible-form').submit();
                    }
                }
            }
        ),
    }
</script>

<template>
    <div class="col-md-12" style="padding-top: 2%;" v-cloak>
        <div class="row">
            <div class="col-md-5 text-left">
                <template v-if="isProvider">
                    <form class="inline-block" style="text-align: left"
                          :action="routeApproveOwn"
                          method="POST">
                        <div v-html="csrfInput"></div>
                        <input class="btn btn-sm btn-info" aria-label="..."
                               type="submit"
                               :value="providerCanApproveOwnCarePlansSubmitButtonText">
                    </form>
                </template>
            </div>
            <div class="col-md-2 text-center">
                <template v-if="showReadyForDrButton">
                    <form id="form-approve"
                          :class="{'with-tooltip': readyForDrButtonDisabled || readyForDrButtonAlreadyClicked}"
                          :title="readyForDrButtonTooltip"
                          :action="routeApprove"
                          method="POST" style="display: inline">
                        <div v-html="csrfInput"></div>
                        <button class="btn btn-success btn-sm inline-block"
                                aria-label="..."
                                form="form-approve"
                                type="submit"
                                id="btn-rn-approve"
                                :disabled="readyForDrButtonDisabled || readyForDrButtonAlreadyClicked"
                                role="button">
                            Ready for Dr.
                        </button>
                    </form>
                    <button @click="openReadyForDrModal" class="btn btn-xs btn-info">
                        <i class="fa fa-question-circle">

                        </i>
                    </button>

                </template>
            </div>
            <div class="col-md-5 text-right">
                <a v-if="patientCarePlanPdfsHasItems" :href="routeSwitchToPdf"
                   class="btn btn-info btn-sm inline-block">PDF CarePlans</a>
                <template v-if="shouldShowApprovalButton">
                    <form v-if="!isCareCoach"
                          id="form-approve"
                          :action="routeApprove"
                          method="POST" style="display: inline">
                        <div v-html="csrfInput"></div>
                        <button class="btn btn-info btn-sm inline-block"
                                aria-label="..."
                                form="form-approve"
                                type="submit"
                                role="button">Approve
                        </button>
                    </form>

                    <template v-if="isProvider || isAdmin">
                        <form id="form-approve-next"
                              :action="routeApproveViewNext"
                              method="POST" style="display: inline">
                            <div v-html="csrfInput"></div>
                            <input class="btn btn-success btn-sm inline-block"
                                   aria-label="..."
                                   type="submit"
                                   role="button" value="Approve and View Next">
                        </form>

                        <form :action="routeCarePlanNotEligible"
                              method="POST" id="not-eligible-form" style="display: inline">
                            <div v-html="csrfInput"></div>
                            <button type="button" style="margin-right:10px;"
                                    @click="notEligibleClick"
                                    class="btn btn-danger btn-sm text-right">
                                Not Eligible
                            </button>
                        </form>

                    </template>

                </template>
                <!--<a :href="assessmentUrl" v-if="patientCarePlan.status == 'provider_approved'" class="btn btn-info btn-sm inline-block">View Assessment</a>-->
                <a @click="openModal()" class="btn btn-info btn-sm inline-block">Upload PDF</a>
                <a class="btn btn-info btn-sm inline-block" aria-label="..."
                   role="button"
                   :href="routePrintCarePlan">Print
                    This Page</a>
            </div>
        </div>

        <div class="row" v-if="patientCarePlan.mode == 'pdf'">
            <div class="col-md-12 list-group">
                <div class="list-group-item list-group-item-action top-20" v-for="(pdf, index) in patientCarePlan.pdfs"
                     :key="index">
                    <h3 class="pdf-title">
                        <a :href="pdf.url" target="_blank">{{pdf.label}} </a>
                        <button @click="deletePdf(pdf)" class="btn btn-xs btn-danger problem-delete-btn">
                            <span>
                                <i class="glyphicon glyphicon-remove"></i>
                            </span>
                        </button>
                    </h3>
                    <div class="pdf-body">
                        <object :data="pdf.url" type="application/pdf" width="100%" height="100%">
                            <iframe :src="pdf.url" width="100%" height="100%" style="border: none;">
                                <div>
                                    Sorry, your browser does not support PDF Embeds ...

                                    Please update it as soon as possible, or click <a :href="pdf.url">here</a> to
                                    download the PDF
                                </div>
                            </iframe>
                        </object>
                    </div>
                </div>
                <div class="list-group-item list-group-item-action top-20 pointer" @click="openModal()"
                     v-if="patientCarePlan.pdfs.length === 0">
                    <h3 class="pdf-title text-center">
                        No PDF files uploaded yet ... Click to upload
                    </h3>
                </div>
            </div>
        </div>

        <div class="row" v-if="mode === Modes.Pdf && patientCarePlan.mode !== 'pdf'">
            <div class="col-md-12">
                <center>
                    <h3>
                        This Careplan is in Web mode. Click <a :href="viewCareplanUrl">here</a> to access it, or <a
                            :href="pdfSwitchUrl">here</a> to switch to PDF mode.
                    </h3>
                </center>
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
                        :maxFileSizeInMB="20"
                        :createImageThumbnails="false"
                        :uploadMultiple="true">
                    <input type="hidden" name="csrf-token" :value="csrfToken">
                </dropzone>
            </template>
            <template slot="footer">
            </template>
        </modal>

        <modal v-show="showReadyForDrModal">
            <template slot="header">
                <button type="button" class="close" @click="showReadyForDrModal = false">×</button>
                <h4 class="modal-title">Care Plan Ready For Dr.</h4>
            </template>
            <template slot="body">
                Clicking here sends the Care Plan to Dr., so please ensure this is an assessment-driven
                Care Plan per CircleLink <a
                    href="https://circlelinkhealth.zendesk.com/hc/en-us/articles/360040849551-CCM-1st-Call-Script-Welcome-and-History">guidelines</a>.
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

    .top-20 {
        margin-top: 20px;
    }

    .pointer {
        cursor: pointer;
    }
</style>
