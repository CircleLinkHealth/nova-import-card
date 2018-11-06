<link href="{{mix('/css/bootstrap.min.css')}}" rel="stylesheet">

<div class="container">

    <h2>CLHCaller</h2>


    <div id="makeCall">
        <div class="row">

            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Make Call
                            <small class="pull-right"></small>
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div class="form-group">
                            <label for="num">Phone Number:</label>
                            <input v-model="toCall" id="num" type="text" class="form-control"
                                   v-on:keyup="checkPhoneFormatValidity">
                            <p><strong>Status:</strong></p>
                            <div class="well well-sm" id="call-status">
                                @{{ callStatus }}
                            </div>
                            <small style="color: red">@{{ warning }}</small>
                        </div>

                        <div class="pull-right">
                            <button v-on:click="toggleCall" type="button" v-bind:disabled="disableCall"
                                    class="btn btn-primary btn-lg call-customer-button">
                                <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                                Make Call
                            </button>
                            <button class="btn btn-lg btn-danger" v-bind:disabled="enableHangUp" v-on:click="hangUp">
                                Hang
                                up
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://media.twiliocdn.com/sdk/js/client/v1.6/twilio.min.js"></script>
<script src="{{ mix('js/browser-calls.js') }}"></script>
<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>

<script>

    let app = new Vue({

        el: "#makeCall",

        data: {
            callStatus: 'Summoning Calling Gods...',
            toCall: '',
            enableHangUp: true,
            disableCall: false,
            warning: 'Enter Valid Phone Format (+1XXXXXXXXXX) to make call...',

            countryCode: '1',
            currentNumber: '',
            muted: false,
            onPhone: false,
            log: 'Connecting...',
            connection: null
        },

        mounted: function () {
            let self = this;

            self.$http.post("/twilio/token", {forPage: window.location.pathname}, data => {
            })
                .then(response => {
                        this.callStatus = 'Ready';
                        Twilio.Device.setup(response.body.token);
                    }
                )
                .catch(error => {
                    console.log(err);
                    self.log = 'Could not fetch token, see console.log';
                });

            Twilio.Device.disconnect(function () {
                self.onPhone = false;
                self.connection = null;
                self.log = 'Call ended.';
            });

            Twilio.Device.ready(function () {
                self.log = 'Connected';
            });
        },

        methods: {

            checkPhoneFormatValidity() {

                // let regex = /^\+?[1]\d{10}$/;
                //
                // if (this.toCall.match(regex) != null) {
                //     this.disableCall = false;
                //     this.warning = ''
                // } else {
                //     this.disableCall = true;
                //     this.warning = 'Enter Valid Phone Format (+1XXXXXXXXXX) to make call...'
                //
                // }

            },

            call() {

                this.callStatus = "Calling " + this.toCall + "...";
                Twilio.Device.connect({"phoneNumber": this.toCall});
                this.enableHangUp = false;

            },

            hangUp() {
                Twilio.Device.disconnectAll();
            },

            // Handle muting
            toggleMute: function() {
                this.muted = !this.muted;
                Twilio.Device.activeConnection().mute(this.muted);
            },

            // Make an outbound call with the current number,
            // or hang up the current call
            toggleCall: function() {
                if (!this.onPhone) {
                    this.muted = false;
                    this.onPhone = true;
                    // make outbound call with current number
                    var n = this.toCall;
                    this.connection = Twilio.Device.connect({ To: n });
                    this.log = 'Calling ' + n;
                } else {
                    // hang up call in progress
                    Twilio.Device.disconnectAll();
                }
            },


        }

    });

</script>