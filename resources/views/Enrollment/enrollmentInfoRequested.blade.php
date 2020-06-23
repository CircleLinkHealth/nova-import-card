@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Enrollment requested info')
@section('activity', 'Enrollment requested info')
@section('content')
    <div class="container">
        <div class="letter-view">
            <div class="practice-logo">
                <div class="logo">
                    @include('enrollment-consent.practiceLogo')
                </div>
            </div>
            <div>
                <hr>
                <p style="text-align: center">
                    Thanks for requesting a call to discuss our Personalized Care Program!<br>
                    A care ambassador will call you as soon as possible from <strong>{{$practiceNumber}}</strong>.<br>
                    Please save this number
                    <br>
                    <br>
                    @if(!empty($providerName))
                    <strong>Dr. {{$providerName}}'s care team</strong>
                    @endif
                </p>
            </div>
            <div class="logout">
                <a href="{{route('user.enrollee.logout', ['isSurveyOnly' => $isSurveyOnly, 'enrolleeId' => $enrollee->id])}}">
                    <button type="button" class="btn btn-med" style="border-radius: 40px; background-color: #2bbce3">Logout</button>
                </a>
            </div>

        </div>
    </div>
@endsection

<style>
    .letter-view {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .practice-logo {
        text-align: center;
    }

    .logout{
        text-align: center;
    }

    @media (max-width: 490px) {
        /*.logo {*/
        /*    margin-left: 10em;*/
        /*}*/
    }
</style>