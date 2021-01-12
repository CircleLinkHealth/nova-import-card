<html>
<head>

    <meta charset="utf-8">
    <title>@yield('title')</title>

    <meta id="token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <link href="{{ mix('/css/enrollablesearch.css') }}" rel="stylesheet">

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <style>
        span.twitter-typeahead .twitter-typeahead {
            position: absolute !important;
        }
    </style>
    @include('modules.raygun.partials.real-user-monitoring')
    @include('partials.new-relic-tracking')
</head>
<body>
@stack('prescripts')

<script type="text/javascript" src="{{ mix('compiled/js/issue-688.js') }}"></script>

@stack('scripts')


<!-- Dropdown Structure -->
<ul id="dropdown1" class="dropdown-content">
    <li><a href="{{ url('/auth/logout') }}">
            Logout
        </a></li>
</ul>
<nav style="position: fixed">
    <div class="nav-wrapper" style="background: #4fb2e2;">
        <a href="javascript:void(0)" onclick="location.reload()" style="padding-left: 10px" class="brand-logo">CircleLink
            Health Enrollment Center</a>

        <div style="width: 100%; height: 64px;">
            <ul class="right hide-on-med-and-down">
                <li> @include('enrollment-ui.search')</li>
                @if(auth()->user()->hasRole('care-ambassador-view-only'))
                    <li><a href="{{route('patients.dashboard')}}">Patient Dashboard</a></li>
                @endif

                <li id="has_tips" style="display: none">
                    <!-- #tips is a modal in patient-to-enroll.vue -->
                    <a href="#tips" id="tips-link" class="modal-trigger">
                        Tips
                    </a>
                </li>

                <li>
                    <a href="https://circlelinkhealth.zendesk.com/hc/en-us/categories/360002207051-Care-Ambassador-Support"
                       target="_blank">
                        Enrollment Resources
                    </a>
                </li>
                <!-- Dropdown Trigger -->
                <li>
                    <a class="dropdown-trigger" href="#" data-target="dropdown1">
                        {{ auth()->user()->getFullName() }}
                        <i class="material-icons right">settings</i></a>
                </li>
            </ul>
        </div>

    </div>

</nav>

<script>
    $(document).ready(function () {
        M.Dropdown.init($('.dropdown-trigger'), {
            coverTrigger: false,
        });

        function withApp(callback) {
            if (typeof App === 'undefined') {
                setTimeout(() => withApp(callback), 500);
                return;
            }
            callback(App);
        }

        withApp((app) => {
            app.$on('enrollable:loaded', (data) => {
                if (data.has_tips) {
                    $('#has_tips').show();
                }
            })
        });
    });
</script>

@yield('content')

@include('partials.sentry-js')
</body>

</html>
