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
                        <p class="headings" style="padding-top: 20px"> If you're visiting this web page, your Doctor at {{ucwords($practice->name)}} has invited you to
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
                        <button type="submit" class="submit_button mdl-button mdl-js-button mdl-js-ripple-effect">
                            Enroll!
                        </button>
                    </div>

                    <input type="datetime" id="enrolled_time" name="enrolled_time" hidden>
                    <input type="datetime" id="confirmed_time" name="confirmed_time" hidden>
                    <input type="datetime" id="practice_id" name="practice_id" value="{{$practice->id}}" hidden>

                </form>
            </div>
        </div>

    </main>

</div>

<script>
    (function () {
        'use strict';

        var dialog = document.querySelector('#dialog');

        if (!dialog.showModal) {
            dialogPolyfill.registerDialog(dialog);
        }

        $(".submit_button").click(function (e) {
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

        var month = today.getMonth();

        month = month > 9 ? month : "0" + month;

        var date = today.getFullYear()+'-'+month+'-'+today.getDate();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        return date+' '+time;

    }

</script>

@stop

