<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CCD Importer</title>

    <link rel="stylesheet" href="{{ asset('/css/stylesheet.css') }}"/>
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.0/material.teal-blue.min.css"/>
    <!-- Material Design icon font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">


    <link href="/img/favicon.png" rel="icon">

    <!-- Scripts -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <!-- Material Design Lite -->
    <script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
</head>
<body>

<nav class="mdl-grid">
    <div class="mdl-cell mdl-cell--12-col">
        <div class="mdl-typography--text-center">
            <img src="/img/cpm-logo.png" height="50" width="87.5">
        </div>
        <div class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
            <h5><b>CCD Importer</b> v4.0 | March 7th</h5>
            <h6 class="quote">"{{ Inspiring::quote() }}"</h6>
        </div>
    </div>
</nav>

@if(isset($currentVue))
    <component is="{{ $currentVue }}">
@endif

        @yield('content')

@if(isset($currentVue))
    </component>
@endif

@include('partials.footer')

<script src="{{ asset('/js/scripts.js') }}"></script>
<script src="/js/uploader.js"></script>
</body>


