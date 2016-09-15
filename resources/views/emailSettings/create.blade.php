@extends('partials.providerUI')

@section('content')

    <div class="container container--menu">
        <div class="row row-centered">

            <div class="panel panel-info">
                <div class="panel-heading">Reminder Email Frequency Settings</div>

                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('settings.email.store') }}" method="post">
                                    <div class="form-group">

                                        @if(isset($showSuccessMessage))
                                            @if($showSuccessMessage)
                                                <br>
                                                <div class="container">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-success text-left" role="alert">
                                                            Your email preferences have been updated.
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <br>

                                        <div class="col-md-1"></div>

                                        <div class="radio-inline col-md-3">
                                            <input type="radio" id="daily" name="frequency"
                                                   {{ !($settings->frequency == App\Models\EmailSettings::DAILY) ?: 'checked' }}
                                                   value="{{ App\Models\EmailSettings::DAILY }}">
                                            <label for="daily"><span></span>Daily</label>
                                        </div>

                                        <div class="radio-inline col-md-3">
                                            <input type="radio" id="weekly" name="frequency"
                                                   {{ !($settings->frequency == App\Models\EmailSettings::WEEKLY) ?: 'checked' }}
                                                   value="{{ App\Models\EmailSettings::WEEKLY }}">
                                            <label for="weekly"><span></span>Weekly</label>
                                        </div>

                                        <div class="radio-inline col-md-3">
                                            <input type="radio" id="mwf" name="frequency"
                                                   {{ !($settings->frequency == App\Models\EmailSettings::MWF) ?: 'checked' }}
                                                   value="{{ App\Models\EmailSettings::MWF }}">
                                            <label for="mwf"><span></span>Monday, Wednesday, Friday</label>
                                        </div>

                                        <br><br><br>


                                        <div class="col-md-12">
                                            <input type="submit" class="btn btn-primary" value="Update Preferences"
                                                   name="submit">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop