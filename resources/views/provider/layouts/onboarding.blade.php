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

    <div class="mdl-layout mdl-js-layout">
        <div class="mdl-grid full-height">

            <div class="v-center">

                <div class="mdl-card mdl-shadow--1dp onboarding-user-card">

                    <div class="mdl-card__title"></div>

                    <div class="mdl-cell--12-col">
                        <h5 class="mdl-typography--text-center">
                            Welcome to CarePlan Manager!
                        </h5>

                        <div class="mdl-layout-spacer" style="height: 2%;"></div>

                        <h6>
                            @yield('instructions')
                        </h6>
                    </div>

                    <div class="mdl-cell--12-col">
                        @yield('module')
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
