@extends('enrollment-ui.layout')

@section('title', 'Enrollment Center')
@section('activity', 'Enrollment Call')

@section('content')

    <script src="//static.twilio.com/libs/twiliojs/1.3/twilio.min.js"></script>

    <script>
        window['userFullName'] = @json(auth()->user()->getFullName());
        window['hasTips'] = @json(($enrollee->practice->enrollmentTips() ?? collect())->count() > 0);
        window['enrollee'] = @json($enrollee);
        window['providerFullName'] = @json($enrollee->providerFullName);
        window['report'] = @json($report);
    </script>

    <div id="app">
        <enrollment-dashboard></enrollment-dashboard>
    </div>

    <script src="{{mix('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
