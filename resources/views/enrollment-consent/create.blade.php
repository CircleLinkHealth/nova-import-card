<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Circle Link Health - CarePlan Manager Provider Dashboard.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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

    .headings {

        font-size: 23px;
        line-height: 30px;

    }

    .mdl-dialog__content {
        padding: 0px 10px 0px;
    }

    .mdl-list__item {
        font-size: 15px;
        line-height: 17px;
        padding: 10px;
    }

    .submit_button {

        text-align: right;
        color: #26a69a;
        font-size: 25px;

    }

</style>

<div class="mdl-layout mdl-js-layout">
    <header class="mdl-layout__header">
        <div class="mdl-layout-icon" style="overflow: visible "><img src="/img/ui/clh_logo_lt.png"
                                                                     alt="Care Plan Manager"
                                                                     style="position:relative;top:-15px"
                                                                     width="50px"/></div>
        <div class="mdl-layout__header-row">
            <span class="mdl-layout__title" style="color: white;">CircleLink Health</span>
        </div>
    </header>
    <main class="mdl-layout__content">
        <div class="mdl-card mdl-shadow--6dp" style="width: 800px; align-items: center">
            <div class="mdl-card__supporting-text">
                <form method="post" name="enroll" id="enroll"
                      action="{{URL::route('patient.enroll.store', ['program_name' => $practice])}}"
                      class="form-horizontal">

                    <dialog id="dialog" class="mdl-dialog" style="width: 700px">
                        <h3 class="mdl-dialog__title" style="color: #47beab">Great! Remember</h3>
                        <div class="mdl-dialog__content" style="">
                            <ul class="mdl-list">
                                <li class="mdl-list__item">A personal care coach— registered nurse-- will do a quick
                                    phone check-in
                                    periodically
                                <li class="mdl-list__item">You can also leave a message for us 24/7 at the number
                                    indicate in the
                                    letter/e-mail you received
                                <li class="mdl-list__item">All the information we collect is private, securely stored
                                    and communicated, and
                                    available to your
                                    doctor and care team
                                <li class="mdl-list__item">You can withdraw at any time you want. Just give us a call!
                                </li>
                            </ul>
                        </div>
                        <div class="mdl-dialog__actions">
                            <button type="button" id="confirm" class="mdl-button">Acknowledge and Exit</button>
                        </div>
                    </dialog>

                    {{ csrf_field() }}
                    <div>
                        <p class="headings" style="padding-top: 20px"> If you're visiting this web page, Dr.
                            (doctor_name) has invited you to
                            his/her new personalized
                            care management program!</p>

                        <p class="headings">Can we let your Dr. know you consented to enroll in this program?
                            <span style=""> (Remember you can always withdraw if you don’t like it)</span></p>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" name="first_name" id="first_name" required/>
                        <label class="mdl-textfield__label" for="first_name">First Name</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" type="text" name="last_name" id="last_name" required/>
                        <label class="mdl-textfield__label" for="last_name">Last Name</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input dob" name="dob" type="date" id="dob" required/>
                        <label class="mdl-textfield__label" for="dob">DOB</label>
                    </div>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <input class="mdl-textfield__input" name="phone" type="text" id="phone" required/>
                        <label class="mdl-textfield__label" for="phone">Phone</label>
                    </div>
                    <div class="mdl-card__actions mdl-card--border" style="padding: 10px; text-align: right">
                        <button type="submit" class="submit_button mdl-button mdl-js-button mdl-js-ripple-effect">Enroll!
                        </button>
                    </div>

                    <input type="datetime" id="enrolled_time" name="enrolled_time" hidden>
                    <input type="datetime" id="confirmed_time" name="confirmed_time" hidden>

                </form>
            </div>
        </div>

    </main>

</div>

<script>
    (function () {
        'use strict';
        var dialogButton = document.querySelector('.submit_button');

        var dialog = document.querySelector('#dialog');

        if (!dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }

        dialogButton.addEventListener('click', function () {
            $("#enrolled_time").val(Date.now());
            dialog.showModal();
        });

        dialog.querySelector('button:not([disabled])')
                .addEventListener('click', function () {
                    $("#confirmed_time").val(Date.now());
                    $("#enroll").submit();
                    dialog.close();
                });
    }());

</script>


</html>
