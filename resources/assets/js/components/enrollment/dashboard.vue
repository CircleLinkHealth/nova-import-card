<template>
    <div id="enrollment_calls">
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
                <patient-to-enroll :patient-data="patientData"></patient-to-enroll>
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

    import Loader from '../loader.vue';

    const userId = window.userId;
    const userFullName = window.userFullName;


    export default {
        name: 'enrollment-dashboard',
        props: [],
        components: {
            'loader': Loader,
            'patient-to-enroll': PatientToEnroll
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

            App.$on('enrollable-action-complete', () => {
                this.patientData = null;
                this.loading = true;
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
            retrievePatient() {
                return this.axios
                    .get(rootUrl('/enrollment/show'))
                    .then(response => {
                        // this.loading = false
                        // this.loading_modal.close()
                        // this.patientData = response.data.data;
                    })
                    .catch(err => {
                        //to implement
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


