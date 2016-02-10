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
</head>
<body>
<div class="container-fluid">
	<section class="main-form">
		<div class="row">
			<div class="main-form-container col-lg-4 col-lg-offset-4">
				<div class="row">
					<div class="main-form-title main-form-title--login">
						<h4 class="text-sans-serif text-thin">Welcome to</h4>
						<h2>CarePlan<span class="text-thin">Manager&trade;</span></h2>
					</div>
						<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
							<form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">

								<div class="form-group">
									<label class="col-md-4 control-label" for="email">Username</label>
									<div class="col-md-6">
										<input type="text" class="form-control" name="email" value="{{ old('email') }}">
									</div>
								</div>

								<div class="form-group">
									<label class="col-md-4 control-label" for="password">Password</label>
									<div class="col-md-6">
										<input type="password" class="form-control" name="password">
									</div>
								</div>

								<div class="form-group" style="margin-top:25px;">
									<div class="col-md-12 text-center">
										<button type="submit" class="btn btn-primary">Log In</button><br />

										<a class="btn btn-link" href="{{ url('/password/email') }}">Lost Your Password?</a>
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
