<template>
    <div>
        <loader v-if="waiting"></loader>
        <div>{{log}}</div>
        <template v-if="!waiting">
            <div class="row">
                <div class="col-sm-12">
                    <select2 class="form-control" v-model="dropdownNumber" :disabled="onPhone[selectedPatientNumber]">
                        <option v-for="(number, key) in patientNumbers" :key="key" :value="number">{{number}}</option>
                        <option value="patientUnlisted">Other</option>
                    </select2>
                </div>

                <div v-if="dropdownNumber === 'patientUnlisted'" class="col-sm-12" style="margin-top: 5px">
                    <label for="patient-unlisted-number">Please input a 10 digit US Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-addon">+1</span>
                        <!--maxlength="10" minlength="10"-->
                        <input id="patient-unlisted-number" name="patien-unlisted-number"
                               class="form-control" type="tel"
                               title="10-digit US Phone Number" placeholder="1234567890"
                               v-model="patientUnlistedNumber" :disabled="onPhone[patientUnlistedNumber]"/>
                    </div>
                </div>

                <div class="col-sm-12" style="margin-top: 5px">
                    <button class="btn btn-circle" @click="togglePatientCallMessage(selectedPatientNumber)"
                            :disabled="invalidPatientUnlistedNumber"
                            :class="onPhone[selectedPatientNumber] ? 'btn-danger': 'btn-success'">
                        <i class="fa fa-fw fa-phone"
                           :class="onPhone[selectedPatientNumber] ? 'fa-close': 'fa-phone'"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone[selectedPatientNumber]"
                            @click="toggleMuteMessage(selectedPatientNumber)">
                        <i class="fa fa-fw"
                           :class="muted[selectedPatientNumber] ? 'fa-microphone-slash': 'fa-microphone'"></i>
                    </button>
                </div>
            </div>

            <br/>

            <div class="row">


                <div class="col-sm-12">
                    <button class="btn btn-default"
                            :disabled="!isCurrentlyOnPhone"
                            @click="createConference">
                        Create a conference call
                    </button>
                    <loader v-if="waitingForConference"></loader>
                </div>

                <div class="col-sm-12" v-for="(value, key) in otherNumbers" :key="key" style="margin-top: 5px">
                    <span>{{key}} [{{value}}]</span>
                    <button class="btn btn-circle" @click="toggleOtherCallMessage(value)"
                            :disabled="!enableConference"
                            :class="onPhone[value] ? 'btn-danger': 'btn-success'">
                        <i class="fa fa-fw fa-phone" :class="onPhone[value] ? 'fa-close': 'fa-phone'"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone[value]"
                            @click="toggleMuteMessage(value)">
                        <i class="fa fa-fw"
                           :class="muted[value] ? 'fa-microphone-slash': 'fa-microphone'"></i>
                    </button>
                </div>

                <div class="col-sm-12" style="margin-top: 5px">
                    <div class="col-sm-9 no-padding">
                        <div class="input-group">
                            <span class="input-group-addon">+1</span>
                            <!--maxlength="10" minlength="10"-->
                            <input id="other-unlisted-number" name="other-number"
                                   class="form-control" type="tel"
                                   title="10-digit US Phone Number" placeholder="1234567890"
                                   v-model="otherUnlistedNumber"
                                   :disabled="onPhone[otherUnlistedNumber] ||!enableConference"/>
                        </div>
                    </div>
                    <div class="col-sm-3 no-padding" style="margin-top: 4px; padding-left: 2px">
                        <button class="btn btn-circle" @click="toggleOtherCallMessage(otherUnlistedNumber)"
                                :disabled="invalidOtherUnlistedNumber || !enableConference"
                                :class="onPhone[otherUnlistedNumber] ? 'btn-danger': 'btn-success'">
                            <i class="fa fa-fw fa-phone"
                               :class="onPhone[otherUnlistedNumber] ? 'fa-close': 'fa-phone'"></i>
                        </button>
                    </div>
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

    import Twilio from 'twilio-client';

    let self;

    export default {
        name: 'call-number',
        components: {
            loader: LoaderComponent
        },
        props: {
            inboundUserId: String,
            outboundUserId: String,
            patientNumbers: {
                type: Object,
                default: {}
            },
            otherNumbers: {
                type: Object,
                default: {}
            }
        },
        data() {
            return {
                waiting: false,
                waitingForConference: false,
                enableConference: false,
                muted: {},
                onPhone: {},
                log: 'Initializing',
                connections: {},
                //twilio device
                device: null,
                dropdownNumber: Object.values(this.patientNumbers).length > 0 ? Object.values(this.patientNumbers)[0] : null,
                patientUnlistedNumber: '+35799451430',
                otherUnlistedNumber: '',
            }
        },
        computed: {
            invalidPatientUnlistedNumber() {
                // if (this.dropdownNumber === 'patientUnlisted') {
                //     return isNaN(this.patientUnlistedNumber.toString()) || this.patientUnlistedNumber.toString().length !== 10;
                // }
                return false;
            },
            invalidOtherUnlistedNumber() {
                // return isNaN(this.otherUnlistedNumber.toString()) || this.otherUnlistedNumber.toString().length !== 10;
                return false;
            },
            selectedPatientNumber() {
                if (this.dropdownNumber === 'patientUnlisted') {
                    return this.patientUnlistedNumber;
                }
                else {
                    return this.dropdownNumber;
                }
            },
            isCurrentlyOnPhone() {
                return Object.values(this.onPhone).some(x => x);
            }
        },
        methods: {

            toggleMuteMessage: function (number) {
                const action = this.muted[number] ? "call_unmuted" : "call_muted";
                this.toggleMute(number);
                sendRequest(action, {number: {value: number, muted: self.muted[number]}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            toggleMute: function (number) {
                const value = !this.muted[number];
                this.muted[number] = value;
                this.device.activeConnection().mute(value);
            },

            togglePatientCallMessage: function (number) {
                const isUnlisted = this.dropdownNumber === 'patientUnlisted';
                this.toggleCallMessage(number, isUnlisted, true);
            },

            toggleOtherCallMessage: function (number) {
                //if not found in otherNumbers, its unlisted
                const isUnlisted = !Object.values(this.otherNumbers).some(x => x === number);
                this.toggleCallMessage(number, isUnlisted, false);
            },

            toggleCallMessage: function (number, isUnlisted, isCallToPatient) {
                const action = this.onPhone[number] ? "call_ended" : "call_started";
                this.toggleCall(number, isUnlisted, isCallToPatient);
                sendRequest(action, {number: {value: number, muted: self.muted[number]}})
                    .then(() => {

                    })
                    .catch(err => console.error(err));
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number, isUnlisted, isCallToPatient) {

                //important - need to get a copy of the variable here
                //otherwise the computed value changes and our logic does not work
                const isCurrentlyOnPhone = this.isCurrentlyOnPhone;

                if (!this.onPhone[number]) {

                    this.$set(this.muted, number, false);
                    this.$set(this.onPhone, number, true);

                    if (isCurrentlyOnPhone) {
                        this.log = 'Adding to call: ' + number;
                        this.axios
                            .post(rootUrl('twilio/call/join-conference'), {
                                To: number.startsWith('+') ? number : '+1' + number,
                                IsUnlistedNumber: isUnlisted,
                                IsCallToPatient: isCallToPatient,
                                InboundUserId: this.inboundUserId,
                                OutboundUserId: this.outboundUserId,
                                ConferenceName: `${this.outboundUserId}_${this.inboundUserId}`
                            })
                            .then(resp => {

                            })
                            .catch(err => {
                                self.log = err.message;
                                this.$set(this.muted, number, false);
                                this.$set(this.onPhone, number, false);
                            });
                    }
                    else {
                        this.log = 'Calling ' + number;
                        this.connections[number] = this.device.connect({
                            To: number.startsWith('+') ? number : '+1' + number,
                            IsUnlistedNumber: isUnlisted,
                            IsCallToPatient: isCallToPatient,
                            InboundUserId: this.inboundUserId,
                            OutboundUserId: this.outboundUserId,
                            ConferenceName: `${this.outboundUserId}_${this.inboundUserId}`
                        });
                    }
                    EventBus.$emit('tracker:call-mode:enter');

                } else {
                    this.$set(this.muted, number, false);
                    this.$set(this.onPhone, number, false);
                    if (this.connections[number]) {
                        this.connections[number].disconnect();
                    }
                    EventBus.$emit('tracker:call-mode:exit');
                }
            },
            createConference: function () {
                this.waitingForConference = true;
                this.axios.post(rootUrl(`twilio/call/js-create-conference`),
                    {
                        'inbound_user_id': this.inboundUserId,
                        'outbound_user_id': this.outboundUserId,
                        'conference_name': `${this.outboundUserId}_${this.inboundUserId}`
                    })
                    .then(resp => {
                        this.waitingForConference = false;
                        //should check for errors here
                        this.enableConference = true;
                        console.log('conference created. now you should actually add the participant', resp.data);
                    })
                    .catch(err => {
                        this.enableConference = false;
                        this.waitingForConference = false;
                        self.log = err.message;
                    });
            },
            resetPhoneState: function () {

                if (self.onPhone) {
                    sendRequest("call_ended", {number: {value: self.selectedPatientNumber, muted: false}})
                        .then(() => {

                        })
                        .catch(err => console.error(err));
                }
                self.onPhone = {};
                self.muted = {};
                self.waiting = false;
            },
            initTwilio: function () {
                const url = rootUrl(`twilio/token`);

                self.waiting = true;
                self.axios.get(url)
                    .then(response => {
                        self.log = 'Initializing';
                        self.device = new Twilio.Device(response.data.token, {
                            closeProtection: true, //show warning when closing the page with active call
                        });

                        self.device.on('disconnect', () => {
                            console.log('twilio device: disconnect');
                            self.resetPhoneState();
                            self.connections = {};
                            self.log = 'Call ended.';
                        });

                        self.device.on('offline', () => {
                            console.log('twilio device: offline');
                            self.resetPhoneState();
                            self.connections = {};
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
                            self.waiting = false;
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'There was an error. Please refresh the page. If the issue persists please let CLH know via slack.';
                        self.waiting = false;
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
                            number = self.selectedPatientNumber;
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

                    for (let i in self.onPhone) {
                        if (self.onPhone[i]) {
                            self.toggleCall(i);
                        }
                    }

                    //resolve the promise but also close the window
                    return new Promise((resolve, reject) => {
                        resolve({});
                        window.close();
                    });

                }

                registerHandler("end_call", endCallHandler);

                function muteHandler(msg) {

                    for (let i in self.onPhone) {
                        if (self.onPhone[i] && !self.muted[i]) {
                            self.toggleMute(i);
                        }
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

                for (let i in self.onPhone) {
                    if (self.onPhone[i]) {
                        self.toggleCallMessage(i);
                    }
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
            self.initTwilio();
        }
    }
</script>
<style>
    .no-padding {
        padding-left: 0;
        padding-right: 0;
    }
</style>