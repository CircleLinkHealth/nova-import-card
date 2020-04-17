<template>
    <div id="enrollment_calls">
        <div>
            <button style="position: fixed" @click="retrievePatient">Get new enrollee</button>
        </div>
        <div>
            <time-tracker v-show="false"
                          ref="timeTracker"
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
        <div v-if="!loading">
            <div v-if="patientExists">
                <patient-to-enroll :patient-data="patientData" :time-tracker="$refs.timeTracker"></patient-to-enroll>
            </div>
            <div v-else>
                <div v-show="onCall">
                    <a v-on:click="hangUp"
                       class="waves-effect waves-light btn"
                       style="background: red;  margin-top: 1%; margin-left: 50%; position: fixed"><i
                            class="material-icons left">call_end</i>Hang Up</a>
                </div>
                <div class="row">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>

    import Twilio from 'twilio-client';

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
            'timeTrackerInfo'
        ],
        components: {
            'loader': Loader,
            'patient-to-enroll': PatientToEnroll,
            'time-tracker': TimeTracker
        },
        computed: {
            patientExists: function () {
                return this.patientData.enrollable_id;
            }
        },
        data: function () {
            return {
                patientData: [],
                loading: false,
                phone: null,
                callError: null,
                onCall: false,
                callStatus: 'Summoning Calling Gods...',
                practice_phone: null,
                enrollable_user_id: null,
                enrollable_name: null,
                device: null,
                log: null,
                phone_type: null,
                loading_modal: null
            };
        },
        mounted: function () {
            // M.AutoInit();

            this.loading = true;
            M.Modal.init($('#loading'), {
                preventScrolling: true,
                dismissible: false
            });
            this.loading_modal = M.Modal.getInstance(document.getElementById('loading'));
            this.loading_modal.open();

            this.retrievePatient();

            let self = this;
            self.initTwilio();

            App.$on('enrollable:action-complete', () => {
                this.patientData = null;
                this.loading = true;
                this.loading_modal.open();
                this.retrievePatient();
                this.updateCallStatus()
            })

            App.$on('enrollable:call', (data) => {
                for (let [key, value] of Object.entries(data)) {
                    this.$data[key] = value;
                }
                this.call(data.phone, data.type)
            })

            App.$on('enrollable:hang-up', () => {
                this.hangUp()
                this.updateCallStatus()
            })
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
            retrievePatient() {
                this.loading = true;
                return this.axios
                    .get(rootUrl('/enrollment/show'))
                    .then(response => {
                        this.loading = false
                        this.loading_modal.close()

                        let patientData = response.data.data;

                        patientData.onCall = this.onCall
                        patientData.callStatus = this.callStatus
                        patientData.log = this.log
                        patientData.callError = this.callError
                        this.patientData = response.data.data;
                        this.notifyTimeTracker();
                    })
                    .catch(err => {
                        //to implement
                        this.loading = false;
                        console.error(err);
                    });
            },

            getTimeDiffInSecondsFromMS(millis) {
                return Math.round(Date.now() - millis) / 1000;
            },

            updateCallStatus() {
                App.$emit('enrollable:update-call-status', {
                    'onCall': self.onCall,
                    'callStatus': self.callStatus,
                    'log': self.log,
                    'callError': self.callError
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

            call(phone, type) {
                this.device.connect({
                    To: this.phone,
                    // From: this.practice_phone ? this.practice_phone : undefined,
                    From: '+18634171503',
                    IsUnlistedNumber: false,
                    InboundUserId: this.enrollable_user_id,
                    OutboundUserId: userId
                });
            },
            hangUp() {
                this.onCall = false;
                this.callStatus = "Ended Call";
                M.toast({html: this.callStatus, displayLength: 3000});
                this.device.disconnectAll();
            },
            initTwilio: function () {
                const self = this;
                const url = rootUrl(`/twilio/token`);

                self.$http.get(url)
                    .then(response => {
                        self.log = 'Initializing';
                        self.device = new Twilio.Device(response.data.token, {
                            closeProtection: true
                        });

                        self.device.on('disconnect', () => {
                            console.log('twilio device: disconnect');
                            self.log = 'Call ended.';
                            self.onCall = false;
                            this.updateCallStatus()
                        });

                        self.device.on('offline', () => {
                            console.log('twilio device: offline');
                            self.log = 'Offline.';
                            this.updateCallStatus()
                        });

                        self.device.on('error', (err) => {
                            console.error('twilio device: error', err);
                            self.callError = err.message;
                            this.updateCallStatus()
                        });

                        self.device.on('ready', () => {
                            console.log('twilio device: ready');
                            self.log = 'Ready to make call';
                            M.toast({html: self.log, displayLength: 5000});
                            this.updateCallStatus()
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
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

    .cookie {
        margin-top: 10%;
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
</style>


