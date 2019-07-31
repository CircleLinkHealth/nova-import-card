@extends('layouts.surveysMaster')
@section('content')
    <vitals-survey-welcome :patient-id="{{json_encode($patientId)}}"
                           :patient-name="{{json_encode($patientName)}}"
                           :is-provider-logged-in="{{json_encode(!auth()->user()->hasRole('participant'))}}">

    </vitals-survey-welcome>
@endsection
