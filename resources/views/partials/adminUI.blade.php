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

    <!-- Stylesheets -->
    <link href="{{ asset('/css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
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

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

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
    </style>
    @stack('styles')
</head>
<body>
<div id="app">

    @if ( ! Auth::guest() && Cerberus::hasPermission('admin-access'))
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ URL::route('admin.dashboard', array()) }}">
                        <img src="/img/clh_logo_sm.png"
                             alt="Care Plan Manager"
                             style="position:relative;top:-5px"
                             width="50px"/>
                    </a>
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
                                    <li><a href="{{ URL::route('admin.users.index', array()) }}">All Users</a></li>
                                    <li><a href="{{ URL::route('admin.users.create', array()) }}">New User</a></li>
                                    <li><a href="{{ URL::route('admin.observations.index', array()) }}">Observations</a></li>
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

                                    <li><a href="{{ URL::route('admin.patientCallManagement.index', array()) }}">
                                            Manage</a>
                                    <li><a href="{{ URL::route('admin.families.index', array()) }}">Families</a>
                                    <li><a href="{{ URL::route('algo.mock.create', array()) }}">
                                            Algo v{{\App\Algorithms\Calls\SuccessfulHandler::VERSION}} Simulator</a>
                                    <li><a href="{{ URL::route('CallReportController.exportxls', array()) }}">Export
                                            Calls</a></li>

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
                                    <li><a href="{{ URL::route('get.admin.nurse.schedules') }}">Schedules</a>
                                    {{--                                    <li><a href="{{ URL::route('stats.nurse.info') }}">Nurse Statistics</a>--}}
                                    <li><a href="{{ URL::route('admin.reports.nurseTime.index', array()) }}">Nurse
                                            Time</a>
                                    </li>
                                    <li><a href="{{ URL::route('admin.reports.nurse.daily', array()) }}">Daily
                                            Report</a></li>
                                    <li><a href="{{ URL::route('admin.reports.nurse.invoice', array()) }}">
                                            Invoices</a></li>
                                    <li><a href="{{ URL::route('admin.reports.nurse.allocation', array()) }}">
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
                                    <a href="{{ URL::route('patient.enroll.makeReport', array()) }}">Enrollee List</a>
                                </li>
                                <li role="presentation" class="dropdown">
                                    <a href="{{ URL::route('enrollment.ambassador.stats', array()) }}">Care Ambassador
                                        KPIs</a>
                                </li>
                                <li role="presentation" class="dropdown">
                                    <a href="{{ URL::route('enrollment.practice.stats', array()) }}">Practice KPIs</a>
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
                                    <li><a href="{{ URL::route('roles.index', array()) }}">Roles</a></li>
                                    @if(Cerberus::hasPermission('roles-permissions-view'))
                                        <li><a href="{{ URL::route('permissions.index', array()) }}">Permissions</a>
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
                                    <li><a href="{{ URL::route('admin.practices.index', array()) }}">Programs</a></li>
                                    @if(Cerberus::hasPermission('locations-view'))
                                        <li><a href="{{ URL::route('locations.index', array()) }}">Locations</a></li>
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
                                <li><a href="{{ URL::route('view.files.ready.to.import', []) }}">CCDs To Import</a></li>
                                <li><a href="{{ URL::route('EthnicityReportController.getReport', []) }}">Ethnicity/Race
                                    </a></li>
                                <li><a href="{{ route('get.patients.for.insurance.check') }}">Patients For Insurance
                                        Check
                                    </a></li>
                                <li><a href="{{ URL::route('MonthlyBillingReportsController.create', []) }}">Monthly
                                        Billing</a></li>

                                <li><a href="{{ route('monthly.billing.make') }}">Approve Billable Patients</a></li>

                                <li><a href="{{ URL::route('PatientConditionsReportController.getReport', array()) }}">Patient
                                        Conditions (export)</a>
                                </li>

                                <li><a href="{{ URL::route('excel.report.t2', array()) }}">Paused Patients (export)</a>
                                </li>

                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Account Data<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                {{--<li><a href="{{ URL::route('reports.sales.location.create', array()) }}">by Location--}}
                                {{--</a></li>--}}

                                <li><a href="{{ URL::route('reports.sales.provider.create', array()) }}">by Provider
                                    </a></li>

                                <li><a href="{{ URL::route('reports.sales.practice.create', array()) }}">by Practice
                                    </a></li>
                            </ul>
                        </li>


                        @if ( ! Auth::guest())
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Practices <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.programs.create') }}">Add New</a></li>
                                    <li><a href="{{ URL::route('admin.programs.index', []) }}">View Active</a></li>

                                    <li><a href="{{ URL::route('invite.create', []) }}">Send Onboarding Invite</a>
                                    <li>
                                        <a href="{{ URL::route('get.onboarding.create.program.lead.user', []) }}">Onboarding</a>
                                    </li>
                                    <li><a href="{{ URL::route('locations.index', []) }}">Locations</a></li>
                                    <li><a href="{{ URL::route('practice.billing.create', []) }}">Invoice/Billable
                                            Patient Report</a></li>
                                </ul>
                            </li>
                        @endif

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
                                <a href="{{ URL::route('patients.dashboard', array()) }}"
                                   style=""><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                   aria-expanded="false">{{ Auth::user()->full_name }} [ID:{{ Auth::user()->id }}]<span
                                            class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ URL::route('admin.users.edit', array('id' => Auth::user()->id)) }}"
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


<script src="{{asset('compiled/js/app-clh-admin-ui.js')}}"></script>
<script type="text/javascript" src="{{ asset('compiled/js/admin-ui.js') }}"></script>
@stack('scripts')
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/compiled/sw.js')
    .then(function(registration) {
      console.log('Service Worker registration successful with scope: ',
       registration.scope);
    })
    .catch(function(err) {
      console.log(err);
    });
  }
</script>
<div style="clear:both;height:100px;"></div>
</body>
</html>
