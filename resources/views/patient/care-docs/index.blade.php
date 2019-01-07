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
        <style>
            .patient-details {
                width: 1440px;
                height: 158px;
                background-color: #ffffff;
                text-align: center;
            }
            .patient-documents-container {
                background-color: #f2f6f9;
            }

        </style>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-12 patient-details">
                        <div class="col-sm-12">
                            <div class="col-sm-4">
                                Patient Name<br>
                                {{$patient->getFullName()}}
                            </div>
                            <div class="col-sm-4">
                                Date of Birth (DOB)<br>
                                {{$patient->getBirthDate()}}
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                {{$patient->getPhone()}}
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="col-sm-4">
                                Provider Name<br>
                                {{$patient->getBillingProviderName()}}
                            </div>
                            <div class="col-sm-4">
                                Practice<br>
                                {{$patient->getPrimaryPracticeName()}}
                            </div>
                            <div class="col-sm-4">
                                Phone Number<br>
                                {{$patient->getBillingProviderPhone()}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 patient-documents-container">
                        <div>
                            <div>
                                <div>
                                    @include('errors.errors')
                                    @include('errors.messages')
                                </div>
                                <div>
                                    <care-docs-index :patient="{{$patient}}" ref="CareDocs"></care-docs-index>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop