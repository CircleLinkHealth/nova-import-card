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
                                    <div class="col-sm-12">
                                        <h3 style="{{$ccm_time_achieved ? 'color: #47beab;' : ''}}">{{$monthlyTime}}
                                            @if($ccm_time_achieved)
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
