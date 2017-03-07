@extends('enrollment-ui.layout')

@section('title', 'Enrollment Calls')

<style>

    .sidebar-demo-list {

        height: 27px;
        font-size: 17px;
        padding-left: 15px;

    }

</style>

@section('content')
    <div id="enrollment_calls">
        <ul style="width:25%; margin-top:65px;" class="side-nav fixed">
            <div class="col s12" style="width: 100%; padding: 0px 10px">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text">
                        <p>You’ve done [x] calls today and
                            Enrolled [x] patients. Nice work!
                            Elapsed time: xx:xx minutes</p>
                    </div>
                </div>
            </div>

            <span>
            <li class="sidebar-demo-list"><span id="name">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="cell_phone">(972) 762 2642</span></li>
            <li class="sidebar-demo-list"><span id="home_phone">(201) 201 2011</span></li>
            <li class="sidebar-demo-list"><span id="address">100 Lunar Way, Moon Plaza, Moon</span></li>
            <li class="sidebar-demo-list"><span id="email">rohanm</span></li>
            <li class="sidebar-demo-list"><span id="family">[Family/POAs/Caretakers]</span></li>
            <li class="sidebar-demo-list"><span id="dob">16/07/1992</span></li>
         </span>

            <hr>

            <li class="sidebar-demo-list"><span id="billing_provider">Dr. John Doe</span></li>

        </ul>

        <div style="margin-left: 375px; margin-top: 10px;">
            <a class="waves-effect waves-light btn" href="#consented" v-on:click="consented">Patient Consented</a>
            <a class="waves-effect waves-light btn" href="#utc" v-on:click="utc" style="background: #ecb70e">No Answer / Requested Call Back</a>
            <a class="waves-effect waves-light btn" href="#rejected" v-on:click="rejected" style="background: red;">Patient Declined</a>
        </div>

        <!-- MODALS -->
        <div id="confirm" class="modal confirm modal-fixed-footer">
            <div class="modal-content">
                <h4 class="" style="color: #47beab">Great! We’ll be in touch shortly!</h4>
                <blockquote style="border-left: 5px solid #26a69a;">
                    Optionally, you can tell us the best time to reach you:
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
                    <div class="col s12 m6 select-custom">
                        <label for="time" class="label">Times</label>
                        <select class="browser-default" name="time" id="time">
                            <option disabled selected>Select Times</option>
                            <option value="10:00-12:00">10AM - Noon</option>
                            <option value="12:00-15:00">Noon - 3PM</option>
                            <option value="15:00-18:00">3PM - 6PM</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="submit" name="submit"
                        class="modal-action waves-effect waves-green btn-flat">Acknowledge and Exit
                </button>
            </div>
        </div>

    </div>


@stop


@section('scripts')
    <script>

        Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        let app = new Vue({

            el: '#enrollment_calls',

            data: {},

            ready: function () {


            },

            methods: {

                consented(){

                    $('.confirm').modal('open');

                },

                utc(){

                },

                rejected(){

                }


            }


        });

    </script>
@stop