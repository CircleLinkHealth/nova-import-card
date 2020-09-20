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
            margin: 0;
            padding: 0;
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 32px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">@if(isset($message)) {!! $message !!} @else Unauthorized. You don't have permissions to view
            this page. @endif</div>

        @if(isset($hideLinks))
        @else
            <a class="btn btn-primary" href="{{ URL::previous() }}"
               style="text-decoration:none;font-weight:bold;">Go back</a> or

            <a class="btn btn-primary" href="{{ route('home', array()) }}"
               style="text-decoration:none;font-weight:bold;">Go to Homepage</a>
        @endif
    </div>
</div>
</body>
</html>
