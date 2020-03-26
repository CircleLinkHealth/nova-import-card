<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Circle Link Health - CarePlan Manager Provider Dashboard.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="practice-id" content="{{ $practice->id ?? null }}">

    <title>CarePlan Manager | @yield('title')</title>

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="@yield('meta-image-url')">

    @include('partials.hotjar-code')

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="CarePlan Manager">
    <link rel="apple-touch-icon-precomposed" href="@yield('meta-image-url')">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileColor" content="#3372DF">

    <link href="{{ mix('/img/favicon.png') }}" rel="icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>

    @yield('head')
    @stack('styles')
</head>

<body class="main-container">

<div id="app">
    @yield('content')
</div>

@include('partials.footer')

<script src="{{mix('compiled/js/app-provider-admin-panel-ui.js')}}"></script>
@stack('scripts')

</body>
</html>
