<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-language" content="en-US"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>CPM</title>

@include('partials.hotjar-code')

<!-- Stylesheets -->
    <link href="{{ asset('/css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
    <!--[if lt IE 9]-->
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- JQuery -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" integrity="sha256-iWTx/iC9IoKaoSKD5+WVFef8ZYNIgQ4AxVpMbBw2hig=" crossorigin="anonymous" />

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet"
          href="{{asset('/css/jquery-ui-timepicker-addon.min.css')}}">

    <link rel="stylesheet" href="{{asset('/css/bootstrap.min.css')}}">

    <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css"/>

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

        .top-nav-item {
            background: none !important;
            padding: 15px;
            line-height: 20px;
            cursor: pointer;
        }
    </style>
    @stack('styles')
</head>
<body>
<div id="app">

    <nav class="navbar navbar-default">
        <div class="container-fluid full-width margin-0">
            <div class="row">
                <div class="col-lg-4 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="col-md-3 col-xs-12">
                            <a class="navbar-brand" href="{{ url('/') }}" style="padding: 5px 15px; border: none"><img
                                        src="{{asset('/img/logos/LogoHorizontal_Color.svg')}}"
                                        alt="Care Plan Manager"
                                        style="position:relative;top:-7px"
                                        height="50"
                                        width="105"/></a>

                            <button type="button" class="navbar-toggle collapsed" style="border-color:white"
                                    data-toggle="collapse"
                                    data-target="#navbar-collapse" aria-expanded="false">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar" style="background-color:white"></span>
                                <span class="icon-bar" style="background-color:white"></span>
                                <span class="icon-bar" style="background-color:white"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-sm-12 col-xs-12">
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-3">
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="{{route('report-writer.google-drive')}}" target="_blank"><i class="top-nav-item-icon glyphicon glyphicon glyphicon glyphicon-cloud"></i> My Google Drive Folder</a>
                            </li>
                            <li class="dropdown">
                                <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button" aria-expanded="false" id="csv-templates-dropdown">
                                    <i class="top-nav-item-icon glyphicon glyphicon glyphicon-list-alt"></i>

                                    Templates
                                    <span class="caret text-white"></span>
                                </div>
                                <ul class="dropdown-menu" role="menu" style="background: white !important;">
                                    <li>
                                        <a href="{{route('report-writer.download-template', ['name' => 'Single Fields'])}}">Single field CSV</a>
                                    </li>
                                    <li>
                                        <a href="{{route('report-writer.download-template', ['name' => 'Numbered Fields'])}}">Multi-field CSV</a>
                                    </li>
                                </ul>
                            </li>
                            @include('partials.user-account-dropdown', ['user' => auth()->user()])
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

</div>
</body>

@yield('content')
@if (Agent::isIE())
    <!-- Script for polyfilling Promises on IE9 and 10 -->

    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="{{ asset('js/polyfills/es7-object-polyfill.min.js') }}"></script>
@endif

<script type="text/javascript" src="{{ asset('compiled/js/issue-688.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@stack('scripts')
<div style="clear:both;height:100px;"></div>
</html>