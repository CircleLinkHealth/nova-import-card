<?php
$user_info = array();
?>

@extends('partials.providerUI')

@section('title', 'Patient Demographics')
@section('activity', 'Edit/Modify Care Plan')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    {!! Form::open(array('url' => URL::route('patients.demographics.store', array('patientId' => $patient->ID)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <div class="row" style="margin-top:20px;">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="icon-container col-lg-12">
                @if(isset($patient))
                    @include('wpUsers.patient.careplan.nav')
                @endif
            </div>
            {{-- {!! Form::select('patient_id', array($patient), null, ['class' => 'patient2 form-control']) !!}
            @if(!isset($patient->ID) )
                <div class=" col-lg-8 col-lg-offset-2 alert alert-info">NOTE: Adding a new patient</div>
            @endif
            --}}
            <div class="main-form-container-last col-lg-8 col-lg-offset-2" style="margin-top:20px;">
                <div class="row">
                    @if(isset($patient->ID) )
                        <div class="main-form-title col-lg-12">
                            Edit Patient Profile
                        </div>
                        @include('partials.userheader')
                    @else
                        <div class="main-form-title col-lg-12">
                            Add Patient
                        </div>
                    @endif
                    <div class="">
                        <div class="row">
                            <div class="main-form-block main-form-primary main-form-primary-vertical col-lg-7">
                                <h4 class="form-title">Contact Information</h4>
                                <p><span class="attention">*</span> Required Field</p>
                                <input type=hidden name=user_id value="{{ $patient->ID }}">
                                <input type=hidden name=user_login value="{{ $patient->user_login }}">
                                <input type=hidden name=user_nicename value="{{ $patient->user_nicename }}">
                                <input type=hidden name=display_name value="{{ $patient->display_name }}">
                                <input type=hidden name=role value="{{ $patientRoleId }}">
                                <input type=hidden name=daily_reminder_optin value="Y">
                                <input type=hidden name=daily_reminder_time value="08:00">
                                <input type=hidden name=daily_reminder_areas value="TBD">
                                <input type=hidden name=hospital_reminder_optin value="Y">
                                <input type=hidden name=hospital_reminder_time value="19:00">
                                <input type=hidden name=hospital_reminder_areas value="TBD">
                                <input type=hidden name=qualification
                                       value="<?php /*echo $validation['qualification']['value'];*/ ?>">
                                <input type=hidden name=specialty
                                       value="<?php /*echo $validation['specialty']['value'];*/ ?>">
                                <input type=hidden name=npi_number
                                       value="<?php /*echo $validation['npi_number']['value'];*/ ?>">
                                <div class="row">

                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('first_name') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="first_name">First Name</label>
                                        <input type="text" class="form-control" name="first_name" id="first_name"
                                               placeholder="First Name *"
                                               value="{{ (old('first_name') ? old('first_name') : $patient->first_name) }}">
                                        <span class="help-block">{{ $errors->first('first_name') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('last_name') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="last_name">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" id="last_name"
                                               placeholder="Last Name *"
                                               value="{{ (old('last_name') ? old('last_name') : $patient->last_name) }}">
                                        <span class="help-block">{{ $errors->first('last_name') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('gender') ? 'has-error' : '' }}">
                                        <div class="row">
                                            <div class="col-sm-1 col-lg-3">
                                                <label for="gender">
                                                    Gender <span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="radio-inline">
                                                    <input type="radio" id="radioMale" name="gender"
                                                           value="M" {{ ((old('gender') == 'M') ? 'checked="checked"' : (($patient->gender == 'M') ? 'checked="checked"' : '')) }}>
                                                    <label for="radioMale"><span> </span>Male</label>
                                                </div>
                                                <div class="radio-inline">
                                                    <input type="radio" id="radioFemale" name="gender"
                                                           value="F" {{ ((old('gender') == 'F') ? 'checked="checked"' : (($patient->gender == 'F') ? 'checked="checked"' : '')) }}>
                                                    <label for="radioFemale"><span> </span>Female</label>
                                                </div>
                                                <span class="help-block">{{ $errors->first('gender') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_language') ? 'has-error' : '' }} col-lg-12">
                                        <div class="row">
                                            <div class="col-sm-2 col-lg-3">
                                                <label for="language">Language<span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-10 col-lg-4">
                                                <div class="radio">
                                                    <input type="radio" name="preferred_contact_language"
                                                           id="languageEnglish"
                                                           value="EN" {{ ((old('preferred_contact_language') == 'EN' || !old('preferred_contact_language')) ? 'checked="checked"' : (($patient->preferred_contact_language == 'EN') ? 'checked="checked"' : '')) }}>
                                                    <label for="languageEnglish"><span> </span>English</label>
                                                </div>
                                                <div class="radio radio-v-margin">
                                                    <input type="radio" name="preferred_contact_language"
                                                           id="languageSpanish"
                                                           value="ES" {{ ((old('preferred_contact_language') == 'ES') ? 'checked="checked"' : (($patient->preferred_contact_language == 'ES') ? 'checked="checked"' : '')) }}>
                                                    <label for="languageSpanish"><span> </span>Spanish</label>
                                                </div>
                                                <span class="help-block">{{ $errors->first('preferred_contact_language') }}</span>
                                            </div>
                                            <div class="form-group form-item form-item-spacing col-sm-12 col-lg-5 {{ $errors->first('mrn_number') ? 'has-error' : '' }}">
                                                <label class="sr-only" for="mrn_number">MRN</label>
                                                <input type="text" class="form-control" name="mrn_number"
                                                       id="mrn_number" placeholder="MRN *"
                                                       value="{{ (old('mrn_number') ? old('mrn_number') : ($patient->mrn_number ? $patient->mrn_number : '')) }}">
                                                <span class="help-block">{{ $errors->first('mrn_number') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('birth_date') ? 'has-error' : '' }}">
                                        <label for="birth_date">Date Of Birth<span class="attention">*</span>:</label>
                                        <input id="birth_date" name="birth_date" type="input" class="form-control"
                                               value="{{ (old('birth_date') ? old('birth_date') : ($patient->birth_date ? $patient->birth_date : '01-01-1960')) }}"
                                               data-field="date" data-format="yyyy-MM-dd"/><br/>
                                        <span class="help-block">{{ $errors->first('birth_date') }}</span>
                                        <div id="dtBox"></div>
                                    </div>
                                    <div class="form-item col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('home_phone_number') ? 'has-error' : '' }}">
                                                    <label class="sr-only" for="telephone">Phone</label>
                                                    <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                           class="form-control" name="home_phone_number"
                                                           id="home_phone_number" placeholder="Telephone *"
                                                           value="{{ (old('home_phone_number') ? old('home_phone_number') : ($patient->home_phone_number ? $patient->home_phone_number : '')) }}">
                                                    <span class="help-block">{{ $errors->first('home_phone_number') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('mobile_phone_number') ? 'has-error' : '' }}">
                                                    <label class="sr-only" for="mobile_phone_number">Phone</label>
                                                    <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                           class="form-control" name="mobile_phone_number"
                                                           id="mobile_phone_number" placeholder="Mobile Telephone *"
                                                           value="{{ (old('mobile_phone_number') ? old('mobile_phone_number') : ($patient->mobile_phone_number ? $patient->mobile_phone_number : '')) }}">
                                                    <span class="help-block">{{ $errors->first('mobile_phone_number') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('email') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="lastName">Email Address</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                               placeholder="Email Address"
                                               value="{{ (old('email') ? old('email') : ($patient->user_email ? $patient->user_email : '')) }}">
                                        <span class="help-block">{{ $errors->first('email') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('address') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="address">Street Address</label>
                                        <input type="text" class="form-control" name="address" id="address"
                                               placeholder="Street Address"
                                               value="{{ (old('address') ? old('address') : ($patient->address ? $patient->address : '')) }}">
                                        <span class="help-block">{{ $errors->first('address') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-6 city-input {{ $errors->first('city') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="city">City Name</label>
                                        <input type="text" class="form-control" name="city" id="city"
                                               placeholder="City Name"
                                               value="{{ (old('city') ? old('city') : ($patient->city ? $patient->city : '')) }}">
                                        <span class="help-block">{{ $errors->first('city') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-2 state-selector {{ $errors->first('state') ? 'has-error' : '' }}">
                                        {!! Form::select('state', $states, (old('state') ? old('state') : $patient->state ? $patient->state : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                        <span class="help-block">{{ $errors->first('state') }}</span>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-4 {{ $errors->first('zip') ? 'has-error' : '' }}">
                                        <label class="sr-only" for="zip">Zip Code</label>
                                        <input type="text" class="form-control" name="zip" id="zip"
                                               placeholder="Zip Code"
                                               value="{{ (old('zip') ? old('zip') : ($patient->zip ? $patient->zip : '')) }}">
                                        <span class="help-block">{{ $errors->first('zip') }}</span>
                                    </div>
                                    <div class="form-item col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('agent_name') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="agent_name"
                                                           id="agent_name" placeholder="Agent Name"
                                                           value="{{ (old('agent_name') ? old('agent_name') : ($patient->agent_name ? $patient->agent_name : '')) }}">
                                                    <span class="help-block">{{ $errors->first('agent_name') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('agent_telephone') ? 'has-error' : '' }}">
                                                    <label class="sr-only" for="agent_telephone">Agent Telephone</label>
                                                    <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                           class="form-control" name="agent_telephone"
                                                           id="agent_telephone" placeholder="Agent Telephone"
                                                           value="{{ (old('agent_telephone') ? old('agent_telephone') : ($patient->agent_telephone ? $patient->agent_telephone : '')) }}">
                                                    <span class="help-block">{{ $errors->first('agent_telephone') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-item col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('agent_relationship') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="agent_relationship"
                                                           id="agent_relationship" placeholder="Agent Relationship"
                                                           value="{{ (old('agent_relationship') ? old('agent_relationship') : ($patient->agent_relationship ? $patient->agent_relationship : '')) }}">
                                                    <span class="help-block">{{ $errors->first('agent_relationship') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group {{ $errors->first('agent_email') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="agent_email"
                                                           id="agent_email" placeholder="Agent Email"
                                                           value="{{ (old('agent_email') ? old('agent_email') : ($patient->agent_email ? $patient->agent_email : '')) }}">
                                                    <span class="help-block">{{ $errors->first('agent_email') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="main-form-block main-form-secondary col-lg-5">
                                <h4 class="form-title">Contact Preferences</h4>
                                <div class="row" style=" padding-right: 15px;">
                                        <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_method') ? 'has-error' : '' }}">
                                        @include('partials.patientContactChangeProfile')
                                        </div>

                                        <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_method') ? 'has-error' : '' }}">
                                            <div class="col-sm-6">
                                                <label for="preferred_contact_method" class="contact-method">
                                                    Preferred Contact Method:
                                                </label>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="radio">
                                                    <input type="radio" name="preferred_contact_method"
                                                           id="contactMethodCCT"
                                                           value="CCT" {{ ((old('preferred_contact_method') == 'CCT') ? 'checked="checked"' : (($patient->preferred_contact_method == 'CCT') ? 'checked="checked"' : '')) }}>
                                                    <label for="contactMethodCCT"><span> </span>Care Center</label>
                                                </div>
                                            <!--                                             <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_method" id="contactMethodSMS" value="SMS" {{ ((old('preferred_contact_method') == 'SMS') ? 'checked="checked"' : (($patient->preferred_contact_method == 'SMS') ? 'checked="checked"' : '')) }}>
                                                <label for="contactMethodSMS"><span> </span>SMS</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_method" id="contactMethodApp"  value="APP" {{ (old('preferred_contact_method') == 'APP' ? 'checked="checked"' : (($patient->preferred_contact_method == 'APP') ? 'checked="checked"' : '')) }}>
                                                <label for="contactMethodApp"><span> </span>App</label>
                                            </div> -->
                                            </div>
                                            <span class="help-block">{{ $errors->first('preferred_contact_method') }}</span>
                                        </div>
                                        <div class="form-group form-item  form-item-spacing col-sm-12 {{ $errors->first('timezone') ? 'has-error' : '' }}">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <label for="timezone">Time Zone <span
                                                                class="attention">*</span>:</label>
                                                </div>
                                                <div class="col-sm-12">
                                                    {!! Form::select('timezone', array(
                                    'America/New_York' => 'Eastern Time',
                                    'America/Chicago' => 'Central Time',
                                    'America/Denver' => 'Mountain Time',
                                    'America/Phoenix' => 'Mountain Time (no DST)',
                                    'America/Los_Angeles' => 'Pacific Time',
                                    'America/Anchorage' => 'Alaska Time',
                                    'America/Adak' => 'Hawaii-Aleutian',
                                    'Pacific/Honolulu' => 'Hawaii-Aleutian Time (no DST)',
                                    ), (old('timezone') ? old('timezone') : $patient->timezone ? $patient->timezone : 'America/New_York'), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                                </div>
                                            </div>

                                            <span class="help-block">{{ $errors->first('timezone') }}</span>
                                        </div>
                                        <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('consent_date') ? 'has-error' : '' }}">
                                            <label for="mf-consent_date">Consent Date <span
                                                        class="attention">*</span>:</label>
                                            <input id="consent_date" name="consent_date" class="form-control"
                                                   type="input"
                                                   value="{{ (old('consent_date') ? old('consent_date') : ($patient->consent_date ? $patient->consent_date : '')) }}"
                                                   data-field="date" data-format="yyyy-MM-dd"/><br/>
                                            <span class="help-block">{{ $errors->first('consent_date') }}</span>
                                        </div>
                                        {{--<div class="col-sm-12 text-right">
                                            <span class="btn btn-group  text-right"><a class="btn btn-green btn-sm inline-block" omitsubmit="yes" role="button" target="_Blank" href="https://s3.amazonaws.com/clh-downloads/Circlelink+CCM+Consent+Form.pdf">Download Form</a></span>
                                        </div>--}}

                                        @if(isset($patient->ID) )
                                            @if(($patient->primaryProgram) )
                                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('program_id') ? 'has-error' : '' }} hidden">
                                                    Program:
                                                    <strong>{{ $patient->primaryProgram->display_name }}</strong>
                                                </div>
                                            @endif
                                            <input type=hidden name=program_id value="{{ $programId }}">
                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('program') ? 'has-error' : '' }}">
                                                {!! Form::label('preferred_contact_location', 'Preferred Office Location  *:
    :') !!}
                                                {!! Form::select('preferred_contact_location', $locations, $patient->preferred_contact_location, ['class' => 'form-control select-picker', 'style' => 'width:90;']) !!}
                                            </div>
                                        @else
                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('program_id') ? 'has-error' : '' }}">
                                                {!! Form::label('program_id', 'Program:') !!}
                                                {!! Form::select('program_id', $programs, $patient->program_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}
                                            </div>
                                        @endif

                                        <input type=hidden name=status
                                               value="{{ (old('status') ? old('status') : ($patient->status)) }}">
                                        {{--
                                        <div class="form-group form-item  form-item-spacing col-sm-12 {{ $errors->first('status') ? 'has-error' : '' }}">
                                            <div class="row">
                                                <div class="col-sm-2 col-lg-4">
                                                    <label for="status">Status<span class="attention">*</span>:</label>
                                                </div>
                                                <div class="col-sm-9 col-lg-8 status-buttons">
                                                    <div class="radio">
                                                        <input type="radio" id="statusActive" name="status" value="Active" {{ ((old('status') == 'Active' || !old('status')) ? 'checked="checked"' : (($patient->status == 'SMS') ? 'checked="checked"' : '')) }}>
                                                        <label for="statusActive"><span> </span>Active</label>
                                                    </div>
                                                    <div class="radio radio-v-margin">
                                                        <input type="radio" id="statusInactive" name="status"  value="Inactive" {{ (old('status') == 'Inactive' ? 'checked="checked"' : (($patient->status == 'SMS') ? 'checked="checked"' : '')) }}>
                                                        <label for="statusInactive"><span> </span>Inactive</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="help-block">{{ $errors->first('status') }}</span>
                                        </div>
                                        --}}

                                        <div class="form-group form-item form-item-spacing col-sm-12">
                                            <div class="row">
                                                <div class="col-lg-4">{!! Form::label('ccm_status', 'CCM Enrollment: ') !!}</div>
                                                <div class="col-lg-8">{!! Form::select('ccm_status', array('paused' => 'Paused', 'enrolled' => 'Enrolled', 'withdrawn' => 'Withdrawn'), $patient->ccm_status, ['class' => 'form-control selectpicker', 'style' => 'width:100%;']) !!}</div>
                                            </div>
                                        </div>

                                        <div class="form-group form-item form-item-spacing col-sm-12 hidden">
                                            <div class="row">
                                                <div class="col-lg-4">{!! Form::label('care_plan_id', 'Care Plan: ') !!}</div>
                                                <div class="col-lg-8">{!! Form::select('care_plan_id', $carePlans, $patient->care_plan_id, ['class' => 'form-control selectpicker', 'style' => 'width:100%;']) !!}</div>
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                        <br>

                                        @if(! $insurancePolicies->isEmpty())

                                            <div id="insurance-policies"
                                                 class="form-group form-item form-item-spacing col-sm-12">

                                                <h4 id="policies-title" class="form-title">Insurance Policies</h4>

                                                <?php $counter = 0; ?>

                                                @foreach($insurancePolicies as $insurance)

                                                    <div id="policy-grp-{{$counter++}}">
                                                        <button
                                                                type="button"
                                                                class="full-width btn-default borderless md-line-height"
                                                                data-toggle="collapse"
                                                                data-target="#insurance-{{ $counter }}">
                                                            <span class="pull-left">{{ $insurance->name }}</span>
                                                            <span class="glyphicon glyphicon-pencil pull-right glow"
                                                                  aria-hidden="true"></span>
                                                        </button>

                                                        <div id="insurance-{{ $counter }}"
                                                             class="collapse md-line-height text-right">

                                                            @if(!empty($insurance->type))
                                                                {{ $insurance->type }}
                                                                @else
                                                                {{ 'Insurance type is not available' }}
                                                                @endif


                                                            @if(!empty($insurance->policy_id))
                                                                    / {{ $insurance->policy_id }}
                                                            @else
                                                                    {{ 'Policy ID is not available' }}
                                                            @endif

                                                            <br>

                                                            @if(! $insurance->approved)

                                                                <div class="radio-inline">
                                                                    <input id="approve-{{ $counter }}"
                                                                           name="insurance[{{ $insurance->id }}]"
                                                                           value="1" type="radio">
                                                                    <label for="approve-{{ $counter }}"><span></span>Approve</label>
                                                                </div>

                                                            @endif

                                                            <div class="radio-inline">
                                                                <input id="delete-{{ $counter }}"
                                                                       name="insurance[{{ $insurance->id }}]"
                                                                       value="0" type="radio">
                                                                <label for="delete-{{ $counter }}"><span></span>Delete</label>
                                                            </div>

                                                            <br><br>
                                                        </div>
                                                    </div>
                                                @endforeach


                                            </div>

                                        @endif


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('wpUsers.patient.careplan.footer')
            <br/><br/>

            @if(isset($_GET['scrollTo']))
                <script>
                    $(function () {
                        // Handler for .ready() called.
                        $('html, body').animate({
                            scrollTop: $("#{{ $_GET['scrollTo'] }}").offset().top
                        }, 'slow');

                        $('#insurance-name').focus();

                        $('#policies-title').css('border-left', '15px solid #47beab')
                                .css('padding-left', '5px');

                        $('.glow').addClass('animated flash');

                    });
                </script>
    @endif

    {{--Added this to allow for testing, since submit is done via js--}}
    @if(app()->environment('testing'))
        {!! Form::submit('TestSubmit', ['id' => 'unit-test-submit']) !!}
    @endif

    {!! Form::close() !!}
@stop
