@extends('partials.providerUI')

@section('title', 'Add Patient Activity')
@section('activity', 'Add Patient Activity')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime    = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $providerSelect = $(".provider").select2();
            });
        </script>
    @endpush

    <div class="row" style="margin-top: 5px">
        <div class="col-lg-10 col-lg-offset-1">
            @include('core::partials.errors.messages')
        </div>
    </div>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1"
             style="border-bottom: 3px solid #50b2e2;">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Record New Activity
                </div>
                {!! Form::open(array('url' => route('patient.activity.store', ['patientId' => $patient]), 'class' => 'form-horizontal')) !!}

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
                                           data-width="95px" data-size="10" list max="{{$userTime}}" value="{{$userTime}}"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="col-sm-12">
                                <label for="activityKey">
                                    Performed By
                                </label>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select id="performedBy" name="provider_id"
                                            class="selectpickerX provider dropdown Valid form-control" data-size="10"
                                            required style="width: 100%;">
                                        <option value=""> Select Provider</option>
                                        @foreach ($provider_info as $id => $name)
                                            <option value="{{$id}}"> {{($name && (trim($name) == '')) ? 'Me' : $name}} </option>
                                        @endforeach
                                    </select>
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
                                    <select id="activityValue" name="duration_minutes" class="selectpickerX dropdown Valid form-control" data-size="10" required>
                                        @for($i = 1; $i < 121 ; $i++)
                                            <option value="{{$i}}" name="duration_minutes"> {{$i}} </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-block col-md-6">
                        <div class="row">
                            @foreach($chargeableServices as $chargeableService)
                                <div class="{{ 'col-sm-'. (count($chargeableServices) === 3 ? '4' : (count($chargeableServices) === 2 ? '6' : '12' )) }}">
                                    <label>
                                        <input type="radio"
                                               required
                                               name="chargeable_service_id"
                                               style="display:inline"
                                               value="{{ $chargeableService->getChargeableServiceId()  }}"
                                                {{ $chargeableService->getChargeableServiceId() == old('chargeable_service_id') ? 'checked' : ''}}/>
                                        {{ $chargeableService->getChargeableServiceDisplayName()  }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-block col-md-12">
                        <div class="row">
                            <div class="new-activity-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <textarea id="activity" class="form-control" rows="10" cols="100" placeholder="Enter Comment..."
                                                  name="comment" required></textarea> <br/>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-lg-offset-2">
                                    <div class="row">
                                        <strong>Note: Clinical call time entered manually should not include time spent working in CarePlanManager on unique patient pages.</strong>
                                    </div>
                                </div>
                                <div class="">
                                    <input type="hidden" name="perfomred_at_gmt" value="{{ $userTimeGMT }}">
                                    <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                </div>
                                <div class="new-activity-item">
                                    <div class="form-group">
                                        <div class="center-block">
                                            <div class="form-item form-item-spacing text-center">
                                                <div>
                                                    <button id="update" name="submitAction" type="submit" value="new_activity"
                                                            class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                        Save Offline Activity
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
    </div>
@endsection
