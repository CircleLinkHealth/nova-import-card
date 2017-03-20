<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="container">

    <h2>Support Tickets</h2>

    <p class="lead">
        This is the list of most recent support tickets. Click the "Call customer" button to start a phone call from
        your browser.
    </p>

    <div class="row">
        <div class="col-md-4 col-md-push-8">
            <div class="panel panel-primary client-controls">
                <div class="panel-heading">
                    <h3 class="panel-title">Make a call</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Status</strong></p>
                    <div class="well well-sm" id="call-status">
                        Connecting to Twilio...
                    </div>

                    <button class="btn btn-lg btn-success answer-button" disabled>Answer call</button>
                    <button class="btn btn-lg btn-danger hangup-button" disabled onclick="hangUp()">Hang up</button>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-md-pull-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Ticket
                        <small class="pull-right"></small>
                    </h3>
                </div>

                <div class="panel-body">

                    <div class="pull-right">
                        <button onclick="callCustomer('+19727622642')" type="button"
                                class="btn btn-primary btn-lg call-customer-button">
                            <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                            Call customer
                        </button>
                    </div>

                    <p><strong>Name:</strong></p>
                    <p><strong>Phone number:</strong> 9727622642</p>
                    <p><strong>Description:</strong></p>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="//static.twilio.com/libs/twiliojs/1.2/twilio.min.js"></script>
<script src="{{ asset('js/browser-calls.js', true) }}"></script>
