<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CPM API</title>

    <link href="/css/animate.min.css" rel="stylesheet">
    <link href="/css/stylesheet.css" rel="stylesheet">
    <link href="/img/favicon.png" rel="icon">

    <!-- Fonts -->
    <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <!-- Scripts -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
</head>
<body>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">

            <a class="navbar-brand" href="">
                <img src="/img/cpm-logo.png" height="40" width="70">
            </a>
        </div>

        <div class="collapse navbar-collapse text-right" id="bs-example-navbar-collapse-1">
            <h1 style="color: cornflowerblue;">CCD Importer</h1>
            <h5>Drop CCD Records in the box below, or click on it to browse your computer for CCDs.</h5>
            <h5><b>It is recommended that you import up to 5 CCDs in one go.</b></h5>
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

<div id="success" class="alert hide animated" role="alert"></div>
<div id="notification" class="alert hide animated" role="alert"></div>

<script src="{{ asset('/js/ccd/bluebutton.min.js') }}"></script>
<script src="/js/uploader.js"></script>