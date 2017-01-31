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


    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- Metrialize CDN -->
{{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/4.0.2/bootstrap-material-design.css" />--}}

@if(!isset($isPdf))
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Scripts -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        <!-- http://trentrichardson.com/examples/timepicker/
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>-->

        <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
        <script src="{{ asset('/js/scripts.js') }}"></script>
        <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('/js/typeahead.bundle.js') }}"></script>
        <script src="{{ asset('/js/fab.js') }}"></script>
    @include('partials.searchjs')

    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>
        <script type="text/javascript" src="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.js"></script>

        <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">
        <script src="{{ asset('/webix/codebase/webix.js') }}" type="text/javascript"></script>

        <!-- select2 -->
        <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
        <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
        <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
    @endif
</head>
<body>
@if(!isset($isPdf))

    @if(!empty($impersonatedUserEmail))
        <div class="container-fluid text-center" style="background-color: #E46745; color: black; padding: 0.6%;">
            You are impersonating user with email <b>{{ $impersonatedUserEmail }}</b>
        </div>
        @endif

    @include('partials.providerUI.primarynav')

    @if(!empty($patient->id))

        @include('partials.providerUI.patientnav')

    @endif

    @endif

    @if(!isset($isPdf))
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
        {{--
        PROVIDER UI TEMPLATE:
        <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
        <div class="row">
        <div class="main-form-title col-lg-12">
        title
        </div>
        <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
        content text
        </div>
        </div>
        </div>
        </div>
        --}}
        @if(!isset($isPdf))
            <!-- PAGE TIMER START -->
            @include('partials.providerUItimer')
            <!-- PAGE TIMER END -->

            @if(!empty($patient->id))
                @include('partials.addprovider')
            @endif

            @include('partials.footer')
        @endif


</body>

</html>