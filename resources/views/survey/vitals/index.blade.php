@extends('surveysMaster')
@section('content')
    <survey-vitals :data="{{json_encode($data)}}"></survey-vitals>
@endsection
