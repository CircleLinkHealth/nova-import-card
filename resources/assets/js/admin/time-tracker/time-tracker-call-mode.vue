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
        <button v-if="!callMode" class="btn btn-primary" type="button"
                @click="openCallPage">
            <span>Start Call Mode</span>
        </button>
        <button v-if="callMode" class="btn btn-danger" type="button"
                @click="endCall">
            <span>End Call Mode</span>
        </button>
    </span>
</template>

<script>
    import EventBus from './comps/event-bus'
    import LoaderComponent from '../../components/loader'
    import {rootUrl} from "../../app.config";

    let bc = new BroadcastChannel("cpm");

    export default {
        props: {
            noDisplay: Boolean,
            patientId: Number
        },
        computed: {},
        data() {
            return {
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
            },
            openCallPage(e) {
                const strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes";
                const URL = rootUrl(`manage-patients/${this.patientId}/call`);
                window.open(URL, "_blank", strWindowFeatures);
            },
            endCall(e) {
                bc.postMessage({action: "end_call", data: {}});
            },
            handleBroadcastMessage(message) {

                const action = message.action;
                const data = message.data;
                switch (action) {
                    case "call_status_response":
                    case "twilio_ready":
                        //call mode is on if we receive a number
                        this.callMode = !!data.number;
                        break;
                    case "start_call_response":
                        this.callMode = !data.error;
                        break;
                    case "call_window_close":
                    case "end_call_response":
                        this.callMode = !!data.error;
                        break;
                    case "mute_call_response":
                    case "unmute_call_response":

                        break;
                    default:
                        break;
                }
            }
        },
        mounted() {

            bc.onmessage = (ev) => {
                this.handleBroadcastMessage(ev.data);
            };

            bc.postMessage({action: "call_status", data: {}});

            // EventBus.$on('server:call-mode', (callMode) => {
            //     this.callMode = callMode
            //     this.loaders.callMode = false
            // })
        }
    }
</script>

<style>
    span.call-mode button {
        margin-top: 10px;
    }
</style>