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
            text-align: left;
        }

        .info-list {
            margin-top: -1px;
            margin-bottom: 0px;
            color: #080808;
            background: #c3cdd2;
            padding: 18px 20px;
            text-align: center;
            font-size: 18px;
            border-bottom: white 2px solid;
        }


    </style>

</head>

<nav>
    <div class="nav-wrapper center">
        <div class="mdl-layout__header-row" style="background: #4fb2e2; padding-left: 10px">
            <span class="mdl-layout__title" style="color: white; font-size: 1.4em;">On Behalf of Dr. Mazhar’s Office</span>
        </div>
    </div>
</nav>

<div class="container">
    <p class="headings" style="padding-top: 0px; margin-bottom: -5px; color: black">Dear. John Doe, <br /> <br /> Dr. [Salma Mazhar] invites you to their new and @if(isset($has_copay)) free @endif personalized care program! Please read and enroll below.</p>
</div>

<div class="info">

    <div class="row" id="enrollment_module">

        <form method="post" name="enroll" id="enroll"
              action="{{URL::route('patient.enroll.store', ['program_name' => $practice])}}"
              class="col s12" style="padding-top: 20px;">

            <div class="row center">
                <a class="waves-effect waves-light btn modal-trigger" v-on:click="openModal" href="#confirm">Consent</a>
            </div>

            <p class="info-list">Calls from registered nurses 1-2x monthly on behalf of Dr. [Mazhar]</p>
            <p class="info-list">24/7 health message line (nurses call back shortly): (888) 729-4045</p>
            <p class="info-list">Only one doctor at a time can provide this program</p>
            <p class="info-list">Withdraw anytime by calling: (888) 729-4045</p>
            @if(isset($has_copay))
                <p style="font-size: 20px;" class="flow-text">- Medicare covers the program you may be responsible for a ~$8 per
                    month co-pay</p>
            @endif

            {{ csrf_field() }}

            <input type="datetime" v-model="enrolled_time" id="enrolled_time" name="enrolled_time" hidden>
            <input type="datetime" v-model="confirmed_time" id="confirmed_time" name="confirmed_time" hidden>
            <input type="text" id="practice_id" name="practice_id" value="{{$practice->id}}" hidden>



            <div id="confirm" class="modal modal-fixed-footer">
                <div class="modal-content">
                    <h4 class="" style="color: #47beab">Great! We’ll be in touch shortly!</h4>
                    <blockquote>
                        Optionally, you can tell us the best time to reach you:
                    </blockquote>
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
                                <option value="10:00-12:00">10AM - Noon</option>
                                <option value="12:00-15:00">Noon - 3PM</option>
                                <option value="15:00-18:00">3PM - 6PM</option>
                            </select>
                            <label class="active" for="time">Time</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="submit" name="submit" v-on:onclick="submitForm"
                            class="modal-action waves-effect waves-green btn-flat">Acknowledge and Exit
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
//                $('#enroll').submit();
            },

        }


    });

</script>