@extends('provider.layouts.default')

@section('title', 'Provider Dashboard')

@section('content')
    <div class="provider-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">

        @include('provider.partials.topBarHeader')

        <div class="provider-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">

            @include('provider.partials.avatar')

            @include('provider.navigation.default')

        </div>
        <main class="mdl-layout__content mdl-color--grey-100">
            <div class="mdl-grid provider-content ">
                <div id="app" class="mdl-cell mdl-cell--12-col">
                    @yield('module')
                </div>
            </div>
        </main>
    </div>
@endsection