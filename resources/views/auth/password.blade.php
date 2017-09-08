<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CarePlanManager - Password Reset</title>

	<link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('/compiled/css/stylesheet.css') }}" rel="stylesheet">
	<link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('/img/favicon.png') }}" rel="icon">
	<style type="text/css">
		input[type=text] ,  input[type=password]  {
			display: inline-block;
			margin-bottom: 0;
			font-weight: normal;
			text-align: center;
			vertical-align: middle;
			touch-action: manipulation;
			background-image: none;
			border: 1px solid ;
			white-space: nowrap;
			padding: 6px 12px;
			font-size: 14px;
			line-height: 1.42857;
			border-radius: 4px;
		}
	</style>
</head>
<body>
<nav class="navbar primary-navbar">
	<div class="container-fluid">
		<div class="navbar-header">
			<a href="{{ url('/') }}" class="navbar-brand"><img src="{{ url('/img/clh_logo_lt.png') }}"
															   alt="CarePlan Manager" width='50px'
															   style="position:relative;top:-15px"></a>
			<a href="{{ url('/') }}" class="navbar-title Xcollapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
		</div>
	</div><!-- /container-fluid -->

</nav><!-- /navbar -->
<div class="container-fluid">
	<section class="main-form">
		<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
				@include('errors.errors')
				@include('errors.messages')

				@if(session('status'))
					<div class="alert alert-success success" style="font-size: 20rem;">
						{{session('status')}}
					</div>
				@endif
			</div>
			<div class="main-form-container col-lg-4 col-lg-offset-4">
				<div class="row">
					<div class="main-form-title main-form-title--login">
						<h2>CarePlan<span class="text-thin">Manager&trade;</span></h2>
						Password Reset
					</div>
					<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">

						<form class="form-horizontal" role="form" method="POST"
							  action="{{ url('auth/password/email') }}">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">

							<div class="form-group">
								<label class="col-md-4 control-label">E-Mail Address</label>
								<div class="col-md-6">
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">
								</div>
							</div>

							<div class="form-group">
								<div class="col-md-6 col-md-offset-4">
									<button type="submit" class="btn btn-primary">
										Send Password Reset Link
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
</body>
</html>
