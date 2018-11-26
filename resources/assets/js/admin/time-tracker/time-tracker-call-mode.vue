<template>
    <!--<span class="call-mode" v-if="!noDisplay">-->
    <!--<button class="btn btn-primary" type="button"-->
    <!--@click="enterCallMode" v-if="Number(patientId) && (callMode === false)">-->
    <!--<span>Start Call Mode</span>-->
    <!--</button>-->
    <!--<button class="btn btn-danger" type="button"-->
    <!--@click="exitCallMode" v-if="Number(patientId) && (callMode === true)">-->
    <!--<span>End Call Mode</span>-->
    <!--</button>-->
    <!--<loader v-if="(callMode === null) || loaders.callMode"></loader>-->
    <!--</span>-->
    <span class="call-mode">
        <button class="btn" :class="buttonClass" type="button"
                @click="callButtonClick">
            <span>{{buttonText}}</span>
        </button>
    </span>
</template>

<script>
    import LoaderComponent from '../../components/loader'
    import {rootUrl} from "../../app.config";
    import {sendRequest, registerHandler} from "../../components/bc-job-manager";

    let self;

    export default {
        props: {
            noDisplay: Boolean,
            patientId: Number
        },
        computed: {},
        data() {
            return {
                buttonClass: 'btn-primary',
                buttonText: "Open Calls Page",
                isCallPageOpen: false,
                callMode: null,
                loaders: {
                    callMode: false
                }
            }
        },
        components: {
            'loader': LoaderComponent
        },
        methods: {
            callButtonClick(e) {
                if (self.callMode) {
                    self.endCall(e);
                }
                else {
                    self.openCallPage(e);
                }
            },
            openCallPage(e) {
                //just setting call mode to indicate that the calls page is open
                self.buttonClass = 'btn-info';
                self.buttonText = `Close Calls Page`;
                self.callMode = true;

                const strWindowFeatures = "location=yes,height=400,width=520,scrollbars=yes,status=yes";
                const URL = rootUrl(`manage-patients/${this.patientId}/call`);
                window.open(URL, "_blank", strWindowFeatures);
            },
            endCall(e) {
                sendRequest("end_call", null)
                    .then(msg => {
                        self.callMode = false;
                        self.buttonClass = 'btn-primary';
                        self.buttonText = "Open Calls Page";
                    })
                    .catch(err => console.error(err));
            },
            checkForCallStatus() {
                sendRequest("call_status", null, 1000)
                    .then(msg => {
                        if (!msg) {
                            return;
                        }

                        //if we got a response, it means calls page is open

                        self.callMode = true;
                        if (msg.data && msg.data.number) {
                            const number = msg.data.number.value;
                            const muted = msg.data.number.muted;
                            self.buttonClass = 'btn-danger';
                            self.buttonText = `End Call [${number}]${muted ? ' [muted]' : ''}`;
                        }
                        else {
                            self.buttonClass = 'btn-info';
                            self.buttonText = `Close Calls Page`;
                        }
                    })
                    .catch(err => {
                        // if calls page is not open, we will receive a timeout
                        // console.error(err);
                    });
            },
            registerBroadcastChannelHandlers() {

                const activeCallHandler = (msg) => {
                    self.callMode = true;
                    const number = msg.data.number.value;
                    const muted = msg.data.number.muted;
                    self.buttonClass = 'btn-danger';
                    self.buttonText = `End Call [${number}]${muted ? ' [muted]' : ''}`;
                    return Promise.resolve({});
                };

                registerHandler("call_started", activeCallHandler);
                registerHandler("call_muted", activeCallHandler);
                registerHandler("call_unmuted", activeCallHandler);

                registerHandler("call_ended", (msg) => {
                    //allow sending end call request which will just close the window
                    self.buttonClass = 'btn-info';
                    self.buttonText = `Close Calls Page`;
                    self.callMode = true;
                    return Promise.resolve({});
                });

                registerHandler("calls_page_closed", (msg) => {
                    self.buttonClass = 'btn-primary';
                    self.buttonText = `Open Calls Page`;
                    self.callMode = false;
                });

                //handle case when calls page is refreshed
                registerHandler("calls_page_opened", (msg) => {
                    self.checkForCallStatus();
                    return Promise.resolve({});
                });
            }
        },
        created() {
          self = this;
        },
        mounted() {
            self.registerBroadcastChannelHandlers();

            //handle case when patient page is refreshed
            self.checkForCallStatus();
        }
    }
</script>

<style>
    span.call-mode button {
        margin-top: 10px;
    }
</style>