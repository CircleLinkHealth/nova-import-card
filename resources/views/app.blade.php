<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CPM API</title>

	<link href="{{ asset('/css/app.css') }}" rel="stylesheet">
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
</head>
<body>
	@if(!Request::is('patient/*'))
		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle Navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="{{ url('/') }}">
						<img src="{{ asset('/img/cpm-logo.png') }}" height="40" width="70">
					</a>
				</div>

				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

					<ul class="nav navbar-nav">
						@if ( ! Auth::guest())
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									Users <span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ URL::route('users.index', array()) }}">All Users</a></li>
									<li><a href="{{ URL::route('admin.observations.index', array()) }}">Observations</a></li>
									<li><a href="{{ URL::route('admin.comments.index', array()) }}">Comments</a></li>
									<li><a href="{{ URL::route('admin.ucp.index', array()) }}">UCP</a></li>
								</ul>
							</li>
						@endif

						@if ( ! Auth::guest() && Auth::user()->hasRole(['administrator', 'developer']))
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									Roles<span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ url('admin/roles') }}">Roles</a></li>
									<li><a href="{{ URL::route('admin.permissions.index', array()) }}">Permissions</a></li>
								</ul>
							</li>
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									Programs <span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ action('Admin\WpBlogController@index') }}">Programs</a></li>
									<li><a href="{{ action('LocationController@index') }}">Locations</a></li>
									<li><a href="{{ URL::route('admin.questions.index', array()) }}">Questions</a></li>
									<li><a href="{{ URL::route('admin.items.index', array()) }}">Items</a></li>
								</ul>
							</li>
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									Activities <span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ url('activities/') }}">Activities</a></li>
									<li><a href="{{ action('PageTimerController@index') }}">Page Timer</a></li>
								</ul>
							</li>
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									Rules <span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ action('RulesController@index') }}">Rules</a></li>
									<li><a href="{{ url('rules/create/') }}">Add new</a></li>
								</ul>
							</li>
							<li role="presentation" class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
									API<span class="caret"></span>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ action('ApiKeyController@index') }}">API Keys</a></li>
									<li><a href="{{ action('Redox\ConfigController@create') }}">Redox Engine</a></li>
									<li><a href="{{ action('qliqSOFT\ConfigController@create') }}">qliqSOFT</a></li>
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
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">{{ Auth::user()->user_nicename }} [ID:{{ Auth::user()->ID }}] [WP Role:{{ Auth::user()->role() }}]<span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="{{ url('/auth/logout') }}">Logout</a></li>
								</ul>
							</li>
						@endif
					</ul>
				</div>
			</div>
		</nav>
	@endif



	@if( !Auth::guest() && (Request::is('patient/*') || Request::is('provider/*')) )
		<nav class="navbar primary-navbar">
			<div class="container-fluid">
				<div class="navbar-header">
					<a href="" class="navbar-brand btn btn-orange">{{ $program->blog_id }}</a>
					<a href="" class="navbar-title collapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
				</div>
				<div class="navbar-right hidden-xs ">
					<ul class="nav navbar-nav">
						<li><a href=""><i class="icon--home--white"></i> Home</a></li>
						<li><a href=""><i class="icon--search--white"></i> Select Patient</a></li>
						<li><a href=""><i class="icon--add-user"></i> Add Patient</a></li>
						<li><a href="{{ URL::route('patient.alerts', array('programId' => $program->blog_id)) }}"><i class="icon--alert--white"></i> Alerts</a></li>
						@if ( !Auth::guest() && Auth::user()->hasRole(['administrator', 'developer']))
							@if (!empty($patient))
								<li><a class="btn btn-orange btn-xs" href="{{ URL::route('users.edit', array('id' => $patient->ID)) }}"><i class="icon--home--white"></i> Back to Admin</a></li>
							@else
								<li><a class="btn btn-orange btn-xs" href="{{ URL::route('users.index', array()) }}"><i class="icon--home--white"></i> Back to Admin</a></li>
							@endif
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
					@if (!empty($patient))
						<li class="inline-block dropdown">
							<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Notes/Offline Activity<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ URL::route('patient.notes', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}">Notes/Offline Activities</a></li>
								<li><a href="{{ URL::route('patient.notes', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}">Add New Note</a></li>
							</ul>
						</li>
						<li class="inline-block"><a href="{{ URL::route('patient.summary', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}" role="button">Patient Overview</a></li>
						<li class="inline-block"><a href="{{ URL::route('patient.careplan', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}" role="button">Edit Care Plan</a></li>
						<li class="inline-block dropdown">
							<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Input<span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="{{ URL::route('patient.observation.create', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}">Observations</a></li>
								<li><a href="">Offline Activities</a></li>
							</ul>
						</li>
					@endif
					<li class="inline-block dropdown">
						<a href="" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" omitsubmit="yes">Patient Reports <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							@if (!empty($patient))
								<li><a href="">Patient Alerts</a></li>
								<li><a href="">Progress Report</a></li>
								<li><a href="">Patient Activity Report</a></li>
							@endif
							<li><a href="">Under 20 Minute Report</a></li>
							<li><a href="">Patient Billing Report</a></li>
							<li><a href="">Patient Listing</a></li>
						</ul>
					</li>
					@if (!empty($patient))
						<!-- <li class="inline-block"><a href="" role="button">Patient Notes</a></li> -->
						<li class="inline-block"><a href="{{ URL::route('patient.careplan.print', array('programId' => $program->blog_id, 'id' => $patient->ID)) }}" role="button">Print Care Plan</a></li>
					@endif
				</ul>
			</div>
		</nav><!-- /navbar -->
	@endif

	<!--[if lt IE 8]>
	<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
	<![endif]-->

	@yield('content')
</body>
</html>
