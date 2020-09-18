@extends('sales.partials.report-layout')

<?php

$enrollmentSection = \CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections\EnrollmentSummary::class;
$rangeSection      = \CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections\RangeSummary::class;
$financialSection  = \CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections\FinancialSummary::class;
$practiceSection   = \CircleLinkHealth\CpmAdmin\Services\Reports\Sales\Practice\Sections\PracticeDemographics::class;

?>

<style type="text/css">
    .myTable {
        background-color: #fff;
        border-collapse: collapse;
    }

    .myTable th {
        background-color: #fff;
        color: black;
        width: 50%;
    }

    .myTable td, .myTable th {
        padding: 5px;
        border: 1px solid black;
    }
</style>


@section('content')
    <div class="page-header">
        @if($data['isEmail'])
            <div style="text-align: center">
                <img src="{{mix('img/logos/LogoHorizontal_Color.svg')}}"
                     alt="Care Plan Manager"
                     style="position:relative;"
                     width="200px"/>
                <h1 style="margin-bottom: 0px">{{$data['name']}}'s Weekly CCM Summary</h1>
                <b><span style="font-size: 16px">(Organization-Wide)</span><br/></b>

            </div>
        @else
            <h1>{{$data['name']}}
                <small>CircleLink Health  CCM Summary <b></b>
                    <span>({{$data['start']->format('jS F') . ' - ' . $data['end']->format('jS F') }})</span></small>
            </h1>
        @endif
    </div>

    @if(array_key_exists($rangeSection, $data))

        <div style="font-size: 16px">

            @if(!$data['isEmail'])
                <p>Here's a summary of CCM activities for the period
                    specified: {{$data['start']->format('l, jS F') . ' - ' . $data['end']->format('l, jS F') }}</p>
            @else
                <p>Hope you had a good weekend! Here's a summary of CCM activities for last week:</p>
            @endif

            <p>
                @if(!$data['isEmail']) During the period, @else Last week @endif CircleLink nurses placed
                <b>{{$data[$rangeSection]['no_of_call_attempts']}}</b>
                calls, including <b>{{$data[$rangeSection]['no_of_successful_calls']}}</b> successful phone session,
                totaling
                <b>{{$data[$rangeSection]['total_ccm_time']}}</b>
                care hours. We also collected <b>{{$data[$rangeSection]['no_of_biometric_entries']}}</b>
                vital(s) and adherence reading(s), and our nurses forwarded
                <b>{{$data[$rangeSection]['no_of_forwarded_notes']}}</b>
                note(s) to you.
            </p>

            @if($data['isEmail'] && should_show_notes_report($data['practice_id']))
            <p style="font-size: 16px">
                You can see a list of forwarded notes <a href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
                including <b>{{$data[$rangeSection]['no_of_forwarded_emergency_notes']}}</b> notification(s) indicating
                a patient visited an ER/Hospital.
            </p>
            @endif


        </div>
    @endif

    @if(array_key_exists($enrollmentSection, $data))

        @include('sales.partials.enrollement-section', ['data' => $data])

    @endif

    @if(array_key_exists($financialSection, $data))

        @include('sales.partials.financial-section', ['data' => $data])


    @endif

    @if(array_key_exists($practiceSection, $data))

        @include('sales.partials.practice-section', ['data' => $data])

    @endif

@stop
