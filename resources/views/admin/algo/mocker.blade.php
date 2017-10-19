@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('algo.mock.compute', array()),'id' => 'compute', 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">

                <div class="panel panel-default">
                    <div class="panel-heading">Algo {{\App\Algorithms\Calls\SuccessfulHandler::VERSION}}</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>
                                <!-- Appended Input-->
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="seconds">CCM Time</label>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input id="seconds" name="seconds" class="form-control"
                                                   placeholder="1200"
                                                   type="number" required="">
                                            <span class="input-group-addon">seconds</span>
                                        </div>
                                        <span id="convert"></span> mins

                                    </div>
                                </div>
                                <!-- Multiple Radios (inline) -->
                                {{--<div class="form-group">--}}
                                    {{--<label class="col-md-4 control-label" for="call_success">Called Successfully This--}}
                                        {{--Month?</label>--}}
                                    {{--<div class="col-md-4">--}}
                                        {{--<select id="call_success" name="call_success" id="call_success" class="form-control">--}}
                                            {{--<option value="1" selected>Yes</option>--}}
                                            {{--<option value="2">No</option>--}}
                                        {{--</select>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="days">Call Date</label>
                                    <div class="col-md-4">
                                        <select id="days" name="days[]" class=" dropdown Valid form-control" multiple>
                                            <option value="1">Monday</option>
                                            <option value="2">Tuesday</option>
                                            <option value="3">Wednesday</option>
                                            <option value="4">Thursday</option>
                                            <option value="5">Friday</option>
                                            <option value="6">Saturday</option>
                                            <option value="0">Sunday</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="week">Call Date</label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="date" value="{{\Carbon\Carbon::now()->toDateString()}}" name="date" id="date">
                                    </div>
                                </div>

                                <!-- Select Basic -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="status">Call Status</label>
                                    <div class="col-md-4">
                                        <select id="status" name="status" id="status" class="form-control">
                                            <option value="1" selected>Successful</option>
                                            <option value="0">Unsuccessful</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="submit"></label>
                                    <div class="col-md-4">
                                        <button id="submit" name="submit" class="btn btn-success">Test</button>
                                    </div>
                                </div>

                            </fieldset>
                        </form>

                    </div>
                </div>
            </div>
        </div>

            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="jumbotron text-center">
                        <h1><span id="result"></span></h1>
                    </div>
                    <p><h4>Notes</h4></p>
                    <li> Currently doesn't consider frequency, no of successful calls </li>
                    <li> Remember to add the patient's preference or closest Weekday to call date </li>
                    <li> <a href="https://docs.google.com/spreadsheets/d/1IqXEW18z2ELHBS7nMn64_7i2oO81JqqSBaZeJ9qxh-0/edit#gid=0">Algo Grid Google Sheet </a></li>
                    <li> Note problems with the algorithm and list them <a href="https://circlelink-health2.codebasehq.com/projects/cpm/tickets/594"> here </a></li>
                </div>
            </div>

    </div>
    @push('scripts')
        <script>

        $(document).ready(function () {
            $('input[name=seconds]').keyup(function () {
                var sec = parseInt($(this).val());

                var minutes = Math.floor(sec / 60);
                var seconds = sec % 60;
                var seconds = seconds > 9 ? "" + seconds : "0" + seconds;

                $('#convert').text(minutes + ':' + seconds);
            });
        });

        $("#compute").submit(function (e) {

            var url = '{!! route('algo.mock.compute') !!}'; // the script where you handle the form input.

            $.ajax({
                type: "POST",
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    seconds: $('#seconds').val(),
                    date: $('#date').val(),
                    days: $('#days').val(),
                    status: $('#status').val(),
//                    call_status: $('#call_success').val()
                }, // serializes the form's elements.
                success: function (data) {
                    console.log(data); // show response from the php script.
                    $('#result').text(data);
                }
            });

            e.preventDefault(); // avoid to execute the actual submit of the form.
        });

    </script>
    @endpush
@stop
