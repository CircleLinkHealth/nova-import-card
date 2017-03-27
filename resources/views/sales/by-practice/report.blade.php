@extends('sales.partials.report-layout')

<?php

$enrollmentSection = \App\Reports\Sales\Practice\Sections\EnrollmentSummary::class;
$rangeSection = \App\Reports\Sales\Practice\Sections\RangeSummary::class;
$financialSection = \App\Reports\Sales\Practice\Sections\FinancialSummary::class;
$practiceSection = \App\Reports\Sales\Practice\Sections\PracticeDemographics::class;

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

        <div style="text-align: center">
            @if(!$data['isPDF'])<img src="/img/ui/logo.png"
                                    alt="Care Plan Manager"
                                    style="position:relative;"
                                    width="200px"/>
            @endif
            <h1 style="margin-bottom: 0px">{{$data['name']}}'s Weekly CCM Summary</h1>
            <b><span style="font-size: 16px">(Organization-Wide)</span><br/></b>

            <b><br><span>{{Carbon\Carbon::parse($data['start'])->format('l, jS F') . ' - ' . Carbon\Carbon::parse($data['end'])->format('l, jS F') }}</span></b>

        </div>
    </div>

    @if(array_key_exists($rangeSection, $data))

        <div style="font-size: 16px">

            <p>Hope you had a good weekend! Here's a summary of CCM activities for last week:</p>

            <p>
                Last week CircleLink nurses placed <b>{{$data[$rangeSection]['no_of_call_attempts']}}</b>
                calls, including <b>{{$data[$rangeSection]['no_of_successful_calls']}}</b> successful phone session,
                totaling
                <b>{{$data[$rangeSection]['total_ccm_time']}}</b>
                care hours. We also collected <b>{{$data[$rangeSection]['no_of_biometric_entries']}}</b>
                vital(s) and adherence reading(s), and our nurses forwarded
                <b>{{$data[$rangeSection]['no_of_forwarded_notes']}}</b>
                note(s) to you.
            </p>

            <p style="font-size: 16px">
                You can see a list of forwarded notes <a
                        href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
                including <b>{{$data[$rangeSection]['no_of_forwarded_emergency_notes']}}</b> notification(s) indicating
                a patient visited an ER/Hospital.
            </p>

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
