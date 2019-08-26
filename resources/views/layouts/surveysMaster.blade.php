<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AWV') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

    <!-- Styles -->

    @if (isset($isPdf) && $isPdf)
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @else
        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @endif

    @stack('styles')

</head>
<body>
<div id="app">

    @yield('content')

</div>

@if (isset($isPdf) && $isPdf)
    <script src="{{ asset('js/app.js') }}"></script>
@else
    {{--<script src="{{mix('js/manifest.js')}}"></script>--}}
    {{--<script src="{{mix('js/vendor.js')}}"></script>--}}
    <script src="{{ mix('js/app.js') }}"></script>
@endif

@stack('scripts')

</body>
</html>
