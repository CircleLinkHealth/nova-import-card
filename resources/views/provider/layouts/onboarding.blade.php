@extends('provider.layouts.default')

<head>
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
    </style>
</head>

@section('content')

    <div class="row">
        <nav>
            <div class="nav-wrapper cyan">
                <div class="col s12">
                    <div id="step1" style="display: inline;" class="breadcrumb">Create Practice</div>
                    <div id="step2" style="display: inline;" class="breadcrumb">Add Locations</div>
                    <div id="step3" style="display: inline;" class="breadcrumb">Add Staff Members</div>
                    <div id="step4" style="display: inline;" class="breadcrumb">Welcome Page</div>
                </div>
            </div>
        </nav>
    </div>

    <div class="container">

        <div class="v-center">

            <div class="mdl-card mdl-shadow--1dp onboarding-user-card">

                <div class="mdl-card__title"></div>

                <div class="row">
                    <div class="col s12">
                        <h5 class="center-align">
                            CarePlan Manager Onboarding Process
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


