<div class="form-block col-md-8">
    <div class="row">
        <div class="new-note-item">
            <div class="form-group">
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="form-item form-item-spacing">

                            <div class="col-sm-12">
                                <span style="font-size: 1.1em;">
                                {!! $predicament ? $predicament : ''!!}

                                </span>
                            </div>

                            @if(!empty($logic))
                                <div class="col-sm-12"><br>
                                    <span style="font-size: 1.1em;"><b>Logic:</b> {{$logic}}
                                </span>
                                </div>
                                <div class="col-sm-12"><br>
                                    <span style="font-size: 1.1em;"><b>Schedule Match:</b> <span id="window_match_text">{{$window_match}}</span>
                                </span>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-block col-md-4">
    <div class="row">
        <div class="new-note-item">
            <div class="form-group">
                <div class="col-sm-12" style="padding-left: 0px">
                    <div class="form-group">
                        <div class="form-item form-item-spacing">
                            <div class="col-sm-12" style="margin-bottom: -17px; ">
                                <label for="activityKey">
                                    <b>{{Carbon\Carbon::now()->format('F')}} CCM Time</b>
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h3 style="{{$ccm_above ? 'color: #47beab;' : ''}}">
                                            <time-tracker ref="TimeTrackerApp" :info="timeTrackerInfo"
                                                          :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
                                                          :override-timeout="{{config('services.time-tracker.override-timeout')}}"></time-tracker>
                                            @if($ccm_above)
                                                <span class="glyphicon glyphicon-ok"></span>
                                            @endif
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-right: 35px">
        <div class="new-note-item">
            <div class="form-group">
                <div class="col-sm-12" style="margin-top: -25px; padding-left: 0px">
                    <div class="form-group">
                        <div class="form-item form-item-spacing">
                            <div class="col-sm-12" style="margin-bottom: 9px">
                                <label for="activityKey">
                                    <b>{{Carbon\Carbon::now()->format('F')}} Call Statistics:</b>
                                </label>
                            </div>
                            <div class="col-sm-12" style="width: 120%;">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <b> Successful Calls:</b> <span
                                                style="color: green"> {{$no_of_successful_calls}} </span>
                                    </li>
                                    {{--<li class="list-group-item">--}}
                                    {{--<b> Total Calls:</b> {{$no_of_calls}}--}}
                                    {{--</li>--}}
                                    {{--<li class="list-group-item">--}}
                                    {{--<b> Call Success: {{round($success_percent, 2)}}%</b>--}}
                                    {{--</li>--}}
                                    <li class="list-group-item">
                                        <b> Last Successful Call Date: {{ ($patient->last_successful_contact_time == '0000-00-00' || !$patient->last_successful_contact_time)
                                                                        ? 'N/A'
                                                                        : \Carbon\Carbon::parse($patient->last_successful_contact_time)->toFormattedDateString()}}</b>
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

@push('scripts')
    <script>

        $(document).ready(function () {

            $("#name option").filter(function () {
                return $(this).val() == $("#firstname").val();
            }).attr('selected', true);

            $("#name").on("change", function () {
                $("#firstname").val($(this).find("option:selected").attr("value"));
            });
        });

    </script>
@endpush