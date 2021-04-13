@extends('selfEnrollment::layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Call Me Confirmation')
@section('activity', 'Call Me Confirmation')
@section('content')

    <div class="container">
        <div class="row message">

            <img class="responsive-img" src="{{asset('img/telephone.svg')}}" alt="sorry!"/>
            <h2 class="message-heading">Confirmed</h2>

            <p class="message-content-1">
                We’ll give you a call to finish your enrollment.
            </p>

        </div>

    </div>

@endsection

<style>
    body {
        background: #fff !important;
    }
    .message {
        padding-top: 5%;
        font-family: Montserrat;
        letter-spacing: 1.5px;
        text-align: center;
    }
    .message-heading {
        color: #2DC67A;
        font-size: 60px;
    }
    .message-content-1{
        font-size: 36px;
    }
</style>