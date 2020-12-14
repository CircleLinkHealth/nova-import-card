@extends('partials.providerUI')

@section('title', 'View Appointment')
@section('activity', 'Patient View Appointment')

@section('content')

    @push('scripts')
        <script>
            $(function () {
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

        </style>
    @endpush

    <div class="row" style="margin:30px 0px;">
        <div class="col-lg-10 col-lg-offset-1">
            @include('core::partials.errors.errors')
        </div>
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    View Appointment
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <form id="save" method="get"
                          action="{{route('patient.note.index', array('patientId' => $patient->id))}}">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="form-block col-md-6">
                                <div class="row">
                                    <div class="new-observation-item">
                                        <div class="form-group">
                                            <div class="col-sm-12 provider-label" id="provider-label">
                                                <label for="provider">
                                                    Selected Provider
                                                </label>
                                            </div>
                                            <div class="col-sm-12" id="providerDiv">
                                                <div class="form-group providerBox" id="providerBox">
                                                    <select id="provider" name="provider"
                                                            class="provider selectpickerX dropdownValid form-control"
                                                            data-size="10" required disabled>
                                                        <option value=""
                                                                selected>{{$appointment['provider_name']}}</option>

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
                                                           value="{{ $appointment['date'] }}"
                                                           data-field="date" data-format="yyyy-MM-dd" disabled>
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
                                                           value="{{$appointment['time']}}"
                                                            disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="new-observation-item">
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <div class="radio-inline"><input
                                                                    @if($appointment['is_completed'] == true) checked
                                                                    @endif
                                                                    type="checkbox" value=""
                                                                    disabled/><label
                                                                    for="is_completed"><span> </span>Completed</label>
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
                                        <textarea class="form-control" id="comment" name="comment"
                                                  placeholder="No status available." readonly="readonly"
                                                  rows="2">{{$appointment['type']}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationSource">
                                                Appointment Notes:
                                            </label>
                                        </div>
                                        <div class="col-sm-12" style="margin-top: -11px;">
                                            <div class="form-group">
                                        <textarea class="form-control" id="comment" name="comment"
                                                  placeholder="No notes available." readonly="readonly"
                                                  rows="4">{{$appointment['comment']}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin:30px 0px;">
                                    <div class="col-lg-12">
                                        <div class="text-center" style="margin-right:20px; text-align: right">


                                            {!! Form::submit('Back', array('name' => 'return','class' => 'btn btn-primary save-btn')) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
@stop
