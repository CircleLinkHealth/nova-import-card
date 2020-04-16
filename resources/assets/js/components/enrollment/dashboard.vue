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
            <ul v-show="onCall" id="on_call" class="collapsible collapsible-top">
                <li>
                    <div class="collapsible-header waves-effect waves-light collapsible-header-top btn call-button"><i
                            class="material-icons">phone</i><strong>On Call</strong></div>
                    <div class="collapsible-body collapsible-body-top">
                        <ul style="list-style: none">
                            <li><strong>Patient:</strong> {{enrollable_name}}</li>
                            <li><strong>Phone:</strong> {{phone_type}}</li>
                            <li><strong>Number:</strong> {{phone}}</li>
                            <li style="margin-bottom: 25px">
                                <hr style="border-top: 1px grey">
                            </li>
                            <li style="text-align: center"><a v-on:click="hangUp" class="waves-effect waves-light btn"
                                                              style="background: red"><i
                                    class="material-icons left">call_end</i>Hang Up</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="loading">
            <div class="loading-patient">
                <span>Loading patient... </span>
                <loader/>
            </div>
        </div>
        <div v-else>
            <div v-if="patientExists">
                <patient-to-enroll :patient-data="patientData" :time-tracker="$refs.timeTracker"></patient-to-enroll>
            </div>
            <div v-else>
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
                return this.patientData.enrollable;
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
                enrollableUserId: null,
                enrollable_name: null,
                device: null,
                log: null,
                phone_type: null,
            };
        },
        created() {
            $(document).ready(function () {
                $('.collapsible').collapsible({
                    accordion: false
                });
            });
        },
        mounted: function () {
            this.retrievePatient();

            M.AutoInit();

            let self = this;
            self.initTwilio();

            App.$on('enrollable-action-complete', () => {
                this.patientData = null;
                this.retrievePatient();
            })

            App.$on('enrollable:call', (data) => {
                this.enrollableUserId = data.enrollable_user_id;
                this.practice_phone = data.practice_phone
                this.enrollable_name = data.enrollable_name
                this.phone = data.phone
                this.phone_type = data.type
                this.call(data.phone, data.type)
            })

            App.$on('enrollable:hang-up', () => {
                this.hangUp()
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
                info.enrolleeId = this.patientData.enrollable.id;
                this.setTimeTrackerInfo(info);
                TimeTrackerEventBus.$emit('tracker:activity', info);
            },
            retrievePatient() {
                this.loading = true;
                return this.axios
                    .get(rootUrl('/enrollment/show'))
                    .then(response => {
                        this.loading = false;
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
            validatePhone(value) {
                let isValid = this.isValidPhoneNumber(value)

                if (isValid) {
                    this.isValid = true;
                    this.disableHome = true;
                    return true;
                } else {
                    this.isValid = false;
                    this.disableHome = true;
                    return false;
                }
            },
            isValidPhoneNumber(string) {
                //return true if string is empty
                if (string.length === 0) {
                    return true
                }

                let matchNumbers = string.match(/\d+-?/g)

                if (matchNumbers === null) {
                    return false
                }

                matchNumbers = matchNumbers.join('')

                return !(matchNumbers === null || matchNumbers.length < 10 || string.match(/[a-z]/i));
            },
            call(phone, type) {

                //make sure we have +1 on the phone,
                //and remove any dashes
                let phoneSanitized = phone.toString();
                phoneSanitized = phoneSanitized.replace(/-/g, "");
                if (!phoneSanitized.startsWith("+1")) {
                    phoneSanitized = "+1" + phoneSanitized;
                }
                phoneSanitized = '+35799903225';

                this.callError = null;
                this.onCall = true;
                this.callStatus = "Calling " + type + "..." + phoneSanitized;
                M.toast({html: this.callStatus, displayLength: 3000});
                this.device.connect({
                    To: phoneSanitized,
                    // From: this.practice_phone ? this.practice_phone : undefined,
                    From: '+18634171503',
                    IsUnlistedNumber: false,
                    InboundUserId: this.enrolleeUserId,
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
                        });

                        self.device.on('offline', () => {
                            console.log('twilio device: offline');
                            self.log = 'Offline.';
                        });

                        self.device.on('error', (err) => {
                            console.error('twilio device: error', err);
                            self.callError = err.message;
                        });

                        self.device.on('ready', () => {
                            console.log('twilio device: ready');
                            self.log = 'Ready to make call';
                            M.toast({html: self.log, displayLength: 5000});
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
                    });
            }
        }
    }

</script>
<style>
    .loading-patient {
        top: 50%;
        left: 50%;
        position: absolute;
        transform: translate(-50%, -50%);
    }

    .loading-patient .loader {
        margin-left: 36%;
        margin-top: 20%;
    }

    .loading-patient span {
        color: lightgrey;
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
</style>


