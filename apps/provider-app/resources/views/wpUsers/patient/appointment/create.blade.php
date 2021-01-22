<?php

?>
@extends('partials.providerUI')

@section('title', 'Input Appointments')
@section('activity', 'Patient Input Appointment')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                $(".provider").select2();
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .save-btn {
                width: 100px;
                height: 42px;
                position: relative;
            }

            .select2-container {
                width: 100% !important;;
            }

            .margin-20 {
                margin-top: 10px;
                margin-bottom: 10px;
            }

            .vdp-datepicker input {
                border: unset;
            }
        </style>
    @endpush

    <div class="row" style="margin:30px 0px;">
        <div class="col-lg-10 col-lg-offset-1">
            @include('core::partials.errors.errors')
        </div>
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    New Appointment
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <form id="save" name="save" method="post" class="form-prevent-multi-submit"
                          action="{{route('patient.appointment.store', array('patientId' => $patient->id))}}">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="form-block col-md-6">
                                <div class="row">
                                    <div class="col-sm-12 form-group margin-20">
                                        <div class="form-group">
                                            <div class="provider-label" id="provider-label">
                                                <div id="v-create-appointments-add-care-person">
                                                    <create-appointments-add-care-person></create-appointments-add-care-person>
                                                </div>
                                            </div>
                                            <div class="" id="providerDiv">
                                                <div class="form-group providerBox" id="providerBox">
                                                    <select id="provider" name="provider"
                                                            class="provider selectpickerX dropdownValid form-control"
                                                            data-size="10">
                                                        <option value="null">Unknown</option>
                                                        @foreach ($providers as $key => $value)
                                                            <option value="{{$key}}"> {{$value}} </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        <label for="date">
                                            Appointment Date:
                                        </label>
                                        <v-datepicker name="date" class="selectpickerX form-control" format="MM-dd-yyyy"
                                                      id="appointment-date"
                                                      placeholder="MM-DD-YYYY" pattern="\d{2}\-\d{2}-\d{4}\"
                                                      required></v-datepicker>
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        <label for="time">
                                            Appointment Time:
                                        </label>
                                        <input name="time" id="time" type="time"
                                               class="selectpickerX form-control"
                                               value="12:00"
                                               data-field="time" data-format="H:i" required>
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        <div class="radio-inline">
                                            <input type="checkbox" name="is_completed" id="is_completed"/>
                                            <label for="is_completed">
                                                <span> </span>Attended
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-block col-md-6">
                                <div class="row">
                                    <div class="col-sm-12 form-group margin-20">

                                        <label for="observationSource">
                                            Appointment Type:
                                        </label>
                                        <input class="form-control" id="appointment_type" name="appointment_type"
                                               placeholder="Please specify appointment type..." maxlength="50"
                                               required/>
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        <label for="observationSource">
                                            Additional Details:
                                        </label>
                                        <textarea class="form-control" id="comment" name="comment"
                                                  placeholder="Please enter appointment details..." rows="4"></textarea>
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        <input type="hidden" name="patientId" id="patientId"
                                               value="{{ $patient->id }}">
                                    </div>
                                    <div class="col-sm-12 form-group margin-20">
                                        {!! Form::submit('Save', array('name' => 'save','class' => 'btn btn-primary save-btn btn-prevent-multi-submit')) !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12" style="padding: 100px"></div>
    </div>

    @push("scripts")
        <script>
            const waitForEl = function (selector, callback) {
                if (!$(selector).length) {
                    setTimeout(function () {
                        window.requestAnimationFrame(function () {
                            waitForEl(selector, callback)
                        });
                    }, 100);
                } else {
                    callback();
                }
            };

            $(function() {
                waitForEl('#appointment-date', () => {
                    $('#appointment-date').attr('autocomplete', 'off')
                });
            });
        </script>
    @endpush
@endsection
