@extends('layouts.surveysMaster')
@section('content')
    <vitals-survey-not-authorized :doctors-name="{{json_encode($doctorsName)}}"></vitals-survey-not-authorized>
@endsection
