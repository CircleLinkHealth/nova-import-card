<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>CCD Importer</title>
    <link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">


    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href="{{asset('/img/favicon.png')}}" rel="icon">
</head>
<body>
    <div class="container" id="app">
        @include('partials.importerHeader')

        @yield('content')
    </div>



    <script src="{{ asset('compiled/js/app-provider-ui.js') }}"></script>
</body>
</html>

