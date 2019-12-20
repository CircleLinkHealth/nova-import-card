@extends('partials.adminUI')

@section('content')
    @push('scripts')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <script>
        $(document).ready(function () {
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
    @endpush

    @push('styles')
    <style>
        .form-group {
            margin: 20px;
        }
    </style>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1><span>Editing </span>{{ $patient->getFullName() }}</h1>
                <div class="panel panel-default">
                    <div class="panel-body">

                        @include('errors.errors')

                        {!! Form::open(array('url' => route('admin.users.update', array('id' => $patient->id)), 'class' => 'form-horizontal')) !!}

                        <div class="row" style="">
                            <div class="col-sm-12">
                                @if($patient->hasRole('participant'))
                                    <div class="pull-left" style="margin-left:10px;">
                                        <a href="{{ route('patient.summary', array('patientId' => $patient->id)) }}"
                                           class="btn btn-info">Go To Provider UI</a>
                                    </div>
                                @endif
                                <div class="pull-right">
                                    <a href="{{ route('admin.users.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active">
                                <a href="#program" aria-controls="program" role="tab" data-toggle="tab">User Info</a>
                            </li>
                            @if($patient->hasRole('participant'))
                                <li role="presentation">
                                    <a href="#patientinfo" aria-controls="patientinfo" role="tab" data-toggle="tab">Patient
                                        Info</a>
                                </li>
                                <li role="presentation">
                                    <a href="#usercareteam" aria-controls="usercareteam" role="tab" data-toggle="tab">Care
                                        Team</a>
                                </li>
                            @endif
                            @if($patient->hasRole('provider'))
                                <li role="presentation">
                                    <a href="#providerinfo" aria-controls="providerinfo" role="tab" data-toggle="tab">Provider
                                        Info</a>
                                </li>
                            @endif
                            @if($patient->isCareCoach() && $patient->nurseInfo)
                                <li role="presentation">
                                    <a href="#nurseinfo" aria-controls="nurseinfo" role="tab" data-toggle="tab">Nurse
                                        Info</a>
                                </li>
                            @endif

                            @if($patient->hasRole('care-ambassador') && $patient->careAmbassador)
                                <li role="presentation">
                                    <a href="#careAmbassador" aria-controls="careAmbassador" role="tab"
                                       data-toggle="tab">Care Ambassador Settings</a>
                                </li>
                            @endif

                            <li role="presentation">
                                <a href="#revisions" aria-controls="revisions" role="tab" data-toggle="tab">History</a>
                            </li>
                            <li role="presentation">
                                <a href="#observations" aria-controls="observations" role="tab" data-toggle="tab">Observations</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">

                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('username', 'Login:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('username', $patient->username, ['class' => 'form-control']) !!}</div>

                                    <div class="col-xs-1">{!! Form::label('email', 'Email:') !!}</div>
                                    <div class="col-xs-3">{!! Form::email('email', $patient->email, ['class' => 'form-control']) !!}</div>

                                    <div class="col-xs-1">{!! Form::label('role', 'Role:') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('role', $roles, $role->id, ['class' => 'form-control select-picker']) !!}</div>
                                </div>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('first_name', 'First Name:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('first_name', $patient->getFirstName(), ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('last_name', $patient->getLastName(), ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('suffix', 'Suffix:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('suffix', $patient->suffix, ['class' => 'form-control']) !!}</div>
                                </div>

                                <br>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('access_disabled', 'Access Disabled') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('access_disabled', array('0' => 'No', '1' => 'Yes'), $patient->access_disabled, ['class' => 'form-control select-picker']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('user_status', 'User Status:') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('user_status', array('0' => 'Inactive', '1' => 'Active'), $patient->user_status, ['class' => 'form-control select-picker']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('timezone', 'Timezone:') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('timezone',
                                    [
                                    'America/New_York' => 'Eastern Time',
                                    'America/Chicago' => 'Central Time',
                                    'America/Denver' => 'Mountain Time',
                                    'America/Phoenix' => 'Mountain Time (no DST)',
                                    'America/Los_Angeles' => 'Pacific Time',
                                    'America/Anchorage' => 'Alaska Time',
                                    'America/Adak' => 'Hawaii-Aleutian',
                                    'Pacific/Honolulu' => 'Hawaii-Aleutian Time (no DST)',
                                    ],
                                    $patient->timezone, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                </div>

                                <br>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('address', 'Address:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('address', $patient->address, ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('city', 'City:') !!}</div>
                                    <div class="col-xs-2">{!! Form::text('city', $patient->city, ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('state', 'State:') !!}</div>
                                    <div class="col-xs-1">{!! Form::select('state', $states_arr, $patient->state, ['class' => 'form-control select-picker']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('zip', 'Zip:') !!}</div>
                                    <div class="col-xs-2">{!! Form::text('zip', $patient->zip, ['class' => 'form-control']) !!}</div>
                                </div>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('home_phone_number', 'Home Phone Number:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('home_phone_number', $patient->getHomePhoneNumber(), ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-2">{!! Form::label('can_see_phi', 'Grant access to see PHI:') !!}</div>
                                    <div class="col-xs-1">{!! Form::checkbox('can_see_phi', 0, $patient->canSeePhi(), ['class' => 'form-check-input']) !!}</div>
                                </div>



                                <h2><a data-toggle="collapse" data-target="#programCollapse" class="">Practices</a></h2>

                                <div id="programCollapse" class="collapse in" style="background:#888;padding:20px;">
                                    <div class="form-group">
                                        <div class="col-xs-2">{!! Form::label('program_id', 'Practice') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('program_id', $wpBlogs, $primaryBlog, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('provider_id', 'Billing Provider:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('provider_id', [], '', ['class' => 'form-control select-picker', 'style' => 'width:80%;', 'value' => $patient->getBillingProviderId()]) !!}</div>
                                        <div class="col-xs-6"></div>
                                        <div class="col-xs-4">{!! Form::label('auto_attach_programs', 'Give access to all of ' . auth()->user()->saasAccountName() . '\'s practices') !!}</div>
                                        <div class="col-xs-2">
                                            {!! Form::checkbox('auto_attach_programs', 1, !! $patient->auto_attach_programs) !!}
                                        </div>
                                        @push('scripts')
                                            <script>
                                                (function () {
                                                    function setBillingProvider(practiceId) {
                                                        return $.ajax({
                                                            url: '/api/practices/' + practiceId + '/providers',
                                                            type: 'GET',
                                                            success: function (providers) {
                                                                console.log('practice:providers', providers)
                                                                $('[name="provider_id"]').html('')
                                                                providers.forEach(function (provider) {
                                                                    $('[name="provider_id"]').append($('<option />').val(provider.id).text(provider.name))
                                                                })
                                                                $('[name="provider_id"]').val($('[name="provider_id"]').attr('value'))
                                                            }
                                                        })
                                                    }

                                                    $('[name="program_id"]').change(function () {
                                                        setBillingProvider($(this).val())
                                                    })

                                                    setBillingProvider($('[name="program_id"]').val())
                                                })();

                                            </script>
                                        @endpush
                                    </div>


                                    <a class="btn btn-info panel-title" href="#" id="togglePrograms"><strong>Toggle
                                            Practices list</strong></a><br/><br/>
                                    <div id="programs" style="display:none;">
                                        <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button>
                                        |
                                        <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>

                                        @foreach( $wpBlogs as $wpBlogId => $domain )
                                            <div class="row" id="program_{{ $wpBlogId }}"
                                                 style="border-bottom:1px solid #000;">
                                                <div class="col-sm-2">
                                                    <div class="text-right">
                                                        @if( in_array($wpBlogId, $userPractices) )
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
                                            <div class="col-xs-2">{!! Form::label('ccm_status', 'CCM Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('ccm_status', ['paused' => 'paused', 'enrolled' => 'enrolled', 'withdrawn' => 'withdrawn', 'unreachable' => 'unreachable'], $patient->getCcmStatus(), ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                            <div class="col-xs-2">{!! Form::label('careplan_status', 'Careplan Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('careplan_status', array('draft' => 'draft', 'qa_approved' => 'qa_approved', 'provider_approved' => 'provider_approved'), $patient->careplan->status, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                    </div>

                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('date_paused', 'Date Paused:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('date_paused', $patient->getDatePaused(), ['class' => 'form-control']) !!}</div>
                                            <div class="col-xs-2">{!! Form::label('date_withdrawn', 'Date Withdrawn:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('date_withdrawn', $patient->getDateWithdrawn(), ['class' => 'form-control']) !!}</div>
                                    </div>


                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('status', 'Status:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $patient->status, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('mrn_number', 'MRN Number:') !!}</div>
                                            <div class="col-xs-4">{!! Form::text('mrn_number', $patient->getMRN(), ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('active_date', 'Active Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('active_date', $patient->getActiveDate(), ['class' => 'form-control datepicker', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_time', 'Contact Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('preferred_contact_time', $patient->getPreferredContactTime(), ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('contact-days', 'Preferred Contact Days:') !!}</div>
                                            <div class="col-xs-10">
                                                <div class="radio-inline modal-box-clone label">
                                                    <div class="radio-inline">
                                                        <input id="contact-days-1" name="contact_days[]" value="1"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '1') ? 'checked="checked"' : (in_array('1', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-1"><span></span>&nbsp;M</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-2" name="contact_days[]" value="2"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '2') ? 'checked="checked"' : (in_array('2', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-2"><span></span>&nbsp;T</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-3" name="contact_days[]" value="3"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '3') ? 'checked="checked"' : (in_array('3', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-3"><span></span>&nbsp;W</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-4" name="contact_days[]" value="4"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '4') ? 'checked="checked"' : (in_array('4', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-4"><span></span>&nbsp;Th</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-5" name="contact_days[]" value="5"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '5') ? 'checked="checked"' : (in_array('5', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-5"><span></span>&nbsp;F</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-6" name="contact_days[]" value="6"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '6') ? 'checked="checked"' : (in_array('6', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-6"><span></span>&nbsp;Sa</label>
                                                    </div>
                                                    <div class="radio-inline">
                                                        <input id="contact-days-7" name="contact_days[]" value="7"
                                                               type="checkbox" @if(null !== $patient->getPreferredCcContactDays()){{ ((old('contact_days') == '7') ? 'checked="checked"' : (in_array('7', explode(', ', $patient->getPreferredCcContactDays())) ? 'checked="checked"' : '')) }}@endif>
                                                        <label style="font-size: 120%; margin: -1px;"
                                                               for="contact-days-7"><span></span>&nbsp;Su</label>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_method', 'Contact Method:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_method', array('CCT'), $patient->getPreferredContactMethod(), ['class' => 'form-control select-picker', 'style' => 'width:30%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_language', 'Contact Language:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_language', array('EN'), $patient->getPreferredContactLanguage(), ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('preferred_contact_location', 'Contact Location:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('preferred_contact_location', $locations_arr, $patient->getPreferredContactLocation(), ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('gender', 'Gender:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('gender', array('M', 'F'), $patient->getGender(), ['class' => 'form-control select-picker', 'style' => 'width:20%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('birth_date', 'Birth Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('birth_date', $patient->getBirthDate(), ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('consent_date', 'Consent Date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('consent_date', $patient->getConsentDate(), ['class' => 'form-control', 'style' => 'width:30%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_optin', 'Daily Reminder Optin:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('daily_reminder_optin', array('Y'), $patient->getDailyReminderOptin(), ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_time', 'Daily Reminder Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('daily_reminder_time', $patient->getDailyReminderTime(), ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('daily_reminder_areas', 'Daily Reminder Areas:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('daily_reminder_areas', $patient->getDailyReminderAreas(), ['class' => 'form-control']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_optin', 'Hospital Reminder Optin:') !!}</div>
                                            <div class="col-xs-10">{!! Form::select('hospital_reminder_optin', array('Y'), $patient->getHospitalReminderOptin(), ['class' => 'form-control select-picker', 'style' => 'width:10%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_time', 'Hospital Reminder Time:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hospital_reminder_time', $patient->getHospitalReminderTime(), ['class' => 'form-control', 'style' => 'width:20%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('hospital_reminder_areas', 'Hospital Reminder Areas:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hospital_reminder_areas', $patient->getHospitalReminderAreas(), ['class' => 'form-control']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('registration_date', 'registration_date:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('registration_date', $patient->getRegistrationDate(), ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="usercareteam">
                                    <h2>Care Team</h2>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('care_team', 'Care Team:') !!}</div>
                                            <div class="col-xs-10">
                                                @if (null !== ($patient->getCareTeam()))
                                                    @if (count($patient->getCareTeam()) > 0 && is_array($patient->getCareTeam()))
                                                        <div class="alert alert-warning">
                                                            <ul>
                                                                @foreach ($patient->getCareTeam() as $id)
                                                                    <li>{!! Form::checkbox('care_team[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('send_alert_to', 'Send alerts to:') !!}</div>
                                            <div class="col-xs-10">
                                                @if (null !== ($patient->getSendAlertTo()))
                                                    @if (is_array($patient->getSendAlertTo()) && count($patient->getSendAlertTo()) > 0)
                                                        <div class="alert alert-warning">
                                                            <strong>Send alerts to</strong>
                                                            <ul>
                                                                @foreach ($patient->getSendAlertTo() as $id)
                                                                    <li>{!! Form::checkbox('send_alert_to[]', $id, ['checked' => 'checked']) !!}{{ $id }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('billing_provider', 'Billing Provider:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('billing_provider', $patient->billing_provider, ['class' => 'form-control']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('lead_contact', 'Lead Contact:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('lead_contact', $patient->lead_contact, ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            @endif


                            @if($patient->hasRole('provider'))
                                <div role="tabpanel" class="tab-pane" id="providerinfo">
                                    <h2>Provider Info</h2>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('npi_number', 'NPI Number:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('npi_number', $patient->getNpiNumber(), ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('specialty', 'Specialty:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('specialty', $patient->getSpecialty(), ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('prefix', 'Prefix(DEPR?):') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('prefix', $patient->getPrefix(), ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            @endif


                            @if($patient->isCareCoach() && $patient->nurseInfo)
                                <div role="tabpanel" class="tab-pane" id="nurseinfo">
                                    <h2>Nurse Info</h2>
                                    @include('partials.admin.user.nurse-info', ['nurseInfo' =>  $patient->nurseInfo])
                                </div>
                            @endif

                            @if($patient->hasRole('care-ambassador'))
                                <div role="tabpanel" class="tab-pane" id="careAmbassador">
                                    <h2>Care Ambassador Info</h2>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('hourly_rate', 'Hourly Rate:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hourly_rate', $patient->careAmbassador->hourly_rate, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                            <div class="col-xs-2" style="padding-top: 20px">{!! Form::label('speaks_spanish', 'Spanish Speaking:') !!}</div>
                                            <div class="col-xs-10"style="padding-top: 20px"><input type="checkbox"
                                                                          @if($patient->careAmbassador->speaks_spanish) checked
                                                                          @endif name="speaks_spanish"
                                                                          id="speaks_spanish"></div>
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
                                    {!! Form::hidden('user_id', $patient->id) !!}
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
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