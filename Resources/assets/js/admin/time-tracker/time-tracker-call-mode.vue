<template>
    <div>
        <span v-if="twilioEnabled" class="call-mode">

            <button v-if="!isNaN(patientId)"
                    type="button"
                    class="btn" :class="callMode ? 'btn-danger' : 'btn-primary'"
                    :disabled="callMode === null"
                    @click="toggleCallMode">
                    <span v-if="callMode">End Roaming</span>
                    <span v-else>Roaming</span>
                <loader v-show="(callMode === null) || loaders.callMode"></loader>
            </button>

            <button class="btn" :class="buttonClass" type="button"
                    @click="callButtonClick">
                <span>{{buttonText}}</span>
            </button>

        </span>
        <span v-else class="call-mode">
            <button class="btn btn-primary" type="button"
                    @click="enterCallMode" v-if="Number(patientId) && (callMode === false)">
                <span>Start Call Mode</span>
            </button>
            <button class="btn btn-danger" type="button"
                    @click="exitCallMode" v-if="Number(patientId) && (callMode === true)">
                <span>End Call Mode</span>
            </button>
            <loader v-if="(callMode === null) || loaders.callMode"></loader>
        </span>
    </div>
</template>

<script>
    import EventBus from './comps/event-bus'
    import LoaderComponent from '../../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/loader'
    import {rootUrl} from "../../app.config";
    import {sendRequest, registerHandler} from "../../components/bc-job-manager";

    let self;

    export default {
        props: {
            twilioEnabled: Boolean,
            patientId: Number
        },
        computed: {},
        data() {
            return {
                buttonClass: 'btn-primary',
                buttonText: "Make Call",
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
                if (self.isCallPageOpen) {
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
                self.isCallPageOpen = true;

                const strWindowFeatures = "location=yes,height=700,width=520,scrollbars=yes,status=yes";
                const URL = rootUrl(`manage-patients/${this.patientId}/call`);
                window.open(URL, "_blank", strWindowFeatures);
            },
            endCall(e) {
                self.isCallPageOpen = false;
                self.buttonClass = 'btn-primary';
                self.buttonText = "Make Call";
                sendRequest("end_call", null)
                    .then(msg => {
                    })
                    .catch(err => console.error(err));
            },
            checkForCallStatus() {
                sendRequest("call_status", null, 5000)
                    .then(msg => {
                        if (!msg) {
                            return;
                        }

                        //if we got a response, it means calls page is open
                        self.isCallPageOpen = true;

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
                        self.isCallPageOpen = false;
                        // if calls page is not open, we will receive a timeout
                        console.warn(err);
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
                    self.isCallPageOpen = true;
                    return Promise.resolve({});
                });

                registerHandler("calls_page_closed", (msg) => {
                    self.buttonClass = 'btn-primary';
                    self.buttonText = `Make Call`;
                    self.isCallPageOpen = false;
                    return Promise.resolve({});
                });

                //handle case when calls page is refreshed
                registerHandler("calls_page_opened", (msg) => {
                    self.isCallPageOpen = true;
                    self.checkForCallStatus();
                    return Promise.resolve({});
                });
            },
            toggleCallMode(e) {
                if (e) {
                    e.preventDefault();
                }
                this.loaders.callMode = true;
                if (this.callMode) {
                    EventBus.$emit('tracker:call-mode:exit');
                }
                else {
                    EventBus.$emit('tracker:call-mode:enter');
                }
            },
            enterCallMode(e) {
                if (e) {
                    e.preventDefault()
                }
                this.loaders.callMode = true
                EventBus.$emit('tracker:call-mode:enter')
            },
            exitCallMode(e) {
                if (e) {
                    e.preventDefault()
                }
                this.loaders.callMode = true
                EventBus.$emit('tracker:call-mode:exit')
            }
        },
        created() {
            self = this;
        },
        mounted() {
            self.registerBroadcastChannelHandlers();

            //handle case when patient page is refreshed
            self.checkForCallStatus();

            EventBus.$on('server:call-mode', (callMode) => {
                this.callMode = callMode
                this.loaders.callMode = false
            });
        }
    }
</script>

<style>
    span.call-mode button {
        margin-top: 10px;
    }

    .call-mode .loader {
        display: inline-block;
        vertical-align: middle;
        width: 20px;
        height: 20px;
    }
</style>