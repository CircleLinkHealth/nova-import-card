@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .settings-form {
                padding: 10%;

            }

            .settings-input {
                margin-bottom: 25px;
            }

        </style>
    @endpush

    <div class="container">
        <div class="col-md-12 text-center">
            <h2>Manage Report Settings</h2>
        </div>
        <div class="col-md-12">
            @include('errors.errors')
        </div>
        <div>
            <div class="settings-form">
                <form class="form" action="{{route('report-settings.update')}}" method="POST">
                    {{csrf_field()}}
                    <div class="form-group row">
                        <p class="col-md-8"><strong>Nurse Report Efficiency Index <u>Successful</u>
                                Calls Multiplier:</strong><br> ({{$nurseSuccessful->description}})<br></p>
                        <div class="col-md-4">
                            <label for="nurse_successful">Value:</label>
                            <input class="form-control settings-input" id="nurse_successful" name="nurse_successful" type="text"
                                   value="{{$nurseSuccessful->value}}">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <p class="col-md-8"><strong>Nurse Report Efficiency Index <u>Unuccessful</u>
                                Calls Multiplier:</strong><br> ({{$nurseUnsuccessful->description}})</p>
                        <div class="col-md-4">
                            <label for="nurse_unsuccessful">Value:</label>
                            <input class="form-control settings-input" id="nurse_unsuccessful" name="nurse_unsuccessful" type="text"
                                   value="{{$nurseUnsuccessful->value}}">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <p class="col-md-8"><strong>Average Care Coach time per Billable Patient:</strong><br> ({{$timeGoal->description}})</p>
                        <div class="col-md-4">
                            <label for="time_goal">Value:</label>
                            <input class="form-control settings-input" id="time_goal" name="time_goal" type="text"
                                   value="{{$timeGoal->value}}">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <p><span style="color: red; font-weight: bolder">Warning:</span> Changing these values will directly change the metrics produced in these reports</p>
                        <input type="submit" class="btn btn-md btn-primary settings-input" value="Change Settings">
                    </div>

                </form>
            </div>
        </div>
    </div>



@endsection


