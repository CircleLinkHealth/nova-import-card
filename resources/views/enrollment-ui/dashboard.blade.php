@extends('enrollment-ui.layout')

@section('title', 'Enrollment Center')
@section('activity', 'Enrollment Call')

@section('content')

    <script>
        window['userId'] = @json(auth()->id());
        window['userFullName'] = @json(auth()->user()->getFullName());
    </script>

    <div id="app">
        <enrollment-dashboard></enrollment-dashboard>
    </div>

    <script src="{{mix('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
