@extends('sales.partials.report-layout')

<?php

$enrollmentSection = \App\Reports\Sales\Provider\Sections\EnrollmentSummary::class;
$rangeSection = \App\Reports\Sales\Provider\Sections\RangeSummary::class;
$financialSection = \App\Reports\Sales\Provider\Sections\FinancialSummary::class;
$practiceSection = \App\Reports\Sales\Provider\Sections\PracticeDemographics::class;

?>

@section('content')

    <div class="page-header">
        <div style="text-align: center"><img src="/img/ui/logo.png"
                                             alt="Care Plan Manager"
                                             style="position:relative;"
                                             width="200px"/>
            <h1 style="margin-bottom: 0px">CircleLink Weekly CCM Summary</h1>
            <b><span style="font-size: 16px">(Your Patients)</span></b><br/>

            <b><br><span>{{Carbon\Carbon::parse($data['start'])->format('l, jS F') . ' - ' . Carbon\Carbon::parse($data['end'])->format('l, jS F') }}</span></b>

        </div>
    </div>

    @if(array_key_exists($rangeSection, $data))

        <div style="font-size: 16px">


        <p>Hope you had a good weekend! Here's a summary of CCM activities for last week:</p>

        <p>Last week CircleLink nurses placed
            <b>{{$data[$rangeSection]['no_of_call_attempts']}}</b>
            calls to your patients, including <b>{{$data[$rangeSection]['no_of_successful_calls']}}</b>
            successful phone sessions, totaling <b>{{$data[$rangeSection]['total_ccm_time']}}</b>
            care hours. We also collected <b>{{$data[$rangeSection]['no_of_biometric_entries']}}</b>
            vital and adherence reading(s), and our nurses forwarded
            <b>{{$data[$rangeSection]['no_of_forwarded_notes']}}</b>
            note(s) to you.</p>

        <p>You can see a list of forwarded notes for your patients <a
                    href="{{$data[$rangeSection]['link_to_notes_listing']}}">here</a>,
            including <b>{{$data[$rangeSection]['no_of_forwarded_emergency_notes']}}</b>
            notification(s) indicating a patient visited a ER/Hospital.</p>

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
