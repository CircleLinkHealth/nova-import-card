<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en-US"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <base href="{{asset('')}}">

    <title>CarePlanManager - @yield('title')</title>

    <link href="{{ mix('/css/patientsearch.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css"
          integrity="sha256-/oZ7h/Jkj6AfibN/zTWrCoba0L+QhP9Tf/ZSgyZJCnY=" crossorigin="anonymous"/>

    <link href="{{ mix('/css/wpstyle.css') }}" rel="stylesheet">

    @if (\Illuminate\Support\Str::contains(optional(Route::getCurrentRoute())->getName(), 'admin'))
        <link href="{{mix('/css/bootstrap.min.css')}}" rel="stylesheet">
    @endif

    <link href="{{ mix('/img/favicon.png') }}" rel="icon">

    @if(!isset($isPdf))
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css"
              integrity="sha256-iWTx/iC9IoKaoSKD5+WVFef8ZYNIgQ4AxVpMbBw2hig=" crossorigin="anonymous"/>
    @endif

<!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


    @if(!isset($isPdf))
        <link rel="stylesheet" type="text/css"
              href="//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css"/>

        <link rel="stylesheet" href="{{ mix('/webix/codebase/webix.css') }}" type="text/css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet"/>
    @endif
    <style>
        span.twitter-typeahead .twitter-typeahead {
            position: absolute !important;
        }
    </style>
    <style>
        .custom-tooltip {
            display: none;
            z-index: 9999999;
            position: absolute;
            border: 1px solid #333;
            background-color: #161616;
            border-radius: 5px;
            padding: 10px;
            color: #fff;
            font-size: 12px;
        }
    </style>
    @stack('styles')
    @include('modules.raygun.partials.real-user-monitoring')
    @include('core::partials.new-relic-tracking')
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
    @yield('app')

</div> <!-- end #app -->

@if(!isset($isPdf))
    @include('partials.footer')

    <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

@if (Agent::isIE())
    <!-- Script for polyfilling Promises on IE9 and 10 -->
    <script src='https://cdn.polyfill.io/v2/polyfill.min.js'></script>
    <script src="{{ mix('js/polyfills/es7-object-polyfill.min.js') }}"></script>
@endif

@include('partials.providerUItimer')
@stack('prescripts')

<script type="text/javascript" src="{{mix('compiled/js/app-provider-ui.js')}}"></script>
<script type="text/javascript" src="{{ mix('compiled/js/issue-688.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/intro.min.js"
        integrity="sha256-fOPHmaamqkHPv4QYGxkiSKm7O/3GAJ4554pQXYleoLo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.js"></script>

@stack('scripts')

<script src="{{mix('js/prevent-multiple-submits.js')}}"></script>
<script>
    $(function () {
        try {
            //bootstrap selectpicker is found in issue-688.js (see webpack.mix.js)
            $('.selectpicker').selectpicker('refresh');
        } catch (e) {
            console.debug(e);
        }
    });
</script>
<script>
    (function ($) {
        const maxAttempts = 5;
        const registered = [];

        function setupTooltips(attempt) {
            if (attempt >= maxAttempts) {
                return;
            }

            //try again because some elements are rendered on server side (blade.php) and some on client side (vue.js)
            //vue.js rendering depends on client machine performance
            setTimeout(() => setupTooltips(attempt + 1), 500 * attempt);
            $('.with-tooltip')
                .each(function (index) {
                    if (registered[this]) {
                        return;
                    }
                    registered[this] = 1;
                    $(this).hover(function () {
                        // Hover over code
                        var title = $(this).attr('title');

                        $(this)
                            .data('tipText', title)
                            .removeAttr('title');

                        $('<p class="custom-tooltip"></p>')
                            .text(title)
                            .appendTo('body')
                            .fadeIn('slow');

                    }, function () {
                        // Hover out code
                        $(this).attr('title', $(this).data('tipText'));
                        $('.custom-tooltip').remove();
                    })
                        .mousemove(function (e) {
                            var mousex = e.pageX + 20; //Get X coordinates
                            var mousey = e.pageY + 10; //Get Y coordinates
                            $('.custom-tooltip').css({top: mousey, left: mousex})
                        });
                });

        }

        setupTooltips(1);
    })(jQuery);
</script>
@endif

@auth
    @if(!isset($isPdf) && (auth()->user()->isAdmin() || auth()->user()->isCareCoach()))
        @include('partials.jira-issue-collector')
    @endif
@endauth

@include('core::partials.sentry-js')
</body>

</html>
