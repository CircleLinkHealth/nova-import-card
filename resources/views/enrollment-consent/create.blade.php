<html>
<head>

    <meta charset="utf-8">
    <title>Enroll</title>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>

    <style>
        .headings {

            font-size: 23px;
            line-height: 30px;
        }

        .info-list {
           font-size: 20px;
           margin-top: 10px;
           margin-bottom: 10px;
        }

        .invalid-phone {
            color: red;
        }

        .valid-phone {
            color: green;
        }

    </style>

</head>

<nav>
    <div class="nav-wrapper">
        <div class="mdl-layout__header-row" style="background: #4fb2e2; padding-left: 10px">
            <span class="mdl-layout__title" style="color: white;">{{$practice->display_name}}'s Personalized Care Management Program</span>
        </div>
    </div>
</nav>

<div class="container">
    <div>
        <p class="headings" style="padding-top: 20px; color: black">“Dr. [first_name] [last_name] has invited you to their new personalized care management program for improved wellness!</p>

        <p class="flow-text info-list">- A personal care coach --registered nurse-- will do a quick phone check in about 2x a month for Dr. [Dr._first_name] [Dr._last_name] to provide support, health information and to see how you’re doing</p>
        <p class="flow-text info-list">- You can leave a message 24/7 at (888) 729-4045 if anything comes up, and a care coach will get back to you shortly</p>
        <p class="flow-text info-list">- Only one practice/doctor at a time can provide this program</p>
        <p class="flow-text info-list">- You can withdraw anytime by calling (888) 729-4045</p>
        @if(isset($has_copay))
            <p style="font-size: 20px;" class="flow-text">- Medicare covers the program you may be responsible for a ~$8 per month co-pay</p>
        @endif

    </div>

    <div class="row" id="enrollment_module">
        <form method="post" name="enroll" id="enroll"
              action="{{URL::route('patient.enroll.store', ['program_name' => $practice])}}"
              class="col s12" style="padding-top: 20px;">
            {{ csrf_field() }}

            <div class="row">
                <div class="input-field col s12 m6">
                    <label for="first_name">First Name</label>
                    <input placeholder="Enter First Name..." id="first_name" type="text">
                </div>
                <div class="input-field col s12 m6">
                    <label for="last_name">Last Name</label>
                    <input placeholder="Enter Last Name..." id="last_name" type="text">
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6">
                    <label class="active" for="dob">Date Of Birth</label>
                    <input placeholder="XX-XX-XXXX" type="date" class="datepicker" name="dob" id="dob">
                </div>
                <div class="input-field col s12 m6">
                    <label for="phone"><span v-bind:class="phoneValid">Phone Number</span></label>
                    <input placeholder="XXX-XXX-XXXX" v-on:keyUp="checkPhone" v-model="phone" id="phone" type="text"
                           name="phone">
                </div>
            </div>

            <input type="datetime" v-model="enrolled_time" id="enrolled_time" name="enrolled_time" hidden>
            <input type="datetime" v-model="confirmed_time" id="confirmed_time" name="confirmed_time" hidden>
            <input type="text" id="practice_id" name="practice_id" value="{{$practice->id}}" hidden>

            <div class="row right">
                <a class="waves-effect waves-light btn modal-trigger" v-on:click="openModal" href="#confirm">Consent</a>
            </div>




            <div id="confirm" class="modal modal-fixed-footer">
                <div class="modal-content">
                    <h4 class="" style="color: #47beab">Great! Remember:</h4>
                    <ul>
                        <li style="font-size: 20px;" class=""> - A personal care coach— registered nurse—
                            will do
                            a quick
                            phone check-in
                            periodically
                        <li style="font-size: 20px;" class=""> - You can also leave a message for us 24/7 at
                            (888) 729-4045
                    </ul>
                    <blockquote>Optionally, you can tell us the best time to reach you:</blockquote>
                    <div class="row">
                        <div class="col s12 m6">
                            <select class="input-field" name="day" id="day">
                                <option disabled selected>Select Day</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                            </select>
                            <label class="active" for="day">Day</label>
                        </div>
                        <div class="col s12 m6">
                            <select class="input-field" name="time" id="time">
                                <option disabled selected>Select Day</option>
                                <option value="09:00-12:00">9AM - Noon</option>
                                <option value="12:00-15:00">Noon - 3PM</option>
                                <option value="15:00-18:00">3PM - 6PM</option>
                            </select>
                            <label class="active" for="time">Time</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="submit" name="submit" v-on:onclick="submitForm"
                            class="modal-action modal-close waves-effect waves-green btn-flat">Acknowledge and Exit
                    </button>
                </div>
            </div>




        </form>
    </div>

</div>

</html>

<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>

<script>

    let app = new Vue({


        el: '#enrollment_module',

        data: {
            phone: '',
            phoneValid: '',
            enrolled_time: '',
            confirmed_time: ''

        },

        ready: function () {

        },


        methods: {

            checkPhone(){

                let phoneValidator = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;

                if (phoneValidator.test(this.phone) == true) {
                    this.phoneValid = 'valid-phone';
                } else {
                    this.phoneValid = 'invalid-phone';
                }

            },

            openModal() {
                $('select').material_select();
                $('.modal').modal();
                this.enrolled_time = new Date();
            },

            submitForm(){
                this.confirmed_time = new Date();
                $('#enroll').submit();
            },

        }


    });

</script>