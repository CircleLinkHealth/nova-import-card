@extends('provider.layouts.default')

<head>
    @push('styles')
        <style>
            .onboarding-user-card > .mdl-card__title {
                color: #fff;
                height: 170px;
                background: url({{asset('/img/clh_logo.svg')}}) center / contain;
                background-repeat: no-repeat;
                padding: 0;
                margin: 0;
            }

            .onboarding-user-card.mdl-card {
                width: 600px !important;
            }

            .container {
                padding-top: 3%;
                background-color: #ffffff;
                min-height: 100%;
            }

            body, html {
                background-color: #ffffff !important;
            }
        </style>
    @endpush
</head>

@section('content')

    <div class="container">

        <div class="v-center">

            <div class="mdl-card mdl-shadow--1dp onboarding-user-card">

                <div class="mdl-card__title"></div>

                <div class="row">
                    <div class="col s12">
                        <h5 class="center-align">
                            Welcome to CarePlan Manager!
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12">
                        <h6>Congratulations! You have completed the onboarding process. <a href="{{url('/')}}">Click
                                here</a> to go to the homepage.</h6>
                        <h6>If anything comes up, feel free to reach out. Peace.</h6>
                    </div>
                </div>

                <div class="row">
                    <img src="http://www.washingtonpolicy.org/library/imglib/image-success.gif">
                </div>
            </div>

        </div>

    </div>
@endsection
