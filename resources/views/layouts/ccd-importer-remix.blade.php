<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>CCD Importer</title>
    <link rel="stylesheet" href="{{ asset('/compiled/css/stylesheet.css') }}"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="/img/favicon.png" rel="icon">
</head>
<body>
    <div id="app">
        @include('partials.importerHeader')

        @yield('content')
    </div>

    @include('partials.footer')

    <script src="{{ asset('compiled/js/app-ccd-importer.js') }}"></script>
</body>
</html>

