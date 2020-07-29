<html>
<head>

    <meta charset="utf-8">
    <title>Enroll</title>

    <meta id="token" name="csrf-token" content="{{ csrf_token() }}">

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>

    <style>

        input.select-dropdown {
            -webkit-user-select:none;
            -moz-user-select:none;
            -ms-user-select:none;
            -o-user-select:none;
            user-select:none;
        }

        .headings {

            font-size: 23px;
            line-height: 30px;
            text-align: left;
        }

        .info-list {
            margin-top: -1px;
            margin-bottom: 0px;
            color: black;
            background: #dddddd;
            padding: 18px 20px;
            text-align: center;
            font-size: 18px;
            border-bottom: white 2px solid;
        }

        .select-custom{

            font-size: 12px;

        }

        .label{
            font-size: 20px;
            color: black;
        }


    </style>

</head>

<nav>
    <div class="nav-wrapper center">
        <div class="mdl-layout__header-row" style="background: #4fb2e2; padding-left: 10px">
            <span class="mdl-layout__title" style="color: white; font-size: 1.4em;">Dr. {{$enrollee->provider->getFullName()}}’s Office</span>
        </div>
    </div>
</nav>

<div class="container">
    <p class="headings" style="padding-top: 0px; margin-bottom: 15px; color: black">
        Dear. {{$enrollee->first_name . ' ' . $enrollee->last_name}}, <br /> <br />

        I recommend you join my new personalized care program.
        @if(!$enrollee->has_copay) It’s free so please read below @else Please read below @endif and enroll.
    <br />
    <div class="right headings">- Dr. {{$enrollee->provider->last_name}}</div>

</div>

<div class="info">

    <div class="row" id="enrollment_module">

        <form method="post" name="enroll" id="enroll"
              action="{{route('patient.enroll.update', ['enrollee_id' => $enrollee->id])}}"
              class="col s12" style="padding-top: 20px;">

            <div class="row center">
                <a class="waves-effect waves-light btn modal-trigger" v-on:click="saveConsent" href="#confirm">Enroll</a>
            </div>

            <p class="info-list">Calls from registered nurses ~1-2x monthly so I can stay updated</p>
            <p class="info-list">Health line for any questions (nurses call you back): (888) 729-4045</p>
            <p class="info-list">Only one doctor at a time can provide this program</p>
            <p class="info-list">Withdraw anytime. Just give us a call</p>
            @if($enrollee->has_copay)
                <p class="info-list">Medicare covers the program but you may be responsible for a ~$8 per
                    month co-pay</p>
            @endif

            {{ csrf_field() }}

            <input type="datetime" v-model="enrolled_time" id="enrolled_time" name="enrolled_time" hidden>
            <input type="datetime" v-model="confirmed_time" id="confirmed_time" name="confirmed_time" hidden>
            <input type="text" id="practice_id" name="practice_id" value="{{$enrollee->practice->id}}" hidden>

            <div id="confirm" class="modal modal-fixed-footer">
                <div class="modal-content">
                    <h4 class="" style="color: #47beab">Great! We’ll be in touch shortly!</h4>
                    <blockquote style="border-left: 5px solid #26a69a;">
                        (Optional) Please tell us the best time to reach you:
                    </blockquote>
                    <div class="row">
                        <div class="col s12 m6 select-custom">
                            <label for="days[]" class="label">Day</label>
                            <select class="browser-default" name="days[]" id="days[]" multiple>
                                <option disabled selected>Select Days</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                            </select>
                        </div>
                        <div class="col s12 m6 select-custom" >
                            <label for="time" class="label">Times</label>
                            <select class="browser-default" name="times[]" id="times[]" multiple>
                                <option disabled selected>Select Times</option>
                                <option value="10:00-12:00">10AM - Noon</option>
                                <option value="12:00-15:00">Noon - 3PM</option>
                                <option value="15:00-18:00">3PM - 6PM</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="submit" name="submit" v-on:onclick="updatePreferences"
                            class="modal-action waves-effect waves-green btn-flat">Acknowledge and Exit
                    </button>
                </div>
            </div>

        </form>
    </div>

</div>




</html>

<script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>

<script>

    Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    let app = new Vue({

        el: '#enrollment_module',

        data: {

            enrollee_id: {!!  $enrollee->id !!},
            phone: '',
            phoneValid: '',
            enrolled_time: '',
            confirmed_time: ''

        },

        mounted: function () {

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

            saveConsent() {

                this.enrolled_time = new Date().toLocaleString();

                //send form to update consented_time
                this.$http.post('/join/save',

                        {
                            'consented_at': this.enrolled_time,
                            'enrollee_id' : this.enrollee_id
                        }

                    )

                    .then(response => {

                    console.log(response.body);

                    }
                );

                $('select').material_select();
                $('.modal').modal();


            },

            updatePreferences(){
                this.confirmed_time = new Date();
                $('#enroll').submit();
            },

        }


    });

</script>