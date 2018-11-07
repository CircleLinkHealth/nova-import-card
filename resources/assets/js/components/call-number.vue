<template>
    <div>
        <loader v-if="waiting"></loader>
        <div>{{log}}</div>
        <template v-if="!waiting">
            <div class="row">
                <div class="col-sm-10">
                    <select2 class="form-control" v-model="selectedNumber" :disabled="onAnyCall">
                        <option v-for="(number, index) in numbers" :key="index" :value="number">{{number}}</option>
                    </select2>
                </div>

                <div class="col-sm-2">
                    <button class="btn btn-circle" @click="toggleCall(selectedNumber)"
                            :class="[ onPhone[selectedNumber] ? 'btn-danger': 'btn-success' ]"
                            :disabled="!validPhone(selectedNumber) || onAnyCall || !selectedNumber">
                        <i class="fa fa-fw fa-phone" :class="[ onPhone[selectedNumber] ? 'fa-close': 'fa-phone' ]"></i>
                    </button>
                    <button class="btn btn-circle btn-default" v-if="onPhone[selectedNumber]"
                            @click="toggleMute(selectedNumber)">
                        <i class="fa fa-fw"
                           :class="[ muted[selectedNumber] ? 'fa-microphone-slash': 'fa-microphone' ]"></i>
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
                selectedNumber: null
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
                this.device.activeConnection().mute(value);
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function (number) {
                if (!this.onPhone[number]) {
                    this.muted[number] = false;
                    this.onPhone[number] = true;
                    // make outbound call with current number
                    this.connection = this.device.connect({To: number});
                    this.log = 'Calling ' + number;
                    EventBus.$emit('tracker:call-mode:enter');
                } else {
                    // hang up call in progress
                    this.device.disconnectAll();
                    EventBus.$emit('tracker:call-mode:exit');
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

                self.axios.get(url)
                    .then(response => {
                        this.log = 'Ready';
                        self.device = new Twilio.Device(response.data.token);

                        self.device.on('disconnect', () => {
                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Call ended.';
                        });

                        self.device.on('offline', () => {
                            self.resetPhoneState();
                            self.connection = null;
                            self.log = 'Call ended.';
                        });

                        self.device.on('error', (err) => {
                            self.resetPhoneState();
                            self.log = err.message;
                        });

                        self.device.on('ready', () => {
                            self.log = 'Ready to make call';
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
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