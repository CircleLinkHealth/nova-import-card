@extends('enrollment-ui.layout')

@section('title', 'CA - Loading next patient')
@section('activity', 'CA - Loading next patient')

@section('content')

    <?php
    $user = auth()->user();
    ?>

    <script>
        window['userId'] = @json($user->id);
        window['userFullName'] = @json($user->getFullName());
    </script>

    @include('partials.providerUItimer', ['forEnrollment' => true])

    <div id="app">
        <enrollment-dashboard
                cpm-caller-url="{{config('services.twilio.cpm-caller-url')}}"
                cpm-token="{{$cpmToken}}"
                :debug="{{json_encode(!isProductionEnv())}}">

        </enrollment-dashboard>
    </div>

    <script src="{{mix('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
