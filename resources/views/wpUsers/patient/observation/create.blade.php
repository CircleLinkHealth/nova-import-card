@extends('partials.providerUI')

@section('title', 'Input Observations')
@section('activity', 'Input Observations')

@section('content')


    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    New Observation
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">



                    {!! Form::open(array('url' => URL::route('patient.observation.store', array('patientId' => $patient->ID)), 'class' => 'form-horizontal')) !!}
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
                                                <select id="observationType" name="observationType" class="selectpicker dropdownValid form-control" data-size="10" required>
                                                    <option value=""> Select an Observation </option>
                                                    <optgroup label="Biometrics">
                                                        <option value="RPT/CF_RPT_20">Blood Pressure</option>
                                                        <option value="RPT/CF_RPT_30">Blood Sugar</option>
                                                        <option value="RPT/CF_RPT_60">A1c</option>
                                                        <option value="RPT/CF_RPT_40">Weight</option>
                                                        <option value="RPT/CF_RPT_50">Cigarette Count</option>
                                                    </optgroup>

                                                    <optgroup label="Medications Taken? Y or N">
                                                        <option value="SOL/CF_SOL_MED_BP">Blood Pressure meds</option>
                                                        <option value="SOL/CF_SOL_MED_CHL">Cholesterol meds</option>
                                                        <option value="SOL/CF_SOL_MED_BT">Blood Thinners (e.g., Plavix, Aspirin)</option>
                                                        <option value="SOL/CF_SOL_MED_WPD">Water pills/diuretics</option>
                                                        <option value="SOL/CF_SOL_MED_OHM">Other meds</option>
                                                        <option value="SOL/CF_SOL_MED_OD">Oral diabetes meds</option>
                                                        <option value="SOL/CF_SOL_MED_IID">Insulin or injectable diabetes meds</option>
                                                        <option value="SOL/CF_SOL_MED_BRE">Breathing meds</option>
                                                        <option value="SOL/CF_SOL_MED_DEP">Mood/Depression meds</option>
                                                    </optgroup>

                                                    <optgroup label="Symptoms? (1 - 9)">
                                                        <option value="SYM/CF_SYM_51">Shortness of breath</option>
                                                        <option value="SYM/CF_SYM_52">Coughing or wheezing</option>
                                                        <option value="SYM/CF_SYM_53">Chest pain or chest tightness</option>
                                                        <option value="SYM/CF_SYM_54">Fatigue</option>
                                                        <option value="SYM/CF_SYM_55">Weakness or dizziness</option>
                                                        <option value="SYM/CF_SYM_56">Swelling in legs/feet</option>
                                                        <option value="SYM/CF_SYM_57">Feeling down,  helpless, or sleep changes</option>
                                                    </optgroup>

                                                    <optgroup label="Lifestyle? Y or N">
                                                        <option value="SOL/CF_SOL_LFS_10">Exercise 20 minutes</option>
                                                        <option value="SOL/CF_LFS_40">Eating Nutrient-rich Food</option>
                                                        <option value="SOL/CF_LFS_80">Low salt diet</option>
                                                        <option value="SOL/CF_SOL_LFS_90">Diabetes diet</option>
                                                    </optgroup>


                                                </select>
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
                                                <select id="observationSource" name="observationSource" class="selectpicker dropdownValid form-control" data-size="10"  required>
                                                    <option value=""> Select Source </option>
                                                    <option value="ov_reading" SELECTED>Office Visit (OV) reading</option>
                                                    <option value="lab">Lab Test</option>
                                                    <option value="manual_input">Patient Reported</option>
                                                    <option value="device">Device</option>
                                                </select>
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
                                                Observation Date and Time:
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <input name="observationDate" type="datetime-local" class="selectpicker form-control" data-width="95px" data-size="10" list max="<?php echo date('Y-m-d\TH:i') ?>" value="{{ (old('observationDate') ? old('observationDate') : date('Y-m-d\TH:i')) }}" required>
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
                            <div class="pull-right" style="margin-right:20px;">
                                <input type="hidden" name="patientId" id="patientId" value="{{ $patient->ID }}">
                                <input type="hidden" name="userId" id="userId" value="{{ $patient->ID }}">
                                <input type="hidden" name="programId" id="programId" value="{{ $patient->program_id }}">
                                <a href="{{ URL::route('patient.summary', array('patientId' => $patient->ID)) }}" class="btn btn-danger">Cancel</a>
                                {!! Form::submit('Add Observation', array('class' => 'btn btn-success')) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
