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

    <base href="{{asset('')}}">

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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">

    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

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
<div id="app">

    @if ( ! Auth::guest() && Cerberus::hasPermission('admin-access'))
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                        <img src="/img/clh_logo_sm.png"
                             alt="Care Plan Manager"
                             style="position:relative;top:-5px"
                             width="50px"/>
                    </a>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        @if ( ! Auth::guest())
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Users <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.users.index') }}">All Users</a></li>
                                    <li><a href="{{ route('admin.users.create') }}">New User</a></li>
                                    <li><a href="{{ route('admin.observations.index') }}">Observations</a></li>
                                    <li><a href="{{ route('observations-dashboard.index') }}">Edit/Delete
                                            Observations</a></li>
                                </ul>
                            </li>
                        @endif

                        @if ( ! Auth::guest())
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Calls <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">

                                    <li><a href="{{ route('admin.patientCallManagement.index') }}">
                                            Manage (New)</a></li>
                                    <li><a href="{{ route('admin.patientCallManagement.old') }}">
                                            Manage (Old)</a></li>
                                    <li><a href="{{ route('admin.families.index') }}">Families</a></li>
                                    <li><a href="{{ route('algo.mock.create') }}">
                                            Algo v{{\App\Algorithms\Calls\SuccessfulHandler::VERSION}} Simulator</a>
                                    </li>
                                    <li><a href="{{ route('CallReportController.exportxls') }}">Export
                                            Calls</a></li>
                                    <li><a href="{{ route('CallsDashboard.index') }}">Edit Call Status</a></li>
                                </ul>
                            </li>
                        @endif

                        @if ( ! Auth::guest())
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Nurses <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('get.admin.nurse.schedules') }}">Schedules</a>
                                    {{--                                    <li><a href="{{ route('stats.nurse.info') }}">Nurse Statistics</a>--}}
                                    <li><a href="{{ route('admin.reports.nurseTime.index') }}">Nurse
                                            Time</a>
                                    </li>
                                    <li><a href="{{ route('admin.reports.nurse.daily') }}">Daily
                                            Report</a></li>
                                    <li><a href="{{ route('admin.reports.nurse.monthly') }}">Monthly
                                            Report</a></li>
                                    <li><a href="{{ route('admin.reports.nurse.invoice') }}">
                                            Invoices</a></li>
                                    <li><a href="{{ route('admin.reports.nurse.allocation') }}">
                                            Allocation</a></li>

                                </ul>
                            </li>
                        @endif

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">Enrollment<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
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

                        @if(Cerberus::hasPermission('roles-view'))
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Roles<span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('roles.index') }}">Roles</a></li>
                                    @if(Cerberus::hasPermission('roles-permissions-view'))
                                        <li><a href="{{ route('permissions.index') }}">Permissions</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if(Cerberus::hasPermission('practices-view'))
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Programs <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.practices.index') }}">Programs</a></li>
                                    @if(Cerberus::hasPermission('locations-view'))
                                        <li><a href="{{ route('locations.index') }}">Locations</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Reports<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                <li><a href="{{ route('import.ccd.remix', []) }}">CCDs To Import</a></li>
                                <li><a href="{{ route('EthnicityReportController.getReport', []) }}">Ethnicity/Race
                                    </a></li>
                                <li><a href="{{ route('get.patients.for.insurance.check') }}">Patients For Insurance
                                        Check
                                    </a></li>

                                <li><a href="{{ route('monthly.billing.make') }}">Approve Billable Patients</a></li>

                                <li><a href="{{ route('PatientConditionsReportController.getReport') }}">Patient
                                        Conditions (export)</a>
                                </li>

                                <li><a href="{{ route('excel.report.t2') }}">Paused Patients (export)</a>
                                </li>

                                <li>
                                    <a href="{{route('get.print.paused.letters')}}">Print Paused Patient Letters</a>
                                </li>

                                <li>
                                    <a href="{{route('OpsDashboard.index')}}">Ops Dashboard</a>
                                </li>
                                <li>
                                    <a href="{{route('OpsDashboard.billingChurnIndex')}}">Billing Churn</a>
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
                                <li><a href="{{ route('admin.programs.create') }}">Add New</a></li>
                                <li><a href="{{ route('admin.programs.index', []) }}">View Active</a></li>

                                <li><a href="{{ route('invite.create', []) }}">Send Onboarding Invite</a>
                                <li>
                                    <a href="{{ route('get.onboarding.create.program.lead.user', []) }}">Onboarding</a>
                                </li>
                                <li><a href="{{ route('locations.index', []) }}">Locations</a></li>
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

                                <li role="presentation" class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                       aria-expanded="false">
                                        Practices <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="{{ route('admin.programs.create') }}">Add New</a></li>
                                        <li><a href="{{ route('admin.programs.index', []) }}">View Active</a></li>

                                        <li><a href="{{ route('invite.create', []) }}">Send Onboarding Invite</a>
                                        <li>
                                            <a href="{{ route('get.onboarding.create.program.lead.user', []) }}">Onboarding</a>
                                        </li>
                                        <li><a href="{{ route('locations.index', []) }}">Locations</a></li>
                                        <li><a href="{{ route('practice.billing.create', []) }}">Invoice/Billable
                                                Patient Report</a></li>
                                    </ul>
                                </li>
                                <li><a href="{{route('problem-keywords.index')}}">Problem Keywords
                                    </a></li>
                                <li><a href="{{route('medication-groups-maps.index')}}">Medication Group Map
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
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        @if (Auth::guest())
                            {{--<li><a href="{{ url('/auth/login') }}">Login</a></li>--}}
                            {{--<li><a href="{{ url('/auth/register') }}">Register</a></li>--}}
                        @else
                            <li class="dropdown">
                                <div id="time-tracker"></div>
                            </li>

                            <li class="dropdown">
                                <a href="{{url('/jobs/completed')}}">
                                    <span class="badge">{{auth()->user()->cachedNotificationsList()->count()}}</span>
                                    Jobs Completed
                                </a>
                            </li>

                            <li class="dropdown">
                                <a href="{{ route('patients.dashboard') }}"
                                   style=""><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">{{ Auth::user()->full_name }} [ID:{{ Auth::user()->id }}]<span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
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

    {{--This is for JS variables. Purposefully included before content.--}}
    @include('partials.footer')
    @yield('content')
</div>


@if (Agent::isIE())
    <!-- Script for polyfilling Promises on IE9 and 10 -->

    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="{{ asset('js/polyfills/es7-object-polyfill.min.js') }}"></script>
@endif

<script src="{{asset('compiled/js/app-clh-admin-ui.js')}}"></script>
<script type="text/javascript" src="{{ asset('compiled/js/admin-ui.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@stack('scripts')
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(function (registration) {
                console.log('Service Worker registration successful with scope: ',
                    registration.scope);
            })
            .catch(function (err) {
                console.log(err);
            });
    }
</script>
<div style="clear:both;height:100px;"></div>
</body>
</html>
