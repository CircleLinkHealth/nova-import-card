@extends('partials.providerUI')

@section('title', 'Patient Call Scheduler')
@section('activity', 'Patient Call Scheduler')

@section('content')

    <?php

            $no_of_successful_calls = \App\Call::numberOfSuccessfulCallsForPatientForMonth($patient,Carbon\Carbon::now()->toDateTimeString());
            $no_of_calls = \App\Call::numberOfCallsForPatientForMonth($patient,Carbon\Carbon::now()->toDateTimeString());

            $success_percent = ( ($no_of_successful_calls) / ($no_of_calls) ) * 100;

    // calculate display, fix bug where gmdate('i:s') doesnt work for > 24hrs

    $seconds = $patient->patientInfo()->first()->cur_month_activity_time;

    $ccm_time_achieved = false;
            if($seconds >= 1200){
                $ccm_time_achieved = true;
            }

    $H = floor($seconds / 3600);
    $i = ($seconds / 60) % 60;
    $s = $seconds % 60;
    $monthlyTime = sprintf("%02d:%02d:%02d",$H, $i, $s);

    ?>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1" style="border-bottom:3px solid #50b2e2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    @if($successful)
                        Schedule Next Call
                    @else
                        Schedule Next Call Attempt
                    @endif
                </div>
                {!! Form::open(array('url' => URL::route('call.schedule', array('patient' => $patient->ID)), 'method' => 'POST')) !!}

                <div class="form-block col-md-4" style="padding-top: 13px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label for="observationDate">
                                        Predicted Next Contact Date
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input name="date" type="date"
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

                <div class="form-block col-md-4" style="padding-top: 13px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">

                                <div class="col-sm-12">
                                    <label for="activityKey">
                                        Next Call Window Begin:
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                            <input class="form-control" name="window_start" type="time"
                                                   value="{{$window_start}}"
                                                   id="window_start" placeholder="time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-block col-md-4" style="padding-top: 13px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">

                                <div class="col-sm-12">
                                    <label for="activityKey">
                                        Next Call Window End:
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input class="form-control" name="window_end" type="time"
                                               value="{{$window_end}}"
                                               id="window_start" placeholder="time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="patient_id" value="{{$patient->ID}}"/>


                @if($next_contact_windows)
                <div class="form-block col-md-8">
                    <div class="row" style="border-top: solid 2px #50b2e2;">
                        <div class="new-note-item">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="form-item form-item-spacing">
                                            <div class="col-sm-12" style="padding-bottom: 4px;">
                                                <label for="activityKey">
                                                    Patient's Next Available Call Windows:
                                                </label>
                                            </div>
                                            <div class="col-sm-12">
                                                <ul class="list-group">
                                                @foreach($next_contact_windows as $contact_window)
                                                    <li class="list-group-item">
                                                        On {{\Carbon\Carbon::parse($contact_window['string_start'])->toFormattedDateString()}} between {{\Carbon\Carbon::parse($contact_window['string_start'])->format('h:i A')}} and {{\Carbon\Carbon::parse($contact_window['string_end'])->format('h:i A')}}
                                                    </li>
                                                @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="form-block col-md-4">
                        <div class="row" style="border-top: solid 2px #50b2e2;">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <div class="form-item form-item-spacing">
                                                <div class="col-sm-12" style="margin-bottom: -17px;">
                                                    <label for="activityKey">
                                                        <b>{{Carbon\Carbon::now()->format('F')}} CCM Time</b>
                                                    </label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="row">
                                                        @if($ccm_time_achieved)
                                                            <h3>{{$monthlyTime}}<span class="glyphicon glyphicon-ok"></span></h3>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-4">
                        <div class="row" >
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12" style="margin-top: -25px;">
                                        <div class="form-group">
                                            <div class="form-item form-item-spacing">
                                                <div class="col-sm-12" style="margin-bottom: 9px">
                                                    <label for="activityKey">
                                                        <b>{{Carbon\Carbon::now()->format('F')}} Call Statistics:</b>
                                                    </label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <ul class="list-group">
                                                            <li class="list-group-item">
                                                               <b> Successful Calls:</b> <span style="color: green"> {{$no_of_successful_calls}} </span>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <b> Total Calls:</b> {{$no_of_calls}}
                                                            </li>
                                                        <li class="list-group-item">
                                                            <b> Call Success: {{round($success_percent, 2)}}%</b>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif

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
    <div>
@stop