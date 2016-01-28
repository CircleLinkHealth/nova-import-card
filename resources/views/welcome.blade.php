<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CPM API - {!! Route::current()->getName() !!}</title>

	<link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
	<link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
	<link href="{{ asset('/img/favicon.png') }}" rel="icon">
</head>
<body>
<div class="container-fluid">
	<div class="content text-center" style="margin-top:30px;">
		<a href="http://www.circlelinkhealth.com">
			<img src="img/logo.svg" alt="Logo" class="logo--small" style="width:200px;margin-top:50px;">
		</a>
	</div>
	<div class="row" style="margin-top:30px;">
		<div class="col-md-6 col-md-offset-3">




			<div class="row" style="margin-top:60px;">
				<div class="main-form-container col-lg-8 col-lg-offset-2">
					<div class="row">
						<div class="col-lg-12 text-center" style="background:#50B2E2;color:#fff;padding-bottom:5px;">
							<h3>Welcome to <strong>CarePlan</strong>Manager</h3>
						</div>
						<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
							<form class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">

								<div class="form-group">
									<label class="col-md-4 control-label">Username</label>
									<div class="col-md-6">
										<input type="text" class="form-control" name="email" value="{{ old('email') }}">
									</div>
								</div>

								<div class="form-group">
									<label class="col-md-4 control-label">Password</label>
									<div class="col-md-6">
										<input type="password" class="form-control" name="password">
									</div>
								</div>

								<div class="form-group">
									<div class="col-md-6 col-md-offset-4">
										<button type="submit" class="btn btn-primary">Login</button>

										<a class="btn btn-link" href="{{ url('/password/email') }}">Forgot Your Password?</a>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>










		</div>
	</div>
</div>
</body>
</html>
