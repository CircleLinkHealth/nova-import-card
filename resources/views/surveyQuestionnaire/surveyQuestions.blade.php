@extends('surveysMaster')
@section('content')
<survey-questions :questions="{{json_encode($questions)}}"></survey-questions>
@endsection

