@extends('layouts.surveysMaster')
@section('content')
    <vitals-survey :admin-mode="{{json_encode(!auth()->user()->hasRole('participant'))}}" :data="{{json_encode($data)}}"></vitals-survey>
@endsection
