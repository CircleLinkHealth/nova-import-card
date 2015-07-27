@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add New User
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            {!! Form::open(array('url' => '/wpusers/create', 'class' => 'form-horizontal')) !!}
                        </div>

                        <h1>Program</h1>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('primary_blog', 'Primary Blog:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('primary_blog', $wpBlogs, '', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                            </div>
                        </div>

                        <h1>Role</h1>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('role', array('Provider' => 'Provider', 'Viewer' => 'Viewer'), 'Provider', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>

                        <h1>User Info</h1>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('first_name', '', ['class' => 'form-control']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('last_name', '', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('nickname', 'Nickname:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('nickname', '', ['class' => 'form-control']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('other', 'Other:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('other', '', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>

                        <h1>User Config</h1>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('status', 'Status:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), 'Active', ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('email', 'Email:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('email', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('mrn_number', 'MRN Number:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('mrn_number', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('study_phone_number', 'Study Phone Number:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('study_phone_number', '', ['class' => 'form-control', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('active_date', 'Active Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('active_date', '2015/07/21 15:55:36', ['class' => 'form-control datepicker', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_time', 'Contact Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('preferred_contact_time', '', ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_timezone', 'Contact Timezone:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_timezone', $timezones_arr, 'America/New_York', ['class' => 'form-control select-picker', 'style' => 'width:60%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_method', 'Contact Method:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_method', array('SMS'), 'SMS', ['class' => 'form-control select-picker', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_language', 'Contact Language:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_language', array('EN'), 'EN', ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_location', 'Contact Location(DEPR?):') !!}</div>
                                <div class="col-xs-10">{!! Form::text('preferred_contact_location', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('prefix', 'Prefix(DEPR?):') !!}</div>
                                <div class="col-xs-10">{!! Form::text('prefix', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('gender', 'Gender:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('gender', array('M', 'F'), 'M', ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('address', 'Address:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('address', '', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('city', 'City:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('city', '', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('state', 'State:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('state', $states_arr, 'NJ', ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('zip', 'Zip:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('zip', '', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('birth_date', 'Birth Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('birth_date', '', ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('consent_date', 'Consent Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('consent_date', '', ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_optin', 'Daily Reminder Optin:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('daily_reminder_optin', array('Y'), 'Y', ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_time', 'Daily Reminder Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('daily_reminder_time', '', ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_areas', 'Daily Reminder Areas:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('daily_reminder_areas', 'TBD', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_optin', 'Hospital Reminder Optin:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('hospital_reminder_optin', array('Y'), 'Y', ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_time', 'Hospital Reminder Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('hospital_reminder_time', '', ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_areas', 'Hospital Reminder Areas:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('hospital_reminder_areas', 'TBD', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('location', 'location:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('location', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('registration_date', 'registration_date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('registration_date', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('care_team', 'care_team (ARRAY):') !!}</div>
                                <div class="col-xs-10">{!! Form::text('care_team', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('send_alert_to', 'send_alert_to (ARRAY):') !!}</div>
                                <div class="col-xs-10">{!! Form::text('send_alert_to', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('billing_provider', 'Billing Provider:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('billing_provider', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('lead_contact', 'Lead Contact:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('lead_contact', '--xx--', ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::button('Add User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop