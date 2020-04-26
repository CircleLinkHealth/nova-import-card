<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">

<!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>


    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>


    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get a Care Coach</title>
    {{--    <link href="{{ mix('css/enrollmentSurveyAuth') }}" rel="stylesheet">--}}

    @stack('styles')
</head>

<div id="app" class="enrollment-survey" style="background-color: #f2f6f9; min-height: 100%;">
    @yield('content')
</div>


</html>
