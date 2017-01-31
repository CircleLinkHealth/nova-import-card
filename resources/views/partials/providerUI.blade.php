<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0">
    <meta http-equiv="expires" content={{ Carbon\Carbon::now()->format('D M d Y H:i:s O') }}>
    <meta http-equiv="pragma" content="no-cache">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>CarePlanManager - @yield('title')</title>

    <link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/fab.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/patientsearch.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

@if(!isset($isPdf))
    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>
        <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>

        {{--Prefer to load JS assets in the footer. These have to be loaded here :(--}}
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
        <script src="{{ asset('/webix/codebase/webix.js') }}" type="text/javascript"></script>
    @endif
</head>
<body>

@if(!isset($isPdf))
    @include('partials.providerUI.primarynav')

    @if(!empty($patient->id))
        @include('partials.providerUI.patientnav')
    @endif

    @if(!empty($patient->id))
        @include('partials.fab')
    @endif
@endif

<!--[if lt IE 8]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a
        href="http://browsehappy.com/">upgrade
    your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome
    Frame</a>
    to improve your experience.</p>
<![endif]-->

@yield('content')

@if(!isset($isPdf))
    @include('partials.footer')

         <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
    <script src="{{ asset('/js/scripts.js') }}"></script>
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('/js/typeahead.bundle.js') }}"></script>
    <script src="{{ asset('/js/fab.js') }}"></script>
    @include('partials.searchjs')
@include('partials.providerUItimer')
    <script type="text/javascript"
            src="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.js"></script>
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>

    @yield('scripts')
@endif

</body>

</html>