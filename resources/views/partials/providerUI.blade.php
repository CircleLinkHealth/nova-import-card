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

    <style>
        #bloodhound{
            /*background-color: #fff;*/
            min-width: 250px;
            position: relative;
            margin-bottom: -35px;
            margin-top: -3px;
        }

        #bloodhound li{
            padding: 5px;
        }

        #bloodhound li.active{
            background-color: #eee;
        }

        .typeahead,
        .tt-query,
        .tt-hint {
            /*width: 100% !important;*/
            /*height: 30px;*/
            /*padding: 8px 12px;*/
            /*font-size: 24px;*/
            /*line-height: 30px;*/
            /*border: 2px solid #ccc;*/
            /*-webkit-border-radius: 8px;*/
            /*-moz-border-radius: 8px;*/
            /*border-radius: 8px;*/
            outline: none;
        }

        .typeahead {
            background-color: #fff;
        }

        .twitter-typeahead {


        }

        .typeahead:focus {
            /*border: 2px solid #63bbe8;*/
            /*height: 40px;*/
            /*font-size: 15px;*/
        }

        .tt-query {
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        }

        .tt-hint {
            color: #999
        }

        .tt-menu {
            position: absolute !important;
            left:-2px !important;
            max-height: 250px;
            min-height: 220px;
            overflow-y: auto;
            width: 535px !important;
            margin: 38px 0;
            padding: 3px 0;
            background-color: #fff;
            border: 1px solid #ccc;
            -webkit-border-radius: 8px;
            -moz-border-radius: 8px;
            border-radius: 8px;
            -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            box-shadow: 0 5px 10px rgba(0,0,0,.2);
        }

        .tt-suggestion {
            text-align: left;
            padding: 3px 20px;
            font-size: 16px;
            line-height: 28px;
            color: #4795c1;
        }

        .tt-suggestion:hover {
            cursor: pointer;
            color: #fff;
            background-color: #0097cf;
        }

        .tt-suggestion.tt-cursor {
            color: #fff;
            background-color: #0097cf;

        }

        .tt-suggestion p {
            margin: 0;
        }

    </style>

    <title>CarePlanManager - @yield('title')</title>

    <link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/fab.css') }}" rel="stylesheet">
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

        <script>
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                            (i[r].q = i[r].q || []).push(arguments)
                        }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-9084372-23', 'auto');
            ga('send', 'pageview');

        </script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
        <script src="{{ asset('/js/scripts.js') }}"></script>
        <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('/js/typeahead.bundle.js') }}"></script>
        <script src="{{ asset('/js/fab.js') }}"></script>


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

    @if(!empty($patient->id))
        @include('partials.fab')
    @endif

<!--[if lt IE 8]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
        your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a>
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
    @endif

</body>

</html>