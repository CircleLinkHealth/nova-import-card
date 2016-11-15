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
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

@if(!isset($isPdf))
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Scripts -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
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
    <nav class="navbar primary-navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="{{ URL::route('patients.dashboard') }}" class="navbar-brand"><img src="/img/ui/clh_logo_lt.png"
                                                                                           alt="Care Plan Manager"
                                                                                           style="position:relative;top:-15px"
                                                                                           width="50px"/></a>
                <a href="{{ URL::route('patients.dashboard') }}"
                   class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
            </div>
            <div class="navbar-right hidden-xs ">
                <ul class="nav navbar-nav">
                    {{--URL::route('patients.dashboard', array())--}}
                    <li><a href="{{ URL::route('patients.dashboard') }}"><i class="icon--home--white"></i> Home</a></li>
                    <li><a href="{{ URL::route('patients.search') }}"><i class="icon--search--white"></i> Select Patient</a>
                    </li>
                    <li><a href="{{ URL::route('patient.note.listing') }}"><span class="glyphicon glyphicon-envelope"
                                                                                 aria-hidden="true"
                                                                                 style="height: 16px; width: 22px; font-size: 17px; top: 4px"></span>Notes
                            Report</a></li>
                    <li><a href="{{ URL::route('patients.listing') }}"><i class="icon--patients"></i> Patient List</a>
                    </li>
                    <li><a href="{{ URL::route('patients.demographics.show') }}"><i class="icon--add-user"></i> Add
                            Patient</a></li>

                    @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                        <li><a class="btn btn-primary btn-xs"
                               href="{{ empty($patient->id) ? URL::route('admin.dashboard') : URL::route('admin.users.edit', array('patient' => $patient->id)) }}"><i
                                        class="icon--home--white"></i>Admin</a></li>
                    @endif
                    <li class="dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown" role="button"
                             aria-expanded="false"
                             style="background: none !important;padding: 15px;line-height: 20px;cursor: pointer;">
                            <i class="glyphicon glyphicon-option-vertical"></i>
                            {{ Auth::user()->full_name }}
                            <span class="caret"></span>
                        </div>
                        <ul class="dropdown-menu" role="menu" style="background: white !important;">
                            @if(auth()->user()->hasRole(['care-center']))
                                <li>
                                    <a href="{{ route('care.center.work.schedule.index') }}" id="work-schedule-link">
                                        <i class="glyphicon glyphicon-calendar"></i>
                                        Work Schedule
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ url('/auth/logout') }}">
                                    <i class="glyphicon glyphicon-log-out"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <!-- /navbar-collapse -->
        </div>
        <!-- /container-fluid -->

    </nav><!-- /navbar -->

    <nav class="navbar secondary-navbar hidden-xs">
        <div class="patient__actions text-center">
            <ul class="navbar-nav nav">
                <li class="inline-block dropdown">
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                       omitsubmit="yes">Notes/Offline Activity<span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.note.index', array('patient' => $patient->id)) }}">
                                Notes/Offline Activities
                            </a>
                        </li>
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.note.create', array('patient' => $patient->id)) }}">Add
                                New Note</a>
                        </li>
                    </ul>
                </li>
                <li class="inline-block">
                    <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.summary', array('patient' => $patient->id)) }}"
                       role="button">Patient Overview</a>
                </li>
                <li class="inline-block">
                    <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.careplan.show', array('patient' => $patient->id, 'page' => '1')) }}"
                       role="button">Edit Care Plan</a></li>
                <li class="inline-block dropdown">
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                       omitsubmit="yes">Input<span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.observation.create', array('patient' => $patient->id)) }}">Observations</a>
                        </li>
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.activity.create', array('patient' => $patient->id)) }}">Offline
                                Activities</a>
                        </li>
                    </ul>
                </li>
                <li class="inline-block dropdown">
                    <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                       omitsubmit="yes">Patient Reports <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.reports.progress', array('patient' => $patient->id)) }}">Progress
                                Report</a>
                        </li>
                        <li>
                            <a href="{{ URL::route('patient.note.listing') }}">Notes Report</a>
                        </li>
                        <li>
                            <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.activity.providerUIIndex', array('patient' => $patient->id)) }}">Patient
                                Activity Report</a>
                        </li>
                        <li>
                            <a href="{{URL::route('patient.reports.u20')}}">Under 20 Minute Report</a>
                        </li>
                        <li>
                            <a href="{{URL::route('patient.reports.billing')}}">Patient Billing Report</a>
                        </li>
                        <li>
                            <a href="{{ URL::route('patients.careplan.printlist', array()) }}">Patient Care Plan Print
                                List</a>
                        </li>
                    </ul>
                </li>
                <li class="inline-block">
                    <a href="{{ empty($patient->id) ? URL::route('patients.search') : URL::route('patient.careplan.print', array('patient' => $patient->id)) }}"
                       role="button">View Care Plan</a>
                </li>
            </ul>
        </div>
    </nav><!-- /navbar -->
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