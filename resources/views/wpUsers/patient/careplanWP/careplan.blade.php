<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    {!! Form::open(array('url' => URL::route('patient.careplan.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <div class="row">
        <div class="icon-container col-lg-12">
            @if(isset($patient) && !$new_user )
                @include('wpUsers.patient.careplan.nav')
            @endif
        </div>
    </div>



    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="row">
                    <div class="main-form-container col-lg-8 col-lg-offset-2">
                        <div class="row">
                            @if(isset($patient) && !$new_user )
                            <div class="main-form-title col-lg-12">
                                Edit Patient Careplan
                            </div>
                            @else
                            <div class="main-form-title col-lg-12">
                                Add Patient Careplan
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <input type=hidden name=user_id value="{{ $patient->id }}">
                <input type=hidden name=program_id value="{{ $patient->program_id }}">

                <div class="row">
                    <div class="main-form-container col-lg-8 col-lg-offset-2">
                        <div class="row">
                            {!! $sectionHtml !!}
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