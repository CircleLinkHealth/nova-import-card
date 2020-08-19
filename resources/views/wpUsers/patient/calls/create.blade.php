@extends('partials.providerUI')

@section('title', 'Patient Call Scheduler')
@section('activity', 'Patient Call Scheduler')

@section('content')
    @push('scripts')
        <script>
            $(document).ready(function () {
                $("a").click(function (e) {
                    $("#confirmButtonModal").modal({backdrop: 'static', keyboard: false});
                    e.preventDefault();
                    return false;
                });
            });
        </script>
    @endpush

    <div class="col-lg-8 col-lg-offset-2">
        <div>
            @include('errors.messages')
        </div>
        <div>
            @include('errors.errors')
        </div>
    </div>

    <div id="confirmButtonModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Please Confirm Call</h4>
                </div>
                <div class="modal-body">
                    <p>Please confirm call to continue.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <div class="row" style="margin-top:60px;" xmlns="http://www.w3.org/1999/html">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1"
             style="border-bottom:3px solid #50b2e2">
            <div class="row" style="border-bottom:3px solid #50b2e2">
                <div class="main-form-title col-lg-12">
                    @if($successful)
                        Schedule Next Call
                    @else
                        Schedule Next Call Attempt
                    @endif
                </div>
                {!!
                Form::open(['url' => route('manual.call.store', ['patientId' => $patient->id]), 'method' => 'POST', 'id' => 'sched-call-form', 'class' => 'form-prevent-multi-submit'])
                !!}

                <div class="form-block col-md-4" style="padding-top: 0px">
                    <div class="row">
                        <div class="new-note-item">
                            <div class="form-group">
                                <div class="col-sm-12 col-xs-offset-1">
                                    <label for="date" style="font-weight: 500 !important;">
                                        Predicted Next Contact Date
                                    </label>
                                </div>
                                <div class="col-sm-12 col-xs-10 col-xs-offset-1">
                                    <div class="form-group">
                                        <v-datepicker name="date" class="selectpickerX form-control" format="yyyy-MM-dd"
                                                      id="date"
                                                      :required="true"
                                                      placeholder="YYYY-MM-DD"
                                                      @selected="function (e) { this.dateChanged(e) }"
                                                      value="{{ auth()->user()->resolveTimezoneToGMT(Carbon::parse($date)) }}">
                                        </v-datepicker>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="suggested_date" value="{{\Carbon\Carbon::parse($date)->format('Y-m-d')}}">

                <div class="form-block col-md-8" style="padding-top: 15px">
                    <div class="row form-inline">
                        <div class="new-note-item">
                            <div class="form-group">
                                <div class="col-sm-12 col-xs-offset-1">
                                    <label for="window_start" style="font-weight: 500 !important;">
                                        Next Call Window
                                    </label>
                                </div>
                                <div class="col-sm-12 col-xs-10 col-xs-offset-1">
                                    <div class="form-group">
                                        <input class="form-control" name="window_start" type="time"
                                               required
                                               value="{{$window_start}}"
                                               id="window_start" placeholder="time">

                                    </div>
                                    <div class="form-group">
                                        <label for="window_end">
                                            to
                                        </label>

                                        <input class="form-control" name="window_end" type="time"
                                               required
                                               value="{{$window_end}}"
                                               id="window_end" placeholder="time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="patientId" value="{{$patient->user_id}}"/>
            <input type="hidden" name="attempt_note" value="{{$attempt_note}}"/>


            @include('partials.calls.callInfo')

            <div class="form-block col-md-12">
                <div class="row">
                    <div class="new-note-item">
                        <div class="form-group">
                            <div class="col-sm-12" style="padding-bottom: 10px;">
                                <div class="form-group">
                                    <div class="form-item form-item-spacing text-center">
                                        <div class="col-sm-12">
                                            <input type="hidden" value="new_activity"/>
                                            <button id="update" name="submitAction" type="submit" form="sched-call-form"
                                                    value="new_activity"
                                                    class="btn btn-primary btn-lg form-item--button form-item-spacing btn-prevent-multi-submit">
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
        </div>
    </div>
    {!! Form::close() !!}

@endsection

<style>
    .vdp-datepicker * {
        border: none;
    }
</style>