@extends('provider.layouts.default')

<head>
    <style>
        .onboarding-user-card > .mdl-card__title {
            color: #fff;
            height: 190px;
            background: url({{asset('/img/clh_logo.svg')}}) center / contain;
            background-repeat: no-repeat;
            padding: 0;
            margin: 0;
        }

        .onboarding-user-card.mdl-card {
            width: 500px !important;
        }
    </style>
</head>

@section('content')

    <div class="container">

        <div class="v-center">

            <div class="mdl-card mdl-shadow--1dp onboarding-user-card">

                <div class="mdl-card__title"></div>

                <div class="row">
                    <div class="col s12">
                        <h5 class="mdl-typography--text-center">
                            Welcome to CarePlan Manager!
                        </h5>

                        <div class="mdl-layout-spacer" style="height: 2%;"></div>

                        <h6>
                            @yield('instructions')
                        </h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12">
                        @yield('module')
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
