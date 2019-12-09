@extends('layouts.pdf')

<style>
    h4 {
        color: black !important;
    }
</style>

@section('content')
    <div class="container">

        <div class="text-left">
            <img style="float: left;" src="{{ public_path('img/logos/LogoHorizontal_Color.svg') }}" width="170" height="70"
                 class="img-responsive" alt="CLH Logo">
        </div>

        <div class="clearfix"></div>

        <div class="text-center">
            <h2>
                <span>CCM/BHI Patient Consent Document</span>
            </h2>
        </div>

        <br>
        <br>

        <div style="margin-left: 15%">
            <div>
                <h4>
                    <span><strong>Patient Name:</strong> {{$patientName}}</span>
                </h4>
                <h4>
                    <span><strong>DOB:</strong> {{$dob}}</span>
                </h4>
                <h4>
                    <span><strong>MRN:</strong> {{$mrn}}</span>
                </h4>
            </div>
            <br>
            <br>
            <div>
                <h4>
                <span>Verbal consent to enroll {{$patientName}} in the CCM/BHI program was
                obtained on {{$date}}.</span>
                </h4>
            </div>
        </div>

    </div>
@endsection