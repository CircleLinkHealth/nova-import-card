<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0">
    <meta http-equiv="expires" content={{ Carbon\Carbon::now()->format('D M d Y H:i:s O') }}>
    <meta http-equiv="pragma" content="no-cache">

    <title>CarePlanManager - Error</title>

    <link href="{{ asset('/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <link href='//fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>

    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Scripts -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- http://trentrichardson.com/examples/timepicker/
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>-->

    <script>
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

        ga('create', 'UA-9084372-23', 'auto');
        ga('send', 'pageview');

    </script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
    <script src="{{ asset('/js/scripts.js') }}"></script>
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('/js/typeahead.bundle.js') }}"></script>

    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>
    <script type="text/javascript" src="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.js"></script>

    <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">
    <script src="{{ asset('/webix/codebase/webix.js') }}" type="text/javascript"></script>

    <!-- select2 -->
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #000;
            display: table;
            font-weight: 300;
        }

        .container {
            width: 100%;
            display: table-cell;
            vertical-align: middle;
        }

        .title {
            font-size: 32px;
            font-family: 'Lato';
        }
    </style>
</head>
<body>
<div class="container">
    <div style="width: 80%; margin-left: 10%;">

        <h2 class="title"><b>Sorry to interrupt: </b><br><br>
            You already have a patient session open.<br><br>
            Are you sure you want to continue?<br>
            (You may lose time ONLY if you have multiple tabs open.)<br>
        </h2>

        <hr style="width: 100%; text-align: left;">

        <br>

        <a href="{{URL::previous()}}" class="btn btn-warning">No</a>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="{{URL::current() . '?clearSession=asd7abacd7asjkdy3'}}" class="btn btn-success">Yes</a>
    </div>
</div>

@include('partials.providerUItimer')

</body>
</html>
