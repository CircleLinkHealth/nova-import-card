<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CarePlanManager - Log In</title>

	<link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
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
                                                                       style="position:relative;top:-5px"></a>
				   	<a href="{{ url('/') }}" class="navbar-title Xcollapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
				</div>
			</div><!-- /container-fluid -->

		</nav><!-- /navbar -->
<div class="container">
	<section class="main-form">
		<div class="row">
			<div class="col-lg-8 col-lg-offset-2">
				@include('errors.errors')
				@include('errors.messages')
			</div>
			<div class="main-form-container col-lg-4 col-lg-offset-4">
				<div class="row">
					<div class="main-form-title main-form-title--login">
						<h4 class="text-sans-serif text-thin">Welcome to</h4>
						<h2>CarePlan<span class="text-thin">Manager&trade;</span></h2>
					</div>
						<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
							<form class="" role="form" method="POST" action="{{ url('/auth/login') }}">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">

									<div class="col-md-12">
								<p>
                                    <label class="col-md-12 control-label text-center" for="email">Username</label></BR>
									<input id="email" type="text" class="form-control" name="email"
										   value="{{ old('email') }}">
								</p>
									</div>

									<div class="col-md-12">
								<p>
									<label class="col-md-12 control-label text-center" for="password">Password</label></br>
									<input id="password" type="password" class="form-control" name="password">
								</p>
									</div>

								<div class="form-group" style="margin-top:25px;">
									<div class="col-md-12 text-center">
										<button type="submit" class="btn btn-primary btn-large" style="background-color: #50B2E2;">Log In</button><br />

										<a class="btn btn-link" href="{{ url('auth/password/reset') }}">Lost/Need a
											password? Click Here</a>
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
