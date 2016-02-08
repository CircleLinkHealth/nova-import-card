<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CCD Importer</title>

    <link rel="stylesheet" href="https://code.getmdl.io/1.1.0/material.teal-blue.min.css" />
    <link href="/css/animate.min.css" rel="stylesheet">
    <link href="/img/favicon.png" rel="icon">

    <style>
        .dropzone {
            width: 100%;
            height: 300px;
            border: 2px dashed #ccc;
            color: #ccc;
            line-height: 300px;
            text-align: center;
            background-color: rgba(174, 219, 239, 0.21);
        }

        .dropzone.dragover {
            border-color: #000;
            color:#000;
        }
    </style>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Scripts -->
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
</head>
<body>
<nav class="mdl-grid">
    <div class="mdl-cell mdl-cell--12-col">
        <div class="mdl-typography--text-center">
            <img src="/img/cpm-logo.png" height="50" width="87.5">
        </div>
        <div class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
            <h5><b>CCD Importer</b></h5>
        </div>
    </div>
</nav>

<div id="ccd-uploader" class="mdl-grid">
    <div class="mdl-cell mdl-cell--12-col">
        <mdl-progress :progress="progress" :buffer="buffer" class="mdl-cell mdl-cell--12-col"></mdl-progress>
        <p :message="message" class="mdl-cell mdl-cell--12-col mdl-typography--text-left">@{{ message }}</p>
    </div>

    <form method="POST" v-on:submit="onSubmitForm" enctype="multipart/form-data" class="mdl-cell mdl-cell--12-col">

        <input type="file" id="ccd" class="dropzone" multiple>

        <div class="mdl-typography--text-center mdl-cell mdl-cell--12-col">
            <mdl-button primary raised v-mdl-ripple-effect type="submit" :disabled="!enabled">
                Upload CCD Records
            </mdl-button>
        </div>
    </form>
</div>


<div id="success" class="alert hide animated" role="alert"></div>
<div id="notification" class="alert hide animated" role="alert"></div>

<script src="{{ asset('/js/scripts.js') }}"></script>
<script src="/js/uploader.js"></script>
