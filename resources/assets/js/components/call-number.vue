<template>
    <div>
        <loader v-if="waiting"></loader>
        <div>{{log}}</div>
        <template v-if="!waiting">
            <div class="row">
                <div class="col-sm-10">
                    <select2 class="form-control" v-model="selectedNumber" :disabled="hasActiveCall">
                        <option v-for="(number, index) in numbers" :key="index" :value="number">{{number}}</option>
                    </select2>
                </div>

                <div class="col-sm-2" style="margin-top: 5px">
                    <button class="btn btn-circle" @click="toggleCallMessage(selectedNumber)"
                            :class="onPhone[selectedNumber] ? 'btn-danger': 'btn-success'"
                            :disabled="!validPhone(selectedNumber) || !selectedNumber">
                        <i class="fa fa-fw fa-phone" :class="onPhone[selectedNumber] ? 'fa-close': 'fa-phone'"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone[selectedNumber]"
                            @click="toggleMuteMessage(selectedNumber)">
                        <i class="fa fa-fw"
                           :class="muted[selectedNumber] ? 'fa-microphone-slash': 'fa-microphone'"></i>
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
                hasActiveCall: false,
                waiting: false,
                muted: [],
                onPhone: [],
                log: 'Initializing',
                connection: null,
                //twilio device
                device: null,
                selectedNumber: this.numbers[0] ? this.numbers[0] : null
            }
        },
        methods: {
            validPhone: function (number) {
                // return /^([0-9]|#|\*)+$/.test(number.replace(/[-()\s]/g,''));
                return true;
            },
            // Handle muting
            toggleMute: function (number) {
                const value = !this.muted[number];
                this.muted[number] = value;
                this.device.activeConnection().mute(value);
            },

            toggleMuteMessage: function (number) {
                const action = this.muted[number] ? "call_unmuted" : "call_muted";
                this.toggleMute(number);
                sendRequest(action, {number: {value: number, muted: self.muted[number]}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            toggleCallMessage: function (number) {
                const action = this.onPhone[number] ? "call_ended" : "call_started";
                this.toggleCall(number);
                sendRequest(action, {number: {value: number, muted: self.muted[number]}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number) {
                if (!this.onPhone[number]) {
                    this.hasActiveCall = true;
                    this.muted[number] = false;
                    this.onPhone[number] = true;
                    // make outbound call with current number
                    this.connection = this.device.connect({To: number});
                    this.log = 'Calling ' + number;
                    EventBus.$emit('tracker:call-mode:enter');
                } else {
                    this.hasActiveCall = false;
                    this.muted[number] = false;
                    this.onPhone[number] = false;
                    // hang up call in progress
                    this.device.disconnectAll();
                    EventBus.$emit('tracker:call-mode:exit');
                }
            },
            resetPhoneState: function () {
                self.hasActiveCall = false;
                self.numbers.forEach(x => {
                    self.onPhone[x] = false;
                    self.muted[x] = false;
                });
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
            getActiveCalNumber() {
                let number = null;
                for (let i in self.onPhone) {

                    if (!self.onPhone.hasOwnProperty(i)) {
                        continue;
                    }

                    if (self.onPhone[i]) {
                        number = i;
                        break;
                    }
                }
                return number;
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
                        if (status === "ready") {
                            number = self.getActiveCalNumber();
                        }
                    }
                    return Promise.resolve({
                        status: status,
                        number: number ? {
                            value: number,
                            muted: self.muted[number]
                        } : null
                    });
                });

                function endCallHandler(msg) {

                    const number = self.getActiveCalNumber();
                    if (number) {
                        self.toggleCall(number);
                    }

                    //resolve the promise but also close the window
                    return new Promise((resolve, reject) => {
                        resolve({});
                        window.close();
                    });

                }

                registerHandler("end_call", endCallHandler);

                function muteHandler(msg) {

                    const number = self.getActiveCalNumber();
                    if (number) {
                        self.toggleMute(number);
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
            this.registerBroadcastChannelHandlers();

            window.onbeforeunload = function (event) {
                let number = self.getActiveCalNumber();
                if (number) {
                    self.toggleCallMessage(number);
                }
                sendRequest("calls_page_closed", {})
                    .then((msg) => {})
                    .catch((err) => {});
            };

            //just handling the case of refresh
            sendRequest("calls_page_opened", {})
                .then((msg) => {})
                .catch((err) => {});
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