<html>
<head>
    <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #000;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            margin: 2% 5% 2% 5%;
            padding: 2% 5% 2% 5%;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: left;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <img
                src="{{asset('/img/logos/LogoHorizontal_Color.svg')}}"
                alt="Care Plan Manager"
                style="margin-bottom: 1%;"
                width="180"/>

        <h1><b><u>Apologies</u>, there's been a an issue.</b></h1>

        <h2>@if(!empty($exception->getMessage()))
                {{$exception->getMessage()}}
            @else
                The page you requested does not exist.
            @endif</h2>

        @if(!auth()->guest())
            <div style="margin-top: 5%;margin-bottom: 20%;">
                <b><h2>Where to next?</h2></b>

                <a class="btn btn-primary" href="{{ URL::previous() }}"
                   style="text-decoration:none;font-weight:bold;"><u>Go back</u></a> or

                <a class="btn btn-primary" href="{{ route('patients.dashboard', array()) }}"
                   style="text-decoration:none;font-weight:bold;"><u>Go to Dashboard</u></a>
            </div>
        @endif
    </div>
</div>
</body>
</html>
