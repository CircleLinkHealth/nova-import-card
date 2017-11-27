@extends('partials.providerUI')

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')

<?php
    if (isset($patient)) {
        $seconds = optional($patient->patientInfo)->cur_month_activity_time ?? 0;
        $H = floor($seconds / 3600);
        $i = ($seconds / 60) % 60;
        $s = $seconds % 60;
        $monthlyTime = sprintf("%02d:%02d:%02d", $H, $i, $s);
    }
    else {
        $monthlyTime = "";
    }
?>

<style>
    .revert-btn {
        background-color: #c72e29;
        border-radius: 0 !important;
        color: white;
    }

    .top-20 {
        margin-top: 20px
    }

    div.tt-container {
        padding-right: 28px;
    }
</style>

@section('content')
    <div id="v-pdf-careplans" class="container">
        <div class="row">
            <div class="col-md-12 top-20 text-right tt-container">
                <span style="font-size: 22px;">
                    <span data-monthly-time="{{$monthlyTime}}" style="color: inherit">
                        @if (isset($disableTimeTracking) && $disableTimeTracking)
                            <div class="color-grey">
                                <a href="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', ['patient' => $patient->id]) }}">
                                    <server-time-display url="{{env('WS_SERVER_URL')}}" patient-id="{{$patient->id}}" provider-id="{{Auth::user()->id}}" value="{{$monthlyTime}}"></server-time-display>
                                </a>
                            </div>
                        @else
                            <?php
                                $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
                                $ccmCountableUser = auth()->user()->isCCMCountable();
                             ?>
                            <time-tracker ref="TimeTrackerApp" class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ($ccmCountableUser ? '' : 'color-grey')}}"
                                    :info="timeTrackerInfo" 
                                    :no-live-count="{{$noLiveCountTimeTracking ? true : ($ccmCountableUser ? 'false' : 'true')}}"></time-tracker>
                        @endif
                    </span>
                </span>
            </div>
        </div>

        <pdf-careplans></pdf-careplans>

        @if(auth()->user()->hasRole(['administrator', 'provider', 'office_admin', 'med_assistant', 'registered-nurse']))
            <div class="row" style="padding-top: 20%;">
                <div class="col-md-12 text-center">
                    <a href="{{route('switch.to.web.careplan', ['carePlanId' => $patient->carePlan->id])}}"
                       class="btn revert-btn inline-block">REVERT TO EDITABLE CAREPLAN FROM CCD/PATIENT DATA</a>
                </div>
            </div>
        @endif
    </div>
@endsection