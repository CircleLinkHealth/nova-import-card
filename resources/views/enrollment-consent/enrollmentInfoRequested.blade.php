@extends('layouts.EnrollmentSurvey.enrollmentSurveyMaster')
@section('title', 'Enrollment requested info')
@section('activity', 'Enrollment requested info')
@section('content')
    <div class="container">
        <p>
            Thanks for requesting a call to discuss our Personalized Care Program!<br>
            A care ambassador will call you as soon as possible from {{$practiceNumber}}.<br>
            Pleas save this number

            Dr. {{$providerName}}'s care team
        </p>
    </div>
@endsection