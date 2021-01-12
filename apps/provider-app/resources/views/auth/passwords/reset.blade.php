@extends('layouts.no-auth')

@section('content')

    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-sm-12">
        <form role="form" method="POST"
              action="{{ url('auth/password/reset') }}" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label class="col-sm-12">Email</label>
                <div class="col-sm-12">
                    <input type="email" class="form-control"
                           @if(! empty($user_is_patient)) readonly @endif
                           name="email" value="{{ old('email') ?: ($email ?: null) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-12">Password</label>
                <div class="col-sm-12">
                    <input type="password" class="form-control" name="password" style="text-align: left;"
                           autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-12">Confirm Password</label>
                <div class="col-sm-12">
                    <input type="password" class="form-control" name="password_confirmation" style="text-align: left;"
                           autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-12 text-center">
                    <button type="submit" class="btn btn-primary auth-submit-btn">
                        @if(! empty($user_is_patient))
                            Create/Reset Password
                        @else
                            Reset Password
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
