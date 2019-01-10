<template>
    <div>
        <loader v-if="waiting"></loader>
        <div class="window-close-banner">
            <strong>
                When the call is ended, this window will close after {{endCallWindowCloseDelay}} seconds.
            </strong>
            <br />
            <strong>
                If you would like to make another call, please click on 'Open Calls Page' again.
            </strong>
        </div>
        <div>{{log}}</div>
        <div v-show="closeCountdown > 0">This window will close in <span class="countdown-seconds">{{closeCountdown}}</span> seconds.</div>
        <template v-if="!waiting">
            <div class="row">
                <div class="col-xs-12">
                    <select2 class="form-control" v-model="dropdownNumber" :disabled="onPhone[selectedPatientNumber]">
                        <option v-for="(number, key) in patientNumbers" :key="key" :value="number">{{number}}</option>
                        <option value="patientUnlisted">Other</option>
                    </select2>
                </div>

                <div v-if="dropdownNumber === 'patientUnlisted'" class="col-xs-12" style="margin-top: 5px">
                    <label>Please input a 10 digit US Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-addon">+1</span>

                        <template v-if="debug">
                            <input name="patient-unlisted-number"
                                   class="form-control" type="tel"
                                   title="10-digit US Phone Number" placeholder="1234567890"
                                   v-model="patientUnlistedNumber" :disabled="onPhone[patientUnlistedNumber]"/>
                        </template>
                        <template v-else>
                            <input name="patient-unlisted-number"
                                   maxlength="10" minlength="10"
                                   class="form-control" type="tel"
                                   title="10-digit US Phone Number" placeholder="1234567890"
                                   v-model="patientUnlistedNumber" :disabled="onPhone[patientUnlistedNumber]"/>
                        </template>


                    </div>
                </div>

                <div class="col-xs-12" style="margin-top: 5px">
                    <button class="btn btn-circle" @click="togglePatientCallMessage(selectedPatientNumber)"
                            :disabled="invalidPatientUnlistedNumber || closeCountdown > 0"
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

            <div class="row" v-if="allowConference" v-show="isCurrentlyOnPhone">

                <div class="col-xs-12">
                    <loader v-if="waitingForConference"></loader>
                </div>

                <div class="col-xs-12" v-for="(value, key) in otherNumbers" :key="key" style="margin-top: 5px">
                    <span>{{key}} [{{value}}]</span>
                    <button class="btn btn-circle" @click="toggleOtherCallMessage(value)"
                            :disabled="!onPhone[value] && isCurrentlyOnConference"
                            :class="onPhone[value] ? 'btn-danger': 'btn-success'">
                        <i class="fa fa-fw fa-phone" :class="onPhone[value] ? 'fa-close': 'fa-phone'"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone[value]"
                            @click="toggleMuteMessage(value)">
                        <i class="fa fa-fw"
                           :class="muted[value] ? 'fa-microphone-slash': 'fa-microphone'"></i>
                    </button>
                </div>

                <div class="col-xs-12" style="margin-top: 5px">
                    <div class="col-xs-9 no-padding">
                        <div class="input-group">
                            <span class="input-group-addon">+1</span>

                            <template v-if="debug">
                                <input name="other-number"
                                       class="form-control" type="tel"
                                       title="10-digit US Phone Number" placeholder="1234567890"
                                       v-model="otherUnlistedNumber"
                                       :disabled="onPhone[otherUnlistedNumber] || isCurrentlyOnConference"/>
                            </template>
                            <template v-else>
                                <input name="other-number"
                                       maxlength="10" minlength="10"
                                       class="form-control" type="tel"
                                       title="10-digit US Phone Number" placeholder="1234567890"
                                       v-model="otherUnlistedNumber"
                                       :disabled="onPhone[otherUnlistedNumber] || isCurrentlyOnConference"/>
                            </template>

                        </div>
                    </div>
                    <div class="col-xs-3 no-padding" style="margin-top: 4px; padding-left: 2px">
                        <button class="btn btn-circle" @click="toggleOtherCallMessage(otherUnlistedNumber)"
                                :disabled="invalidOtherUnlistedNumber || (!onPhone[otherUnlistedNumber] && isCurrentlyOnConference)"
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

    const PARTICIPANT_ADDED_IN_CONFERENCE_THRESHOLD = 60000;

    export default {
        name: 'call-number',
        components: {
            loader: LoaderComponent,
        },
        props: {
            debug: {
                type: Boolean,
                default: false
            },
            fromNumber: {
                type: String,
                default: null
            },
            allowConference: Boolean,
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
                queuedNumbersForConference: [],
                addedNumbersInConference: [],
                muted: {},
                onPhone: {},
                log: 'Initializing',
                endCallWindowCloseDelay: 5,
                closeCountdown: 0,
                closeCountdownInterval: null,
                connection: null,
                //twilio device
                device: null,
                dropdownNumber: Object.values(this.patientNumbers).length > 0 ? Object.values(this.patientNumbers)[0] : 'patientUnlisted',
                patientUnlistedNumber: '',
                otherUnlistedNumber: '',
                callSids: {}
            }
        },
        computed: {
            invalidPatientUnlistedNumber() {
                if (this.debug) {
                    return false;
                }
                if (this.dropdownNumber === 'patientUnlisted') {
                    return isNaN(this.patientUnlistedNumber.toString()) || this.patientUnlistedNumber.toString().length !== 10;
                }
                return false;
            },
            invalidOtherUnlistedNumber() {
                if (this.debug) {
                    return false;
                }
                return isNaN(this.otherUnlistedNumber.toString()) || this.otherUnlistedNumber.toString().length !== 10;
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
            },
            isCurrentlyOnConference() {
                return Object.values(this.onPhone).filter(x => x).length > 1;
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
                if (this.device && this.device.activeConnection()) {
                    this.device.activeConnection().mute(value);
                }
            },

            togglePatientCallMessage: function (number) {
                const isUnlisted = this.dropdownNumber === 'patientUnlisted';
                let makeTheCall = true;

                if (!this.onPhone[number] && isUnlisted && !confirm('This is a new number. Please confirm this is a patient-related call.')) {
                    makeTheCall = false;
                }

                if (makeTheCall) {
                    this.toggleCallMessage(number, isUnlisted, true);
                }
            },

            toggleOtherCallMessage: function (number) {
                //if not found in otherNumbers, its unlisted
                const isUnlisted = !Object.values(this.otherNumbers).some(x => x === number);
                let makeTheCall = true;

                if (!this.onPhone[number] && isUnlisted && !confirm('This is a new number. Please confirm this is a patient-related call.')) {
                    makeTheCall = false;
                }

                if (makeTheCall) {
                    this.toggleCallMessage(number, isUnlisted, false);
                }
            },

            toggleCallMessage: function (number, isUnlisted, isCallToPatient) {
                const action = this.onPhone[number] ? "call_ended" : "call_started";
                this.toggleCall(number, isUnlisted, isCallToPatient);

                //inform other pages only about patient calls
                if (isCallToPatient) {
                    sendRequest(action, {number: {value: number, muted: self.muted[number]}})
                        .then(() => {

                        })
                        .catch(err => console.error(err));
                }
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number, isUnlisted, isCallToPatient) {

                //important - need to get a copy of the variable here
                //otherwise the computed value changes and our logic does not work
                const isCurrentlyOnPhone = this.isCurrentlyOnPhone;
                const isCurrentlyOnConference = this.isCurrentlyOnConference;

                if (!this.onPhone[number]) {

                    this.$set(this.muted, number, false);
                    this.$set(this.onPhone, number, true);

                    if (isCurrentlyOnPhone) {
                        this.log = 'Adding to call: ' + number;
                        this.queuedNumbersForConference.push({number, isUnlisted, isCallToPatient});
                        this.createConference();
                    }
                    else {
                        this.log = 'Calling ' + number;
                        this.connection = this.device.connect(this.getTwimlAppRequest(number, isUnlisted, isCallToPatient));
                    }
                    EventBus.$emit('tracker:call-mode:enter');

                } else {

                    //remove from numbers that are in conference
                    for (let i = 0; i < this.addedNumbersInConference.length; i++) {
                        if (this.addedNumbersInConference[i].number === number) {
                            this.addedNumbersInConference.splice(i, 1);
                            break;
                        }
                    }

                    this.$set(this.muted, number, false);
                    this.$set(this.onPhone, number, false);

                    if (isCurrentlyOnConference) {
                        this.log = `Hanging up call to ${number}`;
                        this.axios
                            .post(rootUrl('twilio/call/end'), {
                                CallSid: this.callSids[number],
                                InboundUserId: this.inboundUserId,
                                OutboundUserId: this.outboundUserId,
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
                        this.log = 'Ending call';
                        if (this.connection) {
                            this.connection.disconnect();
                        }
                    }

                }
            },
            getTwimlAppRequest: function (number, isUnlisted, isCallToPatient) {

                //if calling a practice, the fromNumber will be practice's phone number,
                //the same as the 'to' number. so don't send From and will be decided on server
                const to = number.startsWith('+') ? number : ('+1' + number);
                const from = this.fromNumber !== to ? this.fromNumber : undefined;

                return {
                    From: from,
                    To: to,
                    IsUnlistedNumber: isUnlisted ? 1 : 0,
                    IsCallToPatient: isCallToPatient ? 1 : 0,
                    InboundUserId: this.inboundUserId,
                    OutboundUserId: this.outboundUserId,
                };
            },
            createConference: function () {
                this.waitingForConference = true;
                this.axios.post(rootUrl(`twilio/call/js-create-conference`),
                    {
                        'inbound_user_id': this.inboundUserId,
                        'outbound_user_id': this.outboundUserId,
                    })
                    .then(resp => {

                        if (resp.data.errors) {
                            throw new Error(resp.data.errors.join('\n'));
                        }
                        setTimeout(() => {
                            this.getConferenceInfo();
                        }, 1000);
                    })
                    .catch(err => {
                        this.waitingForConference = false;
                        self.log = err.message;
                    });
            },
            getConferenceInfo: function () {

                if (!this.connection) {
                    //no need to keep asking for conference if we have no connection
                    return;
                }

                this.axios.post(rootUrl(`twilio/call/get-conference-info`),
                    {
                        'inbound_user_id': this.inboundUserId,
                        'outbound_user_id': this.outboundUserId,
                    })
                    .then(resp => {

                        if (resp.data.errors) {
                            throw new Error(resp.data.errors.join('\n'));
                        }

                        if (resp.data.participants) {

                            //set the callsids
                            //also set the onphone using status

                            const participants = resp.data.participants;
                            for (let i = 0; i < participants.length; i++) {
                                const participant = participants[i];

                                let to = participant.to;

                                //we might have called with '+' but on client side we entered without the '+'
                                if (typeof this.onPhone[to] === 'undefined') {
                                    to = to.substring(1);
                                }

                                //number not on client, ignore
                                if (typeof this.onPhone[to] === 'undefined') {
                                    continue;
                                }

                                this.$set(this.callSids, to, participant.call_sid);

                                if (participant.status === 'in-progress' || participant.status === 'queued') {

                                    let found = false;
                                    for (let i = 0; i < this.addedNumbersInConference.length; i++) {
                                        if (this.addedNumbersInConference[i].number === to) {
                                            found = true;
                                            break;
                                        }
                                    }

                                    if (!found) {
                                        this.addedNumbersInConference.push({to, date: Date.now()});
                                    }

                                    //should never actually have to change from false to true, but leaving here for my sanity
                                    this.$set(this.onPhone, to, true);
                                }
                                else {

                                    for (let i = 0; i < this.addedNumbersInConference.length; i++) {
                                        if (this.addedNumbersInConference[i].number === to) {
                                            this.addedNumbersInConference.splice(i, 1);
                                            break;
                                        }
                                    }

                                    this.$set(this.onPhone, to, false);
                                    this.$set(this.muted, to, false);
                                }
                            }

                            for (let i in this.onPhone) {
                                if (!this.onPhone.hasOwnProperty(i)) {
                                    continue;
                                }

                                //this number is queued to be added in the conference,
                                //it will not be found in participants but we should not mark as onPhone=false
                                //since we will add soon
                                if (this.queuedNumbersForConference.some(x => x.number === i)) {
                                    continue;
                                }

                                //if only recently added, do not mark as onPhone=false
                                const numberAddedEntry = this.addedNumbersInConference.find(x => x.number === i);
                                if (numberAddedEntry && ((Date.now() - numberAddedEntry.date) < PARTICIPANT_ADDED_IN_CONFERENCE_THRESHOLD)) {
                                    continue;
                                }

                                //we might have called with '+' but on client side we entered without the '+'
                                let info = participants.find(x => x.to === i);
                                if (!info) {
                                    info = participants.find(x => x.to.substring(1) === i);
                                }
                                if (!info) {
                                    this.$set(this.onPhone, i, false);
                                    this.$set(this.muted, i, false);
                                }
                            }
                        }

                        if (resp.data.status === 'in-progress') {
                            this.waitingForConference = false;
                            this.addQueuedParticipants();
                        }

                        //this might be problematic if:
                        // 1. Nurse on call with patient
                        // 2. Nurse calls practice (conference)
                        // 3. Practice hangs up
                        // 4. Nurse decides to call Practice again
                        // 5. Patient hangs up before practice answers
                        // At this moment, the conference has no participants (practice not answered yet),
                        // so we decide to end the connection. A 'corner-case' you might say.
                        // By letting this piece of code here, we provide the convenience of
                        // ending the conference if there are no more participants
                        // (and not specifically asking for the nurse to press the end call button)
                        // NOTE: this applies only to conference calls (not direct outbound calls)
                        if (!this.isCurrentlyOnPhone && this.connection != null) {
                            this.connection.disconnect();
                        }

                        setTimeout(this.getConferenceInfo.bind(this), 1000);
                    })
                    .catch(err => {
                        this.waitingForConference = false;
                        self.log = err.message;
                    });
            },
            addQueuedParticipants: function () {
                if (!this.queuedNumbersForConference || !this.queuedNumbersForConference.length) {
                    return;
                }

                const {number, isUnlisted, isCallToPatient} = this.queuedNumbersForConference.pop();
                this.addedNumbersInConference.push({number, date: Date.now()});
                this.axios
                    .post(rootUrl('twilio/call/join-conference'), this.getTwimlAppRequest(number, isUnlisted, isCallToPatient))
                    .then(resp => {
                        console.log(resp.data);
                        if (resp && resp.data && resp.data.call_sid) {
                            this.$set(this.callSids, number, resp.data.call_sid);
                        }

                        //continue adding participants until all are added
                        this.addQueuedParticipants();
                    })
                    .catch(err => {
                        self.log = err.message;
                        this.$set(this.muted, number, false);
                        this.$set(this.onPhone, number, false);

                        //continue adding participants until all are added
                        this.addQueuedParticipants();
                    });

            },
            resetPhoneState: function () {

                if (self.isCurrentlyOnPhone) {
                    sendRequest("call_ended", {number: {value: self.selectedPatientNumber, muted: false}})
                        .then(() => {

                        })
                        .catch(err => console.error(err));
                }
                self.onPhone = {};
                self.muted = {};
                self.waiting = false;

                self.waitingForConference = false;
                self.queuedNumbersForConference = [];
                self.addedNumbersInConference = [];
            },
            closeWindow: function (delayInSeconds, force) {

                //we are already closing the window
                if (self.closeCountdownInterval) {
                    return;
                }

                if (force) {
                    window.close();
                }

                if (!delayInSeconds) {
                    delayInSeconds = 0;
                }

                self.closeCountdown = delayInSeconds;
                self.closeCountdownInterval = setInterval(() => {

                    if (self.closeCountdown === 0) {
                        window.close();
                    }
                    else {
                        self.closeCountdown = self.closeCountdown - 1;
                    }

                }, 1000);
            },
            twilioOffline: function () {
                self.waiting = true;
            },
            twilioOnline: function () {
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
                            //exit call mode when all calls are disconnected
                            EventBus.$emit('tracker:call-mode:exit');
                            console.log('twilio device: disconnect');
                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Call ended.';

                            self.closeWindow(self.endCallWindowCloseDelay, false);

                            //make sure UI on the other page is up to date
                            sendRequest('call_ended', {number: {value: '', muted: false}})
                                .then(() => {

                                })
                                .catch(err => console.error(err));

                        });

                        self.device.on('offline', () => {
                            //this event can be raised on a temporary disconnection
                            //we should disable any actions when this event is fired
                            console.log('twilio device: offline');
                            self.twilioOffline();
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
                            self.twilioOnline();
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
                        if ((status === "ready" || status === "busy") && self.isCurrentlyOnPhone) {
                            number = self.selectedPatientNumber;
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

                    for (let i in self.onPhone) {
                        if (self.onPhone[i]) {
                            self.toggleCall(i);
                        }
                    }

                    //resolve the promise but also close the window
                    return new Promise((resolve, reject) => {
                        resolve({});
                        self.closeWindow(0, true);
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

    .window-close-banner {
        margin: 10px 0;
    }

    .countdown-seconds {
        font-weight: bold;
        color: red;
    }
</style>