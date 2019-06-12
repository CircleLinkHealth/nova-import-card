@extends('surveysMaster')
@section('content')
    <vitals-survey-welcome :patients-name="{{json_encode($patientsName)}}"></vitals-survey-welcome>
@endsection
