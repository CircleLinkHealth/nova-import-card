<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AWV') }}</title>

    <!-- Scripts -->
    {{--    <script src="{{ mix('js/manifest.js') }}" defer></script>--}}
    {{--    <script src="{{ mix('js/vendor.js') }}" defer></script>--}}
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @stack('styles')

</head>
<body>
<div id="app">

    @yield('content')

</div>

{{--<script src="{{mix('js/manifest.js')}}"></script>--}}
{{--<script src="{{mix('js/vendor.js')}}"></script>--}}
<script src="{{mix('js/app.js')}}"></script>

</body>
</html>
