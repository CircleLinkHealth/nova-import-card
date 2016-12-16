<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    {!! Form::open(array('url' => URL::route('patients.demographics.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <div class="row">
        <div class="icon-container col-lg-12">
            @if(isset($patient->id) && !$new_user )
                @include('wpUsers.patient.careplan.nav')
            @endif
        </div>
    </div>
    <div class="row" style="margin-top:60px;">
        @if(!isset($patient->id) && !$new_user )
            <div class=" col-lg-8 col-lg-offset-2 alert alert-info">NOTE: Adding a new patient</div>
        @endif
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title">
                    @if(isset($patient->id) && !$new_user )
                        <div class="main-form-title col-lg-12">
                            Edit Patient
                        </div>
                    @else
                        <div class="main-form-title col-lg-12">
                            Add Patient
                        </div>
                    @endif
                </div>
                <div class="main-form-block main-form-horizontal col-md-12">
                    <div class="row">
                        <div class="main-form-block main-form-primary main-form-primary-vertical col-lg-7">
                            <h4 class="form-title">Contact Information</h4>
                            <p><span class="attention">*</span> Required Field</p>
                            <input type=hidden name=user_id value="{{ $patient->id }}">
                            <input type=hidden name=program_id value="{{ $programId }}">
                            <input type=hidden name=display_name value="{{ $patient->display_name }}">
                            <input type=hidden name=role value="{{ $patientRoleId }}">
                            <input type=hidden name=daily_reminder_optin value="Y">
                            <input type=hidden name=daily_reminder_time value="08:00">
                            <input type=hidden name=daily_reminder_areas value="TBD">
                            <input type=hidden name=hospital_reminder_optin value="Y">
                            <input type=hidden name=hospital_reminder_time value="19:00">
                            <input type=hidden name=hospital_reminder_areas value="TBD">
                            <input type=hidden name=qualification value="<?php /*echo $validation['qualification']['value'];*/ ?>">
                            <input type=hidden name=specialty value="<?php /*echo $validation['specialty']['value'];*/ ?>">
                            <input type=hidden name=npi_number value="<?php /*echo $validation['npi_number']['value'];*/ ?>">
                            <div class="row">

                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('first_name') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="first_name">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name *" value="{{ (old('first_name') ? old('first_name') : $patient->first_name) }}">
                                    <span class="help-block">{{ $errors->first('first_name') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('last_name') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="last_name">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name *"value="{{ (old('last_name') ? old('last_name') : $patient->last_name) }}">
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
                                                <input type="radio" id="radioMale" name="gender" value="M" {{ ((old('gender') == 'M') ? 'checked="checked"' : ($userConfig['gender'] == 'M') ? 'checked="checked"' : '') }}>
                                                <label for="radioMale"><span> </span>Male</label>
                                            </div>
                                            <div class="radio-inline">
                                                <input type="radio" id="radioFemale" name="gender" value="F" {{ ((old('gender') == 'F') ? 'checked="checked"' : ($userConfig['gender'] == 'F') ? 'checked="checked"' : '') }}>
                                                <label for="radioFemale"><span> </span>Female</label>
                                            </div>
                                            <span class="help-block">{{ $errors->first('gender') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_language') ? 'has-error' : '' }} col-lg-12">
                                    <div class="row">
                                        <div class="col-sm-2 col-lg-3">
                                            <label for="language" >Language<span class="attention">*</span>:
                                            </label>
                                        </div>
                                        <div class="col-sm-10 col-lg-4">
                                            <div class="radio">
                                                <input type="radio" name="preferred_contact_language" id="languageEnglish" value="EN" {{ ((old('preferred_contact_language') == 'EN' || !old('preferred_contact_language')) ? 'checked="checked"' : ($userConfig['preferred_contact_language'] == 'EN') ? 'checked="checked"' : '') }}>
                                                <label for="languageEnglish"><span> </span>English</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_language" id="languageSpanish"  value="ES" {{ ((old('preferred_contact_language') == 'ES') ? 'checked="checked"' : ($userConfig['preferred_contact_language'] == 'ES') ? 'checked="checked"' : '') }}>
                                                <label for="languageSpanish"><span> </span>Spanish</label>
                                            </div>
                                            <span class="help-block">{{ $errors->first('preferred_contact_language') }}</span>
                                        </div>
                                        <div class="form-group form-item form-item-spacing col-sm-12 col-lg-5 {{ $errors->first('mrn_number') ? 'has-error' : '' }}">
                                            <label class="sr-only" for="mrn_number">MRN</label>
                                            <input type="text" class="form-control" name="mrn_number" id="mrn_number" placeholder="MRN *" value="{{ (old('mrn_number') ? old('mrn_number') : $userConfig['mrn_number'] ? $userConfig['mrn_number'] : '') }}">
                                                <span class="help-block">{{ $errors->first('mrn_number') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('birth_date') ? 'has-error' : '' }}">
                                    <label for="birth_date">Date Of Birth<span class="attention">*</span>:</label>
                                    <input id="birth_date" name="birth_date" type="input" class="form-control" value="{{ (old('birth_date') ? old('birth_date') : $userConfig['birth_date'] ? $userConfig['birth_date'] : '') }}"/><br />
                                    <span class="help-block">{{ $errors->first('birth_date') }}</span>
                                </div>
                                <div class="form-item col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group {{ $errors->first('study_phone_number') ? 'has-error' : '' }}">
                                                <label class="sr-only" for="telephone">Phone</label>
                                                <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}' class="form-control" name="study_phone_number" id="study_phone_number" placeholder="Telephone *" value="{{ (old('study_phone_number') ? old('study_phone_number') : $userConfig['study_phone_number'] ? $userConfig['study_phone_number'] : '') }}">
                                                <span class="help-block">{{ $errors->first('study_phone_number') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('email') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="lastName">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email Address" value="{{ (old('email') ? old('email') : $userConfig['email'] ? $userConfig['email'] : '') }}">
                                    <span class="help-block">{{ $errors->first('email') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('address') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="address">Street Address</label>
                                    <input type="text" class="form-control" name="address" id="address" placeholder="Street Address" value="{{ (old('address') ? old('address') : $userConfig['address'] ? $userConfig['address'] : '') }}">
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-6 city-input {{ $errors->first('city') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="city">City Name</label>
                                    <input type="text" class="form-control" name="city" id="city" placeholder="City Name" value="{{ (old('city') ? old('city') : $userConfig['city'] ? $userConfig['city'] : '') }}">
                                    <span class="help-block">{{ $errors->first('city') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-2 state-selector {{ $errors->first('state') ? 'has-error' : '' }}">
                                    {!! Form::select('state', $states, (old('state') ? old('state') : $userConfig['state'] ? $userConfig['state'] : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                    <span class="help-block">{{ $errors->first('state') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-4 {{ $errors->first('zip') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="zip">Zip Code</label>
                                    <input type="text" class="form-control" name="zip" id="zip" placeholder="Zip Code" value="{{ (old('zip') ? old('zip') : $userConfig['zip'] ? $userConfig['zip'] : '') }}">
                                    <span class="help-block">{{ $errors->first('zip') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="main-form-block main-form-secondary col-lg-5">
                            <h4 class="form-title">Contact Preferences</h4>
                            <div class="row">
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_time') ? 'has-error' : '' }}">
                                    <label for="mf-contact">Preferred Contact Time <span class="attention">*</span>:</label>
                                    <input id="preferred_contact_time" class="form-control" name="preferred_contact_time" type="input" value="{{ (old('preferred_contact_time') ? old('preferred_contact_time') : $userConfig['preferred_contact_time'] ? $userConfig['preferred_contact_time'] : '') }}"/><br />
                                    (Should be between 4pm and 9pm)
                                    <span class="help-block">{{ $errors->first('preferred_contact_time') }}</span>
                                </div>

                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_method') ? 'has-error' : '' }}">
                                    <div class="row">
                                        <div class="col-sm-2 col-lg-7">
                                            <label for="preferred_contact_method" class="text-right contact-method">
                                                Preferred Contact Method:
                                            </label>
                                        </div>
                                        <div class="col-sm-10 col-lg-5 contact-method">
                                            <div class="radio">
                                                <input type="radio" name="preferred_contact_method" id="contactMethodSMS" value="SMS" {{ ((old('preferred_contact_method') == 'SMS' || !old('preferred_contact_method')) ? 'checked="checked"' : ($userConfig['preferred_contact_method'] == 'SMS') ? 'checked="checked"' : '') }}>
                                                <label for="contactMethodSMS"><span> </span>SMS</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_method" id="contactMethodApp"  value="APP" {{ (old('preferred_contact_method') == 'APP' ? 'checked="checked"' : ($userConfig['preferred_contact_method'] == 'APP') ? 'checked="checked"' : '') }}>
                                                <label for="contactMethodApp"><span> </span>App</label>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('preferred_contact_method') }}</span>
                                </div>
                                <div class="form-group form-item  form-item-spacing col-sm-12 {{ $errors->first('timezone') ? 'has-error' : '' }}">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="timezone">Time Zone <span class="attention">*</span>:</label>
                                        </div>
                                        <div class="col-sm-12">
                                                {!! Form::select('timezone', $timezones, (old('timezone') ? old('timezone') : $userConfig['preferred_contact_timezone'] ? $userConfig['preferred_contact_timezone'] : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('timezone') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('consent_date') ? 'has-error' : '' }}">
                                    <label for="mf-consent_date">Consent Date <span class="attention">*</span>:</label>
                                    <input id="consent_date" name="consent_date" class="form-control" type="input" value="{{ (old('consent_date') ? old('consent_date') : $userConfig['consent_date'] ? $userConfig['consent_date'] : '') }}"/><br />
                                    <span class="help-block">{{ $errors->first('consent_date') }}</span>
                                </div>
                                <div class="col-sm-12 text-right">
                                    <span class="btn btn-group  text-right"><a class="btn btn-green btn-sm inline-block" omitsubmit="yes" role="button" target="_Blank" href="https://s3.amazonaws.com/clh-downloads/Circlelink+CCM+Consent+Form.pdf">Download Form</a></span>
                                </div>

                                <div class="form-group form-item  form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_location') ? 'has-error' : '' }}">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="timezone">Time Zone <span class="attention">*</span>:</label>
                                        </div>
                                        <div class="col-sm-12">
                                            {!! Form::select('preferred_contact_location', $locations, (old('preferred_contact_location') ? old('preferred_contact_location') : $userConfig['preferred_contact_location'] ? $userConfig['preferred_contact_location'] : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('preferred_contact_location') }}</span>
                                </div>

                                <div class="form-group form-item form-item-spacing col-lg-7 col-sm-12 <?php //echo $validatlidation['status']['class']; ?>">
                                    <div class="row">
                                        <div class="col-sm-2 col-lg-4">
                                            <label for="status">Status<span class="attention">*</span>:</label>
                                        </div>
                                        <div class="col-sm-9 col-lg-8 status-buttons">
                                            <div class="radio">
                                                <input type="radio" id="statusActive" name="status" value="Active" {{ ((old('status') == 'Active' || !old('status')) ? 'checked="checked"' : ($userConfig['status'] == 'SMS') ? 'checked="checked"' : '') }}>
                                                <label for="statusActive"><span> </span>Active</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" id="statusInactive" name="status"  value="Inactive" {{ (old('status') == 'Inactive' ? 'checked="checked"' : ($userConfig['status'] == 'SMS') ? 'checked="checked"' : '') }}>
                                                <label for="statusInactive"><span> </span>Inactive</label>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('status') }}</span>
                                </div>


                                <div class="form-group form-item form-item-spacing col-lg-7 col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-2 col-lg-4">{!! Form::label('ccm_status', 'CCM Status: '.$userMeta['ccm_status']) !!}</div>
                                        <div class="col-sm-9 col-lg-8">{!! Form::select('ccm_status', array('paused' => 'paused', 'enrolled' => 'enrolled', 'withdrawn' => 'withdrawn'), $userMeta['ccm_status'], ['class' => 'form-control selectpicker', 'style' => 'width:100%;']) !!}</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @include('wpUsers.patient.careplan.footer')
                <br /><br />
                </form>
            </div>
        </div>
    </div>
@stop
