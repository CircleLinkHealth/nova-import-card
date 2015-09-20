@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">


    <?php
        $user_info = array();
        $new_user = false;
    ?>

    {!! Form::open(array('url' => URL::route('patient.careplan.save', array('programId' => $program->blog_id)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <div class="container">
        <section class="main-form">
            <div class="row">
                <?php //include('patient-nav-cp.php'); ?>
                <div class="">
                    <div class="row">
                        <div class="icon-container col-lg-12">
                            @if(isset($patient) && !$new_user )
                                @include('wpUsers.patient.careplan.nav')
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="icon-container col-lg-12">&nbsp;
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2">
                    <div class="row">
                        @if(isset($patient) && !$new_user )
                        <div class="main-form-title col-lg-12">
                            Edit Patient
                        </div>
                        @else
                        <div class="main-form-title col-lg-12">
                            Add Patient
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2">
                    <div class="row"><?php /*
                        $header = false;
                        if($user_info && !$new_user ) $header = true;
                        ?><?php if($user_info && !$new_user) { echo display_user_summary_header($user_info, $header); } */ ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="main-form-container-last col-lg-8 col-lg-offset-2">
                    <div class="row">
                        <div class="main-form-block main-form-primary main-form-primary-vertical col-lg-7">
                            <h4 class="form-title">Contact Information</h4>
                            <p><span class="attention">*</span> Required Field</p>
                            <input type=hidden name=user_id value="{{ $patient->ID }}">
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

                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('firstName') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="firstName">First Name</label>
                                    <input type="text" class="form-control" name="firstName" id="firstName" placeholder="First Name *" value="{{ (old('firstName') ? old('firstName') : '') }}">
                                    <span class="help-block">{{ $errors->first('firstName') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('lastName') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="lastName">Last Name</label>
                                    <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Last Name *"value="{{ (old('lastName') ? old('lastName') : '') }}">
                                    <span class="help-block">{{ $errors->first('lastName') }}</span>
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
                                                <input type="radio" id="radioMale" name="gender" value="M" {{ ((old('gender') == 'M') ? 'checked="checked"' : '') }}>
                                                <label for="radioMale"><span> </span>Male</label>
                                            </div>
                                            <div class="radio-inline">
                                                <input type="radio" id="radioFemale" name="gender" value="F" {{ ((old('gender') == 'F') ? 'checked="checked"' : '') }}>
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
                                                <input type="radio" name="preferred_contact_language" id="languageEnglish" value="EN" {{ ((old('preferred_contact_language') == 'EN' || !old('preferred_contact_language')) ? 'checked="checked"' : '') }}>
                                                <label for="languageEnglish"><span> </span>English</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_language" id="languageSpanish"  value="ES" {{ ((old('preferred_contact_language') == 'EN') ? 'checked="checked"' : '') }}>
                                                <label for="languageSpanish"><span> </span>Spanish</label>
                                            </div>
                                            <span class="help-block">{{ $errors->first('preferred_contact_language') }}</span>
                                        </div>
                                        <div class="form-group form-item form-item-spacing col-sm-12 col-lg-5 {{ $errors->first('mrn_number') ? 'has-error' : '' }}">
                                            <label class="sr-only" for="mrn_number">MRN</label>
                                            <input type="text" class="form-control" name="mrn_number" id="mrn_number" placeholder="MRN *" value="{{ (old('mrn_number') ? old('mrn_number') : '') }}">
                                                <span class="help-block">{{ $errors->first('mrn_number') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('birth_date') ? 'has-error' : '' }}">
                                    <label for="birth_date">Date Of Birth<span class="attention">*</span>:</label>
                                    <input id="birth_date" name="birth_date" type="input" class="form-control" value="{{ (old('birth_date') ? old('birth_date') : '') }}"/><br />
                                    <span class="help-block">{{ $errors->first('birth_date') }}</span>
                                </div>
                                <div class="form-item col-sm-12">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group {{ $errors->first('telephone') ? 'has-error' : '' }}">
                                                <label class="sr-only" for="telephone">Telephone</label>
                                                <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}' class="form-control" name="telephone" id="telephone" placeholder="Telephone *" value="{{ (old('telephone') ? old('telephone') : '') }}">
                                                <span class="help-block">{{ $errors->first('telephone') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('email') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="lastName">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Email Address" value="{{ (old('email') ? old('email') : '') }}">
                                    <span class="help-block">{{ $errors->first('email') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('address') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="address">Street Address</label>
                                    <input type="text" class="form-control" name="address" id="address" placeholder="Street Address" value="{{ (old('address') ? old('address') : '') }}">
                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-6 city-input {{ $errors->first('city') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="city">City Name</label>
                                    <input type="text" class="form-control" name="city" id="city" placeholder="City Name" value="{{ (old('city') ? old('city') : '') }}">
                                    <span class="help-block">{{ $errors->first('city') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-2 state-selector {{ $errors->first('state') ? 'has-error' : '' }}">
                                    <select name="state" class="selectpicker" data-header="State*" data-width="90px" data-title="State" value="{{ (old('state') ? old('state') : '') }}">
                                        <option value="">State</option>
                                        <?php //showOptionsDrop($states_arr, $validation['state']['value'], true); ?>
                                    </select>
                                    <span class="help-block">{{ $errors->first('state') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-4 {{ $errors->first('zip') ? 'has-error' : '' }}">
                                    <label class="sr-only" for="zip">Zip Code</label>
                                    <input type="text" class="form-control" name="zip" id="zip" placeholder="Zip Code" value="{{ (old('zip') ? old('zip') : '') }}">
                                    <span class="help-block">{{ $errors->first('zip') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="main-form-block main-form-secondary col-lg-5">
                            <h4 class="form-title">Contact Preferences</h4>
                            <div class="row">
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('preferred_contact_time') ? 'has-error' : '' }}">
                                    <label for="mf-contact">Preferred Contact Time <span class="attention">*</span>:</label>
                                    <input id="preferred_contact_time" class="form-control" name="preferred_contact_time" type="input" value="{{ (old('preferred_contact_time') ? old('preferred_contact_time') : '') }}"/><br />
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
                                                <input type="radio" name="preferred_contact_method" id="contactMethodSMS" value="SMS" {{ ((old('preferred_contact_method') == 'SMS' || !old('preferred_contact_method')) ? 'checked="checked"' : '') }}>
                                                <label for="contactMethodSMS"><span> </span>SMS</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" name="preferred_contact_method" id="contactMethodApp"  value="APP" {{ (old('preferred_contact_method') == 'APP' ? 'checked="checked"' : '') }}>
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
                                            <select id="timezone" name="timezone" class="selectpicker form-control" title="Select Time Zone">
                                                <option value="">Select Time Zone</option>
                                                <?php //if ($validation['timezone']['value'] == '') $validation['timezone']['value'] = 'America/New_York' ?>
                                                <?php //showOptionsDrop($timezones_arr, $validation['timezone']['value'], true); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('timezone') }}</span>
                                </div>
                                <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('consent_date') ? 'has-error' : '' }}">
                                    <label for="mf-consent_date">Consent Date <span class="attention">*</span>:</label>
                                    <input id="consent_date" name="consent_date" class="form-control" type="input" value="{{ (old('consent_date') ? old('consent_date') : '') }}"/><br />
                                    <span class="help-block">{{ $errors->first('consent_date') }}</span>
                                </div>
                                <div class="col-sm-12 text-right">
                                    <span class="btn btn-group  text-right"><a class="btn btn-green btn-sm inline-block" omitsubmit="yes" role="button" target="_Blank" href="https://s3.amazonaws.com/clh-downloads/Circlelink+CCM+Consent+Form.pdf">Download Form</a></span>
                                </div>
                                <div class="form-group form-item  form-item-spacing col-sm-12 <?php //echo $validatlidation['preferred_contact_location']['class']; ?>">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label for="preferred_contact_location">Preferred Office Location <span class="attention">*</span>:</label>
                                        </div>
                                        <div class="col-sm-12">
                                            <?php //if( !empty($locations_arr) ) { ?>
                                            <select name="preferred_contact_location" class="selectpicker" data-width="240px" data-size="5">
                                                <?php //showOptionsDrop($locations_arr, $validation['preferred_contact_location']['value'], true); ?>
                                            </select>
                                            <?php //} else { echo "No Locations Available"; } ?>
                                        </div>
                                    </div>
                                    <?php //echo $validatlidation['preferred_contact_location']['text']; ?>
                                </div>
                                <div class="form-group form-item form-item-spacing col-lg-7 col-sm-12 <?php //echo $validatlidation['status']['class']; ?>">
                                    <div class="row">
                                        <div class="col-sm-2 col-lg-4">
                                            <label for="status">Status<span class="attention">*</span>:</label>
                                        </div>
                                        <div class="col-sm-9 col-lg-8 status-buttons">
                                            <div class="radio">
                                                <input type="radio" id="statusActive" name="status" value="Active" {{ ((old('status') == 'Active' || !old('status')) ? 'checked="checked"' : '') }}>
                                                <label for="statusActive"><span> </span>Active</label>
                                            </div>
                                            <div class="radio radio-v-margin">
                                                <input type="radio" id="statusInactive" name="status"  value="Inactive" {{ (old('status') == 'Inactive' ? 'checked="checked"' : '') }}>
                                                <label for="statusInactive"><span> </span>Inactive</label>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('status') }}</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @include('wpUsers.patient.careplan.footer')
        <br /><br />
        </section>
    </div>
    </form>
@stop
