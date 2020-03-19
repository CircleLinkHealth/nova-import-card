@extends('layouts.surveysMaster')
@section('content')
    <survey-questions survey-name="enrollees"
                      :admin-mode="{{json_encode(false)}}"
                      cpm-caller-url="{{config('services.twilio.cpm-caller-url')}}"
                      cpm-caller-token="{{getCpmCallerToken()}}"
                      clh-phone-number="{{config('services.twilio.from')}}"
                      :debug="{{json_encode(config('app.env') !== 'production')}}"
                      :survey-data="{{json_encode($data)}}">
    </survey-questions>
@endsection
