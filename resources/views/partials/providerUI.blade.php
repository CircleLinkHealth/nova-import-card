<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0">
    <meta http-equiv="expires" content={{ Carbon\Carbon::now()->format('D M d Y H:i:s O') }}>
    <meta http-equiv="pragma" content="no-cache">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <script type="text/javascript">
        window.heap = window.heap || [], heap.load = function (e, t) {
            window.heap.appid = e, window.heap.config = t = t || {};
            var r = t.forceSSL || "https:" === document.location.protocol, a = document.createElement("script");
            a.type = "text/javascript", a.async = !0, a.src = (r ? "https:" : "http:") + "//cdn.heapanalytics.com/js/heap-" + e + ".js";
            var n = document.getElementsByTagName("script")[0];
            n.parentNode.insertBefore(a, n);
            for (var o = function (e) {
                return function () {
                    heap.push([e].concat(Array.prototype.slice.call(arguments, 0)))
                }
            }, p = ["addEventProperties", "addUserProperties", "clearEventProperties", "identify", "removeEventProperty", "setEventProperties", "track", "unsetEventProperty"], c = 0; c < p.length; c++) heap[p[c]] = o(p[c])
        };
        heap.load("4070082021");
    </script>

    <title>CarePlanManager - @yield('title')</title>

    <link href="{{ asset('/compiled/css/stylesheet.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/fab.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/patientsearch.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    @if(!isset($isPdf))
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    @endif

<!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


    @if(!isset($isPdf))
    <!-- http://curioussolutions.github.io/DateTimePicker/ -->
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/datetimepicker/latest/DateTimePicker.min.css"/>
        <link rel="stylesheet" href="{{ asset('/webix/codebase/webix.css') }}" type="text/css">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>
        @stack('styles')
    @endif
    <style>
        span.twitter-typeahead .twitter-typeahead {
            position: absolute !important;
        }

        #bloodhound > .twitter-typeahead > .tt-menu > .tt-dataset.tt-dataset-User_list {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body>

<div id="app">
    <!--[if lt IE 8]>
    <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a
            href="http://browsehappy.com/">upgrade
        your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome
        Frame</a>
        to improve your experience.</p>
    <![endif]-->

    @if(!isset($isPdf))
        @include('partials.providerUI.primarynav')

        @if(!empty($patient->id))
            @include('partials.providerUI.patientnav')
        @endif

        @if(!empty($patient->id))
            @include('partials.fab')
        @endif
    @endif


    @yield('content')

    @if(isset($showBanner))
        @include('partials.providerUI.notification-banner')
    @endif

</div> <!-- end #app -->
    </div>
@if(!isset($isPdf))
    @include('partials.footer')

    <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<script src="{{asset('compiled/js/app-provider-ui.js')}}"></script>
<script type="text/javascript" src="{{ asset('compiled/js/issue-688.js') }}"></script>

@include('partials.searchjs')
@include('partials.providerUItimer')

@stack('scripts')
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
    .then(function(registration) {
      console.log('Service Worker registration successful with scope: ',
       registration.scope);
    })
    .catch(function(err) {
      console.log(err);
    });
  }
  </script>
@endif
</body>

</html>
