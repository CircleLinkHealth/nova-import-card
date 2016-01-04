@extends('partials.providerUI')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = $userTime;
    $userTime->setTimezone('GMT');
    $userTime = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    View Note
                </div>
                @include('partials.userheader')

                <div class="main-form-block main-form-horizontal col-md-12">
        <div class="row">
            <div class="form-block col-md-6">
                <div class="row">
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="activityKey">
                                    Activity Performed:
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select id="activityKey" name="type" class="selectpicker" data-size="10" required disabled>
                                        <option value="">Select Activity</option>
                                        <optgroup label="">
                                            <option value="" selected>{{$activity['type']}}</option>                                                         </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label for="performedBy">
                                    Performed By:
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select id="performedBy" name="provider_id" class="selectpicker" data-size="10" disabled>
                                        <option value="391" >{{$activity['provider_name']}}</option>
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
                                <label for="activityDate">
                                    When (Patient Local Time):
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input readonly name="performed_at" type="datetime-local" class="selectpicker" data-width="95px" data-size="10" list max="{{$userTime}}"
                                           value="{{$userTime}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="new-activity-item">
                        <div class="form-group">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityValue">
                                            For how long?
                                        </label>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <select name="duration" id="activityValue" class="selectpicker" data-size="10" disabled>
                                            <option value="">{{$activity['duration']}}</option>
                                        </select>
                                          </div>
                        </div>
                    </div>
                </div>
            </div>
                    </div>
                </div>
            <div class="new-activity-item">
                <div class="form-group">
                    <div class="col-sm-12">
                        <input type="hidden" name="meta[0][meta_key]" value="comment">
                        <textarea readonly class="form-control" placeholder="Enter Comment..." name="meta[0][meta_value]">er</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2"><BR><strong>Note: Clinical Call time entered manually should not include time spent in the <i>CarePlanManager</i> portal while viewing or inputting/changing patient information and care plans.</strong></div>
            </div>
            <div class="form-item form-item-spacing text-center">
                <input type="hidden" value="update_activity" />
                <a href="{{URL::route('patient.activity.providerUIIndex', ['patientId' => $patient])}}" class="btn btn-primary btn-lg form-item--button form-item-spacing" role="button">Return to Notes/Offline Activities</a>

            </div>
            <div class="form-item form-item-spacing text-center">
            </div>

        </div>
    </div>
    </div>
    </div>
@stop