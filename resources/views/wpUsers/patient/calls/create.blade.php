@extends('partials.providerUI')

@section('title', 'Patient Call Scheduler')
@section('activity', 'Patient Call Scheduler')

@section('content')

    <script>
        $(document).ready(function(){
            /* $( ".submitFormBtn").click(function(e) { */
            $( "a").click(function(e) {
                alert('Please confirm call to leave this page');
                e.preventDefault();
                return false;
            });
        });
    </script>
    <div class="row" style="margin-top:60px;" xmlns="http://www.w3.org/1999/html">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1"
             style="border-bottom:3px solid #50b2e2">
            <div class="row" style="border-bottom:3px solid #50b2e2">
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

                <input type="hidden" name="suggested_date" value="{{\Carbon\Carbon::parse($date)->format('Y-m-d')}}">

                <div class="form-block col-md-8" style="padding-top: 28px">
                        <div class="row form-inline">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Next Call Window
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input class="form-control" name="window_start" type="time"
                                                   value="{{$window_start}}"
                                                   id="window_start" placeholder="time">

                                        </div>
                                        <div class="form-group">
                                        <label for="window_end">
                                            to
                                        </label>
                                        <input class="form-control" name="window_end" type="time"
                                               value="{{$window_end}}"
                                               id="window_end" placeholder="time">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <input type="hidden" name="patient_id" value="{{$patient->ID}}"/>


            {{--@if($next_contact_windows)--}}
                @include('partials.calls.callInfo')
            {{--@endif--}}

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