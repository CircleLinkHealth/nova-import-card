@extends('partials.providerUI')

@section('title', 'Paused Patients Letter')
@section('activity', 'Paused Patients Letter')

<style type="text/css">
    body {
        margin: 0;
        margin-right: 150px !important;
    }

    div.address {
        line-height: 1.1em;
        font-family: 'Roboto', sans-serif;
    }

    div.breakhere {
        page-break-after: always;
        /*height: 100%;*/
    }

    .address-height-print {
        height: 1in !important;
        max-height: 1in !important;
    }

    .sender-address-print {
        font-size: 16px !important;
    }

    .receiver-address-print {
        font-size: 16px !important;
        height: 1in !important;
    }

    .receiver-address-padding {
        padding-top: 1.7in !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .welcome-copy {
        font-size: 24px;
        margin-top: 0.5in !important;
    }

    .omr-bar {
        height: 15px;
        background-color: black;
        width: 35%;
        margin-left: 120%;
        margin-top: 15%;
    }

    .row {
        line-height: 1.0em;
    }
</style>

@section('content')
    @include("patient.letters.$lang.paused", ['patient' => $patient])
@endsection
