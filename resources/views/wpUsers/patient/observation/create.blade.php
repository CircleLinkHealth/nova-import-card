@extends('app')

@section('content')
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('patient.observation.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container">
        <section class="main-form">
            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2">
                    <div class="row">
                        <div class="main-form-title">
                            New Observation
                        </div>
                        @include('errors.errors')
                        <div class="main-form-block main-form-horizontal col-md-12">
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
                                                                <option value="SYM/CF_SYM_MNU_01/1/CF_SYM_20">Shortness of breath</option>
                                                                <option value="SYM/CF_SYM_MNU_01/2/CF_SYM_20">Coughing or wheezing</option>
                                                                <option value="SYM/CF_SYM_MNU_01/3/CF_SYM_20">Chest pain or chest tightness</option>
                                                                <option value="SYM/CF_SYM_MNU_01/4/CF_SYM_20">Fatigue</option>
                                                                <option value="SYM/CF_SYM_MNU_01/5/CF_SYM_20">Weakness or dizziness</option>
                                                                <option value="SYM/CF_SYM_MNU_01/6/CF_SYM_20">Swelling in legs/feet</option>
                                                                <option value="SYM/CF_SYM_MNU_01/7/CF_SYM_20">Feeling down,  helpless, or sleep changes</option>
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
                                                            <option value="OV_Reading" SELECTED>Office Visit (OV) reading</option>
                                                            <option value="Lab">Lab Test</option>
                                                            <option value="Patient_Reported">Patient Reported</option>
                                                            <option value="Device">Device</option>
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
                        </div>

                        <div class="row" style="margin:30px 0px;">
                            <div class="col-lg-12">
                                <div class="pull-right" style="margin-right:20px;">
                                    <input type="hidden" name="userId" id="userId" value="{{ $patient->ID }}">
                                    <input type="hidden" name="programId" id="programId" value="{{ $program->blog_id }}">
                                    <a href="{{ URL::route('patient.summary', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Add Observation', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
    </form>
@stop
