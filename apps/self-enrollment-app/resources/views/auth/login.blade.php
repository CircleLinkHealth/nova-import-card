<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CarePlanManager | Log In</title>

    @include('cpm-module-raygun::partials.real-user-monitoring')
    @include('core::partials.new-relic-tracking')

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
</head>
<body>
@include('core::partials.errors.errors')

<div class="container-fluid" style="margin-top: 10%;">
    <section class="main-form col-lg-12 col-sm-11">
        <div class="col-lg-4 col-lg-offset-4">
            <div class="row">
                <div class="col-lg-6 col-lg-offset-3">
                    <img class="img-responsive" src="{{ asset('img/logos/LogoHorizontal_Color.svg') }}"
                         alt="CarePlan Manager">
                </div>
            </div>
            <div class="row">
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <form class="form-prevent-multi-submit" role="form" method="POST"
                          action="{{ url('/auth/login') }}"
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
            </div>
        </div>
    </section>
</div>
</body>
</html>
