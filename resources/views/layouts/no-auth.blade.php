<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CarePlanManager - Log In</title>

    @include('partials.hotjar-code')
    @include('cpm-module-raygun::partials.real-user-monitoring')

    <link href="{{ mix('/css/wpstyle.css') }}" rel="stylesheet">
    <link href="{{ mix('/compiled/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ mix('/img/favicon.png') }}" rel="icon">
    <style type="text/css">
        input[type=text], input[type=password] {
            display: inline-block;
            margin-bottom: 0;
            font-weight: normal;
            vertical-align: middle;
            touch-action: manipulation;
            background-image: none;
            border: 1px solid;
            white-space: nowrap;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857;
            border-radius: 4px;
        }

        .auth-pages-title {
            margin: 5px 0;
        }
        .auth-pages-title-container {
            font-family: "Roboto Slab",Georgia,Times,"Times New Roman",serif;
            background: #50b2e2;
            color: #fff;
            text-align: center;
        }

        .auth-submit-btn {
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <section class="main-form col-lg-12 col-sm-11">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                @include('errors.errors')
                @include('errors.messages')
            </div>
            <div class="main-form-container col-lg-4 col-lg-offset-4 col-sm-4 col-sm-offset-4"
                 style="box-shadow: #28323336 2px -2px 10px;">
                <div class="row">
                    <div class="main-form-title--login">
                        <div class="col-lg-12"
                             style="background-color: white;margin: 0 0px 10px 0px;border-top: #50b2e1 3px solid;">
                            <div class="col-lg-8 col-lg-offset-2">
                                <img class="img-responsive" src="{{ mix('img/logos/LogoHorizontal_Color.svg') }}"
                                     alt="CarePlan Manager">
                            </div>
                        </div>
                        <div class="col-lg-12 auth-pages-title-container">
                            <h2 class="auth-pages-title">CarePlan<span class="text-thin">Manager&trade;</span></h2>
                        </div>
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </section>
</div>

<script src="{{mix('js/prevent-multiple-submits.js')}}"></script>
</body>
</html>