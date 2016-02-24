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
                    <br />
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
                                @if($carePlan->careSections)
                                    @foreach($careSectionNames as $careSectionName)
                                        @foreach($carePlan->careSections as $careSection)
                                            @if($careSectionName == $careSection->name)
                                                @include('partials.carePlans.section')
                                            @endif
                                        @endforeach
                                    @endforeach
                                @endif
                            @else
                                <div class="row" style="margin:60px 0px;">
                                    <div class="col-lg-8 col-lg-offset-2 text-center">
                                        No careplan found for this patient<br />Please contact an administrator.
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
            <br /><br />

                </section>
            </div>

    </div>
    </form>
@stop