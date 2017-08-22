<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CCD Importer</title>

    <link rel="stylesheet" href="{{ asset('/compiled/css/stylesheet.css') }}"/>
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.3/material.blue-green.min.css" />
    <!-- Material Design icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">


    <link href="/img/favicon.png" rel="icon">

    <!-- Scripts -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <!-- Material Design Lite -->
    <script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
</head>
<body>

@if(isset($currentVue))
    <component is="{{ $currentVue }}">
@endif

        @yield('content')

@if(isset($currentVue))
    </component>
@endif

@include('partials.footer')

<script src="{{ asset('/compiled/js/scripts.js') }}"></script>
<script src="/js/uploader.js"></script>
</body>


