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
                        User ID: {{ $wpUser->ID }}
                    </div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (count($messages) > 0)
                            <div class="alert alert-success">
                                <strong>Messages:</strong><br><br>
                                <ul>
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            {!! Form::open(array('url' => '/wpusers/'.$wpUser->ID.'/edit', 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-left">
                                    <a href="{{ url('wpusers/'.$wpUser->ID.'/msgcenter') }}" class="btn btn-primary">Message Center</a>
                                </div>
                                <div class="pull-left" style="margin-left:10px;">
                                    <a href="{{ url('wpusers/'.$wpUser->ID.'/careplan') }}" class="btn btn-primary">Care Plan Feed</a>
                                </div>
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <h2>Program</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('primary_blog', 'Primary Blog:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('primary_blog', $wpBlogs, $primaryBlog, ['class' => 'form-control select-picker', 'disabled' => 'disabled', 'style' => 'width:80%;']) !!}</div>
                            </div>
                        </div>

                        <h2>Role</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('role', $providers_arr, $role, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>

                        <h2>User Info</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('first_name', $userMeta['first_name'], ['class' => 'form-control']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('last_name', $userMeta['last_name'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('nickname', 'Nickname:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('nickname', $userMeta['nickname'], ['class' => 'form-control']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('description', 'Description:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('description', $userMeta['description'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>

                        <h2>User Config</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('status', 'Status:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $userConfig['status'], ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                <div class="col-xs-1">{!! Form::label('email', 'Email:') !!}</div>
                                <div class="col-xs-5">{!! Form::text('email', $userConfig['email'], ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('mrn_number', 'MRN Number:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('mrn_number', $userConfig['mrn_number'], ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('npi_number', 'NPI Number:') !!}</div>
                                <div class="col-xs-4">{!! Form::text('npi_number', $userConfig['npi_number'], ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('qualification', 'Qualification:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('qualification', $userConfig['qualification'], ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('specialty', 'Specialty:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('specialty', $userConfig['specialty'], ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('study_phone_number', 'Study Phone Number:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('study_phone_number', $userConfig['study_phone_number'], ['class' => 'form-control', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('active_date', 'Active Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('active_date', $userConfig['active_date'], ['class' => 'form-control datepicker', 'style' => 'width:40%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_time', 'Contact Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('preferred_contact_time', $userConfig['preferred_contact_time'], ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_timezone', 'Contact Timezone:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_timezone', $timezones_arr, $userConfig['preferred_contact_timezone'], ['class' => 'form-control select-picker', 'style' => 'width:60%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_method', 'Contact Method:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_method', array('SMS'), $userConfig['preferred_contact_method'], ['class' => 'form-control select-picker', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_language', 'Contact Language:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_language', array('EN'), $userConfig['preferred_contact_language'], ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('preferred_contact_location', 'Contact Location:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('preferred_contact_location', $locations_arr, $userConfig['preferred_contact_location'], ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('prefix', 'Prefix(DEPR?):') !!}</div>
                                <div class="col-xs-10">{!! Form::text('prefix', $userConfig['prefix'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('gender', 'Gender:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('gender', array('M', 'F'), $userConfig['gender'], ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('address', 'Address:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('address', $userConfig['address'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('city', 'City:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('city', $userConfig['city'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('state', 'State:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('state', $states_arr, $userConfig['state'], ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                <div class="col-xs-1">{!! Form::label('zip', 'Zip:') !!}</div>
                                <div class="col-xs-5">{!! Form::text('zip', $userConfig['zip'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('birth_date', 'Birth Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('birth_date', $userConfig['birth_date'], ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('consent_date', 'Consent Date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('consent_date', $userConfig['consent_date'], ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_optin', 'Daily Reminder Optin:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('daily_reminder_optin', array('Y'), $userConfig['daily_reminder_optin'], ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_time', 'Daily Reminder Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('daily_reminder_time', $userConfig['daily_reminder_time'], ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('daily_reminder_areas', 'Daily Reminder Areas:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('daily_reminder_areas', $userConfig['daily_reminder_areas'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_optin', 'Hospital Reminder Optin:') !!}</div>
                                <div class="col-xs-10">{!! Form::select('hospital_reminder_optin', array('Y'), $userConfig['hospital_reminder_optin'], ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_time', 'Hospital Reminder Time:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('hospital_reminder_time', $userConfig['hospital_reminder_time'], ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('hospital_reminder_areas', 'Hospital Reminder Areas:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('hospital_reminder_areas', $userConfig['hospital_reminder_areas'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('registration_date', 'registration_date:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('registration_date', $userConfig['registration_date'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('care_team', 'Care Team:') !!}</div>
                                <div class="col-xs-10">
                                    @if (count($userConfig['care_team']) > 0)
                                        <div class="alert alert-warning">
                                            <strong>Care Team</strong>
                                            <ul>
                                                @foreach ($userConfig['care_team'] as $id)
                                                    <li>{!! Form::checkbox('care_team[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('send_alert_to', 'Send alerts to:') !!}</div>
                                <div class="col-xs-10">
                                    @if (count($userConfig['send_alert_to']) > 0)
                                        <div class="alert alert-warning">
                                            <strong>Send alerts to</strong>
                                            <ul>
                                                @foreach ($userConfig['send_alert_to'] as $id)
                                                    <li>{!! Form::checkbox('send_alert_to[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('billing_provider', 'Billing Provider:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('billing_provider', $userConfig['billing_provider'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('lead_contact', 'Lead Contact:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('lead_contact', $userConfig['lead_contact'], ['class' => 'form-control']) !!}</div>
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop