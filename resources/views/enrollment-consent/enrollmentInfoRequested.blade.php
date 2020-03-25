@extends('layouts.EnrollmentSurvey.enrollmentSurveyMaster')
@section('title', 'Enrollment requested info')
@section('activity', 'Enrollment requested info')
@section('content')
    <div class="container">
        <div class="letter-view">
            <div class="practice-logo">
                @include('enrollment-consent.practiceLogo')
            </div>
            <div style="">
                <hr>
                <p style="text-align: center">
                    Thanks for requesting a call to discuss our Personalized Care Program!<br>
                    A care ambassador will call you as soon as possible from <strong>{{$practiceNumber}}</strong>.<br>
                    Please save this number
                    <br>
                    <br>
                    <strong>Dr. {{$providerName}}'s care team</strong>
                </p>
            </div>
            <div>
            </div>

        </div>
    </div>
@endsection

<style>
    .letter-view {
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>