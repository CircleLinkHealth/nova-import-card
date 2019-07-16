@extends('layouts.surveysMaster')
@section('content')
    <survey-questions :admin-mode="{{json_encode(!auth()->user()->hasRole('participant'))}}" :survey-data="{{json_encode($data)}}"></survey-questions>
@endsection
