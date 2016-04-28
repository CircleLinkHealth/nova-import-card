<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@if($patient->careplanStatus == 'provider_approved')
    @section('title', 'Edit/Modify Care Plan')
@section('activity', 'Edit/Modify Care Plan')
@else
    @section('title', 'Initial Care Plan Setup')
@section('activity', 'Initial Care Plan Setup')
@endif

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    {!! Form::open(array('url' => URL::route('patient.careplan.store', array('patientId' => $patient->ID)), 'class' => '', 'id' => 'ucpForm')) !!}



    <div id="content" class="row">

        <div class="container">
            <section class="">
                <div class="row">
                    <div class="icon-container col-lg-12">
                        @if(isset($patient) && !$new_user )
                            @include('wpUsers.patient.careplan.nav')
                        @endif
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="main-form-container col-lg-8 col-lg-offset-2">
                        <div class="row">
                            <div class="main-form-title col-lg-12">
                                Edit Patient Care Plan
                            </div>
                            @include('partials.userheader')
                        </div>
                    </div>
                </div>

                <div class="row">
                    <input type=hidden name=user_id value="{{ $patient->ID }}">
                    <input type=hidden name=program_id value="{{ $patient->program_id }}">

                    @if($carePlan)
                        <input type=hidden name=careplan_id value="{{ $carePlan->id }}">

                        {{-- Call CPM Partials Here --}}

                        {{--Page 1--}}
                        {{--Problems--}}
                        @if(!empty($cptProblems))
                            <?php $itemType = 'problem'; ?>
                            <?php $title = 'Diagnosis / Problems to Monitor'; ?>
                            <?php $cpmCollection = $cptProblems; ?>
                            @include('partials.cpm-models.section')
                        @endif

                        {{--Lifestyles--}}
                        @if(!empty($cptLifestyles))
                            <?php $itemType = 'lifestyle'; ?>
                            <?php $title = 'Lifestyle to Monitor'; ?>
                            <?php $cpmCollection = $cptLifestyles; ?>
                            @include('partials.cpm-models.section')
                        @endif

                        {{--Medications--}}
                        @if(!empty($cptMedicationGroups))
                            <?php $itemType = 'medication-group'; ?>
                            <?php $title = 'Medications to Monitor'; ?>
                            <?php $cpmCollection = $cptMedicationGroups; ?>
                            @include('partials.cpm-models.section')
                        @endif


                        {{--Page 2--}}
                        {{--Symptoms--}}
                        @if(!empty($cptBiometrics))
                            <?php $itemType = 'biometric'; ?>
                            <?php $title = 'Biometrics to Monitor'; ?>
                            <?php $cpmCollection = $cptBiometrics; ?>
                            @include('partials.cpm-models.section')
                        @endif

                        {{--Miscellaneous--}}
                        @if(!empty($cptTransitionalCareManagement))
                            <?php $itemType = 'transitional-care'; ?>
                            <?php $title = 'Transitional Care Management'; ?>
                            <?php $cpmCollection = $cptTransitionalCareManagement; ?>
                            @include('partials.cpm-models.section')
                        @endif


                        {{--Page 3--}}
                        {{--Symptoms--}}
                        @if(!empty($cptSymptoms))
                            <?php $itemType = 'symptom'; ?>
                            <?php $title = 'Symptoms to Monitor'; ?>
                            <?php $cpmCollection = $cptSymptoms; ?>
                            @include('partials.cpm-models.section')
                        @endif

                        {{--Miscellaneous--}}
                        @if(!empty($cptAdditionalInfo))
                            <?php $itemType = 'additional-info'; ?>
                            <?php $title = 'Additional Information'; ?>
                            <?php $cpmCollection = $cptAdditionalInfo; ?>
                            @include('partials.cpm-models.section')
                        @endif
                    @else
                        <div class="row" style="margin:60px 0px;">
                            <div class="col-lg-8 col-lg-offset-2 text-center">
                                No careplan found for this patient<br/>
                            </div>
                        </div>
                    @endif

                </div>

                <input id="save" name="formSubmit" value="Save" tabindex="0" type="hidden">
                <div class="main-form-progress">
                    <div class="row row-centered">
                    </div>
                </div><!-- /main-form-progress -->


                <!--footer-->
                @include('wpUsers.patient.careplan.footer')
                <br/><br/>

            </section>
        </div>

    </div>
    </form>
@stop