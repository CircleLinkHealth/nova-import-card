@extends('partials.providerUI')

@section('title', 'Care Plan Approval Emails')
@section('activity', 'Care Plan Approval Emails')

@section('content')

    <div class="container container--menu">
        <div class="row row-centered">

            <div class="panel panel-info">
                <div class="panel-heading">When should we send care plan approval e-mails?</div>

                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('email.store') }}" method="post">
                                    {{ csrf_field() }}
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
                                                   {{ !($settings->frequency == CircleLinkHealth\SharedModels\Entities\EmailSettings::DAILY) ?: 'checked' }}
                                                   value="{{ CircleLinkHealth\SharedModels\Entities\EmailSettings::DAILY }}">
                                            <label for="daily"><span></span>Daily</label>
                                        </div>

                                        <div class="radio-inline col-md-3">
                                            <input type="radio" id="weekly" name="frequency"
                                                   {{ !($settings->frequency == CircleLinkHealth\SharedModels\Entities\EmailSettings::WEEKLY) ?: 'checked' }}
                                                   value="{{ CircleLinkHealth\SharedModels\Entities\EmailSettings::WEEKLY }}">
                                            <label for="weekly"><span></span>Weekly</label>
                                        </div>

                                        <div class="radio-inline col-md-3">
                                            <input type="radio" id="mwf" name="frequency"
                                                   {{ !($settings->frequency == CircleLinkHealth\SharedModels\Entities\EmailSettings::MWF) ?: 'checked' }}
                                                   value="{{ CircleLinkHealth\SharedModels\Entities\EmailSettings::MWF }}">
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