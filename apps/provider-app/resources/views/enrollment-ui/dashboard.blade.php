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
                cpm-caller-url="{{config('twilio-notification-channel.cpm-caller-url')}}"
                cpm-token="{{$cpmToken}}"
                :debug="{{json_encode(!isProductionEnv())}}"
                cookie-img-url="{{asset('img/cookie.png')}}"
        >

        </enrollment-dashboard>
    </div>

    @if($user->isAllowedToBubbleChat())
        @include('intercom-chat.clh-chat-bubble', ['user' => $user, 'alignment'=>'right'])
    @endif

    <script src="{{asset('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
