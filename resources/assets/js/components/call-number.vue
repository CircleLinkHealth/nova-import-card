<template>
    <div>
        <loader v-if="waiting"></loader>
        <template v-if="!waiting" v-for="number in numbers">
            <button class="btn btn-circle" @click="toggleCall(number)"
                    :class="[ onPhone[number] ? 'btn-danger': 'btn-success' ]"
                    :disabled="!validPhone(number) || onAnyCall">
                {{number}} <i class="fa fa-fw fa-phone"
                              :class="[ onPhone[number] ? 'fa-close': 'fa-phone' ]"></i>
            </button>
            <button class="btn btn-circle btn-default" v-if="onPhone[number]" @click="toggleMute(number)">
                <i class="fa fa-fw" :class="[ muted[number] ? 'fa-microphone-slash': 'fa-microphone' ]"></i>
            </button>
        </template>
        <div>{{log}}</div>
    </div>
</template>
<script>
    import {rootUrl} from "../app.config";
    import LoaderComponent from '../components/loader';

    let Twilio;

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
                muted: [],
                onPhone: [],
                log: 'Initializing',
                connection: null,
                //twilio device
                device: null,
            }
        },
        computed: {
            onAnyCall: function () {
                return this.onPhone.some(x => x);
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
                Twilio.Device.activeConnection().mute(value);
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number) {
                if (!this.onPhone[number]) {
                    this.muted[number] = false;
                    this.onPhone[number] = true;
                    // make outbound call with current number
                    this.connection = Twilio.Device.connect({To: number});
                    this.log = 'Calling ' + n;
                } else {
                    // hang up call in progress
                    Twilio.Device.disconnectAll();
                }
            },
            resetPhoneState: function () {
                let self = this;
                self.numbers.forEach(x => {
                    self.onPhone[x] = false;
                    self.muted[x] = false;
                });
            },
            setTwilio: function (onDone) {
                let self = this;
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
                let self = this;

                const url = rootUrl(`/twilio/token`);
                let device = null;

                self.axios.get(url)
                    .then(response => {
                        this.log = 'Ready';
                        device = Twilio.Device.setup(response.data.token);
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
                    });

                Twilio.Device.disconnect(function () {
                    self.resetPhoneState();
                    self.connection = null;
                    self.log = 'Call ended.';
                });

                Twilio.Device.error(function (err) {
                    self.resetPhoneState();
                    self.log = err.message;
                });

                Twilio.Device.ready(function () {
                    self.log = 'Ready to make call';
                });
            }
        },
        created() {
            this.resetPhoneState();
        },
        mounted() {
            let self = this;
            self.setTwilio(() => {
                self.initTwilio();
            });
        }
    }
</script>
<style>

</style>