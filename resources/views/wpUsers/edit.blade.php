@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#togglePrograms").click(function (event) {
                event.preventDefault();
                $("#programs").toggle();
                return false;
            });

            $(function () {
                $("#programsCheckAll").click(function () {
                        $(".programs").prop("checked", true);
                        return false;
                });

                $("#programsUncheckAll").click(function () {
                    $(".programs").prop("checked", false);
                    return false;
                });
            });
        });
    </script>
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h1>{{ $patient->fullName }}</h1>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Edit {{ $patient->fullNameWithID }}
                    </div>
                    <div class="panel-body">

                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.users.update', array('id' => $patient->ID)), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                @if($patient->hasRole('participant'))
                                    {{--<div class="pull-left">
                                        <a href="{{ URL::route('admin.users.msgCenter', array('patientId' => $patient->ID)) }}" class="btn btn-primary">App Simulator</a>
                                    </div>--}}
                                    <div class="pull-left" style="margin-left:10px;">
                                        <a href="{{ URL::route('patient.summary', array('patientId' => $patient->ID)) }}" class="btn btn-info">Go To Provider UI</a>
                                    </div>
                                @endif
                                {{--<div class="pull-left" style="margin-left:10px;">
                                    <a href="{{ URL::route('admin.users.careplan', array('patientId' => $patient->ID)) }}" class="btn btn-primary">View Care Plan Feed JSON</a>
                                </div>--}}
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.users.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active"><a href="#program" aria-controls="program" role="tab" data-toggle="tab">User Info</a></li>
                            @if($patient->hasRole('participant'))
                                <li role="presentation"><a href="#patientinfo" aria-controls="patientinfo" role="tab" data-toggle="tab">Patient Info</a></li>
                                <li role="presentation"><a href="#usercareteam" aria-controls="usercareteam" role="tab" data-toggle="tab">Care Team</a></li>
                            @endif
                            @if($patient->hasRole('provider'))
                                <li role="presentation"><a href="#providerinfo" aria-controls="providerinfo" role="tab" data-toggle="tab">Provider Info</a></li>
                            @endif
                            <li role="presentation"><a href="#revisions" aria-controls="revisions" role="tab" data-toggle="tab">History</a></li>
                            <li role="presentation"><a href="#observations" aria-controls="observations" role="tab" data-toggle="tab">Observations</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">

                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_login', 'Login:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('user_login', $patient->user_login, ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_email', 'user_email:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('user_email', $patient->user_email, ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('first_name', $patient->first_name, ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('last_name', $patient->last_name, ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_status', 'User Status:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('user_status', array('0' => '0', '1' => '1'), $patient->user_status, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                        <div class="col-xs-2"></div>
                                        <div class="col-xs-4"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('address', 'Address:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('address', $patient->address, ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('city', 'City:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('city', $patient->city, ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('home_phone_number', 'Home Phone Number:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('home_phone_number', $patient->home_phone_number, ['class' => 'form-control', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('state', 'State:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('state', $states_arr, $patient->state, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                        <div class="col-xs-1">{!! Form::label('zip', 'Zip:') !!}</div>
                                        <div class="col-xs-5">{!! Form::text('zip', $patient->zip, ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>



                                <h2>Role:</h2>
                                <div id="roles">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('role', $roles, $role->id, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                        </div>
                                    </div>
                                </div>




                                <h2><a data-toggle="collapse" data-target="#programCollapse" class="">Programs</a></h2>

                                <div id="programCollapse" class="collapse in" style="background:#888;padding:20px;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('program_id', 'Primary Program:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('program_id', $wpBlogs, $primaryBlog, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                            <div class="col-xs-2"><strong>Program Config:</strong><br />Auto attach to new programs</div>
                                            <div class="col-xs-4">
                                                <br />
                                                <input id="auto_attach_programs" name="auto_attach_programs" value="1" type="checkbox" @if(isset($patient->auto_attach_programs)){{ ((old('auto_attach_programs') == '1') ? 'checked="checked"' : ($patient->auto_attach_programs == '1') ? 'checked="checked"' : '') }}@endif>
                                            </div>
                                        </div>
                                    </div>


                                    <a class="btn btn-info panel-title" href="#" id="togglePrograms"><strong>Toggle Programs list</strong></a><br /><br />
                                    <div id="programs" style="display:none;">
                                        <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button> |
                                        <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>
                                        @foreach( $wpBlogs as $wpBlogId => $domain )
                                            <div class="row" id="program_{{ $wpBlogId }}" style="border-bottom:1px solid #000;">
                                                <div class="col-sm-2">
                                                    <div class="text-right">
                                                    @if( in_array($wpBlogId, $patient->programs()->lists('blog_id')->all()) )
                                                        {!! Form::checkbox('programs[]', $wpBlogId, ['checked' => "checked"], ['style' => '', 'class' => 'programs']) !!}
                                                    @else
                                                        {!! Form::checkbox('programs[]', $wpBlogId, [], ['style' => '', 'class' => 'programs']) !!}
                                                    @endif
                                                    </div>
                                                </div>
                                                <div class="col-sm-10">{!! Form::label('Value', 'Program: '.$domain, array('class' => '')) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if($patient->hasRole('participant'))
                                <div role="tabpanel" class="tab-pane" id="patientinfo">
                                    <h2>Patient Info</h2>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('ccm_status', 'CCM Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('ccm_status', array('paused' => 'paused', 'enrolled' => 'enrolled', 'withdrawn' => 'withdrawn'), $patient->ccmStatus, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                            <div class="col-xs-2">{!! Form::label('careplan_status', 'Careplan Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('careplan_status', array('draft' => 'draft', 'qa_approved' => 'qa_approved', 'provider_approved' => 'provider_approved'), $patient->careplan_status, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('date_paused', 'Date Paused:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('date_paused', $patient->date_paused, ['class' => 'form-control']) !!}</div>
                                            <div class="col-xs-2">{!! Form::label('date_withdrawn', 'Date Withdrawn:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('date_withdrawn', $patient->date_withdrawn, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>

                                    <h2>Care Plan:</h2>
                                    <div id="roles">
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-lg-2">{!! Form::label('care_plan_id', 'Care Plan: ') !!}</div>
                                                <div class="col-lg-10">{!! Form::select('care_plan_id', $carePlans, $patient->care_plan_id, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('status', 'Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $patient->status, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                            <div class="col-xs-1">{!! Form::label('email', 'Email:') !!}</div>
                                            <div class="col-xs-5">{!! Form::text('email', $patient->email, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('mrn_number', 'MRN Number:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('mrn_number', $patient->mrn_number, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('active_date', 'Active Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('active_date', $patient->active_date, ['class' => 'form-control datepicker', 'style' => 'width:40%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_time', 'Contact Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('preferred_contact_time', $patient->preferred_contact_time, ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('contact-days', 'Preferred Contact Days:') !!}</div>
                                            <div class="col-xs-10">
                                                <div class="radio-inline modal-box-clone label">
                                                    <div class="radio-inline">
                                                        <input id="contact-days-1" name="contact_days[]" value="1" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '1') ? 'checked="checked"' : (in_array('1', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-1"><span></span>&nbsp;M</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-2" name="contact_days[]" value="2" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '2') ? 'checked="checked"' : (in_array('2', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-2"><span></span>&nbsp;T</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-3" name="contact_days[]" value="3" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '3') ? 'checked="checked"' : (in_array('3', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-3"><span></span>&nbsp;W</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-4" name="contact_days[]" value="4" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '4') ? 'checked="checked"' : (in_array('4', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-4"><span></span>&nbsp;Th</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-5" name="contact_days[]" value="5" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '5') ? 'checked="checked"' : (in_array('5', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-5"><span></span>&nbsp;F</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-6" name="contact_days[]" value="6" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '6') ? 'checked="checked"' : (in_array('6', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-6"><span></span>&nbsp;Sa</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-7" name="contact_days[]" value="7" type="checkbox" @if(isset($patient->preferred_cc_contact_days)){{ ((old('contact_days') == '7') ? 'checked="checked"' : (in_array('7', explode(', ', $patient->preferred_cc_contact_days)) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;" for="contact-days-7"><span></span>&nbsp;Su</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_timezone', 'Contact Timezone:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_timezone', $timezones_arr, $patient->preferred_contact_timezone, ['class' => 'form-control select-picker', 'style' => 'width:60%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_method', 'Contact Method:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_method', array('CCT'), $patient->preferred_contact_method, ['class' => 'form-control select-picker', 'style' => 'width:30%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_language', 'Contact Language:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_language', array('EN'), $patient->preferred_contact_language, ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_location', 'Contact Location:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_location', $locations_arr, $patient->preferred_contact_location, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('gender', 'Gender:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('gender', array('M', 'F'), $patient->gender, ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('birth_date', 'Birth Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('birth_date', $patient->birth_date, ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('consent_date', 'Consent Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('consent_date', $patient->consent_date, ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_optin', 'Daily Reminder Optin:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('daily_reminder_optin', array('Y'), $patient->daily_reminder_optin, ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_time', 'Daily Reminder Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('daily_reminder_time', $patient->daily_reminder_time, ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_areas', 'Daily Reminder Areas:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('daily_reminder_areas', $patient->daily_reminder_areas, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_optin', 'Hospital Reminder Optin:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('hospital_reminder_optin', array('Y'), $patient->hospital_reminder_optin, ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_time', 'Hospital Reminder Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hospital_reminder_time', $patient->hospital_reminder_time, ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_areas', 'Hospital Reminder Areas:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hospital_reminder_areas', $patient->hospital_reminder_areas, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('registration_date', 'registration_date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('registration_date', $patient->registration_date, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="usercareteam">
                                    <h2>Care Team</h2>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('care_team', 'Care Team:') !!}</div>
                                            <div class="col-xs-10">
                                                @if (isset($patient->care_team))
                                                    @if (count($patient->care_team) > 0 && is_array($patient->care_team))
                                                        <div class="alert alert-warning">
                                                            <ul>
                                                                @foreach ($patient->care_team as $id)
                                                                    <li>{!! Form::checkbox('care_team[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('send_alert_to', 'Send alerts to:') !!}</div>
                                            <div class="col-xs-10">
                                                @if (isset($patient->send_alert_to))
                                                    @if (is_array($patient->send_alert_to) && count($patient->send_alert_to) > 0)
                                                        <div class="alert alert-warning">
                                                            <strong>Send alerts to</strong>
                                                            <ul>
                                                                @foreach ($patient->send_alert_to as $id)
                                                                    <li>{!! Form::checkbox('send_alert_to[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('billing_provider', 'Billing Provider:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('billing_provider', $patient->billing_provider, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('lead_contact', 'Lead Contact:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('lead_contact', $patient->lead_contact, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                            @if($patient->hasRole('provider'))
                                <div role="tabpanel" class="tab-pane" id="providerinfo">
                                    <h2>Provider Info</h2>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('npi_number', 'NPI Number:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('npi_number', $patient->npi_number, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('qualification', 'Qualification:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('qualification', $patient->qualification, ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('specialty', 'Specialty:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('specialty', $patient->specialty, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('prefix', 'Prefix(DEPR?):') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('prefix', $patient->prefix, ['class' => 'form-control']) !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div role="tabpanel" class="tab-pane" id="revisions">
                                @include('partials.revisions')
                            </div>

                            <div role="tabpanel" class="tab-pane" id="observations">
                                @if ($patient->observations()->count() > 0)
                                    found {{ $patient->observations()->count() }}
                                @else
                                    <br><br><em>No observations found for this user</em>
                                @endif
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::previous() }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::hidden('user_id', $patient->ID) !!}
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