<template>
    <!-- Modal -->
    <div class="call-assistance-modal">
        <div class="content">
            <div class="header">
                <mdb-btn @click="handleClick" class="btn-transparent">
                    <mdb-icon icon="angle-left" size="2x"></mdb-icon>
                </mdb-btn>
                <span class="title">Call</span>
            </div>
            <br>
            <div class="body">

                <div class="spinner-overlay" v-show="waiting">
                    <div class="text-center">
                        <mdb-icon icon="spinner" :spin="true"/>
                    </div>
                </div>

                Request Call Assistance
                <br/>
                {{phoneNumber}}

                <mdb-btn size="sm"
                         @click="toggleCall"
                         :disabled="!ready"
                         :color="isCurrentlyOnPhone ? 'danger' : 'success'">
                    <mdb-icon :icon="isCurrentlyOnPhone ? 'phone-slash' : 'phone'"></mdb-icon>
                </mdb-btn>

                <br/>

                <div :class="hasError ? 'error-logs' : ''">{{log}}</div>
                <div v-if="hasError" style="display: none">
                    <button class="btn btn-circle btn-warning" @click="initTwilio">
                        Retry
                    </button>
                </div>

            </div>
        </div>
    </div>

</template>

<script>

    import Twilio from 'twilio-client';
    import {mdbBtn, mdbIcon} from 'mdbvue';

    let self;

    export default {
        name: "callAssistance",
        components: {mdbIcon, mdbBtn},
        props: {
            phoneNumber: {
                type: String,
                required: true
            },
            cpmCallerToken: {
                type: String,
                required: true,
            },
            cpmCallerUrl: {
                type: String,
                required: true,
            },
            debug: {
                type: Boolean,
                default: false
            },
            fromNumber: {
                type: String,
                default: null
            },
        },
        data() {
            return {
                onPhone: {},
                hasError: false,
                log: null,
                ready: false,
                waiting: false,
                device: null,
                connection: null,
            }
        },

        created() {
            self = this;
            this.resetPhoneState();
        },

        mounted() {
            self.initTwilio();
        },

        computed: {
            isCurrentlyOnPhone() {
                return Object.values(this.onPhone).some(x => x);
            },
        },

        methods: {
            handleClick() {
                this.$emit('closeCallAssistanceModal')
            },

            resetPhoneState: function () {
                self.onPhone = {};
                self.waiting = false;
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function () {

                const number = this.phoneNumber;
                const isDebug = this.debug;

                //important - need to get a copy of the variable here
                //otherwise the computed value changes and our logic does not work
                if (!this.onPhone[number]) {
                    this.$set(this.onPhone, number, true);

                    if (!isDebug) {
                        this.log = 'Calling ' + number;
                        this.connection = this.device.connect(this.getTwimlAppRequest(number));
                    }
                } else {
                    this.$set(this.onPhone, number, false);

                    if (!isDebug) {
                        this.log = 'Ending call';
                        if (this.connection) {
                            this.connection.disconnect();
                        }
                    }
                }
            },
            getTwimlAppRequest: function (number) {

                //if calling a practice, the fromNumber will be practice's phone number,
                //the same as the 'to' number. so don't send From and will be decided on server
                const to = number.startsWith('+') ? number : ('+1' + number);

                // cpm caller has a bug and if we do not supply From, it returns validation error 422,
                // even though field is supposed to be set on server
                const from = this.fromNumber !== to ? this.fromNumber : undefined;

                return {
                    From: from,
                    To: to,
                    IsUnlistedNumber: 0,
                    IsCallToPatient: 0,
                    // InboundUserId: this.inboundUserId,
                    // OutboundUserId: this.outboundUserId,
                };
            },

            twilioOffline: function () {
                self.ready = false;
                self.waiting = true;
            },
            twilioOnline: function () {
                self.ready = true;
                self.waiting = false;
            },

            getCpmCallerUrl: function (path) {
                if (this.cpmCallerUrl[this.cpmCallerUrl.length - 1] === "/") {
                    return this.cpmCallerUrl + path;
                }
                else {
                    return this.cpmCallerUrl + "/" + path;
                }
            },

            initTwilio: function () {
                const url = this.getCpmCallerUrl(`twilio/token?cpm-token=${this.cpmCallerToken}`);

                self.log = "Fetching token from server";
                self.ready = false;
                self.waiting = true;
                axios.get(url, {withCredentials: true})
                    .then(response => {
                        self.log = 'Initializing Twilio';
                        self.device = new Twilio.Device(response.data.token, {
                            closeProtection: true, //show warning when closing the page with active call - NOT WORKING
                            // debug: true,
                            // region: 'us1' //default to US East Coast (Virginia)
                        });

                        self.device.on('disconnect', () => {
                            //exit call mode when all calls are disconnected
                            console.log('twilio device: disconnect');

                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Call ended.';
                        });

                        self.device.on('offline', () => {
                            //this event can be raised on a temporary disconnection
                            //we should disable any actions when this event is fired
                            console.log('twilio device: offline');
                            self.twilioOffline();
                            self.log = 'Offline.';
                        });

                        self.device.on('error', (err) => {
                            self.resetPhoneState();
                            self.log = err.message;
                        });

                        self.device.on('ready', () => {
                            console.log('twilio device: ready');
                            self.log = 'Ready to make call';
                            self.twilioOnline();
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.hasError = true;
                        self.log = 'There was an error. Please refresh the page. If the issue persists please let CLH know via slack.';
                        self.ready = false;
                        self.waiting = false;
                    });
            },
        },
    }

</script>

<style scoped>
    .call-assistance-modal {
        width: 250px;
        height: 250px;
        box-shadow: 0 0 5px 0 rgba(0, 0, 0, 0.1);
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        margin-left: 2%;
        margin-bottom: 1%;
    }

    .header {
        width: 248px;
        height: 50px;
        box-shadow: 0 0 5px 0 #50b2e2;
        border: solid 1px #50b2e2;
        background-color: #50b2e2;
    }

    .title {
        font-family: Poppins, serif;
        letter-spacing: 1px;
        color: #ffffff;
    }

    .body {
        position: relative;
        height: 46px;
        font-family: Poppins, serif;
        font-size: 14px;
        letter-spacing: 0.8px;
        padding-left: 15px;
        padding-right: 15px;
        color: #1a1a1a;
    }

    .body .text-style-1 {
        font-weight: 600;
        color: #50b2e2;
    }

    .fa-angle-left {
        color: #ffffff;
    }

    .btn-transparent {
        background-color: transparent !important;
        padding: 0 10px;
        box-shadow: none;
    }

    .error-logs {
        color: red;
        font-size: 10px;
    }
</style>
