@extends('cpm-admin::partials.adminUI')

@section('content')

    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
        <form class="form-prevent-multi-submit" role="form" method="POST" action="{{ url('/auth/login') }}"
              autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="col-lg-12 col-sm-10">
                <p>
                    <label class="col-md-12 control-label" for="email">Email or Username</label>
                    <input id="email" type="text" class="form-control" name="email"
                           value="{{ old('email') }}">
                </p>
            </div>

            <div class="col-md-12">
                <p>
                    <label class="col-md-12 control-label" for="password">Password</label>
                    <input id="password" type="password" class="form-control" name="password">
                </p>
            </div>

            <div class="form-group" style="margin-top:25px;">
                <div class="col-md-12 text-center">
                    <button id="login-submit-button" type="submit"
                            class="btn btn-primary btn-large btn-prevent-multi-submit auth-submit-btn"
                            style="background-color: #50B2E2;">Log In
                    </button>
                    <br/>

                    <a class="btn btn-link" href="{{ url('auth/password/reset') }}">Lost/Need a
                        password? Click Here</a>
                </div>
            </div>
        </form>
    </div>

@endsection


