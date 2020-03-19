@extends('layouts.surveysMaster')
@section('content')
    <survey-questions survey-name="hra"
                      :admin-mode="{{json_encode(!auth()->user()->hasRole('participant'))}}"
                      cpm-caller-url="{{config('services.twilio.cpm-caller-url')}}"
                      cpm-caller-token="{{getCpmCallerToken()}}"
                      :debug="@json(config('app.env') !== 'production')"
                      :survey-data="{{json_encode($data)}}">
    </survey-questions>
@endsection
