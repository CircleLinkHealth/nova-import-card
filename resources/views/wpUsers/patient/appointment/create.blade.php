@extends('partials.providerUI')

@section('title', 'Input Appointments')
@section('activity', 'Patient Input Appointment')

@section('content')

    <script>
        $(document).ready(function () {
            $(".provider").select2();

        });
    </script>

    <style>

        .save-btn {
            width: 100px;
            height: 42px;
            position: relative;
        }

    </style>

    @include('partials.addprovider')

    <div class="row" style="margin:30px 0px;">
        <div class="col-lg-10 col-lg-offset-1">
            @include('errors.errors')
        </div>
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    New Appointment
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <form id="save" name="save" method="post" action="{{URL::route('patient.appointment.store', array('patientId' => $patient->id))}}">
                        {{ csrf_field() }}

                        <div class="row">
                        <div class="form-block col-md-6">
                            <div class="row">
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12 provider-label" id="provider-label">
                                            <label for="provider">
                                                Select Existing Provider (or, <span style="color: #4fb2e2"><a id="addNewProvider"
                                                                                                href="#">add new</a></span>)
                                            </label>
                                        </div>
                                        <div class="col-sm-12" id="providerDiv">
                                            <div class="form-group providerBox" id="providerBox">
                                                <select id="provider" name="provider"
                                                        class="provider selectpickerX dropdownValid form-control"
                                                        data-size="10" required>
                                                    <option value=""></option>
                                                @foreach ($providers as $key => $value)
                                                        <option value="{{$key}}"> {{$value}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="date">
                                                Appointment Date:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="date" type="date" class="selectpickerX form-control"
                                                       value=""
                                                       data-field="date" data-format="yyyy-MM-dd" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="time">
                                                Appointment Time:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="time" id="time" type="time" class="selectpickerX form-control"
                                                       value="12:00"
                                                       data-field="time" data-format="H:i" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="new-observation-item">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <div class="radio-inline"><input type="checkbox"
                                                                                     name="is_completed"
                                                                                     id="is_completed"/><label
                                                                for="is_completed"><span> </span>Attended</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-block col-md-6">
                            <div class="new-observation-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="observationSource">
                                            Appointment Type:
                                        </label>
                                    </div>
                                    <div class="col-sm-12" style="margin-top: -11px;">
                                        <div class="form-group">
                                        <textarea class="form-control" id="appointment_type" name="appointment_type"
                                                  placeholder="Please specify appointment type..."
                                                  rows="1" maxlength="50"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="new-observation-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="observationSource">
                                            Additional Details:
                                        </label>
                                    </div>
                                    <div class="col-sm-12" style="margin-top: -11px;">
                                        <div class="form-group">
                                        <textarea class="form-control" id="comment" name="comment"
                                                  placeholder="Please enter appointment details..." rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" style="margin:30px 0px;">
                                <div class="col-lg-12">
                                    <div class="text-center" style="margin-right:20px; text-align: right">

                                        <input type="hidden" name="patientId" id="patientId" value="{{ $patient->id }}">

                                        {!! Form::submit('Save', array('name' => 'save','class' => 'btn btn-primary save-btn')) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
            </div>
        </div>
            <script>

                $("#create").on('click', function () {

                    var url = '{!! route('provider.store') !!}'; // the script where you handle the form input.
                    var name = null;
                    var id = null;

                    $.ajax({
                        type: "POST",
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            phone: $('#phone').val(),
                            first_name: $('#first_name').val(),
                            address: $('#address').val(),
                            last_name: $('#last_name').val(),
                            provider: $('#provider').val(),
                            specialty: $('#specialty').val(),
                            practice: $('#practice').val(),
                            type: $('#type').val(),
                            email: $('#email').val(),
                            is_completed: $('#is_completed').val(),
                            created_by: $('#created_by').val(),
                            patient_id: $('#patient_id').val()
                        },
                        success: function (data) {


                            var dataArray = JSON.parse(data);

                            //setting up the select2 and dynamic picking wasn't working,
                            //quick work around to replace the whole innerhtml with a
                            //disabled div

                            $('#providerBox').replaceWith("" +
                                    "<select id='provider' " +
                                    "name='provider' " +
                                    "class='provider selectpickerX dropdownValid form-control' " +
                                    "data-size='10' disabled>  " +
                                    "<option value=" + dataArray['user_id'] + ">" + dataArray['name'] +
                                    "</option>");
                            $('#providerDiv').css('padding-bottom','10px');
                            $("#save").append('<input type="hidden" value="'+dataArray['user_id']+'" id="provider" name="provider">');
                            $("#addProvider").modal('hide');

                        },completed: function (data) {

                        }
                    });



                    return false;
                });

            </script>
@stop
