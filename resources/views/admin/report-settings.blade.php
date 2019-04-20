@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .settings-input {
                padding: 10px;
            }
        </style>
    @endpush

    <div class="container">
        <div class="col-md-12 text-center">
            <h2>Manage Report Settings</h2>
        </div>
        <div>
            <div>
                <form class="form-group">
                    <div class="settings-input">
                        <label for="nurse_successful" class="col-md-6 control-label">Nurse Report Efficiency Index <u>Successful</u> Calls Multiplier:</label>
                        <div class="col-md-6">
                            <input class="form-control" id="nurse_successful" name="nurse_succesful_calls" type="text" value="{{$settings->where('name', 'nurse_report_successful')->first()->value}}">
                        </div>
                    </div>
                    <div class="settings-input">
                        <label for="nurse_unsuccessful" class="col-md-6 control-label">Nurse Report Efficiency Index <u>Unuccessful</u> Calls Multiplier</label>
                        <div class="col-md-6">
                            <input class="form-control" id="nurse_unsuccessful" name="nurse_unsuccesful_calls" type="text" value="{{$settings->where('name', 'nurse_report_unsuccessful')->first()->value}}">
                        </div>
                    </div>
                    <div class="settings-input">
                        <label for="time-goal" class="col-md-6 control-label">Nurse Report Efficiency Index <u>Unuccessful</u> Calls Multiplier</label>
                        <div class="col-md-6">
                            <input class="form-control" id="time-goal" name="nurse_unsuccesful_calls" type="text" value="{{$settings->where('name', 'nurse_report_unsuccessful')->first()->value}}">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>



@endsection


