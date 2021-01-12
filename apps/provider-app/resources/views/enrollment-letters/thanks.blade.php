<html>
<head>

    <meta charset="utf-8">
    <title>Enroll</title>

    <meta id="token" name="csrf-token" content="{{ csrf_token() }}">

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>

    <style>
        .success-card {
            margin-top: 23px;
            height: 350px;
            background: #26a69a;
            font-size: 2em;
            text-align: center;
        }
    </style>

</head>

<nav>
    <div class="nav-wrapper center">
        <div class="mdl-layout__header-row" style="background: #4fb2e2; padding-left: 10px">
            <span class="mdl-layout__title"
                  style="color: white; font-size: 1.4em;">On Behalf of Dr. {{$enrollee->provider->last_name}}’s Office</span>
        </div>
    </div>
</nav>



<div class="container">

    <div class="row">
        <div class="col s12 m5">
            <div class="card-panel success-card">
                <p><i class="material-icons" style="color: white; font-size: 100px;">done</i></p>
          <span class="white-text" >Thanks, {{$enrollee->first_name}} <br/> <br/> We'll be in touch soon!
          </span>
            </div>
        </div>
    </div>

</div>

</html>

