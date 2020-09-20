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

            .patient-documents-container {
                letter-spacing: 1.33px;
            }

            .strong-custom {
                color: #000000;
                font-weight: 500;
            }

            .btn-static {
                opacity: 1 !important;
            }

        </style>
    @endpush
    <div>
        @include('core::partials.errors.errors')
        @include('core::partials.errors.messages')
    </div>
    <div class="container-fluid patient-documents-container">
        <div class="row">
            <div class="col-md-12">
                <div class="patient-details">
                    <div class="patient-details-row">
                        <div class="col-md-offset-2 col-md-8 col-sm-12">
                            <div class="col-sm-4">
                                Patient Name<br>
                                <span class="strong-custom">{{$patient->getFullName()}}</span>
                            </div>
                            <div class="col-sm-4">
                                Date of Birth (DOB)<br>
                                <span class="strong-custom">{{$patient->getBirthDate()}}</span>
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                <span class="strong-custom">{{$patient->getPhone()}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="patient-details-row">
                        <div class="col-md-offset-2 col-md-8 col-sm-12">
                            <div class="col-sm-4">
                                Provider Name<br>
                                <span class="strong-custom">{{$patient->getBillingProviderName()}}</span>
                            </div>
                            <div class="col-sm-4">
                                Practice<br>
                                <span class="strong-custom">{{$patient->getPrimaryPracticeName()}}</span>
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                <span class="strong-custom">{{$patient->getBillingProviderPhone()}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 patient-documents-container">
                        <div style="margin-left: 40px">
                            <div>
                                <div>
                                    <care-docs-index patient-id="{{$patient->id}}"
                                                     awv-url="{{config('services.awv.url')}}"
                                                     ref="CareDocs"></care-docs-index>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop