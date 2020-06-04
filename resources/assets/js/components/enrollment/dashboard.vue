<template>
    <div id="enrollment_calls">
        <div>
            <time-tracker ref="timeTracker"
                          :hide-tracker="true"
                          :twilio-enabled="true"
                          :info="getTimeTrackerInfo()"
                          :no-live-count="false"
                          :override-timeout="false">
            </time-tracker>
        </div>
        <div id="loading" class="modal confirm" style="width: 40% !important">
            <div class="modal-content">
                <div class="loading-patient">
                    <span>Loading patient... </span>
                    <loader/>
                </div>
                <div v-show="onCall" id="on_call" class="on-call-info">
                    <ul>
                        <li style="margin-bottom: 5%">
                            <span>Calling {{enrollable_name}} on {{phone}}</span>
                        </li>
                        <li style="margin-bottom: 25px">
                            <hr style="border-top: 1px grey">
                        </li>
                        <li><a v-on:click="hangUp"
                               class="waves-effect waves-light btn"
                               style="background: red"><i
                                class="material-icons left">call_end</i>Hang Up</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="error" class="modal confirm" style="width: 40% !important">
            <div class="modal-content">
                <div><i
                        class="material-icons left modal-error-icon">error</i></div>
                <div class="error-header">
                    <span>Oops! Something went wrong... </span>
                </div>
                <div class="error-message">
                    <span>{{this.error}}</span>
                </div>
                <div v-if="error_retrieve_next" class="container center-align">
                    <a v-on:click="closeAndRetrievePatient()"
                       class="waves-effect waves-light btn"
                       style="background: green"><i
                            class="material-icons left">call</i>Get Next Patient</a>
                </div>
            </div>
        </div>
        <div v-if="!loading">
            <div v-if="patientExists">
                <patient-to-enroll :patient-data="patientData" :time-tracker="$refs.timeTracker"
                                   :debug="debug"></patient-to-enroll>
            </div>
            <div v-else>
                <div v-show="onCall">
                    <a v-on:click="hangUp"
                       class="waves-effect waves-light btn"
                       style="background: red;  margin-top: 1%; margin-left: 40%; position: fixed"><i
                            class="material-icons left">call_end</i>Hang Up</a>
                </div>
                <div v-if="shouldShowCookie" class="row">
                    <div class="col cookie">
                        <div class="card horizontal">
                            <div class="card-image">
                                <img :src="'/img/cookie.png'">
                            </div>
                            <div class="card-stacked">
                                <div class="card-content">
                                    <h2 class="header" style="color: #47beab">Oops!</h2>
                                    <p>You’re out of patients to call, please contact your administrator to request more
                                        calls.</p>
                                    <br>
                                    <p>In the meantime, enjoy this cookie.</p>
                                    <br>
                                    <p v-if="pendingPatientsExist">
                                        You have {{patients_pending}} pending patient(s). Next call attempt will be at
                                        {{next_attempt_at}}.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="this.error" class="row">
                    <i
                            class="material-icons left error-image">error_outline</i>
                </div>
            </div>
        </div>

    </div>
</template>

