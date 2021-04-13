@extends('selfEnrollment::layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Self-Enrollment error')
@section('activity', 'Self-Enrollment error')
@section('content')
    <div class="container">
        <div class="row message">
            <div class="col s12"> <img class="responsive-img" src="img/sorry_image.svg" alt="sorry!"/> </div>
            <div class="col s12">
                <h2 class="message-heading">Our apologies! There was an error.</h2>

                <div class="message-content">
                    <p class="message-content-1">
                        Don’t worry, our care team is working to fix this. Please try enrolling again in a few hours. A care coach will contact you soon if the issue is not resolved.
                    </p>

                    <p class="message-content-2">
                        Want us to give you a call to finish your enrollment?
                    </p>
                </div>

                @if(isset($userId))
                <a href="{{route('patient.requests.callback', $userId)}}" class="waves-effect btn call-me-btn" >Call Me</a>
                @endif

            </div>

        </div>

    </div>
    @endsection

<style>
    body {
        background: #fff !important;
    }
    .container {
        max-width: 1180px !important;
    }
    .message {
        padding-top: 5%;
        font-family: Montserrat;
        letter-spacing: 1.5px;
        text-align: center;
    }
    .message-heading {
        color: #2D9CDB;
        font-size: 60px;
    }
    .message-content {
        color: #4F4F4F;
    }
    .message-content-1{
        font-size: 36px;
    }
    .message-content-2{
        font-size: 24px;
    }
    .call-me-btn{
        background: #2D9CDB !important;
        font-size: 18px !important;
        width: 207px;
        height: 42px !important;
        text-transform: none !important;
        padding-top: 4px !important;
    }
</style>
