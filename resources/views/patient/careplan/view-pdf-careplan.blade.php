@extends('core::partials.providerUI')

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')

<?php
if (isset($patient)) {
    $seconds     = $patient->getCcmTime();
    $H           = floor($seconds / 3600);
    $i           = ($seconds / 60) % 60;
    $s           = $seconds % 60;
    $monthlyTime = sprintf('%02d:%02d:%02d', $H, $i, $s);
} else {
    $monthlyTime = '';
}
$canSwitchToWeb = $patient->carePlan && CircleLinkHealth\SharedModels\Entities\CarePlan::PDF == $patient->carePlan->mode && auth()->user()->hasRole(['administrator', 'provider', 'office_admin', 'med_assistant', 'registered-nurse']);
?>

<style>
    a.revert-btn {
        background-color: #c72e29;
        border-radius: 3px;
        color: white;
    }

    a.revert-btn:hover, a.revert-btn:focus {
        color: white;
        background-color: #b61d18;
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
                    @include('partials.providerUItimerComponent')
                </span>
            </div>
        </div>

        <careplan-actions mode="pdf"
                          route-switch-to-web="{{route('switch.to.web.careplan', ['carePlanId' => $patient->carePlan ? $patient->carePlan->id : 0])}}"
                          :can-switch-to-web="@json($canSwitchToWeb)">
        </careplan-actions>
    </div>
@endsection
