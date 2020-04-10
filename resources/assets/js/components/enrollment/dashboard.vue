<template>
    <div id="enrollment_calls">
        <div v-if="this.loading">
            <div class="loading-patient">
                <span>Loading patient... </span>
                <loader/>
            </div>
        </div>
        <div v-if="patientData">
            <patient-to-enroll></patient-to-enroll>
        </div>
    </div>
</template>

<script>

    import Twilio from 'twilio-client';
    import PatientToEnroll from './patient-to-enroll';

    import Loader from '../loader.vue';

    export default {
        name: 'enrollment-dashboard',
        props: [],
        components: {
            'loader': Loader,
            'patient-to-enroll': PatientToEnroll
        },
        computed: {
            loading: function () {
                return !this.patientData;
            }
        },
        data: function () {
            return {
                patientData: null
            };
        },
        mounted: function () {


        },
        methods: {
            handleSubmit(event) {
                if (this.suggested_family_members.length > 0 && this.confirmed_family_members.length == 0) {
                    event.preventDefault();
                    this.pending_form = event.target;
                    let modal = M.Modal.getInstance(document.getElementById('suggested-family-members-modal'));
                    modal.open();
                }
            },
            submitPendingForm() {
                this.pending_form.submit();
            },
            getSuggestedFamilyMembers() {
                return this.axios
                    .get(rootUrl('/enrollment/get-suggested-family-members/' + enrollee.id))
                    .then(response => {
                        this.family_loading = false;
                        this.suggested_family_members = response.data.suggested_family_members;
                        this.confirmed_family_members = response.data.suggested_family_members.map(function (member) {
                            return member.is_confirmed ? member.id : null;
                        }).filter(x => !!x);
                    })
                    .catch(err => {
                        this.family_loading = false;
                        this.bannerText = err.response.data.message;
                        this.bannerType = 'danger';
                        this.showBanner = true;
                    });
            },

            getTimeDiffInSecondsFromMS(millis) {
                return Math.round(Date.now() - millis) / 1000;
            },

            //triggered when cilck on Soft Decline
            //gets reset when modal closes
            softReject() {
                this.isSoftDecline = true;
            },
            getTipsSettings() {
                const tipsSettingsStr = localStorage.getItem('enrollment-tips-per-practice');
                if (tipsSettingsStr) {
                    return JSON.parse(tipsSettingsStr);
                }
                return null;
            },
            setTipsSettings(settings) {
                localStorage.setItem('enrollment-tips-per-practice', JSON.stringify(settings));
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

                this.callError = null;
                this.onCall = true;
                this.callStatus = "Calling " + type + "..." + phoneSanitized;
                M.toast({html: this.callStatus, displayLength: 3000});
                this.device.connect({
                    To: phoneSanitized,
                    From: this.practice_phone ? this.practice_phone : undefined,
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
</style>


