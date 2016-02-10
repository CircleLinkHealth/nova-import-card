<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@section('title', 'Edit/Modify Care Plan')
@section('activity', 'Edit/Modify Care Plan')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    {!! Form::open(array('url' => URL::route('patient.careplan.store', array('patientId' => $patient->ID)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <div class="row">
        <div class="icon-container col-lg-12">
            @if(isset($patient) && !$new_user )
                @include('wpUsers.patient.careplan.nav')
            @endif
        </div>
    </div>

    <div class="row" style="margin-top:20px;">
        <div class="main-form-container-last col-lg-8 col-lg-offset-2">
            <div class="row">
                @if(isset($patient) && !$new_user )
                    <div class="main-form-title col-lg-12">
                        Edit Patient Careplan
                    </div>
                    @include('partials.userheader')
                @else
                    <div class="main-form-title col-lg-12">
                        Add Patient Careplan
                    </div>
                @endif
            </div>
            <div class="row">
                <input type=hidden name=user_id value="{{ $patient->ID }}">
                <input type=hidden name=program_id value="{{ $patient->program_id }}">
                @if($carePlan)
                    <input type=hidden name=careplan_id value="{{ $carePlan->id }}">
                    @if($carePlan->careSections)
                        @foreach($carePlan->careSections as $careSection)
                            @if(in_array($careSection->name, $careSectionNames))
                                @include('partials.carePlans.section')
                            @endif
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
        </div>
    </div>
    @include('wpUsers.patient.careplan.footer')
    <br /><br />
    </form>
@stop