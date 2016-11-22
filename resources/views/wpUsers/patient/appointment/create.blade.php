@extends('partials.providerUI')

@section('title', 'Input Appointments')
@section('activity', 'Input Appointments')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/patient/observation-create.js') }}"></script>

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
                    <form id="save" method="post" action="{{URL::route('patient.appointment.store', array('patientId' => $patient->id))}}">
                        {{ csrf_field() }}

                        <div class="row">
                        <div class="form-block col-md-6">
                            <div class="row">
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationDate">
                                                Select Existing Provider <span style="color: #4fb2e2">(or, <a id="addNewProvider"
                                                                                                href="#">add new)</a></span>
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
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
                                            <label for="observationDate">
                                                Appointment Date:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="date" type="date" class="selectpickerX form-control"
                                                       value="{{ date('Y-m-d') }}"
                                                       data-field="date" data-format="yyyy-MM-dd" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationDate">
                                                Appointment Time:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="time" type="time" class="selectpickerX form-control"
                                                       value="{{date('H:i')}}"
                                                       data-field="time" data-format="H:i" required>
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
                                            Additional Details:
                                        </label>
                                    </div>
                                    <div class="col-sm-12" style="margin-top: -11px;">
                                        <div class="form-group">
                                        <textarea class="form-control" id="comment" name="comment"
                                                  placeholder="Please enter appointment details..." rows="8"></textarea>
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
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
            </div>
        </div>
@stop
