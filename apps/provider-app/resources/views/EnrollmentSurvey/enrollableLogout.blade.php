@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Self-Enrollment logout')
@section('activity', 'Self-Enrollment logout')
@section('content')
    <div class="container">
        <div class="practice-logo">
            <div class="logo">
                @include('enrollment-letters.practiceLogo', compact('practiceLogoSrc'))
            </div>
        </div>
       <div class="message">
           <hr>
           <h4>Done! You can close this window.</h4>
       </div>
    </div>
    @endsection

<style>
    .message {
        /*width: 300px;
        height: 58px;*/
        font-family: Poppins, sans-serif;
        font-size: 18px;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 20px;
        margin-bottom: 10px;
        /*margin-left: 111px;*/
        color: #50b2e2;
    }

    .practice-logo{
        text-align: center;
        padding-top: 40px;
    }
</style>
