@extends('provider.layouts.default')

@section('title', 'Provider Dashboard')

@section('head')
    <link rel="stylesheet" href="{{ asset('/css/provider-dashboard.css') }}"/>
@endsection

<style>
    main, header {
        padding-left: 300px;
    }
</style>

@section('content')
    <header>
        <div class="row">
            @include('provider.partials.topBarHeader')
        </div>

        <ul id="slide-out" class="side-nav" style="transform: translateX(0);">
            <li class="center-align">
                <img src="{{asset('/img/clh_logo.svg')}}" height="64" class="brand-logo">
            </li>
            <li>
                <div class="col s12" style="background-color: rgba(173, 216, 230, 0.42);color: #74b0d7;">
                    <p class="center-align">{{$practice->display_name}}</p>
                </div>
            </li>
            <li>
                <div class="divider"></div>
            </li>
            <li>
                @include('provider.navigation.default')
            </li>
        </ul>
    </header>

    <main>
        <div style="padding: 2%;">
            @yield('module')
        </div>
    </main>
@endsection

@section('scripts')

@endsection