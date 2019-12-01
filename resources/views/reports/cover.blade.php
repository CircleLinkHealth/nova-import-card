@extends('layouts.surveysMaster')

@section('content')

    @if (isset($isPdf) && $isPdf)
        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

        <!-- found in surveysMaster but for some reason dompdf has issues with it -->
        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/pdf.css') }}" rel="stylesheet">
    @endif

    <link href="{{ asset('/css/coverReport.css') }}" rel="stylesheet">

    <div class="position-ref cover">
        <div class="content">
            <div>
                {{$practiceName}}
                <br/>
                Dr. {{$providerName}}
            </div>
            <div style="margin-top: 160px">
                <img src="{{ asset('/images/doctors.png') }}" height="240"/>
            </div>
            <div class="title">
                <p>
                    {{$title1}}
                    @if (isset($title2))
                        <br/>
                        {{$title2}}
                    @endif
                </p>
            </div>
            <div>
                <p>Patient:&nbsp;<span class="font-weight-bold patient-name">{{$patient->display_name}}</span></p>
                <p>Date of Birth:&nbsp;<span class="font-weight-bold">{{$patient->patientInfo->dob()}}</span></p>
                <p class="watermarked">Generated:&nbsp;<span class="font-weight-bold">{{$generatedAt}}</span></p>
            </div>
            <div class="footer text-center">
                <img src="{{ asset('/images/lightning_bolt.png')}}" height="28px"/>&nbsp;by&nbsp;<span class="by-circlelink">CircleLink Health</span>
            </div>
        </div>
    </div>

    <div class="page-break"></div>
@endsection
