<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CPM API</title>

    <!-- Stylesheets -->
    {{-- <link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet"> --}}
    {{-- <link href="{{ asset('/css/lavish-2.css') }}" rel="stylesheet"> --}}
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
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- idleTime -->
    <script src="{{ asset('/js/idle-timer.min.js') }}"></script>

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>

    <!-- Parsley -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>

    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>
    <script type="text/javascript" src="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.js"></script>

    <!-- START BOOTSTRAP -->
    <!-- Latest compiled and minified CSS -->
    <link href="{{ asset('/css/'.$app_config_admin_stylesheet) }}" rel="stylesheet">

    <!-- select2 -->
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>

    <style>
        .table-striped > tbody > tr:nth-child(odd) > td,
        .table-striped > tbody > tr:nth-child(odd) > th {
            /* background-color: #eee; */
        }
    </style>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous"></script>
    <!-- END BOOTSTRAP -->

    <!-- select2 -->
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>

    <!-- misc scripts -->
    <script src="{{ asset('/js/scripts.js') }}"></script>

</head>
<body>
@if ( ! Auth::guest() && Entrust::can('admin-access'))
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ URL::route('admin.dashboard', array()) }}">
                    <img src="/img/ui/clh_logo_lt.png"
                         alt="Care Plan Manager"
                         style="position:relative;top:-15px"
                         width="50px"/>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @if ( ! Auth::guest())
                        <li role="presentation" 0lass="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Users <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('admin.users.index', array()) }}">All Users</a></li>
                                <li><a href="{{ URL::route('admin.users.create', array()) }}">New User</a></li>
                                <li><a href="{{ URL::route('admin.observations.index', array()) }}">Observations</a>
                                </li>
                                {{--                                <li><a href="{{ URL::route('comments.index', array()) }}">Comments</a></li>--}}
                                {{--                                <li><a href="{{ URL::route('ucp.index', array()) }}">UCP</a></li>--}}
                            </ul>
                        </li>
                    @endif

                    @if ( ! Auth::guest())
                        <li role="presentation" 0lass="dropdown">
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
                                Nurse Management <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('get.admin.nurse.schedules') }}">Schedules</a>
                                {{--                                    <li><a href="{{ URL::route('stats.nurse.info') }}">Nurse Statistics</a>--}}
                                <li><a href="{{ URL::route('admin.reports.nurseTime.index', array()) }}">Nurse Time</a>
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
                                <a href="{{ URL::route('enrollment.ambassador.stats', array()) }}">Care Ambassador KPIs</a>
                            </li>
                            <li role="presentation" class="dropdown">
                                <a href="{{ URL::route('enrollment.practice.stats', array()) }}">Practice KPIs</a>
                            </li>
                        </ul>
                    </li>

                    @if(Entrust::can('roles-view'))
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Roles<span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('roles.index', array()) }}">Roles</a></li>
                                @if(Entrust::can('roles-permissions-view'))
                                    <li><a href="{{ URL::route('permissions.index', array()) }}">Permissions</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(Entrust::can('practices-view'))
                        <li role="presentation" class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                               aria-expanded="false">
                                Programs <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('admin.practices.index', array()) }}">Programs</a></li>
                                @if(Entrust::can('locations-view'))
                                    <li><a href="{{ URL::route('locations.index', array()) }}">Locations</a></li>
                                @endif
                                {{--@if(Entrust::can('practices-manage'))--}}
                                {{--<li><a href="{{ URL::route('admin.questions.index', array()) }}">Questions</a></li>--}}
                                {{--<li><a href="{{ URL::route('admin.questionSets.index', array()) }}">Question--}}
                                {{--Sets</a></li>--}}
                                {{--<li><a href="{{ URL::route('admin.items.index', array()) }}">Items</a></li>--}}
                                {{--@endif--}}
                            </ul>
                        </li>
                    @endif

                    {{--@if(Entrust::can('practices-view'))--}}
                    {{--<li role="presentation" class="dropdown">--}}
                    {{--<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"--}}
                    {{--aria-expanded="false">--}}
                    {{--Care Plans <span class="caret"></span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu" role="menu">--}}
                    {{--<li><a href="{{ URL::route('careplans.index', array()) }}">Care Plans</a></li>--}}
                    {{--<li><a href="{{ URL::route('careitems.index', array()) }}">Care Items</a></li>--}}
                    {{--</ul>--}}
                    {{--</li>--}}
                    {{--@endif--}}

                    {{--@if(Entrust::can('activities-view'))--}}
                    {{--<li role="presentation" class="dropdown">--}}
                    {{--<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"--}}
                    {{--aria-expanded="false">--}}
                    {{--Activities <span class="caret"></span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu" role="menu">--}}
                    {{--<li><a href="{{ URL::route('admin.activities.index', array()) }}">Activities</a></li>--}}
                    {{--@if(Entrust::can('activities-pagetimer-view'))--}}
                    {{--<li><a href="{{ URL::route('admin.pagetimer.index', array()) }}">Page Timer</a></li>--}}
                    {{--@endif--}}
                    {{--</ul>--}}
                    {{--</li>--}}
                    {{--@endif--}}

                    {{--@if(Entrust::can('rules-engine-view'))--}}
                    {{--<li role="presentation" class="dropdown">--}}
                    {{--<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"--}}
                    {{--aria-expanded="false">--}}
                    {{--Rules <span class="caret"></span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu" role="menu">--}}
                    {{--<li><a href="{{ URL::route('admin.rules.index', array()) }}">Rules</a></li>--}}
                    {{--@if(Entrust::can('rules-engine-manage'))--}}
                    {{--<li><a href="{{ URL::route('admin.rules.create', array()) }}">Add new</a></li>--}}
                    {{--@endif--}}
                    {{--</ul>--}}
                    {{--</li>--}}
                    {{--@endif--}}
                    {{--@if(Entrust::can('apikeys-view'))--}}
                    {{--<li role="presentation" class="dropdown">--}}
                    {{--<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"--}}
                    {{--aria-expanded="false">--}}
                    {{--API<span class="caret"></span>--}}
                    {{--</a>--}}
                    {{--<ul class="dropdown-menu" role="menu">--}}
                    {{--<li><a href="{{ URL::route('admin.apikeys.index', array()) }}">API Keys</a></li>--}}
                    {{--<li><a href="{{ action('Redox\ConfigController@create') }}">Redox Engine</a></li>--}}
                    {{--<li><a href="{{ action('qliqSOFT\ConfigController@create') }}">qliqSOFT</a></li>--}}
                    {{--</ul>--}}
                    {{--</li>--}}
                    {{--@endif--}}

                    <li role="presentation" class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                           aria-expanded="false">
                            Reports<span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                            <li><a href="{{ URL::route('view.files.ready.to.import', []) }}">CCDs To Import</a></li>
                            <li><a href="{{ URL::route('EthnicityReportController.getReport', []) }}">Ethnicity/Race
                                </a></li>
                            <li><a href="{{ route('get.patients.for.insurance.check') }}">Patients For Insurance Check
                                </a></li>
                            <li><a href="{{ URL::route('MonthlyBillingReportsController.create', []) }}">Monthly
                                    Billing</a></li>
                            <li><a href="{{ URL::route('PatientConditionsReportController.getReport', array()) }}">Patient
                                    Conditions (export)</a>
                            </li>

                            <li><a href="{{ URL::route('excel.report.t2', array()) }}">Paused Patients (export)</a></li>

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
                                Practice Management <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('invite.create', []) }}">Send Onboarding Invite</a>
                                <li>
                                    <a href="{{ URL::route('get.onboarding.create.program.lead.user', []) }}">Onboarding</a>
                                </li>
                                <li><a href="{{ URL::route('admin.programs.index', []) }}">Practices</a></li>
                                <li><a href="{{ URL::route('locations.index', []) }}">Locations</a></li>
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
                            <a href="{{ URL::route('patients.dashboard', array()) }}" class="btn-xs btn-primary"
                               style=""><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a></li>
                        @if(Entrust::can('app-config-view'))
                            <li role="presentation" class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                   aria-expanded="false">
                                    Settings<span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ URL::route('appConfig.index', array()) }}">App Config</a></li>
                                </ul>
                            </li>
                        @endif
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">{{ Auth::user()->full_name }} [ID:{{ Auth::user()->id }}]<span
                                        class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{ URL::route('admin.users.edit', array('id' => Auth::user()->id)) }}"
                                       class=""> My Account</a></li>
                                <li><a href="{{ url('/auth/logout') }}">Logout</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!--[if lt IE 8]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade
        your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a>
        to improve your experience.</p>
    <![endif]-->
@endif

{{--This is for JS variables. Purposefully included before content.--}}
@include('partials.footer')

@yield('content')
@yield('javascript')
<div style="clear:both;height:100px;"></div>
</body>
</html>
