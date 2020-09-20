<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CarePlanManager - @yield('title')</title>

	<link href="{{ mix('/css/wpstyle.css') }}" rel="stylesheet">
	<link href="{{ mix('/img/favicon.png') }}" rel="icon">
    <style type="text/css">
        input[type=text] ,  input[type=password]  {
            display: inline-block;
            margin-bottom: 0;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            touch-action: manipulation;
            background-image: none;
            border: 1px solid ;
            white-space: nowrap;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857;
            border-radius: 4px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar primary-navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="{{ url('/') }}" class="navbar-brand"><img src="{{ mix('img/logos/LogoHorizontal_Color.svg') }}"
                                                                alt="CarePlan Manager" width='50px'
                                                                style="position:relative;top:-5px"></a>
                <a href="{{ url('/') }}" class="navbar-title Xcollapse navbar-collapse navbar-text navbar-left">CarePlan<span class="thin">Manager™</span></a>
            </div>
        </div><!-- /container-fluid -->

    </nav><!-- /navbar -->
    <div class="container">
        <section class="main-form">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    @include('core::partials.errors.errors')
                    @include('core::partials.core::partials.errors.messages')
                </div>
                @yield('content')
            </div>
        </section>
    </div>
    @stack('scripts')
</body>
</html>
