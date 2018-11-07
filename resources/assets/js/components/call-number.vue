<template>
    <div>
        <button class="btn btn-circle" @click="toggleCall()"
                :class="[ onPhone ? 'btn-danger': 'btn-success' ]"
                :disabled="!onPhone && status !== 'ready'">
            {{number}} <i class="fa fa-fw fa-phone"
                          :class="[ onPhone ? 'fa-close': 'fa-phone' ]"></i>
        </button>

        <button class="btn btn-circle btn-default" v-if="onPhone" @click="toggleMute">
            <i class="fa fa-fw" :class="[ muted ? 'fa-microphone-slash': 'fa-microphone' ]"></i>
        </button>
    </div>
</template>
<script>
    import {rootUrl} from "../app.config";

    export default {
        name: 'call-number',
        props: [
            'number',
        ],
        data() {
            return {
                muted: false,
                onPhone: false,
                log: 'Initializing',
                connection: null,
                //twilio device
                device: null,
            }
        },
        computed: {
            validPhone: function () {
                // return /^([0-9]|#|\*)+$/.test(this.number.replace(/[-()\s]/g,''));
                return true;
            },
            status: function () {
                if (!this.device) {
                    return null;
                }
                return Twilio.Device.status();
            }
        },
        methods: {
            // Handle muting
            toggleMute: function () {
                this.muted = !this.muted;
                Twilio.Device.activeConnection().mute(this.muted);
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function () {
                if (!this.onPhone) {
                    this.muted = false;
                    this.onPhone = true;
                    // make outbound call with current number
                    var n = this.number;
                    this.connection = Twilio.Device.connect({To: n});
                    this.log = 'Calling ' + n;
                } else {
                    // hang up call in progress
                    Twilio.Device.disconnectAll();
                }
            },
        },
        mounted() {
            let self = this;

            const url = rootUrl(`/twilio/token`);

            self.axios.get(url)
                .then(response => {
                        this.log = 'Connecting';
                        this.device = Twilio.Device.setup(response.data.token);
                    }
                )
                .catch(error => {
                    console.log(error);
                    self.log = 'Could not fetch token, see console.log';
                });

            Twilio.Device.disconnect(function () {
                self.onPhone = false;
                self.connection = null;
                self.log = 'Call ended.';
            });

            Twilio.Device.ready(function () {
                self.log = 'Ready to make call';
            });
        }
    }
</script>
<style>

</style>