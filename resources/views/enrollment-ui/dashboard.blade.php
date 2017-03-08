@extends('enrollment-ui.layout')

@section('title', 'Enrollment Calls')
@section('activity', 'Enrollment Call')

@section('content')

    <style>

        .sidebar-demo-list {

            height: 27px;
            font-size: 17px;
            padding-left: 15px;

        }

    </style>

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
            <li class="sidebar-demo-list"><span id="name">Name: @{{name}}</span></li>
            <li class="sidebar-demo-list"><span id="cell_phone">Primary Phone: @{{primary_phone}}</span></li>
            <li class="sidebar-demo-list"><span id="cell_phone">Cell Phone: @{{cell_phone}}</span></li>
            <li class="sidebar-demo-list"><span id="home_phone">Home Phone: @{{home_phone}}</span></li>
            <li class="sidebar-demo-list"><span id="address">Address: @{{address}}</span></li>
            <li class="sidebar-demo-list"><span id="address">Email: @{{email}}</span></li>
            <li class="sidebar-demo-list"><span id="dob">DOB: @{{dob}}</span></li>
         </span>

            <hr>

            <li class="sidebar-demo-list"><span id="billing_provider">Dr. John Doe</span></li>

        </ul>

        <div style="margin-left: 375px; margin-top: 10px;">
            <a class="waves-effect waves-light btn" href="#consented">Patient Consented</a>
            <a class="waves-effect waves-light btn" href="#utc" style="background: #ecb70e">No Answer /
                Requested Call Back</a>
            <a class="waves-effect waves-light btn" href="#rejected" style="background: red;">Patient
                Declined</a>
        </div>

        <!-- MODALS -->

        <!-- Success / Patient Consented -->
        <div id="consented" class="modal confirm modal-fixed-footer">
            <form method="post" id="consented_form" action="{{URL::route('enrollment-center.store')}}"
                  class="">

                {{ csrf_field() }}

                <div class="modal-content">
                    <h4 style="color: #47beab">Awesome! Please confirm patient details.</h4>

                    <div class="row">
                        <blockquote style="border-left: 5px solid #26a69a;">
                            Please confirm the patient's preferred contact details:
                        </blockquote>
                        <div class="col s6 m3 select-custom">
                            <label for="phone" class="label">Home Phone</label>
                            <input class="input-field" name="home_phone" id="phone" v-model="home_phone"/>
                        </div>
                        <div class="col s6 m3 select-custom">
                            <label for="address" class="label">Cell Phone</label>
                            <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                        </div>
                        <div class="col s12 m6 select-custom">
                            <label for="address" class="label">Preferred Address</label>
                            <input class="input-field" name="address" id="address" v-model="address"/>

                        </div>

                    </div>
                    <div class="row">
                        <blockquote style="border-left: 5px solid #26a69a;">
                            Please confirm the patient's preferred contact details:
                        </blockquote>
                        <div class="col s12 m6">
                            <label for="days[]" class="label">Day</label>
                            <select name="days[]" id="days[]" multiple>
                                <option disabled selected>Select Days</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                            </select>
                        </div>
                        <div class="col s12 m6">
                            <label for="times[]" class="label">Times</label>
                            <select name="times[]" id="times[]" multiple>
                                <option disabled selected>Select Times</option>
                                <option value="10:00-12:00">10AM - Noon</option>
                                <option value="12:00-15:00">Noon - 3PM</option>
                                <option value="15:00-18:00">3PM - 6PM</option>
                            </select>
                        </div>
                    </div>
                    <div class="row input-field">
                        <blockquote style="border-left: 5px solid #26a69a;">
                            Is there anything else we should know about the patient?
                        </blockquote>
                        <div class="col s12 m6 select-custom">
                            <textarea class="materialize-textarea input-field" id="extra" name="extra"
                                      placeholder="Optional additional information"></textarea>
                        </div>
                    </div>

                    <input type="hidden" name="status" value="consented">
                    <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">

                </div>
                <div class="modal-footer">
                    <button id="submit" name="submit" type="submit" v-on:click="consent_submit"
                            class="modal-action waves-effect waves-light btn">Confirm and Call Next Patient
                    </button>
                </div>
            </form>
        </div>

        <!-- Success / Patient Consented -->
        <div id="utc" class="modal confirm modal-fixed-footer">
            <div class="modal-content">
                <h4 class="" style="color: #47beab">Awesome! Please confirm patient details.</h4>
                <div class="row">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Please confirm the patient's preferred contact details:
                    </blockquote>
                    <div class="col s6 m3 select-custom">
                        <label for="phone" class="label">Home Phone</label>
                        <input class="input-field" name="home_phone" id="phone" v-model="home_phone"/>
                    </div>
                    <div class="col s6 m3 select-custom">
                        <label for="address" class="label">Cell Phone</label>
                        <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                    </div>
                    <div class="col s12 m6 select-custom">
                        <label for="address" class="label">Preferred Address</label>
                        <input class="input-field" name="address" id="address" v-model="address"/>

                    </div>

                </div>
                <div class="row">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Please confirm the patient's preferred contact details:
                    </blockquote>
                    <div class="col s12 m6">
                        <label for="days[]" class="label">Day</label>
                        <select name="days[]" id="days[]" multiple>
                            <option disabled selected>Select Days</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                        </select>
                    </div>
                    <div class="col s12 m6">
                        <label for="times[]" class="label">Times</label>
                        <select name="times[]" id="times[]" multiple>
                            <option disabled selected>Select Times</option>
                            <option value="10:00-12:00">10AM - Noon</option>
                            <option value="12:00-15:00">Noon - 3PM</option>
                            <option value="15:00-18:00">3PM - 6PM</option>
                        </select>
                    </div>
                </div>
                <div class="row input-field">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Is there anything else we should know about the patient?
                    </blockquote>
                    <div class="col s12 m6 select-custom">
                        <textarea class="materialize-textarea input-field" id="extra" name="extra"
                                  placeholder="Optional additional information"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button id="submit" name="submit"
                        class="modal-action waves-effect waves-light btn">Confirm and Call Next Patient
                </button>
            </div>
        </div>

        <!-- Success / Patient Consented -->
        <div id="rejected" class="modal confirm modal-fixed-footer">
            <div class="modal-content">
                <h4 class="" style="color: #47beab">Awesome! Please confirm patient details.</h4>
                <div class="row">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Please confirm the patient's preferred contact details:
                    </blockquote>
                    <div class="col s6 m3 select-custom">
                        <label for="phone" class="label">Home Phone</label>
                        <input class="input-field" name="home_phone" id="phone" v-model="home_phone"/>
                    </div>
                    <div class="col s6 m3 select-custom">
                        <label for="address" class="label">Cell Phone</label>
                        <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                    </div>
                    <div class="col s12 m6 select-custom">
                        <label for="address" class="label">Preferred Address</label>
                        <input class="input-field" name="address" id="address" v-model="address"/>

                    </div>

                </div>
                <div class="row">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Please confirm the patient's preferred contact details:
                    </blockquote>
                    <div class="col s12 m6">
                        <label for="days[]" class="label">Day</label>
                        <select name="days[]" id="days[]" multiple>
                            <option disabled selected>Select Days</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                        </select>
                    </div>
                    <div class="col s12 m6">
                        <label for="times[]" class="label">Times</label>
                        <select name="times[]" id="times[]" multiple>
                            <option disabled selected>Select Times</option>
                            <option value="10:00-12:00">10AM - Noon</option>
                            <option value="12:00-15:00">Noon - 3PM</option>
                            <option value="15:00-18:00">3PM - 6PM</option>
                        </select>
                    </div>
                </div>
                <div class="row input-field">
                    <blockquote style="border-left: 5px solid #26a69a;">
                        Is there anything else we should know about the patient?
                    </blockquote>
                    <div class="col s12 m6 select-custom">
                        <textarea class="materialize-textarea input-field" id="extra" name="extra"
                                  placeholder="Optional additional information"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button id="submit" name="submit"
                        class="modal-action waves-effect waves-light btn">Confirm and Call Next Patient
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

            data: {

                name: '{{ $enrollee->first_name . $enrollee->last_name }}',
                primary_phone: '{{ $enrollee->primary_phone ?? 'N/A' }}',
                home_phone: '{{ $enrollee->home_phone ?? 'N/A' }}',
                cell_phone: '{{ $enrollee->cell_phone ?? 'N/A' }}',
                address: '{{ $enrollee->address ?? 'N/A' }}',
                email: '{{ $enrollee->email ?? 'N/A' }}',
                dob: '{{ $enrollee->dob ?? 'N/A' }}'

            },

            mounted: function () {

                $('#consented').modal();
                $('#utc').modal();
                $('#rejected').modal();
                $('select').material_select();

            },

            methods: {

                consented(){

                    $('.confirm').modal('open');

                },

                utc(){

                },

                consent_submit(){

                    $('#consented_form').submit();

                },

                rejected(){

                }


            }


        });

    </script>

    <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
    @include('partials.providerUItimer')

@stop