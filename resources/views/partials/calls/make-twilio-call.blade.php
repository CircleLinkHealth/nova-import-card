<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

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
                            <button v-on:click="call" type="button" v-bind:disabled="disableCall"
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

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="//static.twilio.com/libs/twiliojs/1.3/twilio.min.js"></script>
<script src="{{ asset('js/browser-calls.js', true) }}"></script>
<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>

<script>

    let app = new Vue({

        el: "#makeCall",

        data: {
            callStatus: 'Summoning Calling Gods...',
            toCall: '',
            enableHangUp: true,
            disableCall: true,
            warning: 'Enter Valid Phone Format (+1XXXXXXXXXX) to make call...'
        },

        mounted: function () {

            this.$http.post("/twilio/token", {forPage: window.location.pathname}, function (data) {

                // Set up the Twilio Client Device with the token

            }).then(response => {

                    this.callStatus = 'Ready';
                    Twilio.Device.setup(response.body.token);

                }
            );

        },

        methods: {

            checkPhoneFormatValidity(){

                let regex = /^\+?[1]\d{10}$/;

                if (this.toCall.match(regex) != null) {
                    this.disableCall = false;
                    this.warning = ''
                } else {
                    this.disableCall = true;
                    this.warning = 'Enter Valid Phone Format (+1XXXXXXXXXX) to make call...'

                }

            },

            call(){

                this.callStatus = "Calling " + this.toCall + "...";
                Twilio.Device.connect({"phoneNumber": this.toCall});
                this.enableHangUp = false;

            },

            hangUp(){
                Twilio.Device.disconnectAll();
            }

        }

    });

</script>