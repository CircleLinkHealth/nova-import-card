@extends('enrollment-ui.layout')

@section('title', 'Enrollment Center')
@section('activity', 'Enrollment Call')

@section('content')

    <script>
        window['userId'] = @json(auth()->id());
        window['userFullName'] = @json(auth()->user()->getFullName());
        window['hasTips'] = @json((!!$enrollee->practice->enrollmentTips));
        window['enrollee'] = @json($enrollee);
        window['providerFullName'] = @json($enrollee->providerFullName);
        window['providerInfo'] = @json($enrollee->getProviderInfo());
        window['report'] = @json($report);
        window['script'] = @json($script);
    </script>

    <div id="app">
        <enrollment-dashboard></enrollment-dashboard>
    </div>

    <script src="{{mix('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
