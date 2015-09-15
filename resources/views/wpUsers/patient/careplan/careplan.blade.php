@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">
    <script src="{{ asset('/webix/codebase/webix.js') }}" type="text/javascript"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">


    <?php
        $user_info = array();
        $new_user = false;
    ?>

    {!! Form::open(array('url' => URL::route('patient.careplan.save', array()), 'class' => 'form-horizontal')) !!}
    <form action="" id="ucpForm" method="POST">
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
                    <?php //if($errorMessage) { echo $errorMessage; } else { echo ''; }; ?>
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

                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php /*echo $validation['firstName']['class'];*/ ?>">
                                        <label class="sr-only" for="firstName">First Name</label>
                                        <input type="text" class="form-control" name="firstName" id="firstName" placeholder="First Name *" value="<?php /*echo $validation['firstName']['value'];*/ ?>">
                                        <?php /*echo $validation['firstName']['text'];*/ ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['lastName']['class']; ?>">
                                        <label class="sr-only" for="lastName">Last Name</label>
                                        <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Last Name *"value="<?php //echo $validation['lastName']['value']; ?>">
                                        <?php //echo $validation['lastName']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['gender']['class']; ?>">
                                        <div class="row">
                                            <div class="col-sm-1 col-lg-3">
                                                <label for="gender">
                                                    Gender <span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="radio-inline">
                                                    <input type="radio" id="radioMale" name="gender" value="M" <?php //if($validation['gender']['value'] == 'M') { echo 'checked="checked"'; } ?>>
                                                    <label for="radioMale"><span> </span>Male</label>
                                                </div>
                                                <div class="radio-inline">
                                                    <input type="radio" id="radioFemale" name="gender" value="F" <?php //if($validation['gender']['value'] == 'F') { echo 'checked="checked"'; } ?>>
                                                    <label for="radioFemale"><span> </span>Female</label>
                                                </div>
                                                <?php //echo $validation['gender']['text']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['preferred_contact_language']['class']; ?> col-lg-12">
                                        <div class="row">
                                            <div class="col-sm-2 col-lg-3">
                                                <label for="language" >Language<span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-10 col-lg-4">
                                                <div class="radio">
                                                    <input type="radio" name="preferred_contact_language" id="languageEnglish" value="EN" <?php //if($validation['preferred_contact_language']['value'] == 'EN' || $validation['preferred_contact_language']['value'] == '') { echo 'checked="checked"'; } ?>>
                                                    <label for="languageEnglish"><span> </span>English</label>
                                                </div>
                                                <div class="radio radio-v-margin">
                                                    <input type="radio" name="preferred_contact_language" id="languageSpanish"  value="ES" <?php //if($validation['preferred_contact_language']['value'] == 'ES') { echo 'checked="checked"'; } ?>>
                                                    <label for="languageSpanish"><span> </span>Spanish</label>
                                                </div>
                                            </div>
                                            <div class="form-group form-item form-item-spacing col-sm-12 col-lg-5 <?php //echo $validation['mrn_number']['class']; ?>">
                                                <?php //echo $validation['preferred_contact_language']['text']; ?>
                                                <label class="sr-only" for="mrn_number">MRN</label>
                                                <input type="text" class="form-control" name="mrn_number" id="mrn_number" placeholder="MRN *" value="<?php //echo $validation['mrn_number']['value']; ?>">
                                                <?php //echo $validation['mrn_number']['text']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['DOB']['class']; ?>">
                                        <div class="row">
                                            <div class="col-sm-12 col-lg-4">
                                                <label for="date-of-birth">
                                                    Date Of Birth<span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-12 col-lg-8">
                                                <div class="form-group">
                                                    <select name="DOBMonth" class="selectpicker" data-width="90px" data-size="10">
                                                        <option> Month </option>
                                                        <?php //showOptionsDrop($month_arr, $validation['DOBMonth']['value'], true); ?>
                                                    </select>
                                                    <select name="DOBDay" class="selectpicker" data-width="70px" data-size="10">
                                                        <option value="">Day</option>
                                                        <?php //showOptionsDrop($day_arr, $validation['DOBDay']['value'], true); ?>
                                                    </select>
                                                    <select name="DOBYear" class="selectpicker" data-width="80px" data-size="5">
                                                        <option value="">Year</option>
                                                        <?php //showOptionsDrop($year_arr, $validation['DOBYear']['value'], true); ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <?php //echo $validation['DOB']['text']; ?>
                                    </div>
                                    <div class="form-item col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group <?php //echo $validation['telephone']['class']; ?>">
                                                    <label class="sr-only" for="telephone">Telephone</label>
                                                    <input type="tel" pattern='\d{3}[\-]\d{3}[\-]\d{4}' class="form-control" name="telephone" id="telephone" placeholder="Telephone *" value="<?php //echo $validation['telephone']['value']; ?>">
                                                    <?php //echo $validation['telephone']['text']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['email']['class']; ?>">
                                        <label class="sr-only" for="lastName">Email Address</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email Address" value="<?php //echo $validation['email']['value']; ?>">
                                        <?php //echo $validation['email']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['address']['class']; ?>">
                                        <label class="sr-only" for="address">Street Address</label>
                                        <input type="text" class="form-control" name="address" id="address" placeholder="Street Address" value="<?php //echo $validation['address']['value']; ?>">
                                        <?php ///echo $validation['address']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-6 city-input <?php //echo $validation['city']['class']; ?>">
                                        <label class="sr-only" for="city">City Name</label>
                                        <input type="text" class="form-control" name="city" id="city" placeholder="City Name" value="<?php //echo $validation['city']['value']; ?>">
                                        <?php //echo $validation['city']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-2 state-selector <?php //echo $validation['state']['class']; ?>">
                                        <select name="state" class="selectpicker" data-header="State*" data-width="90px" data-title="State" value="<?php //echo $validation['state']['value']; ?>">
                                            <option value="">State</option>
                                            <?php //showOptionsDrop($states_arr, $validation['state']['value'], true); ?>
                                        </select>
                                        <?php //echo $validation['state']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-4 <?php //echo $validation['zip']['class']; ?>">
                                        <label class="sr-only" for="zip">Zip Code</label>
                                        <input type="text" class="form-control" name="zip" id="zip" placeholder="Zip Code" value="<?php //echo $validation['zip']['value']; ?>">
                                        <?php //echo $validation['zip']['text']; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="main-form-block main-form-secondary col-lg-5">
                                <h4 class="form-title">Contact Preferences</h4>
                                <div class="row">
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['preferred_contact_time']['class']; ?>">
                                        <label for="mf-contact">Preferred Contact Time <span class="attention">*</span>:</label>
                                        <input id="preferred_contact_time" name="preferred_contact_time" type="input" value="<?php //echo $validation['preferred_contact_time']['value']; ?>" required/><br />
                                        (Should be between 4pm and 9pm)
                                        <?php //echo $validation['preferred_contact_time']['text']; ?>
                                    </div>

                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validation['preferred_contact_method']['class']; ?>">
                                        <div class="row">
                                            <div class="col-sm-2 col-lg-7">
                                                <label for="preferred_contact_method" class="text-right contact-method">
                                                    Preferred Contact Method:
                                                </label>
                                            </div>
                                            <div class="col-sm-10 col-lg-5 contact-method">
                                                <div class="radio">
                                                    <input type="radio" name="preferred_contact_method" id="contactMethodSMS" value="SMS" <?php //if($validation['preferred_contact_method']['value'] == 'SMS' || $validation['preferred_contact_method']['value'] == '') { echo 'checked="checked"'; } ?>>
                                                    <label for="contactMethodSMS"><span> </span>SMS</label>
                                                </div>
                                                <div class="radio radio-v-margin">
                                                    <input type="radio" name="preferred_contact_method" id="contactMethodApp"  value="APP" <?php //if($validation['preferred_contact_method']['value'] == 'APP') { echo 'checked="checked"'; } ?>>
                                                    <label for="contactMethodApp"><span> </span>App</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php //echo $validation['preferred_contact_method']['text']; ?>
                                    </div>
                                    <div class="form-group form-item  form-item-spacing col-sm-12 <?php //echo $validatlidation['timezone']['class']; ?>">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label for="timezone">Time Zone <span class="attention">*</span>:</label>
                                            </div>
                                            <div class="col-sm-12">
                                                <select id="timezone" name="timezone" class="selectpicker" title="Select Time Zone">
                                                    <option value="">Select Time Zone</option>
                                                    <?php //if ($validation['timezone']['value'] == '') $validation['timezone']['value'] = 'America/New_York' ?>
                                                    <?php //showOptionsDrop($timezones_arr, $validation['timezone']['value'], true); ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php //echo $validatlidation['timezone']['text']; ?>
                                    </div>
                                    <div class="form-group form-item form-item-spacing col-sm-12 <?php //echo $validatlidation['CDate']['class']; ?>">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <label for="">
                                                    Consent Date <span class="attention">*</span>:
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <select id="CDateMonth" name="CDateMonth" class="selectpicker" data-width="90px" data-size="10">
                                                        <option> Month </option>
                                                        <?php //showOptionsDrop($month_arr, $validation['CDateMonth']['value'], true); ?>
                                                    </select>
                                                    <select id="CDateDay" name="CDateDay" class="selectpicker" data-width="70px" data-size="10">
                                                        <option> Day </option>
                                                        <?php //showOptionsDrop($day_arr, $validation['CDateDay']['value'], true); ?>
                                                    </select>
                                                    <select id="CDateYear" name="CDateYear" class="selectpicker" data-width="80px" data-size="10">
                                                        <option> Year </option>
                                                        <?php //showOptionsDrop($year_arr, $validation['CDateYear']['value'], true); ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-12 text-right">
                                                    <span class="btn btn-group  text-right"><a class="btn btn-green btn-sm inline-block" omitsubmit="yes" role="button" target="_Blank" href="https://s3.amazonaws.com/clh-downloads/Circlelink+CCM+Consent+Form.pdf">Download Form</a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php //echo $validatlidation['CDate']['text']; ?>
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
                                                    <input type="radio" id="statusActive" name="status" value="Active" <?php //if($validation['status']['value'] == 'Active') { echo 'checked="checked"'; } ?>>
                                                    <label for="statusActive"><span> </span>Active</label>
                                                </div>
                                                <div class="radio radio-v-margin">
                                                    <input type="radio" id="statusInactive" name="status"  value="Inactive" <?php //if($validation['status']['value'] == 'Inactive' || $validation['status']['value'] == '') { echo 'checked="checked"'; } ?>>
                                                    <label for="statusInactive"><span> </span>Inactive</label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php //echo $validatlidation['status']['text']; ?>
                                    </div>

    </form>
    </div>
    </div>
    </div>
    </div>

    </div>
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>
    <div class="row">&nbsp;</div>

    @include('wpUsers.patient.careplan.footer')
    <br /><br />
    </section>
    </form>
    </div>
@stop
