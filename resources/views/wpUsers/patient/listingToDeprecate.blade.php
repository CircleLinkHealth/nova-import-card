@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', 'Patient Listing')

@section('content')

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient List
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    @include('partials.webix.patient-list')
                </div>
            </div>
        </div>
    </div>
@stop