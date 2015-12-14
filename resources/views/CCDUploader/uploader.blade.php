<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CPM API</title>

    <link href="/css/stylesheet.css" rel="stylesheet">
    <link href="/img/favicon.png" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Scripts -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- http://trentrichardson.com/examples/timepicker/ -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <script src="/js/scripts.js"></script>
    <script src="/js/bootstrap-select.min.js"></script>

    <link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">

            <a class="navbar-brand" href="">
                <img src="/img/cpm-logo.png" height="40" width="70">
            </a>
        </div>

        <div class="collapse navbar-collapse text-center" id="bs-example-navbar-collapse-1">
            <h1 style="color: cornflowerblue;">CCD Importer</h1>
            <h5>Drop CCD Records in the box below, or click Choose Files to browse your computer for CCDs:</h5>
        </div>
    </div>
</nav>

<!--[if lt IE 8]>
<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
<![endif]-->

<div id="ccd-uploader">
    <form method="POST" v-on:submit="onSubmitForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="ccd"></label>
            <input type="file" id="ccd" class="dropzone" multiple>

        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-green">
                Upload CCD Records
            </button>
        </div>
    </form>
</div>

<script src="{{ asset('/js/ccd/bluebutton.min.js') }}"></script>
<script src="/js/uploader.js"></script>
{{--<script src="{{ asset('/js/ccd/ccdParseUpload.js') }}"></script>--}}
