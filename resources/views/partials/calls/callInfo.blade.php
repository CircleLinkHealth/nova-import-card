<div class="form-block col-md-8">
    <div class="row" >
        <div class="new-note-item">
            <div class="form-group">
                <div class="col-sm-12">
                    <div class="form-group">
                        <div class="form-item form-item-spacing">
                            {{--<div class="col-sm-12" style="padding-bottom: 4px;">--}}
                                {{--<label for="activityKey">--}}
                                    {{--Patient's Next Available Call Windows:--}}
                                {{--</label>--}}
                            {{--</div>--}}
                            <div class="col-sm-12">
                                <span style="font-size: 1.1em;">
                                {!! $predicament ? $predicament : ''!!}
                                {{--<ul class="list-group">--}}
                                    {{--@foreach($next_contact_windows as $contact_window)--}}
                                        {{--<li class="list-group-item">--}}
                                            {{--On {{\Carbon\Carbon::parse($contact_window['string_start'])->toFormattedDateString()}} between {{\Carbon\Carbon::parse($contact_window['string_start'])->format('h:i A')}} and {{\Carbon\Carbon::parse($contact_window['string_end'])->format('h:i A')}}--}}
                                        {{--</li>--}}
                                    {{--@endforeach--}}
                                {{--</ul>--}}
                                </span>
                            </div>
                            @if(app()->environment() != 'production')
                                @if(!empty($logic))
                                <div class="col-sm-12"><br>
                                <span style="font-size: 1.1em;"><b>Logic:</b> {{$logic}}
                                </span>
                            </div>
                                @endif
                                    @if(!empty($logic))
                                        <div class="col-sm-12"><br>
                                            <span style="font-size: 1.1em;"><b>Schedule Match:</b> {{$window_match}}
                                </span>
                                        </div>
                                    @endif
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
                                        <h3 style="{{$ccm_above ? 'color: #47beab;' : ''}}">{{$formatted_monthly_time}}
                                            @if($ccm_above)
                                                <span class="glyphicon glyphicon-ok"></span>
                                            @endif
                                            @if($ccm_complex)
                                                <span id="complex_tag" style="background-color: #ec683e;font-size: 11px;" class="label label-warning"> Complex CCM</span>
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
    <div class="row" style="margin-right: 35px" >
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
                            <div class="col-sm-12" style="width: 135%;">
                                <ul class="list-group">
                                    <li class="list-group-item">
                                        <b> Successful Calls:</b> <span style="color: green"> {{$no_of_successful_calls}} </span>
                                    </li>
                                    {{--<li class="list-group-item">--}}
                                        {{--<b> Total Calls:</b> {{$no_of_calls}}--}}
                                    {{--</li>--}}
                                    {{--<li class="list-group-item">--}}
                                        {{--<b> Call Success: {{round($success_percent, 2)}}%</b>--}}
                                    {{--</li>--}}
                                    <li class="list-group-item">
                                        <b> Last Successful Call Date: {{$patient->last_successful_contact_time == '0000-00-00'
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

<script>

    $(document).ready(function() {

        $("#name option").filter(function() {
            return $(this).val() == $("#firstname").val();
        }).attr('selected', true);

        $("#name").live("change", function() {

            $("#firstname").val($(this).find("option:selected").attr("value"));
        });
    });

</script>