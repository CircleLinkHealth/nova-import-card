@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Self-Enrollment error')
@section('activity', 'Self-Enrollment error')
@section('content')
    <div class="container">
       <div class="message">
           <h4>There was an error. A care coach will contact you soon.</h4>
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
</style>
