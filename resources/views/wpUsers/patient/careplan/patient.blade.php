<?php

$user_info = [];
?>

@extends('partials.providerUI')

@section('title', 'Patient Demographics')
@section('activity', 'Edit/Modify Care Plan')

@section('content')
    {!! Form::open(['url' => optional($patient)->id ? route('patient.demographics.update', [$patient->id]) : route('patient.demographics.store'), 'method' => optional($patient)->id ? 'patch' : 'post', 'class' => 'form-horizontal', 'id' => 'ucpForm']) !!}
    <div class="row" style="margin-top:20px;margin-bottom:20px;">
        <div class="col-lg-10 col-lg-offset-1 col-xs-12 col-xs-offset-0">
            <div class="main-form-container-last col-lg-8 col-lg-offset-2" style="margin-top:20px;margin-bottom:20px;">
                <div class="row"><!-- no-overflow-->
                    @if(isset($patient->id) )
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
                            <div class="col-lg-12 main-form-primary-horizontal" style="border-bottom: 0px">
                                <div class="row">
                                    <div class="main-form-block main-form-primary main-form-primary-vertical col-lg-7">
                                        <h4 class="form-title">Contact Information</h4>
                                        <p><span class="attention">*</span> Required Field</p>
                                        <input type=hidden name=user_id value="{{ $patient->id }}">
                                        <input type=hidden name=username value="{{ $patient->username }}">
                                        <input type=hidden name=display_name value="{{ $patient->display_name }}">
                                        <input type=hidden name=role value="{{ $patientRoleId }}">
                                        <input type=hidden name=daily_reminder_optin value="Y">
                                        <input type=hidden name=daily_reminder_time value="08:00">
                                        <input type=hidden name=daily_reminder_areas value="TBD">
                                        <input type=hidden name=hospital_reminder_optin value="Y">
                                        <input type=hidden name=hospital_reminder_time value="19:00">
                                        <input type=hidden name=hospital_reminder_areas value="TBD">
                                        <input type=hidden name=qualification value="">
                                        <input type=hidden name=specialty"
                                               value="<?php /*echo $validation['specialty']['value'];*/ ?>">
                                        <input type=hidden name=npi_number"
                                               value="<?php /*echo $validation['npi_number']['value'];*/ ?>">
                                        <div class="row">

                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('first_name') ? 'has-error' : '' }}">
                                                <label class="sr-only" for="first_name">First Name</label>
                                                <input type="text" class="form-control" name="first_name"
                                                       id="first_name"
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
                                                            <input dusk="male-gender" type="radio" id="radioMale"
                                                                   name="gender"
                                                                   value="M" {{ ((old('gender') == 'M') ? 'checked="checked"' : (($patient->getGender() == 'M') ? 'checked="checked"' : '')) }}>
                                                            <label for="radioMale"><span> </span>Male</label>
                                                        </div>
                                                        <div class="radio-inline">
                                                            <input dusk="female-gender" type="radio" id="radioFemale"
                                                                   name="gender"
                                                                   value="F" {{ ((old('gender') == 'F') ? 'checked="checked"' : (($patient->getGender() == 'F') ? 'checked="checked"' : '')) }}>
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
                                                                   value="EN" {{ ((old('preferred_contact_language') == 'EN' || !old('preferred_contact_language')) ? 'checked="checked"' : (($patient->getPreferredContactLanguage() == 'EN') ? 'checked="checked"' : '')) }}>
                                                            <label for="languageEnglish"><span> </span>English</label>
                                                        </div>
                                                        <div class="radio radio-v-margin">
                                                            <input type="radio" name="preferred_contact_language"
                                                                   id="languageSpanish"
                                                                   value="ES" {{ ((old('preferred_contact_language') == 'ES') ? 'checked="checked"' : (($patient->getPreferredContactLanguage() == 'ES') ? 'checked="checked"' : '')) }}>
                                                            <label for="languageSpanish"><span> </span>Spanish</label>
                                                        </div>
                                                        <span class="help-block">{{ $errors->first('preferred_contact_language') }}</span>
                                                    </div>
                                                    <div class="form-group form-item form-item-spacing col-sm-12 col-lg-5 {{ $errors->first('mrn_number') ? 'has-error' : '' }}">
                                                        <label class="sr-only" for="mrn_number">MRN</label>
                                                        <input type="text" class="form-control" name="mrn_number"
                                                               id="mrn_number" placeholder="MRN *"
                                                               value="{{ (old('mrn_number') ? old('mrn_number') : ($patient->getMRN() ? $patient->getMRN() : '')) }}">
                                                        <span class="help-block">{{ $errors->first('mrn_number') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('birth_date') ? 'has-error' : '' }}">
                                                <label for="birth_date">Date Of Birth<span
                                                            class="attention">*</span>:</label>
                                                <v-datepicker name="birth_date" class="selectpickerX form-control"
                                                              format="yyyy-MM-dd"
                                                              placeholder="YYYY-MM-DD"
                                                              value="{{ $patient->resolveTimezoneToGMT(old('birth_date') ? old('birth_date') : ($patient->getBirthDate() ? $patient->getBirthDate() : '1960-01-01')) }}"
                                                              required></v-datepicker>
                                                <br/>
                                                <span class="help-block">{{ $errors->first('birth_date') }}</span>
                                            </div>
                                            <div class="form-item col-sm-12">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->first('home_phone_number') ? 'has-error' : '' }}">
                                                            <label class="sr-only" for="telephone">Phone</label>
                                                            <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                                   class="form-control" name="home_phone_number"
                                                                   id="home_phone_number" placeholder="Telephone *"
                                                                   title="Please write a phone number in the format 123-345-7890"
                                                                   value="{{ (old('home_phone_number') ? old('home_phone_number') : ($patient->getHomePhoneNumber() ? (new App\CLH\Helpers\StringManipulation())->formatPhoneNumber($patient->getHomePhoneNumber()) : '')) }}">
                                                            <span class="help-block">{{ $errors->first('home_phone_number') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->first('mobile_phone_number') ? 'has-error' : '' }}">
                                                            <label class="sr-only"
                                                                   for="mobile_phone_number">Phone</label>
                                                            <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                                   class="form-control" name="mobile_phone_number"
                                                                   id="mobile_phone_number"
                                                                   placeholder="Mobile Telephone *"
                                                                   title="Please write a phone number in the format 123-345-7890"
                                                                   value="{{ (old('mobile_phone_number') ? old('mobile_phone_number') : ($patient->getMobilePhoneNumber() ? (new App\CLH\Helpers\StringManipulation())->formatPhoneNumber($patient->getMobilePhoneNumber()) : '')) }}">
                                                            <span class="help-block">{{ $errors->first('mobile_phone_number') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('email') ? 'has-error' : '' }}">
                                                <label class="sr-only" for="lastName">Email Address</label>
                                                <input type="email" class="form-control" name="email" id="email"
                                                       placeholder="Email Address"
                                                       value="{{ (old('email') ? old('email') : ($patient->email ? $patient->email : '')) }}">
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
                                            <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('state') ? 'has-error' : '' }}">
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
                                                                   value="{{ (old('agent_name') ? old('agent_name') : ($patient->getAgentName() ? $patient->getAgentName() : '')) }}">
                                                            <span class="help-block">{{ $errors->first('agent_name') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->first('agent_telephone') ? 'has-error' : '' }}">
                                                            <label class="sr-only" for="agent_telephone">Agent
                                                                Telephone</label>
                                                            <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}'
                                                                   class="form-control" name="agent_telephone"
                                                                   id="agent_telephone" placeholder="Agent Telephone"
                                                                   value="{{ (old('agent_telephone') ? old('agent_telephone') : ($patient->getAgentTelephone() ? $patient->getAgentTelephone() : '')) }}">
                                                            <span class="help-block">{{ $errors->first('agent_telephone') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-item col-sm-12">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->first('agent_relationship') ? 'has-error' : '' }}">
                                                            <input type="text" class="form-control"
                                                                   name="agent_relationship"
                                                                   id="agent_relationship"
                                                                   placeholder="Agent Relationship"
                                                                   value="{{ (old('agent_relationship') ? old('agent_relationship') : ($patient->getAgentRelationship() ? $patient->getAgentRelationship() : '')) }}">
                                                            <span class="help-block">{{ $errors->first('agent_relationship') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group {{ $errors->first('agent_email') ? 'has-error' : '' }}">
                                                            <input type="text" class="form-control" name="agent_email"
                                                                   id="agent_email" placeholder="Agent Email"
                                                                   value="{{ (old('agent_email') ? old('agent_email') : ($patient->getAgentEmail() ? $patient->getAgentEmail() : '')) }}">
                                                            <span class="help-block">{{ $errors->first('agent_email') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="main-form-block main-form-secondary col-lg-5"
                                         style="padding-bottom: 0px">
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
                                                               value="CCT" {{ ((old('preferred_contact_method') == 'CCT') ? 'checked="checked"' : (($patient->getPreferredContactMethod() == 'CCT') ? 'checked="checked"' : '')) }}>
                                                        <label for="contactMethodCCT"><span> </span>Care Center</label>
                                                    </div>

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
                                                        {!! Form::select('timezone', timezones(), (old('timezone') ? old('timezone') : $patient->timezone ? $patient->timezone : 'America/New_York'), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                                    </div>
                                                </div>

                                                <span class="help-block">{{ $errors->first('timezone') }}</span>
                                            </div>
                                            @if(isset($patient->id))
                                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('consent_date') ? 'has-error' : '' }}">
                                                    <label for="mf-consent_date">Consent Date <span
                                                                class="attention">*</span>:</label>
                                                    <v-datepicker name="consent_date" class="selectpickerX form-control"
                                                                  format="yyyy-MM-dd"
                                                                  placeholder="YYYY-MM-DD" pattern="\d{4}\-\d{2}\-\d{2}"
                                                                  value="{{ $patient->resolveTimezoneToGMT($patient->getConsentDate()) }}"
                                                                  required></v-datepicker>
                                                    <br/>
                                                    <span class="help-block">{{ $errors->first('consent_date') }}</span>
                                                </div>
                                            @else
                                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('consent_date') ? 'has-error' : '' }}">
                                                    <label for="mf-consent_date">Consent Date <span
                                                                class="attention">*</span>:</label>
                                                    <v-datepicker name="consent_date" class="selectpickerX form-control"
                                                                  format="yyyy-MM-dd"
                                                                  placeholder="YYYY-MM-DD" pattern="\d{4}\-\d{2}\-\d{2}"
                                                                  value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                                                  required></v-datepicker>
                                                    <br/>
                                                    <span class="help-block">{{ $errors->first('consent_date') }}</span>
                                                </div>
                                            @endif

                                            @if(isset($patient->id) )
                                                @if(($patient->primaryPractice) )
                                                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('program_id') ? 'has-error' : '' }} hidden">
                                                        Program:
                                                        <strong>{{ $patient->primaryPractice->display_name }}</strong>
                                                    </div>
                                                @endif
                                                <input type=hidden name=program_id value="{{ $programId }}">
                                                <div class="form-group form-item form-item-spacing col-lg-12 col-sm-12 col-xs-12 {{ $errors->first('program') ? 'has-error' : '' }}">
                                                    {!! Form::label('preferred_contact_location', 'Preferred Office Location  *:
        :') !!}
                                                    {!! Form::select('preferred_contact_location', $locations, $patient->getPreferredContactLocation(), ['class' => 'form-control select-picker'/*, 'style' => 'width:90;'*/]) !!}
                                                </div>
                                            @else
                                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('program_id') ? 'has-error' : '' }}">
                                                    {!! Form::label('program_id', 'Program:') !!}
                                                    {!! Form::select('program_id', $programs, $patient->program_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}
                                                </div>
                                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('provider_id') ? 'has-error' : '' }}">
                                                    {!! Form::label('provider_id', 'Billing Provider:') !!}
                                                    {!! Form::select('provider_id', $billingProviders, null, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}
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
                                            @endif

                                            <input type=hidden name=status
                                                   value="{{ (old('status') ? old('status') : ($patient->status)) }}">

                                            @if(auth()->user()->isAdmin() || auth()->user()->hasRole('provider'))
                                                <div class="form-group form-item form-item-spacing col-sm-12">
                                                    <div class="row">
                                                        <div class="col-lg-4">{!! Form::label('ccm_status', 'CCM Enrollment: ') !!}</div>
                                                        <div class="col-lg-8">{!! Form::select('ccm_status', [ CircleLinkHealth\Customer\Entities\Patient::PAUSED => 'Paused', CircleLinkHealth\Customer\Entities\Patient::ENROLLED => 'Enrolled', CircleLinkHealth\Customer\Entities\Patient::WITHDRAWN => 'Withdrawn', CircleLinkHealth\Customer\Entities\Patient::UNREACHABLE => 'Unreachable' ], $patient->getCcmStatus(), ['class' => 'form-control selectpicker', 'style' => 'width:100%;']) !!}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="form-group form-item form-item-spacing col-sm-12">
                                                    <div class="row">
                                                        <div class="col-lg-4">{!! Form::label('ccm_status', 'CCM Enrollment: ' . ($patient->getCcmStatus() == '' ? 'enrolled' : ucfirst($patient->getCcmStatus()))) !!}</div>
                                                        <div class="col-lg-8">
                                                            <input type="hidden"
                                                                   value="{{ $patient->getCcmStatus() == '' ? 'enrolled' : $patient->getCcmStatus() }}"
                                                                   name="ccm_status">
                                                        </div>

                                                    </div>
                                                </div>
                                            @endif


                                            <br>
                                            <br>
                                            <br>

                                            @if(! $insurancePolicies->isEmpty())
                                                @include('partials.cpm-models.insurance')
                                            @endif


                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 text-center" style="margin-top: -24px">
                                <hr style="border-top: 3px solid #50b2e2;">
                            </div>
                            <div class="main-form-block main-form-secondary col-lg-12 text-center">
                                <button class="btn btn-primary">Save Profile</button>
                                <a href="{{ route('patients.dashboard') }}" omitsubmit="true" class="btn btn-warning">Cancel</a>
                            </div>
                        </div>
                        @push('styles')
                            <style>
                                .no-overflow {
                                    overflow: hidden;
                                }
                            </style>
                        @endpush
                    </div>
                </div>
            </div>
            <div class="top-20"></div>
        </div>
        @push('styles')
            <style>
                .top-20 {
                    margin-top: 20px;
                }
            </style>
        @endpush
        <br><br><br><br>

        @if(isset($_GET['scrollTo']))
            @push('scripts')
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
            @endpush

    </div>
    @endif
    </div>

    {!! Form::close() !!}
@endsection