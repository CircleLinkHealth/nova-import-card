<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="container">

    <h2>CLHCaller</h2>


    <div id="makeCall">
        <div class="row">
            <div class="col-md-4 col-md-push-8">
                <div class="panel panel-primary client-controls">
                    <div class="panel-heading">
                        <h3 class="panel-title">Make a call</h3>
                    </div>
                    <div class="panel-body">
                        <p><strong>Status</strong></p>
                        <div class="well well-sm" id="call-status">
                           @{{ callStatus }}
                        </div>

                        <button class="btn btn-lg btn-success answer-button" disabled>Answer call</button>
                        <button class="btn btn-lg btn-danger hangup-button" disabled onclick="hangUp()">Hang up</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-md-pull-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Make Call
                            <small class="pull-right"></small>
                        </h3>
                    </div>

                    <div class="panel-body">

                        <div class="form-group">
                            <label for="num">Phone Number:</label>
                            <input v-model="toCall" id="num" type="text" class="form-control">
                        </div>

                        <div class="pull-right">
                            <button v-on:click="call" type="button"
                                    class="btn btn-primary btn-lg call-customer-button">
                                <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                                Call customer
                            </button>
                        </div>

                        <p><strong>Phone number:</strong> @{{ toCall }}</p>

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

<script>

    let app = new Vue({

        el: "#makeCall",

        data: {
            callStatus: '',
            toCall: ''

        },
        methods: {
            call(){
                callCustomer(this.toCall)
            }

        }

    });

</script>