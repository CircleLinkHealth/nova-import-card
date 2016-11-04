@extends('partials.providerUI')

@section('title', 'Add Patient Activity')
@section('activity', 'Add Patient Activity')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Record New Activity
                </div>
                {!! Form::open(array('url' => URL::route('patient.activity.store', ['patientId' => $patient]), 'class' => 'form-horizontal')) !!}

                @include('partials.userheader')

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Activity Topic
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="activityKey" name="type" class="selectpickerX dropdownValid form-control"
                                                    data-size="10" required>
                                                <option value=""> Select Topic</option>
                                                @foreach ($activity_types as $activity_type)
                                                    <option value="{{$activity_type}}"> {{$activity_type}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="observationDate">
                                            Observation Date and Time:
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input name="performed_at" type="datetime-local" class="selectpickerX form-control"
                                                   data-width="95px" data-size="10" list max="{{$userTime}}" value="{{$userTime}}"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Performed By
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="performedBy" name="provider_id"
                                                    class="selectpickerX dropdown Valid form-control" data-size="10" required>
                                                <option value=""> Select Provider</option>
                                                @foreach ($provider_info as $id => $name)
                                                    <option value="{{$id}}"> {{$name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityValue">
                                            For How Long?
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="activityValue" name="duration" class="selectpickerX dropdown Valid form-control" data-size="10" required>
                                                @for($i = 1; $i < 121 ; $i++)
                                                    <option value="{{$i}}" name="duration"> {{$i}} </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-block col-md-12">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="hidden" name="meta[1][meta_key]" value="comment">
                                        <textarea id="activity" class="form-control" rows="10" cols="100" placeholder="Enter Comment..."
                                                  name="meta[1][meta_value]" required></textarea> <br/>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-lg-offset-2">
                                    <div class="row">
                                        <strong>Note: Clinical call time entered manually should not include time spent working in CarePlanManager on unique patient pages.</strong>
                                    </div>
                                </div>
                                <div class="">
                                    <input type="hidden" name="duration_unit" value="seconds">
                                    <input type="hidden" name="perfomred_at_gmt" value="{{ $userTimeGMT }}">
                                    <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                    <input type="hidden" name="logged_from" value="manual_input">
                                    <input type="hidden" name="logger_id" value="{{Auth::user()->id}}">
                                    <input type="hidden" name="patientID" id="patientID" value="{{$patient->id}}">
                                    <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                </div>
                                <div class="new-activity-item">
                                    <div class="form-group">
                                        <div class="center-block">
                                            <div class="form-item form-item-spacing text-center">
                                                <div>
                                                    <input type="hidden" value="new_activity"/>
                                                    <button id="update" name="submitAction" type="submit" value="new_activity"
                                                            class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                        Save Offline Activity
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <script>

                                    </script>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection