<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en-US"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <base href="{{asset('')}}">

    <title>CarePlanManager - @yield('title')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css" integrity="sha256-/oZ7h/Jkj6AfibN/zTWrCoba0L+QhP9Tf/ZSgyZJCnY=" crossorigin="anonymous" />

    <link href="{{ mix('/css/wpstyle.css') }}" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

    <link href="{{ mix('/img/favicon.png') }}" rel="icon">
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css">


    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">



    <style>
        span.twitter-typeahead .twitter-typeahead {
            position: absolute !important;
        }
    </style>
    @stack('styles')
    @include('cpm-module-raygun::partials.real-user-monitoring')
    @include('partials.new-relic-tracking')
</head>
<body>

<div id="app">
    <!--[if lt IE 8]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a
            href="http://browsehappy.com/">upgrade
        your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome
        Frame</a>
        to improve your experience.</p>
    <![endif]-->
    @yield('app')

</div> <!-- end #app -->

@include('partials.sentry-js')
@stack('scripts')
</body>
</html>
