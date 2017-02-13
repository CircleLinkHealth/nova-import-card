@extends('layouts.enrollment-consent-layout')

@section('content')
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
            line-height: 25px;

        }

        .mdl-dialog__content {
            padding: 0px 10px 0px;
        }

        .mdl-list__item {
            font-size: 17px;
            line-height: 17px;
            padding: 10px;
        }

        .submit_button {

            text-align: right;
            color: #26a69a;
            font-size: 25px;

        }

    </style>

    <nav>
        <div class="nav-wrapper" style="background: #4fb2e2;">
            <a href="#" class="brand-logo left" style="color: white; font-size: 17px;">{{$practice->display_name}}'s
                Personalized Care Management Program</a>
        </div>
    </nav>

    <form method="post" name="enroll" id="enroll"
          action="{{URL::route('patient.enroll.store', ['program_name' => $practice])}}"
          class="form-horizontal">

        {{ csrf_field() }}
        <dialog id="dialog" class="mdl-dialog" style="width: 80%">
            <h3 class="mdl-dialog__title" style="color: #47beab">Great! Remember:</h3>
            <div class="mdl-dialog__content" style="">
                <ul class="mdl-list">
                    <li style="font-size: 19px; line-height: 25px;" class="mdl-list__item">A personal care coach—
                        registered nurse—
                        will do
                        a quick
                        phone check-in
                        periodically
                    <li style="font-size: 19px; line-height: 25px;" class="mdl-list__item">You can also leave a message
                        for us 24/7 at
                        (888) 729-4045
                </ul>

                <div class="row">
                    <div class="m12 s12">
                        <p class="headings" style="padding-top: 19px; font-size: 18px; color: black">
                            When would you like us to contact you? (Optional)</p>
                    </div>

                </div>

                <div class="row">
                    <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <select style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input"
                                name="day" id="day">
                            <option value="" disabled selected></option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                        </select>
                        <label style="font-size: 20px" class="mdl-textfield__label" for="day">Select Day</label>
                    </div>

                    <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                        <select style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input"
                                name="time" id="time">
                            <option value="" disabled selected></option>
                            <option value="09:00-17:00">All Day</option>
                            <option value="09:00-12:00">9am-noon</option>
                            <option value="12:00-15:00">noon-3pm</option>
                            <option value="15:00-18:00">3pm-6pm</option>
                        </select>
                        <label style="font-size: 20px" class="mdl-textfield__label" for="time">Select Time</label>
                    </div>
                </div>

            </div>
            <div class="mdl-dialog__actions">
                <button type="button" id="confirm" class="mdl-button">Acknowledge and Exit</button>
            </div>
        </dialog>

        <main class="mdl-layout__content">
            <div class="mdl-card mdl-shadow--6dp s12 m6" style="width: 100%; align-items: center">
                <div class="mdl-card__supporting-text">
                    <div class="row">
                        <p class="headings" style="padding-top: 19px; font-size: 18px; color: black">Your Doctor
                            at {{ucwords($practice->name)}} has invited you to
                            his/her new personalized
                            care management program!</p>

                        <p class="headings" style="color: black; font-size: 19px;">

                            Please enroll by completing below form, and note:
                        <li class="mdl-list__item"> - Only one practice or doctor at a time
                            can provide this program
                        </li>
                        <li style="margin-top: -15px;" class="mdl-list__item"> - You can withdraw anytime by calling
                            your doctor or leaving a message at (888) 729-4045
                        </li>

                        </p>
                    </div>

                    <div class="row">
                        <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input"
                                   type="text" name="first_name" id="first_name"/>
                            <label style="font-size: 20px" class="mdl-textfield__label" for="first_name">First
                                Name*</label>
                        </div>
                        <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input"
                                   type="text" name="last_name" id="last_name"/>
                            <label style="font-size: 20px" class="mdl-textfield__label" for="last_name">Last
                                Name*</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input dob"
                                   name="dob" type="date" id="dob"/>
                            <label style="font-size: 20px" class="mdl-textfield__label" for="dob">Date Of
                                Birth*</label>
                        </div>
                        <div class="s8 m12 mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                            <input style="font-size: 20px; padding-top: 20px;" class="mdl-textfield__input"
                                   name="phone" type="text" id="phone"/>
                            <label style="font-size: 20px" class="mdl-textfield__label" id="phone_label"
                                   for="phone">Phone Number*</label>
                        </div>
                    </div>
                    <div class="mdl-card__actions mdl-card--border" style="padding: 10px; text-align: right">
                        <button type="submit"
                                class="submit_button mdl-button mdl-js-button mdl-js-ripple-effect">
                            Enroll!
                        </button>
                    </div>

                    <input type="datetime" id="enrolled_time" name="enrolled_time" hidden>
                    <input type="datetime" id="confirmed_time" name="confirmed_time" hidden>
                    <input type="text" id="practice_id" name="practice_id" value="{{$practice->id}}" hidden>

                </div>
            </div>
        </main>
    </form>


    <script>
        (function () {
            'use strict';

            $("#phone").bind('input propertychange', function () {

                var VAL = this.value;
                var label = $("#phone_label");

                var phoneno = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;

                if (VAL.match(phoneno)) {
                    label.css({"color": "green"});
                    label.html('Valid');
                }
                else {
                    label.css({"color": "red"});
                    label.html('Please enter a valid phone number..');
                }

            });

            var dialog = document.querySelector('#dialog');

            if (!dialog.showModal) {
                dialogPolyfill.registerDialog(dialog);
            }

            $(".submit_button").click(function (e) {

                if ($
                    ('#phone').val().length == 0
                    || $('#dob').val().length == 0
                    || $('#first_name').val().length == 0
                    || $('#last_name').val().length == 0

                ) {
                    alert('Please enter all required to continue.');
                    return false;
                }

                $("#enrolled_time").val(formatCurrentJSTime());
                dialog.showModal();
                e.preventDefault();
                return false;

            });

            dialog.querySelector('button:not([disabled])')
                .addEventListener('click', function () {
                    $("#confirmed_time").val(formatCurrentJSTime());
                    $("#enroll").submit();
                    dialog.close();
                });
        }());


        function formatCurrentJSTime() {
            var today = new Date();

            var month = today.getMonth() + 1;

            month = month > 9 ? month : "0" + month;

            var date = today.getFullYear() + '-' + month + '-' + today.getDate();
            var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            return date + ' ' + time;

        }

    </script>

@stop

