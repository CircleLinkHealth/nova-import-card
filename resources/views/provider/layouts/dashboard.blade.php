@extends('provider.layouts.default')

@section('title', 'Provider Dashboard')

@section('content')
    <div class="container full-width">

        <div class="row">
            <div class="col s3">
                @include('provider.partials.avatar')
                @include('provider.navigation.default')
            </div>
            <main class="col s9">

                <div class="col s12">
                    @include('provider.partials.topBarHeader')
                </div>

                <div id="app" class="">
                    @yield('module')
                </div>
            </main>

        </div>

    </div>
@endsection

@section('scripts')

@endsection