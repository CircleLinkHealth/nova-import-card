<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AWV') }}</title>

    @stack('styles')

</head>
<body>
<div id="app">

    @yield('content')

</div>

<script src="{{ mix('js/app.js') }}"></script>

@stack('scripts')

</body>
</html>
