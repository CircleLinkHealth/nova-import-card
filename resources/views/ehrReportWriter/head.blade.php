<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-language" content="en-US"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>CPM API</title>

@include('partials.hotjar-code')

<!-- Stylesheets -->
    <link href="{{ mix('/css/admin.css') }}" rel="stylesheet">
    <link href="{{ mix('/img/favicon.png') }}" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
    <!--[if lt IE 9]-->
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- JQuery -->
    <link rel="stylesheet" href="{{mix('/css/smoothness-jquery-ui-1.11.4.css')}}">

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet"
          href="{{mix('/css/jquery-ui-timepicker-addon.min.css')}}">

    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>

    <link rel="stylesheet" href="{{mix('/css/bootstrap.min.css')}}">

    <!-- select2 -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>

    <style>
        .table-striped > tbody > tr:nth-child(odd) > td,
        .table-striped > tbody > tr:nth-child(odd) > th {
            /* background-color: #eee; */
        }

        .modal-dialog {
            z-index: 1051 !important;
        }

        .select2 {
            width: 100%;
        }
    </style>
    @stack('styles')
</head>
<body>
<ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
               aria-expanded="false">{{ Auth::user()->getFullName() }} [ID:{{ Auth::user()->id }}]<span
                        class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
                <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
            </ul>
        </li>
</ul>
</body>
@include('partials.footer')
@yield('content')
@if (Agent::isIE())
    <!-- Script for polyfilling Promises on IE9 and 10 -->

    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="{{ mix('js/polyfills/es7-object-polyfill.min.js') }}"></script>
@endif

<script src="{{mix('compiled/js/app-clh-admin-ui.js')}}"></script>
<script type="text/javascript" src="{{ mix('compiled/js/admin-ui.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@stack('scripts')
<div style="clear:both;height:100px;"></div>
</html>