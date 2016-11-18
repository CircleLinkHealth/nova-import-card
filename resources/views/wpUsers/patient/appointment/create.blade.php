@extends('partials.providerUI')

@section('title', 'Input Observations')
@section('activity', 'Input Observations')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/patient/observation-create.js') }}"></script>
    <div id="dtBox"></div>
    <div class="row" style="margin:60px 0px;">
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


                    {!! Form::open(array('url' => URL::route('patient.appointments.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal')) !!}
                    <div class="row">
                        <div class="form-block col-md-6">
                            <div class="row">
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationType">
                                                Observation Type:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <select id="observationType" name="observationType" class="selectpickerX dropdownValid form-control" data-size="10" required>
                                                    <option value=""> Select a Provider </option>
                                                </select>
                                                or, <a href="#">add new</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationSource">
                                                Source of Observation:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <textarea id="comment" name="comment" placeholder="Please enter appointment details..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-block col-md-6">
                            <div class="row">
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationDate">
                                                Appointment Date:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="date" type="text" class="selectpickerX form-control" value="{{ (old('date') ? old('date') : date('Y-m-d')) }}" data-field="date" data-format="yyyy-MM-dd" required>
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
                                                <input name="time" type="text" class="selectpickerX form-control" value="{{ (old('time') ? old('time') : date('H:i')) }}" data-field="time" data-format="H:i" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="new-observation-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <label for="observationValue">
                                                Value:
                                            </label>
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <input type="text" class="form-control" name="observationValue" id="observationValue" placeholder="Enter Data" value="{{ (old('observationValue') ? old('observationValue') : '') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin:30px 0px;">
                        <div class="col-lg-12">
                            <div class="text-center" style="margin-right:20px;">
                                <input type="hidden" name="patientId" id="patientId" value="{{ $patient->id }}">
                                <input type="hidden" name="userId" id="userId" value="{{ $patient->id }}">
                                <input type="hidden" name="programId" id="programId" value="{{ $patient->program_id }}">
                            <!-- <a href="{{ URL::route('patient.summary', array('patientId' => $patient->id)) }}" class="btn btn-danger">Cancel</a> -->
                                {!! Form::submit('Save', array('class' => 'btn btn-primary')) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
