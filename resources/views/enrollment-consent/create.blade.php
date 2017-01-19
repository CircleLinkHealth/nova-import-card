<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Circle Link Health - CarePlan Manager Provider Dashboard.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CarePlan Manager | Join </title>

    <!-- Add to homescreen for Chrome on Android -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" sizes="192x192" href="images/android-desktop.png">

    <!-- Add to homescreen for Safari on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="CarePlan Manager">
    <link rel="apple-touch-icon-precomposed" href="images/ios-desktop.png">

    <!-- Tile icon for Win8 (144x144 + tile color) -->
    <meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
    <meta name="msapplication-TileColor" content="#3372DF">

    <link rel="shortcut icon" href="images/favicon.png">


    <link href="{{ asset('/img/favicon.png') }}" rel="icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.8/css/materialize.min.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.light_blue-blue.min.css">
    <script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/parsley.js/2.0.7/parsley.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>


</head>

<style>
    .mdl-layout {
        align-items: center;
    }

    .mdl-layout__content {
        padding: 24px;
        flex: none;
    }
</style>

<div class="mdl-layout mdl-js-layout">
    <header class="mdl-layout__header">
        <div class="mdl-layout-icon"></div>
        <div class="mdl-layout__header-row">
            <span class="mdl-layout__title" style="color: white;">CircleLink Health!</span>
        </div>
    </header>
    <main class="mdl-layout__content">
        <div class="mdl-card mdl-shadow--6dp" style="width: 800px;">
            <div class="mdl-card__title mdl-color--primary mdl-color-text--white">
                <h2 class="mdl-card__title-text">Acme Co.</h2>
            </div>
            <div class="mdl-card__supporting-text">
                <form action="#">
                    <div>
                       <p> If you're visiting this web page, Dr. (doctor_name) has invited you to his/her new personalized
                        care management program!"</p>

                        <p>Can we let your Dr. know you consented to enroll in this program? (Remember you can always
                        withdraw if you don’t like it)"</p>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield">
                        <input class="mdl-textfield__input" type="text" id="first_name"/>
                        <label class="mdl-textfield__label" for="first_name">First Name</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield">
                        <input class="mdl-textfield__input" type="text" id="last_name"/>
                        <label class="mdl-textfield__label" for="last_name">Last Name</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield">
                        <input class="mdl-textfield__input" type="date" id="dob"/>
                        <label class="mdl-textfield__label" for="dob">DOB</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield">
                        <input class="mdl-textfield__input" type="text" id="phone"/>
                        <label class="mdl-textfield__label" for="phone">Phone</label>
                    </div>
                </form>
            </div>
            <div class="mdl-card__actions mdl-card--border">
                <button class="confirm mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">Enroll!</button>
            </div>
        </div>

        <dialog id="dialog" class="mdl-dialog">
            <h3 class="mdl-dialog__title">MDL Dialog</h3>
            <div class="mdl-dialog__content">
                <p>
                    This is an example of the Material Design Lite dialog component.
                    Please use responsibly.
                </p>
            </div>
            <div class="mdl-dialog__actions">
                <button type="button" class="mdl-button">Close</button>
                <button type="button" class="mdl-button" disabled>Disabled action</button>
            </div>
        </dialog>


    </main>

</div>

<script>
    (function() {
        'use strict';
        var dialogButton = document.querySelector('.confirm');
        var dialog = document.querySelector('#dialog');
        if (! dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }
        dialogButton.addEventListener('click', function() {
            dialog.showModal();
        });
        dialog.querySelector('button:not([disabled])')
            .addEventListener('click', function() {
                dialog.close();
            });
    }());
</script>


</html>
