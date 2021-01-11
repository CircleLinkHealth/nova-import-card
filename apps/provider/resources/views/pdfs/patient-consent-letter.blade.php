@extends('layouts.pdf')

<style>
    h3 {
        color: black !important;
    }

    .logo {
        background: url({{asset('img/logos/LogoHorizontal_Color.svg')}}) no-repeat;
        width: 300px;
        height: 300px;
    }
</style>

@section('content')
    <div class="container">

        <div class="logo">
        </div>

        <div class="text-center">
            <h1>
                <span>CCM/BHI Patient Consent Document</span>
            </h1>
        </div>

        <br>
        <br>

        <div style="margin-left: 15%">
            <div>
                <h3>
                    <span><strong>Patient Name:</strong> {{$patientName}}</span>
                </h3>
                <h3>
                    <span><strong>DOB:</strong> {{$dob}}</span>
                </h3>
                <h3>
                    <span><strong>MRN:</strong> {{$mrn}}</span>
                </h3>
            </div>
            <br>
            <br>
            <div>
                <h3>
                <span>Verbal consent to enroll {{$patientName}} in the CCM/BHI program was
                obtained on {{$date}}.</span>
                </h3>
            </div>
        </div>

    </div>
@endsection