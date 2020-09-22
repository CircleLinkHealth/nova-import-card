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

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css"
          href="//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css"/>

    <!-- select2 -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>

    <style>
        body {
            font-family: Roboto, sans-serif;
        }

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css"
          integrity="sha256-/oZ7h/Jkj6AfibN/zTWrCoba0L+QhP9Tf/ZSgyZJCnY=" crossorigin="anonymous"/>
    @include('modules.raygun.partials.real-user-monitoring')
    @include('core::partials.new-relic-tracking')
</head>
<body>
<div id="app">

    @if (  ! Auth::guest() && Cerberus::hasPermission('admin-access') )
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                        <img src="{{asset('/img/logos/LogoHorizontal_Color.svg')}}"
                             alt="Care Plan Manager"
                             style="position:relative;top:-15px"
                             width="100px"/>
                    </a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Users <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('admin.users.index') }}">All Users</a></li>
                                <li><a href="{{ route('admin.users.create') }}">New User</a></li>
                                <li><a href="{{ route('observations-dashboard.index') }}">Edit/Delete
                                        Observations</a></li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Activities <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('admin.patientCallManagement.v2.index') }}">Manage (V2)</a></li>
                                <li><a href="{{ route('admin.families.index') }}">Families</a></li>
                                <li><a href="{{ route('CallsDashboard.index') }}">Edit Call Status</a></li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Nurses <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('admin.offline-activity-time-requests.index') }}">Offline Activity
                                        Time Requests</a>
                                <li><a href="{{ route('get.admin.nurse.schedules') }}">Schedules</a>
                                <li><a href="{{ route('admin.reports.nurse.daily') }}">Daily
                                        Report</a></li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">Enrollment<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li role="presentation" class="dropdown">
                                    <a href="{{ route('ca-director.index') }}">CA Director</a>
                                </li>
                                <li role="presentation" class="dropdown">
                                    <a href="{{ route('patient.enroll.makeReport') }}">Enrollee List</a>
                                </li>
                                <li role="presentation" class="dropdown">
                                    <a href="{{ route('enrollment.ambassador.stats') }}">Care Ambassador
                                        KPIs</a>
                                </li>
                                <li role="presentation" class="dropdown">
                                    <a href="{{ route('enrollment.practice.stats') }}">Practice KPIs</a>
                                </li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Reports<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                <li><a href="{{ route('import.ccd.remix', ['v3']) }}">CCDs To Import</a></li>

                                <li><a href="{{ route('monthly.billing.make') }}">Approve Billable Patients</a></li>

                                <li><a href="{{ route('excel.report.unreachablePatients') }}">Unreachable Patients
                                        (export)</a>
                                </li>

                                <li>
                                    <a href="{{route('get.print.paused.letters')}}">Print Paused Patient Letters</a>
                                </li>

                                <li>
                                    <a href="{{route('OpsDashboard.index')}}">Ops Dashboard</a>
                                </li>
                                {{--                                to remove?--}}
                                {{--                                <li>--}}
                                {{--                                    <a href="{{route('OpsDashboard.index.chart')}}">Ops Chart</a>--}}
                                {{--                                </li>--}}
                                <li><a href="{{ route('admin.reports.nurse.metrics') }}">
                                        Nurse Performance Report</a></li>
                                <li>
                                    <a href="{{route('revisions.all.activity')}}">All Activity</a>
                                </li>
                                <li>
                                    <a href="{{route('revisions.phi.activity')}}">PHI Activity</a>
                                </li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Account Data<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                {{--<li><a href="{{ route('reports.sales.location.create') }}">by Location--}}
                                {{--</a></li>--}}

                                <li><a href="{{ route('reports.sales.provider.create') }}">by Provider
                                    </a></li>

                                <li><a href="{{ route('reports.sales.practice.create') }}">by Practice
                                    </a></li>
                            </ul>
                        </li>
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Practices <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('practice.billing.create', []) }}">Invoice/Billable
                                        Patient Report</a></li>
                            </ul>
                        </li>
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Settings<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                <li><a href="{{route('manage-cpm-problems.index')}}">Manage CPM Problems
                                    </a></li>
                                <li><a href="{{route('medication-groups-maps.index')}}">Medication Group Map
                                    </a></li>
                                <li><a href="{{route('report-settings.index')}}">Manage Report Settings
                                    </a></li>
                            </ul>
                        </li>

                        @if(auth()->user()->isSaas())
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    SaaS Accounts<span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('saas-accounts.create', []) }}">Create New</a></li>
                                </ul>
                            </li>
                        @endif

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Medical Records <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ route('eligibility.batches.index') }}">Eligibility</a></li>
                                <li><a href="{{ getEhrReportWritersFolderUrl() }}" target="_blank">EHR Report Writers
                                        Google Folder</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        @if (! Auth::guest())
                            <li class="dropdown">
                                <div id="time-tracker"></div>
                            </li>

                            <li class="dropdown">
                                <a href="{{ url('/superadmin') }}"
                                   style=""><i class="glyphicon glyphicon-fire"></i> SuperAdmin</a>
                            </li>
                            @if(config('core.apps.cpm-provider.url'))
                                <li class="dropdown">
                                    <a href="{{ config('core.apps.cpm-provider.url').'manage-patients/dashboard' }}"
                                       style=""><i class="glyphicon glyphicon-eye-open"></i> Provider</a>
                                </li>
                            @endif
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">{{ Auth::user()->getFullName() }} [ID:{{ Auth::user()->id }}
                                    ]<span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    @include('core::partials.last-login')
                                    <li><a href="{{ route('admin.users.edit', array('id' => Auth::user()->id)) }}"
                                           class="">My Account</a></li>
                                    <li><a href="{{ url('/admin/api-clients') }}">Api Clients</a></li>
                                    <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <!--[if lt IE 8]>
        <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a
                href="http://browsehappy.com/">upgrade
            your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome
            Frame</a>
            to improve your experience.</p>
        <![endif]-->
    @endif

    @yield('content')
</div>

@if (Agent::isIE())
    <!-- Script for polyfilling Promises on IE9 and 10 -->

    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="{{ asset('js/polyfills/es7-object-polyfill.min.js') }}"></script>
@endif

<script src="{{asset('compiled/js/app-clh-admin-ui.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.js"></script>

<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js"
        integrity="sha256-fOPHmaamqkHPv4QYGxkiSKm7O/3GAJ4554pQXYleoLo=" crossorigin="anonymous"></script>

@include('core::partials.sentry-js')

<div style="clear:both;height:100px;"></div>

</body>
</html>
