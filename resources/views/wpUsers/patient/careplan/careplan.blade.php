<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@section('content')
    <div class="row" style="margin-top:60px;">
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
            <div class="row">
                @if($carePlan->careSections)
                    @foreach($carePlan->careSections as $careSection)
                        @include('partials.carePlans.section')
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@stop