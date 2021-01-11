<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CPM | Error</title>

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
        <h4>Care Plan Manager</h4>

        <h1><b><u>Apologies</u>, there's been an issue.</b></h1>

        <h2>
            This is what the server said:
        </h2>

        <h3>
            @if(!empty(session('saml2_error')) && isset(session('saml2_error')['last_error_reason']))
                {{session('saml2_error')['last_error_reason']}}
            @else
                Please try again.
            @endif
        </h3>
    </div>
</div>
</body>
</html>
