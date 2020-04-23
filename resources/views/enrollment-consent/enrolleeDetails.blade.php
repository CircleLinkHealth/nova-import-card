@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Enrollee to call details')
@section('activity', 'Enrollee to call details')
@section('content')
    <div class="container">
        <ul>
            First Name:{{$enrolleeData['enrolleeFirstName']}}<br>
            Last Name:{{$enrolleeData['enrolleeLastName']}}<br>
            Cell Phone: {{$enrolleeData['cellPhone']}}<br>
            Home Phone:{{$enrolleeData['homePhone']}}<br>
            Other Phone:{{$enrolleeData['otherPhone']}}
        </ul>
    </div>
@endsection