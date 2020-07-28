@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Enrolled Page View')
@section('activity', 'Enrolled Page View')
@section('content')
    <div class="survey-main-title">
        <label>Thanks for signing up!</label>
    </div>
    <div class="survey-sub-welcome-text" style="text-align: center;">
        Your care coach will contact you in the next few days from <a href="tel:{{$practiceNumber}}">{{$practiceNumber}}</a>.<br>
        Please save this number to your phone ASAP.
        <br>
        <br>
        <div style="font-weight: bold">
            Dr {{$doctorName}}'s care team.
        </div>
    </div>
@endsection

<style>
    .survey-main-title {
        font-family: Poppins, sans-serif;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 30px;
        color: #1a1a1a;
    }

    .survey-sub-welcome-text {
        font-family: Poppins;
        font-size: 18px;
        font-weight: normal;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: center;
        margin-top: 25px;
        margin-left: 13%;
        width: 75%;
        color: #1a1a1a;
    }

</style>
