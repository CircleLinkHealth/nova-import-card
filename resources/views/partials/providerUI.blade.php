<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CPM API</title>

    <link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/lavish.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
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

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="{{ asset('/js/scripts.js') }}"></script>
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>

    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
</head>
<body>
<nav class="navbar primary-navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="" class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
        </div>
        <div class="navbar-right hidden-xs ">
            <ul class="nav navbar-nav">
                {{--URL::route('patients.dashboard', array())--}}
                <li><a href="{{ URL::route('patients.dashboard') }}"><i class="icon--home--white"></i> Home</a></li>
                <li><a href="{{ URL::route('patients.select') }}"><i class="icon--search--white"></i> Select Patient</a></li>
                <li><a href="{{ URL::route('patients.demographics.show') }}"><i class="icon--add-user"></i> Add Patient</a></li>
                <li><a href="{{ URL::route('patients.alerts') }}"><i class="icon--alert--white"></i> Alerts</a></li>
                @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                    <li><a class="btn btn-orange btn-xs" href="{{ URL::route('admin.dashboard') }}"><i class="icon--home--white"></i> Admin</a></li>
                @elseif (!Auth::guest())
                    <li>
                        <a href="">
                            <i class="icon--logout"></i>Logout</a>
                    </li>
                @else
                    <li>
                        <a href="">
                            <i class="icon--logout"></i>Login</a>
                    </li>
                @endif
            </ul>
        </div><!-- /navbar-collapse -->
    </div><!-- /container-fluid -->

</nav><!-- /navbar -->

<nav class="navbar secondary-navbar hidden-xs">
    <div class="patient__actions text-center">
        <ul class="navbar-nav nav">
            <li class="inline-block dropdown">
                <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Notes/Offline Activity<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ empty($patient) ? '' : URL::route('patient.note.index', array('patient' => $patient->ID)) }}">Notes/Offline Activities</a>
                    </li>
                    <li>
                        <a href="{{ empty($patient) ? '' : URL::route('patient.note.create', array('patient' => $patient->ID)) }}">Add New Note</a>
                    </li>
                </ul>
            </li>
            <li class="inline-block">
                <a href="{{ empty($patient) ? '' : URL::route('patient.summary', array('patient' => $patient->ID)) }}" role="button">Patient Overview</a>
            </li>
            <li class="inline-block">
                <a href="{{ empty($patient) ? '' : URL::route('patient.demographics.show', array('patient' => $patient->ID)) }}" role="button">Edit Care Plan</a></li>
            <li class="inline-block dropdown">
                <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Input<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ empty($patient) ? '' : URL::route('patient.observation.create', array('patient' => $patient->ID)) }}">Observations</a>
                    </li>
                    <li>
                        <a href="{{ empty($patient) ? '' : URL::route('patient.activity.create', array('patient' => $patient->ID)) }}">Offline Activities</a>
                    </li>
                </ul>
            </li>
            <li class="inline-block dropdown">
                <a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Patient Reports <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ empty($patient) ? '' : '' }}">Patient Alerts</a>
                    </li>
                    <li>
                        <a href="{{ empty($patient) ? '' : URL::route('patient.reports.progress', array('patient' => $patient->ID)) }}">Progress Report</a>
                    </li>
                    <li>
                        <a href="{{ empty($patient) ? URL::route('patient.activity.providerUIIndex', array('patient' => $patient->ID)) : '#' }}">Patient Activity Report</a>
                    </li>
                    <li>
                        <a href="{{ URL::route('patient.reports.u20', array('patient' => $patient->ID)) }}">Under 20 Minute Report</a>
                    </li>
                    <li>
                        <a href="{{ URL::route('patient.reports.billing', array('patient' => $patient->ID)) }}">Patient Billing Report</a>
                    </li>
                    <li>
                        <a href="{{ URL::route('patients.listing') }}">Patient Listing</a>
                    </li>
                </ul>
            </li>
            <li class="inline-block">
                <a href="{{ empty($patient) ? '' : '' }}" role="button">Print Care Plan</a>
            </li>
        </ul>
    </div>
</nav><!-- /navbar -->

	<!--[if lt IE 8]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->

    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">
    <script src="{{ asset('/webix/codebase/webix.js') }}" type="text/javascript"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
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
</body>
</html>
