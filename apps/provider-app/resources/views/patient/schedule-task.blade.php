@extends('partials.providerUI')

@section('title', 'Schedule Task')
@section('activity', 'Schedule Task')

@section('content')
    <schedule-patient-activity ref="addActionModal" patient-id="{{$patientId}}" patient-name="{{$patientName}}" practice-id="{{$practiceId}}" practice-name="{{$practiceName}}" type="task" sub-type="Call Back" care-coach-id="{{$careCoachId}}" care-coach-name="{{$careCoachName}}" auth-role="{{auth()->user()->practiceOrGlobalRole()->name}}"></schedule-patient-activity>

@endsection
