@extends('surveysMaster')
@section('content')
<survey-questions :surveyData="{{json_encode($surveyData)}}"></survey-questions>
@endsection

