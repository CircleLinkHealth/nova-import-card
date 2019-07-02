@extends('layouts.surveysMaster')
@section('content')
    <vitals-survey :data="{{json_encode($data)}}"></vitals-survey>
@endsection
