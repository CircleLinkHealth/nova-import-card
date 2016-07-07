@extends('partials.providerUI')

@section('title', 'Patient Call Scheduler')
@section('activity', 'Patient Call Scheduler')

@section('content')

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1" style="border-bottom:3px solid #50b2e2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Schedule Next Call
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.index', array('patient' => $patient->ID)), 'method' => 'GET')) !!}

                <div class="form-block col-md-6" style="padding-top: 13px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="observationDate">
                                        Predicted Next Contact Date (Patient Local Time)
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input name="performed_at" type="date"
                                               class="selectpickerX form-control"
                                               data-width="95px" data-size="10"
                                               value="{{\Carbon\Carbon::parse($date)->format('Y-m-d')}}"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-block col-md-6" style="padding-top: 13px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">

                                <div class="col-sm-12">
                                    <label for="activityKey">
                                        Predicted Time Window (Patient Local Time)
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <select id="activityKey" name="type"
                                                class="selectpickerX dropdownValid form-control"
                                                data-size="10" required>
                                            <option value="9:30am - 12n" {{$window == App\PatientInfo::CALL_WINDOW_0930_1200 ? 'selected' : ''}}> 9:30am - 12n</option>
                                            <option value="12n - 3pm" {{$window == App\PatientInfo::CALL_WINDOW_1200_1500 ? 'selected' : ''}}> 12n - 3pm</option>
                                            <option value="3pm - 6pm" {{$window == App\PatientInfo::CALL_WINDOW_1500_1800 ? 'selected' : ''}}> 3pm - 6pm</option>

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--<div class="form-block col-md-6">--}}
                    {{--<div class="row" style="margin-bottom: 0px">--}}
                        {{--<div class="new-note-item">--}}
                            {{--<div class="form-group">--}}
                                {{--<div class="col-sm-12">--}}
                                    {{--<div class="panel-group inline" id="accordion" style="margin-bottom: 0px">--}}
                                        {{--<label data-toggle="collapse" data-target="#collapseOne">--}}
                                            {{--<div class="radio"><input type="checkbox" name="status"--}}
                                                                      {{--id="status"--}}
                                                                      {{--value="status"/><label--}}
                                                        {{--for="status"><span> </span>Patient Status Override (currently Enrolled)</label>--}}
                                            {{--</div>--}}
                                        {{--</label>--}}

                                        {{--<div id="collapseOne" class="panel-collapse collapse in">--}}
                                            {{--<div><select id="status" name="status"--}}
                                                                              {{--class="selectpickerX dropdownValid form-control"--}}
                                                                              {{--data-size="10">--}}
                                                    {{--<option value=""> Enrolled</option>--}}
                                                    {{--<option value=""> Withdrawn</option>--}}
                                                    {{--<option value=""> Paused</option>--}}

                                                {{--</select>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

        <div class="form-block col-md-12">
            <div class="row">
                <div class="new-note-item">
                    <div class="form-group">
                        <div class="col-sm-12" style="padding-bottom: 10px;">
                            <div class="form-group">
                                <div class="form-item form-item-spacing text-center">
                                    <div class="col-sm-12">
                                        <input type="hidden" value="new_activity"/>
                                        <button id="update" name="submitAction" type="submit"
                                                value="new_activity"
                                                class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $('.collapse').collapse();

            $("input:checkbox").on('click', function () {
                var $box = $(this);
                if ($box.is(":checked")) {

                    var group = "input:checkbox[name='" + $box.attr("name") + "']";
                    $(group).prop("checked", false);
                    $box.prop("checked", true);
                } else {
                    $box.prop("checked", false);
                }
            });
        </script>

@stop