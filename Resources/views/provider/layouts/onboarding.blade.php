@extends('cpm-admin::provider.layouts.default')

@section('head')
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
                width: 650px !important;
            }

            .container {
                padding-top: 3%;
                background-color: #ffffff;
                min-height: 100%;
            }

            .breadcrumb:before {
                color: #dadada !important;
            }
        </style>

        <link rel="stylesheet" href="{{ asset('/css/onboarding.css') }}"/>
    @endpush
@endsection

@section('content')

    <div class="container">

        <div class="v-center">

            <div class="mdl-card mdl-shadow--1dp onboarding-user-card">

                <div class="mdl-card__title"></div>

                <br>


                <div class="row">
                    <nav class="transparent z-depth-0">
                        <div class="nav-wrapper">
                            <div class="col s12 center-align">
                                <div id="step0" style="display: inline;"
                                     class="breadcrumb light-blue-text text-lighten-4">Lead
                                </div>
                                <div id="step1" style="display: inline;"
                                     class="breadcrumb light-blue-text text-lighten-4">Organization
                                </div>
                                <div id="step2" style="display: inline;"
                                     class="breadcrumb light-blue-text text-lighten-4">Location(s)
                                </div>
                                <div id="step3" style="display: inline;"
                                     class="breadcrumb light-blue-text text-lighten-4">CCM Staff
                                </div>
                                <div id="step4" style="display: inline;"
                                     class="breadcrumb light-blue-text text-lighten-4">Welcome!
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>

                <div class="row">
                    <div class="col s12">
                        <h5 class="left-align">
                            @yield('instructions')
                        </h5>

                        <div class="mdl-layout-spacer" style="height: 2%;"></div>
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


