@extends('partials.providerUI')

@section('title', 'Request Offline Activity Time')
@section('activity', 'Request Offline Activity Time')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime    = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    <div class="container">
        <div class="row" style="margin-top:60px;">
            <div class="col-md-12">
                @include('core::partials.errors.errors')
            </div>
        </div>
    </div>


    <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1"
         style="border-bottom: 3px solid #50b2e2;">
        <div class="row">
            <div class="main-form-title col-lg-12">
                Request Offline Activity Time
            </div>
            {!! Form::open(array('url' => route('offline-activity-time-requests.store', ['patientId' => $patient]), 'class' => 'form-horizontal')) !!}

            <div>
                @include('partials.userheader')
                <div class="row"></div>
            </div>

            <div class="main-form-block">
                <div class="form-block col-md-6">
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="activityKey">
                                Activity Topic
                            </label>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <select id="activityKey" name="type"
                                        class="selectpickerX dropdownValid form-control"
                                        data-size="10" required>
                                    <option value="">Select Topic</option>
                                    @foreach ($activity_types as $activity_type)
                                        <option value="{{$activity_type}}" {{old('type') == $activity_type ? 'selected' : ''}}> {{$activity_type}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-block col-md-6">
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="observationDate">
                                Observation Date and Time:
                            </label>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input name="performed_at" type="datetime-local" class="selectpickerX form-control"
                                       data-width="95px" data-size="10" list max="{{$userTime}}"
                                       value="{{old('performed_at') ? old('performed_at') : $userTime}}"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-block col-md-6">
                    <div class="row">
                        <div class="col-sm-12">
                            <label for="activityValue">
                                For How Long? (Minutes)
                            </label>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input name="duration_minutes" type="number" min="0" max="120"
                                       value="{{old('duration_minutes') ? old('duration_minutes') : 0}}"
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                @if ($patient->isCcm() && $patient->isBhi())
                    <div class="form-block col-md-6">
                        <div class="row">
                            <label class="col-md-12" for="is_behavioral">Activity Type</label>
                            <div class="col-sm-6">
                                <input type="radio" name="is_behavioral" style="display:inline" value="0"
                                        {{! old('is_behavioral') ? 'checked' : ''}}/> CCM Time

                            </div>
                            <div class="col-sm-6">
                                <input type="radio" name="is_behavioral" style="display:inline"
                                       value="1" {{old('is_behavioral') ? 'checked' : ''}}/>
                                BHI Time
                            </div>
                        </div>
                    </div>
                @endif


                <div class="form-block col-md-12">
                    <div class="row">
                        <div class="new-activity-item">
                            <div class="form-group">
                                <div class="col-sm-12">
                                        <textarea id="activity" class="form-control" rows="10" cols="100"
                                                  placeholder="Enter Comment..."
                                                  name="comment" required>{{old('comment')}}</textarea> <br/>
                                </div>
                            </div>

                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="center-block">
                                        <div class="form-item form-item-spacing text-center">
                                            <div>
                                                <input type="hidden" value="new_activity"/>
                                                <button id="update" name="submitAction" type="submit"
                                                        value="new_activity"
                                                        class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                    Request Offline Activity Time
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row"></div>
            </div>

        </div>
    </div>
@endsection