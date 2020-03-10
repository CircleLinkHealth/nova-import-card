<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AWV') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
<div id="app">
    <navigation user-display-name="{{ optional(Auth::user())->display_name }}"
                :is-login-route="@json(Route::is('login'))"
                :is-guest="@json(Auth::guest())">
    </navigation>

    <main class="py-4">
        @yield('content')
    </main>
</div>

<!-- Scripts -->
{{--    <script src="{{ mix('js/manifest.js') }}" defer></script>--}}
{{--    <script src="{{ mix('js/vendor.js') }}" defer></script>--}}
<script src="{{ mix('js/app.js') }}" defer></script>

@stack('scripts')
@include('partials.sentry-js')

</body>
</html>
