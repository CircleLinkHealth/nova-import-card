@if(!isset($isPdf))
    @extends('partials.providerUI')
@endif

<?php

if (isset($patient) && ! empty($patient)) {
    $today = \Carbon\Carbon::now()->toFormattedDateString();

    $alreadyShown = [];
}
?>

@if(!isset($isPdf))
    @section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')
@endif

@section('content')
    @push('styles')
        <link href="https://fonts.googleapis.com/css?family=Roboto:500&display=swap" rel="stylesheet">
        <style>
            .patient-details {
                height: 158px;
                background-color: #ffffff;
                padding: initial;
            }
            .patient-documents-container {
                background-color: #f2f6f9;
                min-height: 1000px;
                padding: initial;
            }

            .patient-details-row {
                margin: auto;
                padding-bottom: 35px;
                padding-top: 25px;
                width: 80%;
                text-align: center;
            }
            body {
                font-family: 'Roboto', sans-serif !important;
            }
            h4 {
                font-family: 'Roboto', sans-serif !important;
            }
            b {
                font-weight: bolder;
            }
        </style>
    @endpush
    <div>
        @include('errors.errors')
        @include('errors.messages')
    </div>
    <div class="container-fluid patient-documents-container">
        <div class="row">
            <div class="col-md-12">
                <div class="patient-details">
                    <div class="patient-details-row">
                        <div class="col-sm-12">
                            <div class="col-sm-4">
                                Patient Name<br>
                                <strong>{{$patient->getFullName()}}</strong>
                            </div>
                            <div class="col-sm-4">
                                Date of Birth (DOB)<br>
                                <strong>{{$patient->getBirthDate()}}</strong>
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                <strong>{{$patient->getPhone()}}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="patient-details-row">
                        <div class="col-sm-12">
                            <div class="col-sm-4">
                                Provider Name<br>
                                <strong>{{$patient->getBillingProviderName()}}</strong>
                            </div>
                            <div class="col-sm-4">
                                Practice<br>
                                <strong>{{$patient->getPrimaryPracticeName()}}</strong>
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                <strong>{{$patient->getBillingProviderPhone()}}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 patient-documents-container">
                        <div style="margin-left: 40px">
                            <div>
                                <div>
                                    <care-docs-index :patient="{{$patient}}" awv-url="{{config('services.awv.url')}}" ref="CareDocs"></care-docs-index>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop