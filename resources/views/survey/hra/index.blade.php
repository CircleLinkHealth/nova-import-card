@extends('surveysMaster')
@section('content')
    <survey-questions :survey-data="{{json_encode($data)}}"></survey-questions>
@endsection
