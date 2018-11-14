<template>
    <div>
        <loader v-if="waiting"></loader>
        <div>{{log}}</div>
        <template v-if="!waiting">
            <div class="row">
                <div class="col-sm-12">
                    <select2 class="form-control" v-model="dropdownNumber" :disabled="onPhone">
                        <option v-for="(number, index) in numbers" :key="index" :value="number">{{number}}</option>
                        <option :key="numbers ? numbers.length : 0" value="other">Other</option>
                    </select2>
                </div>


                <div v-if="dropdownNumber === 'other'" class="col-sm-12" style="margin-top: 5px">
                    <label for="other-number">Please input a 10 digit US Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-addon">+1</span>
                        <input id="other-number" name="other-number"
                               maxlength="10" minlength="10"
                               class="form-control" type="tel"
                               title="10-digit US Phone Number" placeholder="1234567890"
                               v-model="otherNumber" :disabled="onPhone"/>
                    </div>
                </div>

                <div class="col-sm-12" style="margin-top: 5px">
                    <button class="btn btn-circle" @click="toggleCallMessage()"
                            :class="onPhone ? 'btn-danger': 'btn-success'"
                            :disabled="!validNumber">
                        <i class="fa fa-fw fa-phone" :class="onPhone ? 'fa-close': 'fa-phone'"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone"
                            @click="toggleMuteMessage()">
                        <i class="fa fa-fw"
                           :class="muted ? 'fa-microphone-slash': 'fa-microphone'"></i>
                    </button>
                </div>
            </div>
        </template>

    </div>
</template>
<script>
    import {rootUrl} from "../app.config";
    import EventBus from '../admin/time-tracker/comps/event-bus'
    import LoaderComponent from '../components/loader';
    import {registerHandler, sendRequest} from "./bc-job-manager";

    let Twilio;
    let self;

    export default {
        name: 'call-number',
        components: {
            loader: LoaderComponent
        },
        props: [
            'numbers',
        ],
        data() {
            return {
                waiting: false,
                muted: false,
                onPhone: false,
                log: 'Initializing',
                connection: null,
                //twilio device
                device: null,
                dropdownNumber: this.numbers[0] ? this.numbers[0] : null,
                otherNumber: null
            }
        },
        computed: {
            selectedNumber() {
                return this.dropdownNumber === 'other' ? ('+1' + this.otherNumber) : this.dropdownNumber;
            },
            validNumber() {
                if (!this.selectedNumber) {
                    return false;
                }
                if (isNaN(this.selectedNumber.substring(1))) {
                    return false;
                }
                return this.selectedNumber.toString().length === 12;
            }
        },
        methods: {

            toggleMute: function () {
                const value = !this.muted;
                this.muted = value;
                this.device.activeConnection().mute(value);
            },

            toggleMuteMessage: function () {
                const action = this.muted ? "call_unmuted" : "call_muted";
                this.toggleMute();
                sendRequest(action, {number: {value: this.selectedNumber, muted: self.muted}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            toggleCallMessage: function () {
                const number = this.selectedNumber;
                const action = this.onPhone ? "call_ended" : "call_started";
                this.toggleCall(number);
                sendRequest(action, {number: {value: number, muted: self.muted}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number) {
                if (!this.onPhone) {
                    this.muted = false;
                    this.onPhone = true;
                    const isUnlisted = this.dropdownNumber === 'other';
                    // make outbound call with current number
                    this.connection = this.device.connect({To: number, IsUnlistedNumber: isUnlisted});
                    this.log = 'Calling ' + number;
                    EventBus.$emit('tracker:call-mode:enter');
                } else {
                    this.muted = false;
                    this.onPhone = false;
                    // hang up call in progress
                    this.device.disconnectAll();
                    EventBus.$emit('tracker:call-mode:exit');
                }
            },
            resetPhoneState: function () {

                if (self.onPhone) {
                    sendRequest("call_ended", {number: {value: self.selectedNumber, muted: false}})
                        .then(() => {

                        })
                        .catch(err => console.error(err));
                }
                self.onPhone = false;
                self.muted = false;
            },
            setTwilio: function (onDone) {
                self.waiting = true;
                if (window.Twilio) {
                    self.waiting = false;
                    Twilio = window.Twilio;
                    onDone();
                    return;
                }
                setTimeout(() => {
                    self.setTwilio(onDone);
                }, 100);
            },
            initTwilio: function () {
                const url = rootUrl(`/twilio/token`);

                self.axios.get(url)
                    .then(response => {
                        this.log = 'Ready';
                        self.device = new Twilio.Device(response.data.token);

                        self.device.on('disconnect', () => {
                            console.log('twilio device: disconnect');
                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Call ended.';
                        });

                        self.device.on('offline', () => {
                            console.log('twilio device: offline');
                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Offline.';
                        });

                        self.device.on('error', (err) => {
                            console.log('twilio device: error');
                            self.resetPhoneState();
                            self.log = err.message;
                        });

                        self.device.on('ready', () => {
                            console.log('twilio device: ready');
                            self.log = 'Ready to make call';
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
                    });
            },
            registerBroadcastChannelHandlers: function () {
                registerHandler("call_status", (msg) => {
                    let status = null;
                    let number = null;

                    if (!self.device || !self.device.isInitialized) {
                        status = "twilio_not_ready";
                    }
                    else {
                        status = self.device.status();
                        if (status === "ready" && self.onPhone) {
                            number = self.selectedNumber;
                        }
                    }
                    return Promise.resolve({
                        status: status,
                        number: number ? {
                            value: number,
                            muted: self.muted
                        } : null
                    });
                });

                function endCallHandler(msg) {

                    if (self.onPhone) {
                        self.toggleCall(self.selectedNumber);
                    }

                    //resolve the promise but also close the window
                    return new Promise((resolve, reject) => {
                        resolve({});
                        window.close();
                    });

                }

                registerHandler("end_call", endCallHandler);

                function muteHandler(msg) {

                    if (self.onPhone) {
                        self.toggleMute();
                    }
                    return Promise.resolve({});
                }

                registerHandler("mute_call", muteHandler);
                registerHandler("unmute_call", muteHandler);
            }

        },
        created() {

            self = this;
            this.resetPhoneState();
            //this.registerBroadcastChannelHandlers();

            window.onbeforeunload = function (event) {
                if (self.onPhone) {
                    self.toggleCallMessage();
                }
                sendRequest("calls_page_closed", {})
                    .then((msg) => {
                    })
                    .catch((err) => {
                    });
            };

            //just handling the case of refresh
            sendRequest("calls_page_opened", {})
                .then((msg) => {
                })
                .catch((err) => {
                });
        },
        mounted() {
            self.setTwilio(() => {
                self.initTwilio();
            });
        }
    }
</script>
<style>

</style>