<script>

    import {Device} from 'twilio-client';

    import PatientToEnroll from './patient-to-enroll';

    import {rootUrl} from '../../app.config';

    import TimeTracker from '../../admin/time-tracker';
    import TimeTrackerEventBus from '../../admin/time-tracker/comps/event-bus';

    import Loader from '../loader.vue';

    const userId = window.userId;
    const userFullName = window.userFullName;

    export default {
        name: 'enrollment-dashboard',
        props: [
            'cpmToken',
            'cpmCallerUrl',
            'timeTracker',
            'debug'
        ],
        components: {
            'loader': Loader,
            'patient-to-enroll': PatientToEnroll,
            'time-tracker': TimeTracker
        },
        computed: {
            patientExists: function () {
                return this.patientData && this.patientData.enrollable_id;
            },
            shouldShowCookie: function () {
                return !this.patientExists && !this.error
            },
            pendingPatientsExist: function () {
                return this.patients_pending > 0;
            }
        },
        data: function () {
            return {
                patientData: [],
                loading: false,
                error: null,
                phone: null,
                callError: null,
                onCall: false,
                callStatus: 'Summoning Calling Gods...',
                practice_phone: null,
                enrollable_id: null,
                enrollable_user_id: null,
                enrollable_name: null,
                device: null,
                log: null,
                phone_type: null,
                loading_modal: null,
                error_modal: null,
                patients_pending: 0,
                next_attempt_at: null,
                error_retrieve_next: false,
                error_enrollee_id: null,
            };
        },
        mounted: function () {

            this.loading = true;
            M.Modal.init($('#loading'), {
                preventScrolling: true,
                dismissible: false
            });

            M.Modal.init($('#error'), {
                preventScrolling: true,
                dismissible: true
            });
            this.loading_modal = M.Modal.getInstance(document.getElementById('loading'));
            this.error_modal = M.Modal.getInstance(document.getElementById('error'));

            this.loading_modal.open();

            this.retrievePatient();

            this.initTwilio();

            App.$on('enrollable:action-complete', () => {
                this.patientData = null;
                this.loading = true;
                this.loading_modal.open();
                this.retrievePatient();
                this.updateCallStatus();
            })

            App.$on('enrollable:call', (data) => {
                this.phone = data.phone;
                this.type = data.type;
                this.practice_phone = data.practice_phone;
                this.enrollable_user_id = data.enrollable_user_id;
                this.enrollable_name = data.enrollable_name;
                this.callError = data.callError;
                this.onCall = data.onCall;
                this.callStatus = data.callStatus;

                this.call();
            });

            App.$on('enrollable:hang-up', () => {
                this.hangUp();
                this.updateCallStatus();
            });

            App.$on('enrollable:numpad-input', (input) => {
                if (this.device) {
                    const {allInput, lastInput} = input;
                    console.debug('Sending digits to twilio', lastInput.toString());
                    const connection = this.device.activeConnection();
                    if (connection) {
                        connection.sendDigits(lastInput.toString());
                    }
                }
            });

            App.$on('enrollable:load-from-search-bar', () => {
                this.retrievePatient();
                this.loading_modal.open();
            });

            App.$on('enrollable:error', (enrollableId) => {
                this.patientData = null;
                this.error_retrieve_next = true;
                this.error_enrollee_id = enrollableId;

                this.error = 'Something went wrong while saving patient details. We are investigating the issue. Please click on button to get next Patient.';
                this.error_modal.open()
            });
        },
        methods: {
            getTimeTrackerInfo() {
                return window['timeTrackerInfo'];
            },
            setTimeTrackerInfo(info) {
                //in case connection is lost and time tracker re-connects,
                // we need to have the timeTrackerInfo object up to date
                window['timeTrackerInfo'] = info;
            },
            notifyTimeTracker() {
                const info = this.getTimeTrackerInfo();
                info.enrolleeId = this.enrollable_id;
                this.setTimeTrackerInfo(info);
                TimeTrackerEventBus.$emit('tracker:activity', info);
            },
            closeAndRetrievePatient() {
                this.error_modal.close()
                this.retrievePatient()
                this.loading_modal.open();
                this.updateCallStatus()
                this.error_retrieve_next = false;
                this.error_enrollee_id = null;
            },
            retrievePatient() {
                this.loading = true;
                let url = rootUrl('/enrollment/show');

                let href = window.location.href
                let tags = href.split('#')
                if (tags[1] && tags[1] !== '!') {
                    url = url + '/' + tags[1]
                }
                let errorData = null;
                if (this.error_retrieve_next) {
                    errorData = {
                        params: {
                            error_enrollable_id: this.error_enrollee_id
                        }
                    }
                }

                return this.axios
                    .get(url, errorData)
                    .then(response => new Promise(resolve => setTimeout(() => {
                        this.loading = false
                        this.loading_modal.close()

                        let patientData = response.data.data;

                        if (response.data.patients_pending !== undefined) {
                            this.patients_pending = response.data.patients_pending
                            this.next_attempt_at = response.data.next_attempt_at
                            return;
                        }
                        patientData.onCall = this.onCall
                        patientData.callStatus = this.callStatus
                        patientData.log = this.log
                        patientData.callError = this.callError
                        this.patientData = patientData;

                        this.enrollable_id = patientData.enrollable_id;

                        this.notifyTimeTracker();

                        App.$emit('enrollable:loaded', {
                            has_tips: this.patientData.has_tips
                        })
                    }, 2000)))
                    .catch(err => {
                        //to implement
                        this.loading = false;
                        this.loading_modal.close()
                        if (err.response.status == 404) {
                            this.error = err.response.data.message;
                        } else {
                            this.error = 'Something went wrong while retrieving patient. Please contact CLH support.';
                        }

                        this.error_modal.open()
                        console.error(err);
                    });
            },

            getTimeDiffInSecondsFromMS(millis) {
                return Math.round(Date.now() - millis) / 1000;
            },

            updateCallStatus() {
                App.$emit('enrollable:update-call-status', {
                    'onCall': this.onCall,
                    'callStatus': this.callStatus,
                    'log': this.log,
                    'callError': this.callError
                })
            },

            /**
             * used by the tips modal
             * @param e
             */
            doNotShowTipsAgain(e) {
                let settings = this.getTipsSettings();
                if (!settings) {
                    settings = {};
                }
                settings[this.practice_id] = {show: !e.currentTarget.checked};
                this.setTipsSettings(settings);
            },

            call() {
                TimeTrackerEventBus.$emit('tracker:call-mode:enter');
                this.device.connect({
                    To: this.phone,
                    From: this.practice_phone ? this.practice_phone : undefined,
                    IsUnlistedNumber: false,
                    InboundUserId: this.enrollable_user_id,
                    OutboundUserId: userId
                });
            },
            hangUp() {
                TimeTrackerEventBus.$emit('tracker:call-mode:exit');
                this.onCall = false;
                this.callStatus = "Ended Call";
                M.toast({html: this.callStatus, displayLength: 3000});
                //if anything goes wrong with twilio, prevent page from falsely showing  message: 'Calling...'
                if (this.device) {
                    this.device.disconnectAll();
                }
            },
            getUrl: function (path) {
                if (this.cpmCallerUrl && this.cpmCallerUrl.length > 0) {
                    if (this.cpmCallerUrl[this.cpmCallerUrl.length - 1] === "/") {
                        return this.cpmCallerUrl + path;
                    } else {
                        return this.cpmCallerUrl + "/" + path;
                    }
                }
                return rootUrl(path);
            },
            initTwilio: function () {
                const url = this.getUrl(`twilio/token?cpm-token=${this.cpmToken}`);

                this.$http.get(url)
                    .then(response => {
                        this.log = 'Initializing';
                        this.device = new Device(response.data.token, {
                            closeProtection: true
                        });

                        this.device.on('disconnect', () => {
                            console.log('twilio device: disconnect');
                            this.log = 'Call ended.';
                            this.onCall = false;
                            TimeTrackerEventBus.$emit('tracker:call-mode:exit');
                            this.updateCallStatus()
                        });

                        this.device.on('offline', () => {
                            console.log('twilio device: offline');
                            this.log = 'Offline.';
                            this.updateCallStatus()
                        });

                        this.device.on('error', (err) => {
                            console.error('twilio device: error', err);
                            this.callError = err.message;
                            this.updateCallStatus()
                        });

                        this.device.on('ready', () => {
                            console.log('twilio device: ready');
                            this.log = 'Ready to make call';
                            M.toast({html: this.log, displayLength: 5000});
                            this.updateCallStatus()
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        this.log = 'Could not fetch token, see console.log';
                        this.updateCallStatus()
                    });
            }
        }
    }

</script>
<style>
    .loading-patient {
        margin-top: 10%;
        margin-left: 37%;
        margin-bottom: 15%;
    }

    .loading-patient .loader {
        margin-left: 14%;
        margin-top: 10%;
    }

    .loading-patient span {
        color: darkgray;
        font-size: large;
    }

    .error-header {
        margin-top: 25%;
        margin-bottom: 10%;
        text-align: center;
    }

    .error-header span {
        color: darkgray;
        font-size: large;
    }

    .error-message {
        text-align: center;
        font-size: medium;
        padding-bottom: 10%;
    }

    .cookie {
        margin-top: 15%;
        margin-left: 15%;
    }

    .collapsible-top {
        width: 250px;
        margin-left: 45%;
        margin-top: 0.3%;
        position: fixed;
        border-radius: 3px;
    }

    .collapsible-body-top {
        background-color: #fff;
    }

    .collapsible-header-top {
        text-align: center;
        background-color: #fff;
        padding-left: 20%;
    }

    .collapsible-body-top li {
        margin-bottom: 5px;
    }

    .on-call-info {
        margin-top: 20%;
    }

    .on-call-info ul {
        list-style: none;
    }

    .on-call-info ul li {
        text-align: center;
    }

    .error-image {
        color: red;
        margin-left: 44%;
        margin-top: 15%;
        font-size: 190px;
    }

    .modal-error-icon {
        margin-top: 10%;
        margin-left: 45%;
        color: red;
        font-size: 50px;
    }
</style>


