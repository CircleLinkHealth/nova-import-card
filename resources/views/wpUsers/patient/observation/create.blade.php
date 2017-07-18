@extends('partials.providerUI')

@section('title', 'Input Observations')
@section('activity', 'Input Observations')

@section('content')


    <script>
        $(document).ready(function () {
            $(".observation").select2();

        });
    </script>

    <script type="text/javascript" src="{{ asset('/js/patient/observation-create.vue') }}"></script>
    <div id="dtBox"></div>
    <div class="row" style="margin:60px 0px;">
        <div class="col-lg-10 col-lg-offset-1">
            @include('errors.errors')
        </div>
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    New Observation
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">


                    {!! Form::open(array('url' => URL::route('patient.observation.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal')) !!}
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
                                                <select id="observationType" name="observationType" class="observation selectpickerX dropdownValid form-control" data-size="10" required>
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
                                                        <option value="SOL/CF_LFS_40">Following Healthy Diet</option>
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
                                                <select id="observationSource" name="observationSource" class="selectpickerX dropdownValid form-control" data-size="10"  required>
                                                    <option value=""> Select Source </option>
                                                    <option value="ov_reading" >Office Visit (OV) reading</option>
                                                    <option value="lab">Lab Test</option>
                                                    <option value="manual_input" selected>Patient Reported</option>
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
                                                <input name="observationDate" type="text" class="selectpickerX form-control" value="{{ (old('observationDate') ? old('observationDate') : date('Y-m-d H:i')) }}" data-field="datetime" data-format="yyyy-MM-dd HH:mm" required>
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
