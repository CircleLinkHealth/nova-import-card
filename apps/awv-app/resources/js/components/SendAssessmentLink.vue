<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Send {{getSurveyName()}} Assessment Link for {{patientName}}</div>

                    <div class="card-body">

                        <div class="spinner-overlay" v-show="waiting">
                            <div class="text-center">
                                <mdb-icon icon="spinner" :spin="true"/>
                            </div>
                        </div>

                        <p>
                            Click below to send via {{getChannelName()}}.
                        </p>
                        <p>
                            <mdb-btn @click="inputChannelDetails()" :disabled="waiting || success">
                                Select Recipient
                            </mdb-btn>
                        </p>
                        <p>
                            <mdb-alert v-if="error" color="danger">
                                {{error}}
                            </mdb-alert>

                            <mdb-alert v-if="success" color="success">
                                Link sent. You can close this window.
                            </mdb-alert>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <send-link-modal v-if="sendLinkModalOptions.show" :options="sendLinkModalOptions" :only-email="onlyEmail()" :only-sms="onlySms()"></send-link-modal>
    </div>
</template>

<script>

    import {mdbAlert, mdbBtn, mdbIcon} from 'mdbvue';
    import SendLinkModal from './SendLinkModal';

    export default {
        name: "SendAssessmentLink",
        components: {mdbBtn, mdbAlert, mdbIcon, SendLinkModal},
        props: ['patientName', 'patientId', 'surveyName', 'channel', 'debug'],
        data() {
            return {
                sendLinkModalOptions: {
                    debug: this.debug,
                    show: false,
                    survey: null,
                    patientId: null,
                    onDone: null,
                    success: null,
                },
                waiting: false,
                success: false,
                error: null,
            }
        },
        methods: {
            inputChannelDetails() {
                this.sendLinkModalOptions.patientId = this.patientId;
                this.sendLinkModalOptions.survey = this.surveyName;
                this.sendLinkModalOptions.show = true;
                this.sendLinkModalOptions.onDone = () => {
                    this.sendLinkModalOptions.show = false;
                }
                this.sendLinkModalOptions.success = () => {
                    this.success = true;
                }
            },

            onlySms(){
              return this.channel === 'sms';
            },

            onlyEmail(){
                return this.channel === 'email';
            },

            getSurveyName(){
                if (this.surveyName === 'hra'){
                    return 'HRA';
                }else{
                    return 'Vitals';
                }
            },

            getChannelName(){
                if (this.channel === 'sms'){
                    return 'SMS';
                }else{
                    return 'Email'
                }
            },
        },
        mounted() {

        },
        created() {

        }
    }
</script>

<style scoped>

</style>
