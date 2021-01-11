@extends('layouts.no-auth')

@section('content')
	<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
		<form class="form-horizontal" role="form" method="POST" style="padding-top: 15px;"
			  action="{{ url('auth/password/email') }}" autocomplete="off">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			<div class="form-group">
				<label class="col-md-12">Email Address</label>
				<div class="col-md-12">
					<input type="email" class="form-control" name="email" value="{{ isset($email) && ! empty($email) ? $email : (old('email') ?: null) }}" autocomplete="off" placeholder="someone@example.com">
				</div>
			</div>

			<div class="form-group">
				<div class="text-center col-md-12">
					<button type="submit" class="btn btn-primary auth-submit-btn">
						Send Password Reset Link
					</button>
				</div>
			</div>
		</form>
	</div>
@endsection
