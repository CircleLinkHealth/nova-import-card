<html>
<head>

    <meta charset="utf-8">
    <title>@yield('title')</title>

    <meta id="token" name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<!-- Dropdown Structure -->
<ul id="dropdown1" class="dropdown-content">
    <li><a href="{{ url('/auth/logout') }}">
            Logout
        </a></li>
</ul>
<nav style="position: fixed">
    <div class="nav-wrapper" style="background: #4fb2e2;">
        <a href="#!" style="padding-left: 10px" class="brand-logo">CircleLink Health Enrollment Center</a>
        <ul class="right hide-on-med-and-down">
            @if(auth()->user()->hasRole('care-ambassador-view-only'))
                <li><a href="{{route('patients.dashboard')}}">Patient Dashboard</a></li>
            @endif

            @if(isset($enrollee) && ($enrollee->practice->enrollmentTips() ?? collect())->count() > 0)
                <li>
                    <!-- #tips is a modal in dashboard.blade -->
                    <a href="#tips" id="tips-link" class="modal-trigger">
                        Tips
                    </a>
                </li>
            @endif

            <li>
                <a href="https://circlelinkhealth.zendesk.com/hc/en-us/categories/360002207051-Care-Ambassador-Support" target="_blank">
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
</nav>

<script>
    $(document).ready(function () {
        M.Dropdown.init($('.dropdown-trigger'), {
            coverTrigger: false,
        });
    });
</script>

@yield('content')

</html>
