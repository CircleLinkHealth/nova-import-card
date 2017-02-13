@extends('provider.layouts.default')

@section('title', 'Provider Dashboard')

@section('head')
    <link rel="stylesheet" href="{{ asset('/css/provider-dashboard.css') }}"/>
@endsection

@section('content')
    <div class="container full-width">
        <div class="row">
            <div class="col s12">
                @include('provider.partials.topBarHeader')
            </div>

            <div class="col s12 m4 l3">

                <ul id="slide-out" class="side-nav fixed">
                    <li class="center-align">
                        <img src="{{asset('/img/clh_logo.svg')}}" height="64" class="brand-logo">
                    </li>
                    <li>
                        <div class="divider"></div>
                    </li>
                    <li>
                        @include('provider.navigation.default')
                    </li>
                </ul>


            </div>
            <main class="col s12 m8 l9">

                <div id="app" class="">
                    @yield('module')
                </div>
            </main>
        </div>

    </div>
@endsection

@section('scripts')

@endsection